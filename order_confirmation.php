<?php
// order_confirmation.php - Order Confirmation Controller
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/session.php';

if (!isLoggedIn()) { header('Location: /user/login.php'); exit; }

$pageTitle = 'Order Confirmation';
$activePage = 'orders';
$user_id = $_SESSION['user_id'];
$order_id = intval($_GET['order'] ?? 0);

if ($order_id <= 0) { header('Location: /index.php'); exit; }

$order_stmt = $pdo->prepare("SELECT * FROM orders WHERE order_id = ? AND user_id = ?");
$order_stmt->execute([$order_id, $user_id]);
$order = $order_stmt->fetch();

if (!$order) { setFlashMessage('error', 'Order not found.'); header('Location: /user/orders.php'); exit; }

$items_stmt = $pdo->prepare("SELECT oi.*, p.product_name, p.product_image, cat.category_name FROM order_items oi JOIN products p ON oi.product_id = p.product_id LEFT JOIN categories cat ON p.category_id = cat.category_id WHERE oi.order_id = ?");
$items_stmt->execute([$order_id]);
$order_items = $items_stmt->fetchAll();

$payment_labels = ['cash_on_delivery' => 'Cash on Delivery', 'bank_transfer' => 'Bank Transfer', 'gcash' => 'GCash', 'credit_card' => 'Credit/Debit Card'];
$payment_display = $payment_labels[$order['payment_method']] ?? ucfirst($order['payment_method']);
$status_classes = ['pending' => 'status-pending', 'processing' => 'status-processing', 'completed' => 'status-completed', 'cancelled' => 'status-cancelled'];
$status_class = $status_classes[$order['order_status']] ?? '';

// Render view
require_once __DIR__ . '/views/order_confirmation.view.php';
