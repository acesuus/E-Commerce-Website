<?php
// user/orders.php - Customer Order History
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/session.php';

// Require login
requireLogin();

$pageTitle = 'My Orders';
$user_id = $_SESSION['user_id'];

// Get selected order for detail view
$view_order_id = intval($_GET['view'] ?? 0);
$order_detail = null;
$order_items = [];

// If viewing a specific order
if ($view_order_id > 0) {
    $detail_stmt = $pdo->prepare("SELECT * FROM orders WHERE order_id = ? AND user_id = ?");
    $detail_stmt->execute([$view_order_id, $user_id]);
    $order_detail = $detail_stmt->fetch();

    if ($order_detail) {
        $items_stmt = $pdo->prepare("
            SELECT oi.*, p.product_name, p.product_image, p.status as product_status,
                   cat.category_name
            FROM order_items oi
            JOIN products p ON oi.product_id = p.product_id
            LEFT JOIN categories cat ON p.category_id = cat.category_id
            WHERE oi.order_id = ?
            ORDER BY oi.order_item_id
        ");
        $items_stmt->execute([$view_order_id]);
        $order_items = $items_stmt->fetchAll();
    }
}

// Fetch all orders for this user
$orders_stmt = $pdo->prepare("
    SELECT o.*, 
           COUNT(oi.order_item_id) as item_count,
           SUM(oi.quantity) as total_items
    FROM orders o
    LEFT JOIN order_items oi ON o.order_id = oi.order_id
    WHERE o.user_id = ?
    GROUP BY o.order_id
    ORDER BY o.order_date DESC
");
$orders_stmt->execute([$user_id]);
$orders = $orders_stmt->fetchAll();

// Payment method labels
$payment_labels = [
    'cash_on_delivery' => 'Cash on Delivery',
    'bank_transfer' => 'Bank Transfer',
    'gcash' => 'GCash',
    'credit_card' => 'Credit/Debit Card'
];

// Status display config
$status_config = [
    'pending' => ['class' => 'status-pending', 'icon' => 'clock', 'label' => 'Pending'],
    'processing' => ['class' => 'status-processing', 'icon' => 'spinner', 'label' => 'Processing'],
    'completed' => ['class' => 'status-completed', 'icon' => 'check-circle', 'label' => 'Completed'],
    'cancelled' => ['class' => 'status-cancelled', 'icon' => 'times-circle', 'label' => 'Cancelled']
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - E-Commerce Store</title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <a href="/index.php" class="brand"><i class="fas fa-store"></i> E-Store</a>
            <nav class="nav-store">
                <a href="/index.php">Home</a>
                <a href="/products.php">Products</a>
                <a href="/cart.php"><i class="fas fa-shopping-cart"></i> Cart</a>
                <div class="nav-user">
                    <span>Hi, <?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                    <a href="/user/orders.php" class="active">My Orders</a>
                    <a href="/user/logout.php" class="btn-nav-outline">Logout</a>
                </div>
            </nav>
        </div>
    </header>

    <div class="page-container">

        <?php if ($order_detail): ?>
            <!-- =============================================
                 ORDER DETAIL VIEW
                 ============================================= -->
            <a href="/user/orders.php" class="btn-back">
                <i class="fas fa-arrow-left"></i> Back to Orders
            </a>

            <div class="order-detail-header">
                <div>
                    <h1>Order #<?php echo str_pad($order_detail['order_id'], 6, '0', STR_PAD_LEFT); ?></h1>
                    <p class="order-date-display">
                        <i class="fas fa-calendar-alt"></i>
                        Placed on <?php echo date('F j, Y \a\t g:i A', strtotime($order_detail['order_date'])); ?>
                    </p>
                </div>
                <?php $sc = $status_config[$order_detail['order_status']] ?? $status_config['pending']; ?>
                <span class="order-status <?php echo $sc['class']; ?>">
                    <i class="fas fa-<?php echo $sc['icon']; ?>"></i>
                    <?php echo $sc['label']; ?>
                </span>
            </div>

            <div class="order-detail-layout">
                <!-- Order Items -->
                <div class="order-detail-card">
                    <h2><i class="fas fa-box"></i> Items Ordered (<?php echo count($order_items); ?>)</h2>
                    <div class="order-items-list">
                        <?php foreach ($order_items as $item): ?>
                            <div class="order-item-row">
                                <div class="order-item-thumb">
                                    <?php if (!empty($item['product_image'])): ?>
                                        <img src="/<?php echo htmlspecialchars($item['product_image']); ?>" alt="">
                                    <?php else: ?>
                                        <i class="fas fa-box-open"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="order-item-info">
                                    <a href="/product_detail.php?id=<?php echo $item['product_id']; ?>" class="order-item-name">
                                        <?php echo htmlspecialchars($item['product_name']); ?>
                                    </a>
                                    <span class="order-item-category">
                                        <?php echo htmlspecialchars($item['category_name'] ?? 'Uncategorized'); ?>
                                    </span>
                                    <span class="order-item-unit">
                                        $<?php echo number_format($item['unit_price'], 2); ?> x <?php echo $item['quantity']; ?>
                                    </span>
                                </div>
                                <div class="order-item-subtotal">
                                    $<?php echo number_format($item['unit_price'] * $item['quantity'], 2); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Order Total -->
                    <div class="order-total-section">
                        <div class="order-total-row">
                            <span>Subtotal</span>
                            <span>$<?php echo number_format($order_detail['total_amount'], 2); ?></span>
                        </div>
                        <div class="order-total-row">
                            <span>Shipping</span>
                            <span class="free-text">Free</span>
                        </div>
                        <div class="order-total-row total-final">
                            <span>Total</span>
                            <span>$<?php echo number_format($order_detail['total_amount'], 2); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Order Info Sidebar -->
                <div class="order-info-sidebar">
                    <!-- Shipping Info -->
                    <div class="order-detail-card">
                        <h2><i class="fas fa-truck"></i> Shipping</h2>
                        <div class="info-block">
                            <span class="info-label">Address</span>
                            <p class="info-value"><?php echo nl2br(htmlspecialchars($order_detail['shipping_address'])); ?></p>
                        </div>
                    </div>

                    <!-- Payment Info -->
                    <div class="order-detail-card">
                        <h2><i class="fas fa-credit-card"></i> Payment</h2>
                        <div class="info-block">
                            <span class="info-label">Method</span>
                            <p class="info-value">
                                <?php echo $payment_labels[$order_detail['payment_method']] ?? ucfirst($order_detail['payment_method']); ?>
                            </p>
                        </div>
                        <div class="info-block">
                            <span class="info-label">Status</span>
                            <p class="info-value">
                                <?php echo $order_detail['order_status'] === 'completed' ? 'Paid' : 'Pending'; ?>
                            </p>
                        </div>
                    </div>

                    <!-- Notes -->
                    <?php if (!empty($order_detail['notes'])): ?>
                        <div class="order-detail-card">
                            <h2><i class="fas fa-sticky-note"></i> Notes</h2>
                            <p class="info-value"><?php echo htmlspecialchars($order_detail['notes']); ?></p>
                        </div>
                    <?php endif; ?>

                    <!-- Timestamps -->
                    <div class="order-detail-card">
                        <h2><i class="fas fa-clock"></i> Timeline</h2>
                        <div class="timeline">
                            <div class="timeline-item">
                                <div class="timeline-dot active"></div>
                                <div class="timeline-content">
                                    <span class="timeline-label">Order Placed</span>
                                    <span class="timeline-date">
                                        <?php echo date('M j, Y g:i A', strtotime($order_detail['order_date'])); ?>
                                    </span>
                                </div>
                            </div>
                            <?php if (in_array($order_detail['order_status'], ['processing', 'completed'])): ?>
                                <div class="timeline-item">
                                    <div class="timeline-dot active"></div>
                                    <div class="timeline-content">
                                        <span class="timeline-label">Processing</span>
                                        <span class="timeline-date">Order is being prepared</span>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <?php if ($order_detail['order_status'] === 'completed'): ?>
                                <div class="timeline-item">
                                    <div class="timeline-dot active"></div>
                                    <div class="timeline-content">
                                        <span class="timeline-label">Completed</span>
                                        <span class="timeline-date">Order delivered successfully</span>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <?php if ($order_detail['order_status'] === 'cancelled'): ?>
                                <div class="timeline-item">
                                    <div class="timeline-dot cancelled"></div>
                                    <div class="timeline-content">
                                        <span class="timeline-label">Cancelled</span>
                                        <span class="timeline-date">Order was cancelled</span>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <!-- =============================================
                 ORDER LIST VIEW
                 ============================================= -->
            <div class="page-header">
                <h1><i class="fas fa-list-alt" style="color:#667eea;"></i> My Orders</h1>
                <p>View your purchase history and track order status</p>
            </div>

            <?php if (!empty($orders)): ?>
                <!-- Order Stats -->
                <div class="order-stats">
                    <div class="stat-card">
                        <i class="fas fa-shopping-bag"></i>
                        <div>
                            <span class="stat-number"><?php echo count($orders); ?></span>
                            <span class="stat-label">Total Orders</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-check-circle"></i>
                        <div>
                            <span class="stat-number"><?php echo count(array_filter($orders, fn($o) => $o['order_status'] === 'completed')); ?></span>
                            <span class="stat-label">Completed</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-clock"></i>
                        <div>
                            <span class="stat-number"><?php echo count(array_filter($orders, fn($o) => in_array($o['order_status'], ['pending', 'processing']))); ?></span>
                            <span class="stat-label">In Progress</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-dollar-sign"></i>
                        <div>
                            <span class="stat-number">$<?php echo number_format(array_sum(array_column($orders, 'total_amount')), 2); ?></span>
                            <span class="stat-label">Total Spent</span>
                        </div>
                    </div>
                </div>

                <!-- Orders List -->
                <div class="orders-list">
                    <?php foreach ($orders as $order): ?>
                        <?php $sc = $status_config[$order['order_status']] ?? $status_config['pending']; ?>
                        <div class="order-card">
                            <div class="order-card-header">
                                <div class="order-card-id">
                                    <h3>Order #<?php echo str_pad($order['order_id'], 6, '0', STR_PAD_LEFT); ?></h3>
                                    <span class="order-card-date">
                                        <i class="fas fa-calendar-alt"></i>
                                        <?php echo date('M j, Y \a\t g:i A', strtotime($order['order_date'])); ?>
                                    </span>
                                </div>
                                <span class="order-status <?php echo $sc['class']; ?>">
                                    <i class="fas fa-<?php echo $sc['icon']; ?>"></i>
                                    <?php echo $sc['label']; ?>
                                </span>
                            </div>

                            <div class="order-card-body">
                                <div class="order-card-meta">
                                    <div class="meta-pill">
                                        <i class="fas fa-box"></i>
                                        <?php echo $order['total_items'] ?? $order['item_count']; ?> item<?php echo ($order['total_items'] ?? $order['item_count']) != 1 ? 's' : ''; ?>
                                    </div>
                                    <div class="meta-pill">
                                        <i class="fas fa-credit-card"></i>
                                        <?php echo $payment_labels[$order['payment_method']] ?? ucfirst($order['payment_method']); ?>
                                    </div>
                                    <div class="meta-pill">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <?php echo htmlspecialchars(mb_strimwidth($order['shipping_address'], 0, 40, '...')); ?>
                                    </div>
                                </div>
                            </div>

                            <div class="order-card-footer">
                                <span class="order-card-total">
                                    Total: <strong>$<?php echo number_format($order['total_amount'], 2); ?></strong>
                                </span>
                                <a href="/user/orders.php?view=<?php echo $order['order_id']; ?>" class="btn-view-order">
                                    <i class="fas fa-eye"></i> View Details
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

            <?php else: ?>
                <!-- No Orders -->
                <div class="empty-orders">
                    <i class="fas fa-receipt"></i>
                    <h3>No orders yet</h3>
                    <p>You haven't placed any orders. Start shopping to see your order history here!</p>
                    <a href="/products.php" class="btn-shop-now">
                        <i class="fas fa-shopping-bag"></i> Browse Products
                    </a>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <?php require_once __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
