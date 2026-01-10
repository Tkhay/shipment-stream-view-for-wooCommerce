<?php
if (! defined('ABSPATH')) exit;

add_action('admin_menu', function () {
    add_menu_page(
        'Shipment Stream',
        'Shipment Stream',
        'manage_options',
        'ssvfww-main',
        'ssvfww_render_admin_app',
        'dashicons-location-alt',
        6
    );

    // Add Settings submenu
    add_submenu_page(
        'ssvfww-main',
        'Settings',
        'Settings',
        'manage_options',
        'ssvfww-settings',
        'ssvfww_render_settings_app'
    );
});

function ssvfww_render_admin_app()
{
    echo '<div class="wrap">
	<h1>Shipment Stream View - Status Management</h1>
	<div id="ssvfww-admin-app">
	</div>
	</div>';
}

function ssvfww_render_settings_app()
{
    echo '<div class="wrap">
	<h1>Shipment Stream View - Design Settings</h1>
	<div id="ssvfww-settings-app">
	</div>
	</div>';
}

add_action('admin_enqueue_scripts', function ($hook) {
    if ('toplevel_page_ssvfww-main' !== $hook) return;
    $asset_file = include SSVFWW_PLUGIN_PATH . 'build/index.asset.php';
    wp_enqueue_script(
        'ssvfww-script',
        SSVFWW_PLUGIN_URL . 'build/index.js',
        $asset_file['dependencies'],
        $asset_file['version'],
        true
    );

    $wc_statuses = wc_get_order_statuses();
    $formatted_statuses = array();
    foreach ($wc_statuses as $id => $label) {
        $formatted_statuses[] = array('id' => str_replace('wc-', '', $id), 'label' => $label);
    }

    wp_localize_script(
        'ssvfww-script',
        'ssvfwwData',
        array(
            'allStatuses' => $formatted_statuses,
            'savedOrder'  => get_option('ssvfww_tracking_steps', array()),
            'defaultSteps' => array(
                array('id' => 'pending',    'label' => 'Order Received', 'type' => 'milestone'),
                array('id' => 'processing', 'label' => 'Processing',     'type' => 'milestone'),
                array('id' => 'on-hold',    'label' => 'Issue Detected', 'type' => 'exception'),
                array('id' => 'completed',  'label' => 'Delivered',      'type' => 'milestone'),
                array('id' => 'cancelled',  'label' => 'Order Cancelled', 'type' => 'exception')
            )
        )
    );
    wp_enqueue_style(
        'ssvfww-style',
        SSVFWW_PLUGIN_URL . 'build/style-index.css',
        array(),
        $asset_file['version']
    );
});

// Enqueue scripts for Settings page
add_action('admin_enqueue_scripts', function ($hook) {
    if ('shipment-stream_page_ssvfww-settings' !== $hook) return;

    $asset_file = include SSVFWW_PLUGIN_PATH . 'build/settings.asset.php';
    wp_enqueue_script(
        'ssvfww-settings-script',
        SSVFWW_PLUGIN_URL . 'build/settings.js',
        $asset_file['dependencies'],
        $asset_file['version'],
        true
    );

    wp_localize_script(
        'ssvfww-settings-script',
        'ssvfwwSettings',
        array(
            'saved' => get_option('ssvfww_design_settings', array(
                'primary_color' => '#137fec',
                'font_family' => 'Inter',
                'use_theme_color' => false
            ))
        )
    );

    wp_enqueue_style(
        'ssvfww-settings-style',
        SSVFWW_PLUGIN_URL . 'build/settings.css',
        array(),
        $asset_file['version']
    );
});

add_action('rest_api_init', function () {
    // Status tracking endpoint
    register_rest_route(
        'ssvfww/v1',
        '/save-settings',
        array(
            'methods' => 'POST',
            'callback' => function ($request) {
                $params = $request->get_json_params();
                update_option('ssvfww_tracking_steps', $params['steps']);
                return new WP_REST_Response(array('success' => true), 200);
            },
            'permission_callback' => fn(\WP_REST_Request $request) => current_user_can('manage_options')
        )
    );

    // Design settings endpoint
    register_rest_route(
        'ssvfww/v1',
        '/save-design-settings',
        array(
            'methods' => 'POST',
            'callback' => function ($request) {
                $params = $request->get_json_params();
                update_option('ssvfww_design_settings', $params);
                return new WP_REST_Response(array('success' => true), 200);
            },
            'permission_callback' => fn(\WP_REST_Request $request) => current_user_can('manage_options')
        )
    );
});
