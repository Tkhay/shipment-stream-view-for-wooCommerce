<?php

/**
 * Frontend Shortcode Logic - Dynamic Centered Build
 */

if (!defined('ABSPATH')) exit;

function ost_render_frontend_tracker()
{
    // 1. ASSET LOADING
    $asset_file = plugin_dir_path(__DIR__) . 'build/frontend.asset.php';

    if (file_exists($asset_file)) {
        $frontend_asset = include($asset_file);
        wp_enqueue_style(
            'ost-frontend-style',
            plugins_url('../build/frontend.css', __FILE__),
            array(),
            $frontend_asset['version']
        );
        wp_enqueue_style(
            'ost-material-icons',
            'https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1',
            array(),
            null
        );
    }

    // 2. SEARCH & DATA RETRIEVAL
    $order_id = isset($_POST['ost_order_id']) ? sanitize_text_field($_POST['ost_order_id']) : '';
    $email = isset($_POST['ost_email']) ? sanitize_email($_POST['ost_email']) : '';
    $order = false;
    $error = false;
    $saved_steps = get_option('ost_tracking_steps', []);

    if ($order_id && $email) {
        $order = wc_get_order($order_id);
        if (!$order || strtolower($order->get_billing_email()) !== strtolower($email)) {
            $order = false;
            $error = "ORDER NOT FOUND OR EMAIL DOES NOT MATCH.";
        }
    }

    ob_start(); ?>
    <div class="ost-frontend-root">

        <?php if (!$order): ?>
            <section class="ost-search-section">
                <div class="ost-search-header">
                    <h1>Track Your Order</h1>
                    <p>Enter your details below to see your shipment status.</p>
                </div>
                <div class="ost-search-card">
                    <form method="POST" class="ost-form">
                        <div class="ost-input-flex">
                            <div class="ost-input-group">
                                <label>Order ID</label>
                                <div class="ost-input-wrapper">
                                    <span class="material-symbols-outlined">tag</span>
                                    <input type="text" name="ost_order_id" value="<?php echo esc_attr($order_id); ?>" placeholder="e.g. 920192" required>
                                </div>
                            </div>
                            <div class="ost-input-group">
                                <label>Billing Email</label>
                                <div class="ost-input-wrapper">
                                    <span class="material-symbols-outlined">mail</span>
                                    <input type="email" name="ost_email" value="<?php echo esc_attr($email); ?>" placeholder="alex@example.com" required>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="ost-btn-track">
                            TRACK NOW
                            <span class="material-symbols-outlined">arrow_forward</span>
                        </button>
                    </form>
                    <?php if ($error): ?><p class="ost-error"><?php echo $error; ?></p><?php endif; ?>
                </div>
            </section>
        <?php endif; ?>

        <?php if ($order):
            $current_status = $order->get_status();

            // DYNAMIC STATUS MATCHING
            $current_step_index = -1;
            foreach ($saved_steps as $index => $step) {
                if ($step['id'] === $current_status || ($current_status === 'draft' && $index === 0)) {
                    $current_step_index = $index;
                    break;
                }
            }

            // DYNAMIC CENTERED PROGRESS CALCULATION
            $total_steps = count($saved_steps);
            $progress_percentage = 0;
            if ($total_steps > 1 && $current_step_index !== -1) {
                $progress_percentage = ($current_step_index / ($total_steps - 1)) * 100;
            }

            $status_name = ($current_status === 'draft') ? 'New Order' : wc_get_order_status_name($current_status);
        ?>
            <section class="ost-result-section">
                <div class="ost-result-card">
                    <div class="ost-card-header">
                        <div class="ost-header-info">
                            <div class="ost-title-row">
                                <h2>Order #<?php echo $order->get_id(); ?></h2>
                                <span class="ost-badge"><?php echo esc_html($status_name); ?></span>
                            </div>
                            <p class="ost-placed-on">Placed on <strong><?php echo wc_format_datetime($order->get_date_created()); ?></strong></p>
                        </div>
                    </div>

                    <div class="ost-stepper-container" style="--progress: <?php echo $progress_percentage; ?>%;">
                        <div class="ost-progress-line">
                            <div class="ost-progress-fill"></div>
                        </div>
                        <div class="ost-steps">
                            <?php foreach ($saved_steps as $index => $step):
                                $is_active = ($index === $current_step_index);
                                $is_completed = ($index <= $current_step_index && $current_step_index !== -1);
                            ?>
                                <div class="ost-step <?php echo $is_completed ? 'completed' : ''; ?> <?php echo $is_active ? 'active' : ''; ?>">
                                    <div class="ost-step-circle">
                                        <span class="material-symbols-outlined">
                                            <?php echo $is_completed ? 'check' : 'radio_button_unchecked'; ?>
                                        </span>
                                    </div>
                                    <p><?php echo esc_html($step['label']); ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="ost-items-section">
                        <h3><span class="material-symbols-outlined">shopping_bag</span> Order Items</h3>
                        <?php foreach ($order->get_items() as $item_id => $item):
                            $product = $item->get_product();
                        ?>
                            <div class="ost-product-row">
                                <div class="ost-product-img"><?php echo $product ? $product->get_image('thumbnail') : ''; ?></div>
                                <div class="ost-product-info">
                                    <strong><?php echo esc_html($item->get_name()); ?></strong>
                                    <span>Qty: <?php echo esc_html($item->get_quantity()); ?></span>
                                </div>
                                <div class="ost-product-price"><?php echo $order->get_formatted_line_subtotal($item); ?></div>
                            </div>
                        <?php endforeach; ?>

                        <div class="ost-total-row">
                            <span>Total Amount</span>
                            <strong><?php echo $order->get_formatted_order_total(); ?></strong>
                        </div>

                        <div class="ost-reset-container">
                            <a href="<?php echo esc_url(get_permalink()); ?>" class="ost-btn-reset">
                                <span class="material-symbols-outlined">refresh</span>
                                CHECK ANOTHER ORDER
                            </a>
                        </div>
                    </div>
                </div>
            </section>
        <?php endif; ?>
    </div>
<?php
    return ob_get_clean();
}
