<?php

namespace htrxuan\hdob;

if (!defined('ABSPATH')) {
    exit;
}

class HDOB_Admin
{

    private static $instance = null;

    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        require_once HDOB_PLUGIN_DIR . 'includes/class-hdob-hub.php';
        add_filter('hdwebmobile_hub_tabs', array($this, 'register_hub_tabs'));
        add_action('admin_init', array($this, 'register_settings'));
    }

    public function register_hub_tabs($tabs)
    {
        $tabs['order-bump'] = array(
            'label'  => __('Order Bump', 'hdwebmobile-order-bump'),
            'order'  => 149,
            'render' => array($this, 'render_page'),
        );
        return $tabs;
    }

    public function register_settings()
    {
        register_setting('hdob_settings_group', 'hdob_settings', array('htrxuan\\hdob\\HDOB_Repository', 'save_settings'));
    }

    public function render_page()
    {
        if (!current_user_can('manage_woocommerce')) {
            wp_die(esc_html__('You do not have permission to do this.', 'hdwebmobile-order-bump'));
        }

        $settings = HDOB_Repository::get_settings();
        ?>
        <p><?php esc_html_e('Offer one extra product at a discount on the cart page. The price is always computed from the product\'s own regular price and the discount below -- never anything a shopper could submit.', 'hdwebmobile-order-bump'); ?></p>

        <form method="post" action="options.php">
            <?php settings_fields('hdob_settings_group'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><?php esc_html_e('Enable', 'hdwebmobile-order-bump'); ?></th>
                    <td><label><input type="checkbox" name="hdob_settings[enabled]" value="1" <?php checked(!empty($settings['enabled'])); ?> /> <?php esc_html_e('Show the offer on the cart page', 'hdwebmobile-order-bump'); ?></label></td>
                </tr>
                <tr>
                    <th scope="row"><label for="hdob_product_id"><?php esc_html_e('Product', 'hdwebmobile-order-bump'); ?></label></th>
                    <td><?php $this->render_product_select($settings['product_id']); ?></td>
                </tr>
                <tr>
                    <th scope="row"><label for="hdob_discount_percent"><?php esc_html_e('Discount %', 'hdwebmobile-order-bump'); ?></label></th>
                    <td><input type="number" id="hdob_discount_percent" name="hdob_settings[discount_percent]" step="any" min="0" max="99" value="<?php echo esc_attr($settings['discount_percent']); ?>" style="width:100px;" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="hdob_headline"><?php esc_html_e('Headline', 'hdwebmobile-order-bump'); ?></label></th>
                    <td><input type="text" id="hdob_headline" name="hdob_settings[headline]" value="<?php echo esc_attr($settings['headline']); ?>" style="width:100%;max-width:500px;" /></td>
                </tr>
            </table>
            <?php submit_button(__('Save Settings', 'hdwebmobile-order-bump')); ?>
        </form>
        <?php
    }

    private function render_product_select($selected_id)
    {
        $products = wc_get_products(array('limit' => 200, 'status' => 'publish', 'orderby' => 'title', 'order' => 'ASC'));
        ?>
        <select id="hdob_product_id" name="hdob_settings[product_id]">
            <option value="0"><?php esc_html_e('-- Select a product --', 'hdwebmobile-order-bump'); ?></option>
            <?php foreach ($products as $product) : ?>
                <option value="<?php echo esc_attr($product->get_id()); ?>" <?php selected((int) $selected_id, $product->get_id()); ?>>
                    <?php echo esc_html($product->get_name()); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php
    }
}
