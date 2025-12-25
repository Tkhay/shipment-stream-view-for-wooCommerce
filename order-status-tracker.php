<?php

/**
 * Plugin Name: Order Status Tracker
 * Description: A modern visual order tracking bar for WooCommerce.
 * Version: 1.0.0
 * Author: Tieku Asare
 */

if (!defined('ABSPATH'))
    exit;

require_once plugin_dir_path(__FILE__) . 'includes/frontend-tracker.php';

add_shortcode('order_tracker', 'ost_render_frontend_tracker');

add_action('admin_menu', function () {
    add_menu_page(
        'Order Tracker',
        'Order Tracker',
        'manage_options',
        'order-tracker-plugin',
        'ost_render_admin_app',
        'dashicons-location-alt',
        6
    );
});

function ost_render_admin_app()
{
    echo '<div class="wrap"><h1>Order Status Tracker Settings</h1><div id="ost-admin-app"></div></div>';
}

add_action('admin_enqueue_scripts', function ($hook) {
    if ('toplevel_page_order-tracker-plugin' !== $hook) return;

    $asset_file = include(plugin_dir_path(__FILE__) . 'build/index.asset.php');

    wp_enqueue_script('ost-script', plugins_url('build/index.js', __FILE__), $asset_file['dependencies'], $asset_file['version'], true);

    $wc_statuses = wc_get_order_statuses();
    $formatted_statuses = [];
    foreach ($wc_statuses as $id => $label) {
        $formatted_statuses[] = ['id' => str_replace('wc-', '', $id), 'label' => $label];
    }

    $saved_order = get_option('ost_tracking_steps', []);

    // ONE LOCALIZATION CALL ONLY
    wp_localize_script(
        'ost-script',
        'ostData',
        array(
            'allStatuses' => $formatted_statuses,
            'savedOrder'  => $saved_order
        )
    );

    wp_enqueue_style(
        'ost-style',
        plugins_url('build/style-index.css', __FILE__),
        array(),
        $asset_file['version']
    );
});

add_action('rest_api_init', function () {
    register_rest_route('ost/v1', '/save-settings', array(
        'methods' => 'POST',
        'callback' => 'ost_save_plugin_settings',
        'permission_callback' => function () {
            return current_user_can('manage_options');
        }
    ));
});

function ost_save_plugin_settings($request)
{
    $params = $request->get_json_params();
    if (isset($params['steps'])) {
        update_option('ost_tracking_steps', $params['steps']);
        return new WP_REST_Response(array('success' => true), 200);
    }
    return new WP_Error('save_error', 'Invalid data', array('status' => 400));
}
