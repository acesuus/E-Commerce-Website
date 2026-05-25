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
        <!-- Welcome Header -->
        <div class="admin-welcome">
            <div>
                <h1>Dashboard</h1>
                <p>Welcome back, <?php echo htmlspecialchars($_SESSION['full_name']); ?>. Here's your store overview.</p>
            </div>
            <span class="admin-date"><?php echo date('F j, Y'); ?></span>
        </div>

        <!-- Stats Cards -->
        <div class="admin-stats-grid">
            <div class="admin-stat-card stat-revenue">
                <div class="stat-icon"></div>
                <div class="stat-info">
                    <span class="stat-value">$<?php echo number_format($total_revenue, 2); ?></span>
                    <span class="stat-label">Total Revenue</span>
                </div>
            </div>
            <div class="admin-stat-card stat-orders">
                <div class="stat-icon"></div>
                <div class="stat-info">
                    <span class="stat-value"><?php echo $total_orders; ?></span>
                    <span class="stat-label">Total Orders</span>
                </div>
            </div>
            <div class="admin-stat-card stat-products">
                <div class="stat-icon"></div>
                <div class="stat-info">
                    <span class="stat-value"><?php echo $total_products; ?></span>
                    <span class="stat-label">Products</span>
                </div>
            </div>
            <div class="admin-stat-card stat-customers">
                <div class="stat-icon"></div>
                <div class="stat-info">
                    <span class="stat-value"><?php echo $total_customers; ?></span>
                    <span class="stat-label">Customers</span>
                </div>
            </div>
        </div>

        <!-- Order Status Breakdown -->
        <div class="admin-status-bar">
            <div class="status-item pending"><span class="status-count"><?php echo $pending_orders; ?></span><span class="status-name">Pending</span></div>
            <div class="status-item processing"><span class="status-count"><?php echo $processing_orders; ?></span><span class="status-name">Processing</span></div>
            <div class="status-item completed"><span class="status-count"><?php echo $completed_orders; ?></span><span class="status-name">Completed</span></div>
            <div class="status-item cancelled"><span class="status-count"><?php echo $cancelled_orders; ?></span><span class="status-name">Cancelled</span></div>
            <div class="status-item stock"><span class="status-count"><?php echo $available_products; ?> / <?php echo $out_of_stock; ?></span><span class="status-name">In Stock / Out</span></div>
            <div class="status-item categories"><span class="status-count"><?php echo $total_categories; ?></span><span class="status-name">Categories</span></div>
        </div>

        <!-- Dashboard Content Grid -->
        <div class="admin-grid">
            <!-- Recent Orders -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h3>Recent Orders</h3>
                    <a href="<?php echo url('/admin/orders.php'); ?>" class="card-link">View All</a>
                </div>
                <?php if (!empty($recent_orders)): ?>
                    <table class="admin-table">
                        <thead><tr><th>Order</th><th>Customer</th><th>Amount</th><th>Status</th><th>Date</th></tr></thead>
                        <tbody>
                            <?php foreach ($recent_orders as $order): ?>
                                <tr>
                                    <td><strong>#<?php echo str_pad($order['order_id'], 6, '0', STR_PAD_LEFT); ?></strong></td>
                                    <td><?php echo htmlspecialchars($order['full_name']); ?></td>
                                    <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                    <td><span class="order-status <?php echo 'status-' . $order['order_status']; ?>"><?php echo ucfirst($order['order_status']); ?></span></td>
                                    <td><?php echo date('M j, g:i A', strtotime($order['order_date'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="admin-empty">No orders yet.</p>
                <?php endif; ?>
            </div>

            <!-- Sidebar Panels -->
            <div class="admin-sidebar">
                <!-- Low Stock Alert -->
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h3>Low Stock</h3>
                        <a href="<?php echo url('/admin/products.php'); ?>" class="card-link">Manage</a>
                    </div>
                    <?php if (!empty($low_stock_products)): ?>
                        <div class="admin-list">
                            <?php foreach ($low_stock_products as $prod): ?>
                                <div class="admin-list-item">
                                    <div>
                                        <span class="list-title"><?php echo htmlspecialchars($prod['product_name']); ?></span>
                                        <span class="list-sub"><?php echo htmlspecialchars($prod['category_name'] ?? 'Uncategorized'); ?></span>
                                    </div>
                                    <span class="list-badge <?php echo $prod['stock_quantity'] <= 0 ? 'badge-danger' : 'badge-warning'; ?>">
                                        <?php echo $prod['stock_quantity']; ?> left
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="admin-empty">All products well-stocked!</p>
                    <?php endif; ?>
                </div>

                <!-- Top Selling -->
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h3>Top Sellers</h3>
                    </div>
                    <?php if (!empty($top_products)): ?>
                        <div class="admin-list">
                            <?php foreach ($top_products as $i => $tp): ?>
                                <div class="admin-list-item">
                                    <div>
                                        <span class="list-rank">#<?php echo $i + 1; ?></span>
                                        <span class="list-title"><?php echo htmlspecialchars($tp['product_name']); ?></span>
                                    </div>
                                    <span class="list-badge badge-success"><?php echo $tp['total_sold']; ?> sold</span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="admin-empty">No sales data yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php require_once __DIR__ . '/../../includes/footer.php'; ?>
</body>
</html>
