<?php

namespace htrxuan\hdob;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * A single store-wide order-bump offer, and the only place its discounted price is ever
 * computed.
 *
 * Several competing "order bump" / "one-click upsell" plugins have read the bumped item's
 * discounted price straight out of a hidden field the checkout page itself rendered and
 * submitted back -- so a shopper who edited that field before submitting (or simply replayed
 * the request with a different value) could add the bump item at almost any price they chose
 * (CWE-20, Improper Input Validation, leading to price manipulation).
 *
 * This plugin never has that field to tamper with in the first place: get_discounted_price()
 * is the ONLY place a bump price is computed, it takes no price-shaped argument at all, and it
 * always derives the price fresh from the product's own current regular price and the admin's
 * currently-saved discount percentage. The frontend cart-price hook (see HDOB_Frontend) calls
 * this same method every time totals are calculated, so even a cart item that was added a long
 * time ago is always repriced from current settings, never from whatever it was added at.
 */
class HDOB_Repository
{
    public static function get_settings()
    {
        $defaults = array(
            'enabled'          => 0,
            'product_id'       => 0,
            'discount_percent' => 20.0,
            'headline'         => __('Add this to your order and save!', 'hdwebmobile-order-bump'),
        );
        $settings = get_option('hdob_settings', array());
        return wp_parse_args(is_array($settings) ? $settings : array(), $defaults);
    }

    public static function save_settings($input)
    {
        $current = self::get_settings();

        $percent = isset($input['discount_percent']) ? (float) $input['discount_percent'] : $current['discount_percent'];
        if ($percent < 0) {
            $percent = 0.0;
        }
        if ($percent > 99) {
            $percent = 99.0;
        }

        $product_id = isset($input['product_id']) ? absint($input['product_id']) : 0;
        if ($product_id && !wc_get_product($product_id)) {
            $product_id = 0;
        }

        $settings = array(
            'enabled'          => !empty($input['enabled']) ? 1 : 0,
            'product_id'       => $product_id,
            'discount_percent' => $percent,
            'headline'         => isset($input['headline']) ? sanitize_text_field($input['headline']) : $current['headline'],
        );

        update_option('hdob_settings', $settings);
        return $settings;
    }

    /**
     * True only when the offer is turned on AND its configured product genuinely still exists
     * and is purchasable -- never assumed just because an id is saved in the option.
     */
    public static function is_offer_available()
    {
        $settings = self::get_settings();
        if (empty($settings['enabled']) || empty($settings['product_id'])) {
            return false;
        }
        $product = wc_get_product($settings['product_id']);
        return $product instanceof \WC_Product && $product->is_purchasable() && $product->is_in_stock();
    }

    /**
     * The only place a bump price is ever computed. $product is looked up by this method
     * itself from the currently-saved settings -- never accepted as, or influenced by, a
     * caller-supplied price.
     */
    public static function get_discounted_price()
    {
        $settings = self::get_settings();
        $product  = wc_get_product($settings['product_id']);
        if (!$product instanceof \WC_Product) {
            return null;
        }
        $regular = (float) $product->get_regular_price();
        if ($regular <= 0) {
            return null;
        }
        $percent = (float) $settings['discount_percent'];
        return round($regular * (1 - $percent / 100), 2);
    }
}
