<?php

/**
 * Plugin Name: Shipment Stream View for WooCommerce
 * Description: A modern visual order tracking system for WooCommerce that displays shipping milestones, real-time status updates, and custom alerts in a responsive, customizable timeline view.
 * Version: 1.0.0
 * Author: Tieku Asare
 * Author URI: https://www.tiekuasare.com
 * Text Domain: shipment-stream-view-for-woocommerce
 * License: GPL-2.0
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Requires PHP: 7.2
 * Requires at least: 6.9
 * WC requires at least: 8.0
 * WC tested up to: 10.0
 * Requires Plugins: woocommerce
 */

if (! defined('ABSPATH')) exit;

// Path Constants
define('SSVFWW_VERSION', '1.0.0');
define('SSVFWW_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('SSVFWW_PLUGIN_URL', plugin_dir_url(__FILE__));

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
        require_once SSVFWW_PLUGIN_PATH . 'includes/admin-settings.php';
        require_once SSVFWW_PLUGIN_PATH . 'includes/frontend-tracker.php';

        add_shortcode('ssvfw_order_tracker', 'ssvfww_render_frontend_tracker');
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

register_activation_hook(__FILE__, 'ssvfww_set_default_tracking_logic');

function ssvfww_set_default_tracking_logic()
{
    $existing = get_option('ssvfww_tracking_steps');

    if (! $existing) {

        $default_steps = array(
            array('id' => 'pending',    'label' => 'Order Received', 'type' => 'milestone'),
            array('id' => 'processing', 'label' => 'Processing',     'type' => 'milestone'),
            array('id' => 'on-hold',    'label' => 'Issue Detected', 'type' => 'exception'),
            array('id' => 'completed',  'label' => 'Delivered',      'type' => 'milestone'),
            array('id' => 'cancelled',  'label' => 'Order Cancelled', 'type' => 'exception')
        );
        update_option('ssvfww_tracking_steps', $default_steps);
    } else {
        $needs_update = false;
        foreach ($existing as &$step) {
            if (! isset($step['type'])) {
                $step['type'] = 'milestone';
                $needs_update = true;
            }
        }
        if ($needs_update) {
            update_option('ssvfww_tracking_steps', $existing);
        }
    }
}
