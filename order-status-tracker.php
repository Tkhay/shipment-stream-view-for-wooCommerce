<?php

/**
 * Plugin Name: Order Status Tracker
 * Description: A modern visual order tracking bar for WooCommerce.
 * Version: 1.1.0
 * Author: Tieku Asare
 */

if (!defined('ABSPATH')) exit;

// Path Constants
define('OST_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('OST_PLUGIN_URL', plugin_dir_url(__FILE__));

// Load Modules
require_once OST_PLUGIN_PATH . 'includes/admin-settings.php';
require_once OST_PLUGIN_PATH . 'includes/frontend-tracker.php';

// Register Shortcode
add_shortcode('order_tracker', 'ost_render_frontend_tracker');

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
        // Migration: Add 'type' field to existing steps if missing
        $needs_update = false;
        foreach ($existing as &$step) {
            if (!isset($step['type'])) {
                // Default to milestone for backward compatibility
                $step['type'] = 'milestone';
                $needs_update = true;
            }
        }
        if ($needs_update) {
            update_option('ost_tracking_steps', $existing);
        }
    }
}
