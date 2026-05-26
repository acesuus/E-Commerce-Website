<?php
// admin/orders.php - Admin Orders Management Controller
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/session.php';

requireAdmin();

$pageTitle = 'Manage Orders';
$activePage = 'orders';
$flash = getFlashMessage();

// Handle status update (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $order_id = intval($_POST['order_id'] ?? 0);
    $new_status = $_POST['new_status'] ?? '';
    $allowed = ['pending', 'processing', 'completed', 'cancelled'];

    if ($order_id > 0 && in_array($new_status, $allowed)) {
        $stmt = $pdo->prepare("UPDATE orders SET order_status = ? WHERE order_id = ?");
        $stmt->execute([$new_status, $order_id]);
        setFlashMessage('success', "Order #" . str_pad($order_id, 6, '0', STR_PAD_LEFT) . " status updated to " . ucfirst($new_status) . ".");
    } else {
        setFlashMessage('error', 'Invalid order or status.');
    }
    $redirect = url('/admin/orders.php');
    if (!empty($_POST['filter'])) { $redirect .= '?status=' . urlencode($_POST['filter']); }
    header('Location: ' . $redirect);
    exit;
}

// View single order detail
$view_order_id = intval($_GET['view'] ?? 0);
$order_detail = null;
$order_items = [];

if ($view_order_id > 0) {
    $stmt = $pdo->prepare("SELECT o.*, u.full_name, u.email, u.phone, u.address FROM orders o JOIN users u ON o.user_id = u.user_id WHERE o.order_id = ?");
    $stmt->execute([$view_order_id]);
    $order_detail = $stmt->fetch();

    if ($order_detail) {
        $items_stmt = $pdo->prepare("SELECT oi.*, p.product_name, p.product_image, cat.category_name FROM order_items oi JOIN products p ON oi.product_id = p.product_id LEFT JOIN categories cat ON p.category_id = cat.category_id WHERE oi.order_id = ?");
        $items_stmt->execute([$view_order_id]);
        $order_items = $items_stmt->fetchAll();
    }
}

// Status filter
$status_filter = $_GET['status'] ?? '';
$allowed_statuses = ['pending', 'processing', 'completed', 'cancelled'];

$where = "";
$params = [];
if (!empty($status_filter) && in_array($status_filter, $allowed_statuses)) {
    $where = "WHERE o.order_status = ?";
    $params[] = $status_filter;
}

$orders_stmt = $pdo->prepare("
    SELECT o.*, u.full_name, u.email, COUNT(oi.order_item_id) as item_count, SUM(oi.quantity) as total_items
    FROM orders o JOIN users u ON o.user_id = u.user_id LEFT JOIN order_items oi ON o.order_id = oi.order_id
    {$where} GROUP BY o.order_id ORDER BY o.order_date DESC
");
$orders_stmt->execute($params);
$orders = $orders_stmt->fetchAll();

// Stats
$stats = [
    'total' => $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn(),
    'pending' => $pdo->query("SELECT COUNT(*) FROM orders WHERE order_status = 'pending'")->fetchColumn(),
    'processing' => $pdo->query("SELECT COUNT(*) FROM orders WHERE order_status = 'processing'")->fetchColumn(),
    'completed' => $pdo->query("SELECT COUNT(*) FROM orders WHERE order_status = 'completed'")->fetchColumn(),
    'cancelled' => $pdo->query("SELECT COUNT(*) FROM orders WHERE order_status = 'cancelled'")->fetchColumn(),
    'revenue' => $pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE order_status != 'cancelled'")->fetchColumn(),
];

$payment_labels = ['cash_on_delivery' => 'Cash on Delivery', 'bank_transfer' => 'Bank Transfer', 'gcash' => 'GCash', 'credit_card' => 'Credit/Debit Card'];
$payment_short = ['cash_on_delivery' => 'COD', 'bank_transfer' => 'Bank', 'gcash' => 'GCash', 'credit_card' => 'Card'];
$status_config = [
    'pending' => ['class' => 'status-pending', 'icon' => 'clock', 'label' => 'Pending'],
    'processing' => ['class' => 'status-processing', 'icon' => 'spinner', 'label' => 'Processing'],
    'completed' => ['class' => 'status-completed', 'icon' => 'check-circle', 'label' => 'Completed'],
    'cancelled' => ['class' => 'status-cancelled', 'icon' => 'times-circle', 'label' => 'Cancelled']
];

require_once __DIR__ . '/../views/admin/orders.view.php';
