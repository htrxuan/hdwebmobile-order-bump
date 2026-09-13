<?php

/**
 * Plugin Name: HDWebmobile Order Bump
 * Plugin URI: https://hdwebmobile.com/plugins/hdwebmobile-order-bump/
 * Description: A one-click discounted add-on offer at checkout -- the price charged is always the admin's own configured price, never accepted from the request.
 * Version: 1.0.0
 * Author: htrxuan - Han Tran
 * Author URI: https://hdwebmobile.com/
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: hdwebmobile-order-bump
 * Domain Path: /languages
 * Requires Plugins: woocommerce
 * Requires PHP: 7.4
 * Requires at least: 6.9
 */

namespace htrxuan\hdob;

if (!defined('ABSPATH')) {
    exit;
}

define('HDOB_VERSION', '1.0.0');
define('HDOB_CART_ITEM_KEY', 'hdob_bump');
define('HDOB_PLUGIN_FILE', __FILE__);
define('HDOB_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('HDOB_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once HDOB_PLUGIN_DIR . 'includes/class-hdob-activator.php';

register_activation_hook(__FILE__, array(HDOB_Activator::class, 'activate'));

add_action('before_woocommerce_init', function () {
    if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', HDOB_PLUGIN_FILE, true);
    }
});

add_action('plugins_loaded', function () {
    require_once HDOB_PLUGIN_DIR . 'includes/class-hdob-core.php';
    HDOB_Core::get_instance();
});

add_filter('plugin_action_links_' . plugin_basename(__FILE__), function ($links) {
    $donate_link = '<a href="https://paypal.me/htrxuan/20" target="_blank" rel="noopener noreferrer">' . esc_html__('Donate', 'hdwebmobile-order-bump') . '</a>';
    array_unshift($links, $donate_link);
    return $links;
});
