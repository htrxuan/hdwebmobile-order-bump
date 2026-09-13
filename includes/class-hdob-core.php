<?php

namespace htrxuan\hdob;

if (!defined('ABSPATH')) {
    exit;
}

final class HDOB_Core
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
        $this->includes();
        $this->init_hooks();
    }

    private function __clone()
    {
    }

    private function includes()
    {
        require_once HDOB_PLUGIN_DIR . 'includes/class-hdob-repository.php';
        require_once HDOB_PLUGIN_DIR . 'includes/class-hdob-admin.php';
        require_once HDOB_PLUGIN_DIR . 'includes/class-hdob-frontend.php';
    }

    private function init_hooks()
    {
        add_action('admin_notices', array($this, 'render_missing_woocommerce_notice'));

        if (!class_exists('WooCommerce')) {
            return;
        }

        // HDOB_Admin owns the hdwebmobile_hub_tabs registration used by the shared hub
        // page, so it must load unconditionally (not only when is_admin()).
        HDOB_Admin::get_instance();
        HDOB_Frontend::get_instance();
    }

    public function render_missing_woocommerce_notice()
    {
        $screen = get_current_screen();
        if (!$screen || 'plugins' !== $screen->id) {
            return;
        }

        if (!get_transient('hdob_wc_missing_notice')) {
            return;
        }
        delete_transient('hdob_wc_missing_notice');
        ?>
        <div class="notice notice-error is-dismissible">
            <p>
                <?php esc_html_e('HDWebmobile Order Bump requires WooCommerce to be installed and active. The plugin has been deactivated.', 'hdwebmobile-order-bump'); ?>
            </p>
        </div>
        <?php
    }
}
