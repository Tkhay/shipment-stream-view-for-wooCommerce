<?php

/**
 * Frontend Shortcode Logic - Elite Design Build
 */

if (!defined('ABSPATH')) exit;

function ost_render_frontend_tracker()
{
    // 1. ASSET LOADING
    $asset_file = OST_PLUGIN_PATH . 'build/frontend.asset.php';
    if (file_exists($asset_file)) {
        $frontend_asset = include($asset_file);
        wp_enqueue_style('ost-frontend-style', OST_PLUGIN_URL . 'build/frontend.css', array(), $frontend_asset['version']);
        wp_enqueue_style('ost-material-icons', 'https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1', array(), null);
    }

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
                        <button type="submit" class="ost-btn-track">TRACK NOW <span class="material-symbols-outlined">arrow_forward</span></button>
                    </form>
                    <?php if ($error): ?><p class="ost-error"><?php echo $error; ?></p><?php endif; ?>
                </div>
            </section>
        <?php else:
            $current_status = $order->get_status();

            // 2. DYNAMIC LOGIC: Milestones vs. Exceptions
            $milestones = array_values(array_filter($saved_steps, fn($s) => ($s['type'] ?? 'milestone') === 'milestone'));
            $exceptions = array_values(array_filter($saved_steps, fn($s) => ($s['type'] ?? '') === 'exception'));

            // Check for active exception alert
            $active_exception = false;
            foreach ($exceptions as $ex) {
                if ($ex['id'] === $current_status) {
                    $active_exception = $ex;
                    break;
                }
            }

            // Find progress index
            $current_idx = -1;
            foreach ($milestones as $idx => $ms) {
                if ($ms['id'] === $current_status || ($current_status === 'draft' && $idx === 0)) {
                    $current_idx = $idx;
                }
            }

            $progress = ($current_idx !== -1 && count($milestones) > 1) ? ($current_idx / (count($milestones) - 1)) * 100 : 0;
        ?>
            <section class="ost-result-section">
                <div class="ost-result-card">
                    <div class="ost-card-header">
                        <div class="ost-header-info">
                            <div class="ost-title-row">
                                <h2>Order #<?php echo $order->get_id(); ?></h2>
                                <span class="ost-badge"><?php echo wc_get_order_status_name($current_status); ?></span>
                            </div>
                            <p class="ost-placed-on">Placed on <strong><?php echo wc_format_datetime($order->get_date_created()); ?></strong></p>
                        </div>
                    </div>

                    <?php if ($active_exception): ?>
                        <div class="ost-exception-alert <?php echo esc_attr($current_status); ?>">
                            <span class="material-symbols-outlined">warning</span>
                            <div class="ost-alert-content">
                                <strong><?php echo esc_html($active_exception['label']); ?></strong>
                                <p>There is an update regarding your order status. Please contact support.</p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="ost-stepper-container <?php echo $active_exception ? 'ost-dimmed' : ''; ?>">
                        <div class="ost-progress-line">
                            <div class="ost-progress-fill" style="width: <?php echo $progress; ?>%; height: <?php echo $progress; ?>%;"></div>
                        </div>
                        <div class="ost-steps">
                            <?php foreach ($milestones as $idx => $step):
                                $is_active = ($idx === $current_idx);
                                $is_completed = ($idx <= $current_idx && $current_idx !== -1);
                            ?>
                                <div class="ost-step <?php echo $is_completed ? 'completed' : ''; ?> <?php echo $is_active ? 'active' : ''; ?>">
                                    <div class="ost-step-circle"><span class="material-symbols-outlined"><?php echo $is_completed ? 'check' : 'radio_button_unchecked'; ?></span></div>
                                    <p><?php echo esc_html($step['label']); ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="ost-items-section">
                        <h3><span class="material-symbols-outlined">shopping_bag</span> Order Items</h3>
                        <?php foreach ($order->get_items() as $item):
                            $product = $item->get_product();
                            $meta_data = $item->get_formatted_meta_data('');
                        ?>
                            <div class="ost-product-row">
                                <div class="ost-product-img"><?php echo $product ? $product->get_image('thumbnail') : ''; ?></div>
                                <div class="ost-product-info">
                                    <div class="ost-product-details">
                                        <strong><?php echo esc_html($item->get_name()); ?></strong>
                                        <?php if ($meta_data): ?>
                                            <span class="ost-product-meta">
                                                <?php echo implode(' • ', array_map(fn($m) => $m->display_key . ': ' . $m->display_value, $meta_data)); ?>
                                            </span>
                                        <?php endif; ?>
                                        <span class="ost-product-qty">Qty: <?php echo esc_html($item->get_quantity()); ?></span>
                                    </div>
                                    <div class="ost-product-price"><?php echo $order->get_formatted_line_subtotal($item); ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <div class="ost-total-row">
                            <span>Total Amount</span>
                            <strong><?php echo $order->get_formatted_order_total(); ?></strong>
                        </div>

                        <div class="ost-reset-container">
                            <a href="<?php echo esc_url(get_permalink()); ?>" class="ost-btn-reset">
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
