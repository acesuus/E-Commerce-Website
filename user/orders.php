<?php
// user/orders.php - Order History Controller
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/session.php';

requireLogin();

$pageTitle = 'My Orders';
$activePage = 'orders';
$user_id = $_SESSION['user_id'];

$view_order_id = intval($_GET['view'] ?? 0);
$order_detail = null;
$order_items = [];

if ($view_order_id > 0) {
    $detail_stmt = $pdo->prepare("SELECT * FROM orders WHERE order_id = ? AND user_id = ?");
    $detail_stmt->execute([$view_order_id, $user_id]);
    $order_detail = $detail_stmt->fetch();

    if ($order_detail) {
        $items_stmt = $pdo->prepare("SELECT oi.*, p.product_name, p.product_image, p.status as product_status, cat.category_name FROM order_items oi JOIN products p ON oi.product_id = p.product_id LEFT JOIN categories cat ON p.category_id = cat.category_id WHERE oi.order_id = ? ORDER BY oi.order_item_id");
        $items_stmt->execute([$view_order_id]);
        $order_items = $items_stmt->fetchAll();
    }
}

$orders_stmt = $pdo->prepare("SELECT o.*, COUNT(oi.order_item_id) as item_count, SUM(oi.quantity) as total_items FROM orders o LEFT JOIN order_items oi ON o.order_id = oi.order_id WHERE o.user_id = ? GROUP BY o.order_id ORDER BY o.order_date DESC");
$orders_stmt->execute([$user_id]);
$orders = $orders_stmt->fetchAll();

$payment_labels = ['cash_on_delivery' => 'Cash on Delivery', 'bank_transfer' => 'Bank Transfer', 'gcash' => 'GCash', 'credit_card' => 'Credit/Debit Card'];
$status_config = [
    'pending' => ['class' => 'status-pending', 'icon' => 'clock', 'label' => 'Pending'],
    'processing' => ['class' => 'status-processing', 'icon' => 'spinner', 'label' => 'Processing'],
    'completed' => ['class' => 'status-completed', 'icon' => 'check-circle', 'label' => 'Completed'],
    'cancelled' => ['class' => 'status-cancelled', 'icon' => 'times-circle', 'label' => 'Cancelled']
];

// Render view
require_once __DIR__ . '/../views/user/orders.view.php';
