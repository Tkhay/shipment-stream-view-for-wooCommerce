<?php
if (!defined('ABSPATH')) exit;

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

    // Add Settings submenu
    add_submenu_page(
        'order-tracker-plugin',
        'Settings',
        'Settings',
        'manage_options',
        'order-tracker-settings',
        'ost_render_settings_app'
    );
});

function ost_render_admin_app()
{
    echo '<div class="wrap">
    <h1>Order Status Tracker Settings</h1>
    <div id="ost-admin-app">
    </div>
    </div>';
}

function ost_render_settings_app()
{
    echo '<div class="wrap">
    <h1>Design Settings</h1>
    <div id="ost-settings-app">
    </div>
    </div>';
}

add_action('admin_enqueue_scripts', function ($hook) {
    if ('toplevel_page_order-tracker-plugin' !== $hook) return;
    $asset_file = include(OST_PLUGIN_PATH . 'build/index.asset.php');
    wp_enqueue_script(
        'ost-script',
        OST_PLUGIN_URL . 'build/index.js',
        $asset_file['dependencies'],
        $asset_file['version'],
        true
    );

    $wc_statuses = wc_get_order_statuses();
    $formatted_statuses = [];
    foreach ($wc_statuses as $id => $label) {
        $formatted_statuses[] = ['id' => str_replace('wc-', '', $id), 'label' => $label];
    }

    wp_localize_script(
        'ost-script',
        'ostData',
        array(
            'allStatuses' => $formatted_statuses,
            'savedOrder'  => get_option('ost_tracking_steps', []),
            'defaultSteps' => [
                ['id' => 'pending',    'label' => 'Order Received', 'type' => 'milestone'],
                ['id' => 'processing', 'label' => 'Processing',     'type' => 'milestone'],
                ['id' => 'on-hold',    'label' => 'Issue Detected', 'type' => 'exception'],
                ['id' => 'completed',  'label' => 'Delivered',      'type' => 'milestone'],
                ['id' => 'cancelled',  'label' => 'Order Cancelled', 'type' => 'exception']
            ]
        )
    );
    wp_enqueue_style(
        'ost-style',
        OST_PLUGIN_URL . 'build/style-index.css',
        array(),
        $asset_file['version']
    );
});

// Enqueue scripts for Settings page
add_action('admin_enqueue_scripts', function ($hook) {
    if ('order-tracker_page_order-tracker-settings' !== $hook) return;

    $asset_file = include(OST_PLUGIN_PATH . 'build/settings.asset.php');
    wp_enqueue_script(
        'ost-settings-script',
        OST_PLUGIN_URL . 'build/settings.js',
        $asset_file['dependencies'],
        $asset_file['version'],
        true
    );

    wp_localize_script(
        'ost-settings-script',
        'ostSettings',
        array(
            'saved' => get_option('ost_design_settings', [
                'primary_color' => '#137fec',
                'font_family' => 'Inter',
                'use_theme_color' => false
            ])
        )
    );

    wp_enqueue_style(
        'ost-settings-style',
        OST_PLUGIN_URL . 'build/settings.css',
        array(),
        $asset_file['version']
    );
});

add_action('rest_api_init', function () {
    // Status tracking endpoint
    register_rest_route(
        'ost/v1',
        '/save-settings',
        array(
            'methods' => 'POST',
            'callback' => function ($request) {
                $params = $request->get_json_params();
                update_option('ost_tracking_steps', $params['steps']);
                return new WP_REST_Response(array('success' => true), 200);
            },
            'permission_callback' => fn() => current_user_can('manage_options')
        )
    );

    // Design settings endpoint
    register_rest_route(
        'ost/v1',
        '/save-design-settings',
        array(
            'methods' => 'POST',
            'callback' => function ($request) {
                $params = $request->get_json_params();
                update_option('ost_design_settings', $params);
                return new WP_REST_Response(array('success' => true), 200);
            },
            'permission_callback' => fn() => current_user_can('manage_options')
        )
    );
});
