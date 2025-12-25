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
