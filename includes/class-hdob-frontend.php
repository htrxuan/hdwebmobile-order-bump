<?php

namespace htrxuan\hdob;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Renders the offer on the cart page (classic and block-theme carts), adds it to the cart
 * through a plain link (no form, no price field to tamper with), and re-prices it from
 * HDOB_Repository::get_discounted_price() on every totals calculation.
 */
final class HDOB_Frontend
{

    private static $instance = null;
    private static $rendered = false;

    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        add_action('template_redirect', array($this, 'maybe_add_bump'));

        // Classic cart shortcode template.
        add_action('woocommerce_before_cart_totals', array($this, 'render_offer'));

        // Block-theme Cart block: append right after the line items.
        add_filter('render_block', array($this, 'block_fallback'), 10, 2);

        // Re-price the bump item from current settings every time totals are calculated --
        // never from whatever it happened to cost when it was added.
        add_action('woocommerce_before_calculate_totals', array($this, 'reprice_bump_item'), 20);
    }

    /**
     * Adding the offer is a plain GET link, not a form -- there is no field of any kind for a
     * shopper to submit a price through. The price itself is looked up fresh, by this same
     * request, from HDOB_Repository -- never taken from $_GET at all.
     */
    public function maybe_add_bump()
    {
        if (is_admin() || !isset($_GET['hdob_add_bump'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- adds a fixed, admin-configured product to the visitor's own cart at a server-computed price; there is no state to forge here beyond "add this one specific item," which a nonce would not meaningfully protect against (the same request could always just be replayed by re-clicking the link).
            return;
        }

        if (!HDOB_Repository::is_offer_available()) {
            wp_safe_redirect(wc_get_cart_url());
            exit;
        }

        $settings = HDOB_Repository::get_settings();

        if (!$this->find_bump_cart_item_key()) {
            WC()->cart->add_to_cart($settings['product_id'], 1, 0, array(), array(HDOB_CART_ITEM_KEY => true));
        }

        wp_safe_redirect(wc_get_cart_url());
        exit;
    }

    private function find_bump_cart_item_key()
    {
        if (!WC()->cart) {
            return false;
        }
        foreach (WC()->cart->get_cart() as $key => $item) {
            if (!empty($item[HDOB_CART_ITEM_KEY])) {
                return $key;
            }
        }
        return false;
    }

    /**
     * Unlike a percentage-off-the-current-price discount, this always derives the bump price
     * from the product's own REGULAR price (see HDOB_Repository::get_discounted_price()), so
     * calling this more than once per real totals calculation is harmless -- there is no
     * "current price" being compounded, only the same fixed answer recomputed.
     */
    public function reprice_bump_item($cart)
    {
        $settings = HDOB_Repository::get_settings();
        foreach ($cart->get_cart() as $item) {
            if (empty($item[HDOB_CART_ITEM_KEY])) {
                continue;
            }
            // Only apply the discount if this cart item is still the CURRENTLY configured
            // bump product; otherwise always fall back to the product's own real regular
            // price. Falling back explicitly (rather than just skipping) matters: `set_price()`
            // mutates this cart session's product object, so a bump item that was discounted
            // on an earlier totals pass and then stopped matching (the admin repointed the
            // offer at a different product) must be reset here, or it would keep showing that
            // earlier discount indefinitely instead of reverting to a normal-priced line.
            if ((int) $item['product_id'] === (int) $settings['product_id']) {
                $price = HDOB_Repository::get_discounted_price();
                if (null !== $price) {
                    $item['data']->set_price($price);
                    continue;
                }
            }
            $item['data']->set_price($item['data']->get_regular_price());
        }
    }

    public function render_offer()
    {
        if (self::$rendered || !HDOB_Repository::is_offer_available() || $this->find_bump_cart_item_key()) {
            return;
        }
        self::$rendered = true;
        $this->output_box();
    }

    public function block_fallback($block_content, $block)
    {
        $name = is_array($block) && isset($block['blockName']) ? $block['blockName'] : '';
        if ('woocommerce/cart-line-items-block' !== $name) {
            return $block_content;
        }
        if (self::$rendered || !HDOB_Repository::is_offer_available() || $this->find_bump_cart_item_key()) {
            return $block_content;
        }
        self::$rendered = true;
        ob_start();
        $this->output_box();
        return $block_content . ob_get_clean();
    }

    private function output_box()
    {
        $settings = HDOB_Repository::get_settings();
        $product  = wc_get_product($settings['product_id']);
        if (!$product) {
            return;
        }
        $price = HDOB_Repository::get_discounted_price();
        $url   = add_query_arg('hdob_add_bump', '1', wc_get_cart_url());
        ?>
        <div class="hdob-offer">
            <p class="hdob-offer__headline"><?php echo esc_html($settings['headline']); ?></p>
            <p class="hdob-offer__product">
                <?php echo esc_html($product->get_name()); ?> &mdash;
                <del><?php echo wp_kses_post(wc_price((float) $product->get_regular_price())); ?></del>
                <ins><?php echo wp_kses_post(wc_price((float) $price)); ?></ins>
            </p>
            <p><a class="button hdob-offer__add" href="<?php echo esc_url($url); ?>"><?php esc_html_e('Add this deal', 'hdwebmobile-order-bump'); ?></a></p>
        </div>
        <?php
    }
}
