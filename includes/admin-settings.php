<?php
if (!defined('ABSPATH')) exit;

add_action('admin_menu', function () {
    add_menu_page('Order Tracker', 'Order Tracker', 'manage_options', 'order-tracker-plugin', 'ost_render_admin_app', 'dashicons-location-alt', 6);
});

function ost_render_admin_app()
{
    echo '<div class="wrap"><h1>Order Status Tracker Settings</h1><div id="ost-admin-app"></div></div>';
}

add_action('admin_enqueue_scripts', function ($hook) {
    if ('toplevel_page_order-tracker-plugin' !== $hook) return;
    $asset_file = include(OST_PLUGIN_PATH . 'build/index.asset.php');
    wp_enqueue_script('ost-script', OST_PLUGIN_URL . 'build/index.js', $asset_file['dependencies'], $asset_file['version'], true);

    $wc_statuses = wc_get_order_statuses();
    $formatted_statuses = [];
    foreach ($wc_statuses as $id => $label) {
        $formatted_statuses[] = ['id' => str_replace('wc-', '', $id), 'label' => $label];
    }

    wp_localize_script('ost-script', 'ostData', array(
        'allStatuses' => $formatted_statuses,
        'savedOrder'  => get_option('ost_tracking_steps', [])
    ));
    wp_enqueue_style('ost-style', OST_PLUGIN_URL . 'build/style-index.css', array(), $asset_file['version']);
});

add_action('rest_api_init', function () {
    register_rest_route('ost/v1', '/save-settings', array(
        'methods' => 'POST',
        'callback' => function ($request) {
            $params = $request->get_json_params();
            update_option('ost_tracking_steps', $params['steps']); // Saves ID, Label, and Type
            return new WP_REST_Response(array('success' => true), 200);
        },
        'permission_callback' => fn() => current_user_can('manage_options')
    ));
});
