<?php
// order_confirmation.php - Order Success / Confirmation Page
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/session.php';

// Require login
if (!isLoggedIn()) {
    header('Location: /user/login.php');
    exit;
}

$pageTitle = 'Order Confirmation';
$user_id = $_SESSION['user_id'];
$order_id = intval($_GET['order'] ?? 0);

// Validate order belongs to this user
if ($order_id <= 0) {
    header('Location: /index.php');
    exit;
}

$order_stmt = $pdo->prepare("SELECT * FROM orders WHERE order_id = ? AND user_id = ?");
$order_stmt->execute([$order_id, $user_id]);
$order = $order_stmt->fetch();

if (!$order) {
    setFlashMessage('error', 'Order not found.');
    header('Location: /user/orders.php');
    exit;
}

// Fetch order items
$items_stmt = $pdo->prepare("
    SELECT oi.*, p.product_name, p.product_image, cat.category_name
    FROM order_items oi
    JOIN products p ON oi.product_id = p.product_id
    LEFT JOIN categories cat ON p.category_id = cat.category_id
    WHERE oi.order_id = ?
");
$items_stmt->execute([$order_id]);
$order_items = $items_stmt->fetchAll();

// Format payment method display
$payment_labels = [
    'cash_on_delivery' => 'Cash on Delivery',
    'bank_transfer' => 'Bank Transfer',
    'gcash' => 'GCash',
    'credit_card' => 'Credit/Debit Card'
];
$payment_display = $payment_labels[$order['payment_method']] ?? ucfirst($order['payment_method']);

// Format status
$status_classes = [
    'pending' => 'status-pending',
    'processing' => 'status-processing',
    'completed' => 'status-completed',
    'cancelled' => 'status-cancelled'
];
$status_class = $status_classes[$order['order_status']] ?? '';
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
        <!-- Checkout Progress - Completed -->
        <div class="checkout-progress">
            <div class="progress-step completed"><i class="fas fa-shopping-cart"></i> Cart</div>
            <div class="progress-line active"></div>
            <div class="progress-step completed"><i class="fas fa-clipboard-list"></i> Checkout</div>
            <div class="progress-line active"></div>
            <div class="progress-step completed"><i class="fas fa-check-circle"></i> Confirmation</div>
        </div>

        <!-- Success Banner -->
        <div class="order-success-banner">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h1>Order Placed Successfully!</h1>
            <p>Thank you for your purchase. Your order has been received and is being processed.</p>
            <div class="order-number">
                Order #<?php echo str_pad($order_id, 6, '0', STR_PAD_LEFT); ?>
            </div>
        </div>

        <!-- Order Details Card -->
        <div class="confirmation-layout">
            <!-- Order Info -->
            <div class="confirmation-card">
                <h2><i class="fas fa-info-circle"></i> Order Details</h2>
                
                <div class="order-detail-grid">
                    <div class="order-detail-item">
                        <span class="label">Order Number</span>
                        <span class="value">#<?php echo str_pad($order_id, 6, '0', STR_PAD_LEFT); ?></span>
                    </div>
                    <div class="order-detail-item">
                        <span class="label">Order Date</span>
                        <span class="value"><?php echo date('F j, Y \a\t g:i A', strtotime($order['order_date'])); ?></span>
                    </div>
                    <div class="order-detail-item">
                        <span class="label">Status</span>
                        <span class="value"><span class="order-status <?php echo $status_class; ?>"><?php echo ucfirst($order['order_status']); ?></span></span>
                    </div>
                    <div class="order-detail-item">
                        <span class="label">Payment Method</span>
                        <span class="value"><?php echo htmlspecialchars($payment_display); ?></span>
                    </div>
                    <div class="order-detail-item">
                        <span class="label">Shipping Address</span>
                        <span class="value"><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></span>
                    </div>
                    <?php if (!empty($order['notes'])): ?>
                    <div class="order-detail-item">
                        <span class="label">Notes</span>
                        <span class="value"><?php echo htmlspecialchars($order['notes']); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Order Items -->
            <div class="confirmation-card">
                <h2><i class="fas fa-box"></i> Items Ordered</h2>

                <div class="confirmation-items">
                    <?php foreach ($order_items as $item): ?>
                        <div class="confirmation-item">
                            <div class="conf-item-thumb">
                                <?php if (!empty($item['product_image'])): ?>
                                    <img src="/<?php echo htmlspecialchars($item['product_image']); ?>" alt="">
                                <?php else: ?>
                                    <i class="fas fa-box-open"></i>
                                <?php endif; ?>
                            </div>
                            <div class="conf-item-info">
                                <span class="conf-item-name"><?php echo htmlspecialchars($item['product_name']); ?></span>
                                <span class="conf-item-meta">
                                    <?php echo htmlspecialchars($item['category_name'] ?? ''); ?> | 
                                    Qty: <?php echo $item['quantity']; ?> x $<?php echo number_format($item['unit_price'], 2); ?>
                                </span>
                            </div>
                            <div class="conf-item-price">
                                $<?php echo number_format($item['quantity'] * $item['unit_price'], 2); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Order Total -->
                <div class="summary-divider"></div>
                <div class="summary-row summary-total">
                    <span>Order Total</span>
                    <span>$<?php echo number_format($order['total_amount'], 2); ?></span>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="confirmation-actions">
            <a href="/user/orders.php" class="btn-view-orders">
                <i class="fas fa-list"></i> View All Orders
            </a>
            <a href="/products.php" class="btn-continue-shopping-conf">
                <i class="fas fa-shopping-bag"></i> Continue Shopping
            </a>
        </div>
    </div>

    <?php require_once __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
