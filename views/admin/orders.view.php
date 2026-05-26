<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - E-Commerce Store</title>
    <link rel="stylesheet" href="<?php echo url('/css/style.css'); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php require_once __DIR__ . '/partials/nav.view.php'; ?>

    <div class="admin-container">

        <?php if ($order_detail): ?>
            <a href="<?php echo url('/admin/orders.php'); ?>" class="btn-back"><i class="fas fa-arrow-left"></i> Back to Orders</a>
            <div class="order-detail-header">
                <div>
                    <h1>Order #<?php echo str_pad($order_detail['order_id'], 6, '0', STR_PAD_LEFT); ?></h1>
                    <p class="order-date-display"><i class="fas fa-calendar-alt"></i> <?php echo date('F j, Y \a\t g:i A', strtotime($order_detail['order_date'])); ?></p>
                </div>
                <div style="display:flex; align-items:center; gap:12px;">
                    <?php $sc = $status_config[$order_detail['order_status']] ?? $status_config['pending']; ?>
                    <span class="order-status <?php echo $sc['class']; ?>"><i class="fas fa-<?php echo $sc['icon']; ?>"></i> <?php echo $sc['label']; ?></span>
                    <form method="POST" action="<?php echo url('/admin/orders.php'); ?>" class="status-form">
                        <input type="hidden" name="action" value="update_status">
                        <input type="hidden" name="order_id" value="<?php echo $order_detail['order_id']; ?>">
                        <select name="new_status" class="status-select" onchange="this.form.submit()">
                            <option value="">Update...</option>
                            <?php foreach (['pending','processing','completed','cancelled'] as $s): ?>
                                <option value="<?php echo $s; ?>" <?php echo $order_detail['order_status'] === $s ? 'disabled' : ''; ?>><?php echo ucfirst($s); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>
            </div>

            <div class="order-detail-layout">
                <div class="order-detail-card">
                    <h2><i class="fas fa-box"></i> Items (<?php echo count($order_items); ?>)</h2>
                    <div class="order-items-list">
                        <?php foreach ($order_items as $item): ?>
                            <div class="order-item-row">
                                <div class="order-item-thumb"><?php if ($item['product_image']): ?><img src="<?php echo url('/' . htmlspecialchars($item['product_image'])); ?>" alt=""><?php else: ?><i class="fas fa-box-open"></i><?php endif; ?></div>
                                <div class="order-item-info">
                                    <span class="order-item-name"><?php echo htmlspecialchars($item['product_name']); ?></span>
                                    <span class="order-item-category"><?php echo htmlspecialchars($item['category_name'] ?? ''); ?></span>
                                    <span class="order-item-unit">$<?php echo number_format($item['unit_price'], 2); ?> x <?php echo $item['quantity']; ?></span>
                                </div>
                                <div class="order-item-subtotal">$<?php echo number_format($item['unit_price'] * $item['quantity'], 2); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="order-total-section">
                        <div class="order-total-row"><span>Subtotal</span><span>$<?php echo number_format($order_detail['total_amount'], 2); ?></span></div>
                        <div class="order-total-row"><span>Shipping</span><span class="free-text">Free</span></div>
                        <div class="order-total-row total-final"><span>Total</span><span>$<?php echo number_format($order_detail['total_amount'], 2); ?></span></div>
                    </div>
                </div>
                <div class="order-info-sidebar">
                    <div class="order-detail-card"><h2><i class="fas fa-user"></i> Customer</h2><div class="info-block"><span class="info-label">Name</span><p class="info-value"><?php echo htmlspecialchars($order_detail['full_name']); ?></p></div><div class="info-block"><span class="info-label">Email</span><p class="info-value"><?php echo htmlspecialchars($order_detail['email']); ?></p></div><div class="info-block"><span class="info-label">Phone</span><p class="info-value"><?php echo htmlspecialchars($order_detail['phone'] ?? 'N/A'); ?></p></div></div>
                    <div class="order-detail-card"><h2><i class="fas fa-truck"></i> Shipping</h2><div class="info-block"><span class="info-label">Address</span><p class="info-value"><?php echo nl2br(htmlspecialchars($order_detail['shipping_address'])); ?></p></div></div>
                    <div class="order-detail-card"><h2><i class="fas fa-credit-card"></i> Payment</h2><div class="info-block"><span class="info-label">Method</span><p class="info-value"><?php echo $payment_labels[$order_detail['payment_method']] ?? $order_detail['payment_method']; ?></p></div></div>
                    <?php if ($order_detail['notes']): ?><div class="order-detail-card"><h2><i class="fas fa-sticky-note"></i> Notes</h2><p class="info-value"><?php echo htmlspecialchars($order_detail['notes']); ?></p></div><?php endif; ?>
                </div>
            </div>

        <?php else: ?>
            <div class="admin-welcome">
                <div><h1>Orders</h1><p>Monitor and manage customer orders</p></div>
                <span class="admin-badge">$<?php echo number_format($stats['revenue'], 2); ?> revenue</span>
            </div>

            <?php if ($flash): ?>
                <div class="alert alert-<?php echo $flash['type']; ?>"><i class="fas fa-<?php echo $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i> <span><?php echo $flash['message']; ?></span></div>
            <?php endif; ?>

            <div class="admin-status-bar">
                <a href="<?php echo url('/admin/orders.php'); ?>" class="status-item <?php echo empty($status_filter) ? 'stock' : ''; ?>" style="text-decoration:none;"><span class="status-count"><?php echo $stats['total']; ?></span><span class="status-name">All</span></a>
                <a href="<?php echo url('/admin/orders.php?status=pending'); ?>" class="status-item pending" style="text-decoration:none;<?php echo $status_filter === 'pending' ? 'border:2px solid #d69e2e;' : ''; ?>"><span class="status-count"><?php echo $stats['pending']; ?></span><span class="status-name">Pending</span></a>
                <a href="<?php echo url('/admin/orders.php?status=processing'); ?>" class="status-item processing" style="text-decoration:none;<?php echo $status_filter === 'processing' ? 'border:2px solid #3182ce;' : ''; ?>"><span class="status-count"><?php echo $stats['processing']; ?></span><span class="status-name">Processing</span></a>
                <a href="<?php echo url('/admin/orders.php?status=completed'); ?>" class="status-item completed" style="text-decoration:none;<?php echo $status_filter === 'completed' ? 'border:2px solid #38a169;' : ''; ?>"><span class="status-count"><?php echo $stats['completed']; ?></span><span class="status-name">Completed</span></a>
                <a href="<?php echo url('/admin/orders.php?status=cancelled'); ?>" class="status-item cancelled" style="text-decoration:none;<?php echo $status_filter === 'cancelled' ? 'border:2px solid #e53e3e;' : ''; ?>"><span class="status-count"><?php echo $stats['cancelled']; ?></span><span class="status-name">Cancelled</span></a>
            </div>

            <div class="admin-card">
                <div class="admin-card-header">
                    <h3><i class="fas fa-list"></i> <?php echo !empty($status_filter) ? ucfirst($status_filter) . ' Orders' : 'All Orders'; ?> (<?php echo count($orders); ?>)</h3>
                    <?php if (!empty($status_filter)): ?><a href="<?php echo url('/admin/orders.php'); ?>" class="card-link"><i class="fas fa-times"></i> Clear</a><?php endif; ?>
                </div>
                <table class="admin-table">
                    <thead><tr><th>Order</th><th>Customer</th><th>Items</th><th>Total</th><th>Payment</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
                    <tbody>
                        <?php if (!empty($orders)): ?>
                            <?php foreach ($orders as $order): ?>
                                <?php $sc = $status_config[$order['order_status']] ?? $status_config['pending']; ?>
                                <tr>
                                    <td><strong>#<?php echo str_pad($order['order_id'], 6, '0', STR_PAD_LEFT); ?></strong></td>
                                    <td><div><?php echo htmlspecialchars($order['full_name']); ?></div><small style="color:#718096;"><?php echo htmlspecialchars($order['email']); ?></small></td>
                                    <td><?php echo $order['total_items'] ?? $order['item_count']; ?></td>
                                    <td><strong>$<?php echo number_format($order['total_amount'], 2); ?></strong></td>
                                    <td><?php echo $payment_short[$order['payment_method']] ?? $order['payment_method']; ?></td>
                                    <td><span class="order-status <?php echo $sc['class']; ?>"><i class="fas fa-<?php echo $sc['icon']; ?>"></i> <?php echo $sc['label']; ?></span></td>
                                    <td><?php echo date('M j, Y', strtotime($order['order_date'])); ?></td>
                                    <td>
                                        <div class="table-actions">
                                            <a href="<?php echo url('/admin/orders.php?view=' . $order['order_id']); ?>" class="action-btn action-edit" title="View"><i class="fas fa-eye"></i></a>
                                            <form method="POST" action="<?php echo url('/admin/orders.php'); ?>" class="status-form">
                                                <input type="hidden" name="action" value="update_status">
                                                <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                                <input type="hidden" name="filter" value="<?php echo htmlspecialchars($status_filter); ?>">
                                                <select name="new_status" class="status-select" onchange="this.form.submit()">
                                                    <option value="">Status...</option>
                                                    <?php foreach (['pending','processing','completed','cancelled'] as $s): ?>
                                                        <option value="<?php echo $s; ?>" <?php echo $order['order_status'] === $s ? 'disabled' : ''; ?>><?php echo ucfirst($s); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="8" class="admin-empty">No <?php echo $status_filter ?: ''; ?> orders found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <?php require_once __DIR__ . '/../../includes/footer.php'; ?>
</body>
</html>
