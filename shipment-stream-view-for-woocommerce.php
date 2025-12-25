<?php

/**
 * Plugin Name: Shipment Stream View for WooCommerce
 * Description: A modern visual order tracking bar for WooCommerce.
 * Version: 1.1.0
 * Author: Tieku Asare
 * Text Domain: shipment-stream-view-for-woocommerce
 * Author URI: https://www.tiekuasare.com
 * License: GPL-2.0
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Requires PHP: 7.2
 * Requires at least: 6.9
 * WC requires at least: 8.0
 * WC tested up to: 10.0
 */

if (!defined('ABSPATH')) exit;

// Path Constants
define('OST_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('OST_PLUGIN_URL', plugin_dir_url(__FILE__));

// Declare HPOS compatibility for WooCommerce.
add_action('before_woocommerce_init', function () {
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
    }
});

// 1. SAFEGUARD: Only load if WooCommerce is active
add_action('plugins_loaded', function () {
    if (class_exists('WooCommerce')) {
        // Load Modules
        require_once OST_PLUGIN_PATH . 'includes/admin-settings.php';
        require_once OST_PLUGIN_PATH . 'includes/frontend-tracker.php';

        add_shortcode('order_tracker', 'ost_render_frontend_tracker');
    } else {
        // Show admin notice if WooCommerce is missing
        add_action('admin_notices', function () {
?>
            <div class="error">
                <p><strong>Shipment Stream View for WooCommerce</strong> requires <a href="https://wordpress.org/plugins/woocommerce/" target="_blank">WooCommerce</a> to be installed and active.</p>
            </div>
<?php
        });
    }
});

register_activation_hook(__FILE__, 'ost_set_default_tracking_logic');

function ost_set_default_tracking_logic()
{
    $existing = get_option('ost_tracking_steps');

    if (!$existing) {

        $default_steps = [
            ['id' => 'pending',    'label' => 'Order Received', 'type' => 'milestone'],
            ['id' => 'processing', 'label' => 'Processing',     'type' => 'milestone'],
            ['id' => 'on-hold',    'label' => 'Issue Detected', 'type' => 'exception'],
            ['id' => 'completed',  'label' => 'Delivered',      'type' => 'milestone'],
            ['id' => 'cancelled',  'label' => 'Order Cancelled', 'type' => 'exception']
        ];
        update_option('ost_tracking_steps', $default_steps);
    } else {
        $needs_update = false;
        foreach ($existing as &$step) {
            if (!isset($step['type'])) {
                $step['type'] = 'milestone';
                $needs_update = true;
            }
        }
        if ($needs_update) {
            update_option('ost_tracking_steps', $existing);
        }
    }
}
