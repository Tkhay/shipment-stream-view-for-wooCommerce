<?php

/**
 * Frontend Shortcode Logic - Complete Elite Build (Details First)
 */

if (! defined('ABSPATH')) exit;

function ssvfww_render_frontend_tracker()
{
    // 1. ASSET LOADING
    $asset_file = SSVFWW_PLUGIN_PATH . 'build/frontend.asset.php';
    if (file_exists($asset_file)) {
        $frontend_asset = include $asset_file;
        wp_enqueue_style(
            'ssvfww-frontend-style',
            SSVFWW_PLUGIN_URL . 'build/frontend.css',
            array(),
            $frontend_asset['version']
        );

        wp_enqueue_style(
            'ssvfww-material-icons',
            'https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1',
            array(),
            SSVFWW_VERSION
        );

        // 2. LOAD DESIGN SETTINGS AND APPLY
        $design_settings = get_option('ssvfww_design_settings', array(
            'primary_color' => '#137fec',
            'font_family' => 'Inter',
            'use_theme_color' => false
        ));

        // Determine primary color
        $primary_color = '#137fec';
        if (! empty($design_settings['use_theme_color'])) {
            $primary_color = '#137fec'; // Fallback to default
        } else {
            $primary_color = $design_settings['primary_color'];
        }

        $font_family = ! empty($design_settings['font_family']) ? $design_settings['font_family'] : 'Inter';

        // Generate CSS variables
        $custom_css = ".ssvfww-frontend-root { --ssvfww-primary: {$primary_color}; --ssvfww-font-family: '{$font_family}', sans-serif; }";
        wp_add_inline_style('ssvfww-frontend-style', $custom_css);
    }

    $order_id = isset($_POST['ssvfww_order_id']) ? sanitize_text_field(wp_unslash($_POST['ssvfww_order_id'])) : '';
    $email = isset($_POST['ssvfww_email']) ? sanitize_email(wp_unslash($_POST['ssvfww_email'])) : '';
    $order = false;
    $error = false;
    $saved_steps = get_option('ssvfww_tracking_steps', array());

    if ($order_id && $email) {
        if (! empty($_POST['ssvfww_hp_field'])) {
            die("Bot detected.");
        }

        $nonce = isset($_POST['ssvfww_tracker_nonce']) ? sanitize_text_field(wp_unslash($_POST['ssvfww_tracker_nonce'])) : '';

        if (! $nonce || ! wp_verify_nonce($nonce, 'ssvfww_track_order_action')) {
            $error = "SECURITY CHECK FAILED. PLEASE REFRESH THE PAGE.";
        } else {
            $order = wc_get_order($order_id);
            if (! $order || strtolower($order->get_billing_email()) !== strtolower($email)) {
                $order = false;
                $error = "ORDER NOT FOUND OR EMAIL DOES NOT MATCH.";
            }
        }
    }

    ob_start(); ?>
    <div class="ssvfww-frontend-root">

        <?php if (! $order): ?>
            <section class="ssvfww-search-section">
                <div class="ssvfww-search-header">
                    <h1>Track Your Order</h1>
                    <p>Enter your details below to see your shipment status.</p>
                </div>
                <div class="ssvfww-search-card">
                    <form method="POST" class="ssvfww-form">
                        <?php wp_nonce_field('ssvfww_track_order_action', 'ssvfww_tracker_nonce'); ?>

                        <div class="ssvfww-honeypot">
                            <input type="text" name="ssvfww_hp_field" value="">
                        </div>
                        <div class="ssvfww-input-flex">
                            <div class="ssvfww-input-group">
                                <label>Order ID</label>
                                <div class="ssvfww-input-wrapper">
                                    <span class="material-symbols-outlined">tag</span>
                                    <input type="text" name="ssvfww_order_id" value="<?php echo esc_attr($order_id); ?>" placeholder="e.g. 92234" required>
                                </div>
                            </div>
                            <div class="ssvfww-input-group">
                                <label>Billing Email</label>
                                <div class="ssvfww-input-wrapper">
                                    <span class="material-symbols-outlined">email</span>
                                    <input type="email" name="ssvfww_email" value="<?php echo esc_attr($email); ?>" placeholder="email@example.com" required>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="ssvfww-btn-track">TRACK NOW <span class="material-symbols-outlined">arrow_forward</span></button>
                    </form>
                    <?php if ($error): ?><p class="ssvfww-error"><?php echo esc_html($error); ?></p><?php endif; ?>
                </div>
            </section>
        <?php else:
            $current_status = $order->get_status();

            // DYNAMIC CATEGORIZATION
            $milestones = array_values(array_filter($saved_steps, fn($s) => ($s['type'] ?? 'milestone') === 'milestone'));
            $exceptions = array_values(array_filter($saved_steps, fn($s) => ($s['type'] ?? '') === 'exception'));

            $active_exception = false;
            foreach ($exceptions as $ex) {
                if ($ex['id'] === $current_status) {
                    $active_exception = $ex;
                    break;
                }
            }

            $current_idx = -1;
            foreach ($milestones as $idx => $ms) {
                if ($ms['id'] === $current_status || ($current_status === 'draft' && $idx === 0)) {
                    $current_idx = $idx;
                    break;
                }
            }

            // PROGRESS CALCULATION
            $progress = ($current_idx !== -1 && count($milestones) > 1) ? ($current_idx / (count($milestones) - 1)) * 100 : 0;
        ?>
            <section class="ssvfww-result-section">
                <div class="ssvfww-result-card">
                    <div class="ssvfww-card-header">
                        <div class="ssvfww-title-row">
                            <h2>Order #<?php echo esc_html($order->get_id()); ?></h2>
                            <span class="ssvfww-badge"><?php echo esc_html(wc_get_order_status_name($current_status)); ?></span>
                        </div>
                        <p class="ssvfww-placed-on">Placed on <strong><?php echo esc_html(wc_format_datetime($order->get_date_created())); ?></strong></p>
                    </div>

                    <?php if ($active_exception): ?>
                        <div class="ssvfww-exception-alert">
                            <span class="material-symbols-outlined">warning</span>
                            <div class="ssvfww-alert-content">
                                <strong><?php echo esc_html($active_exception['label']); ?></strong>
                                <p>There is an update regarding your order status. Please contact support.</p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="ssvfww-stepper-container <?php echo $active_exception ? 'ssvfww-dimmed' : ''; ?>">
                        <div class="ssvfww-progress-line">
                            <div class="ssvfww-progress-fill" style="width: <?php echo esc_attr($progress); ?>%; height: <?php echo esc_attr($progress); ?>%;"></div>
                        </div>
                        <div class="ssvfww-steps">
                            <?php foreach ($milestones as $idx => $step):
                                $is_active = ($idx === $current_idx);
                                $is_completed = ($idx <= $current_idx && $current_idx !== -1);
                            ?>
                                <div class="ssvfww-step <?php echo $is_completed ? 'completed' : ''; ?> <?php echo $is_active ? 'active' : ''; ?>">
                                    <div class="ssvfww-step-circle">
                                        <span class="material-symbols-outlined"><?php echo $is_completed ? 'check' : 'radio_button_unchecked'; ?>
                                        </span>
                                    </div>
                                    <p><?php echo esc_html($step['label']); ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="ssvfww-details-grid">
                        <div class="ssvfww-detail-box">
                            <h4><span class="material-symbols-outlined">local_shipping</span> Shipping Address</h4>
                            <p><?php echo wp_kses_post($order->get_formatted_shipping_address()); ?></p>
                        </div>
                        <div class="ssvfww-detail-box">
                            <h4><span class="material-symbols-outlined">person</span> Customer Details</h4>
                            <p><strong><?php echo esc_html($order->get_billing_first_name() . ' ' . $order->get_billing_last_name()); ?></strong></p>
                            <p><?php echo esc_html($order->get_billing_email()); ?></p>
                            <p><?php echo esc_html($order->get_billing_phone()); ?></p>
                        </div>
                    </div>

                    <div class="ssvfww-items-section">
                        <h3><span class="material-symbols-outlined">shopping_bag</span> Order Items</h3>
                        <?php foreach ($order->get_items() as $item):
                            $product = $item->get_product();
                            $meta_data = $item->get_formatted_meta_data('');
                        ?>
                            <div class="ssvfww-product-row">
                                <div class="ssvfww-product-img"><?php echo $product ? wp_kses_post($product->get_image('thumbnail')) : ''; ?></div>
                                <div class="ssvfww-product-info">
                                    <div class="ssvfww-product-details">
                                        <strong><?php echo esc_html($item->get_name()); ?></strong>
                                        <?php if ($meta_data): ?>
                                            <span class="ssvfww-product-meta">
                                                <?php echo esc_html(implode(' • ', array_map(fn($m) => $m->display_key . ': ' . $m->display_value, $meta_data))); ?>
                                            </span>
                                        <?php endif; ?>
                                        <span class="ssvfww-product-qty">Qty: <?php echo esc_html($item->get_quantity()); ?></span>
                                    </div>
                                    <div class="ssvfww-product-price"><?php echo wp_kses_post($order->get_formatted_line_subtotal($item)); ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <div class="ssvfww-total-row">
                            <span>Total Amount</span>
                            <strong><?php echo wp_kses_post($order->get_formatted_order_total()); ?></strong>
                        </div>

                        <div class="ssvfww-reset-container">
                            <a href="<?php echo esc_url(get_permalink()); ?>" class="ssvfww-btn-reset">
                                <span class="material-symbols-outlined">refresh</span> CHECK ANOTHER ORDER
                            </a>
                        </div>
                    </div>
                </div>
            </section>
        <?php endif; ?>
    </div>
<?php return ob_get_clean();
}
