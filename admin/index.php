<?php
// admin/index.php - Admin Dashboard Controller
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/session.php';

// Protect admin routes
requireAdmin();

$pageTitle = 'Admin Dashboard';
$activePage = 'dashboard';

// ===== STATISTICS QUERIES =====

// Total users (customers only)
$total_customers = $pdo->query("SELECT COUNT(*) FROM users WHERE user_type = 'customer'")->fetchColumn();

// Total products
$total_products = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$available_products = $pdo->query("SELECT COUNT(*) FROM products WHERE status = 'available'")->fetchColumn();
$out_of_stock = $pdo->query("SELECT COUNT(*) FROM products WHERE status = 'out_of_stock'")->fetchColumn();

// Total orders
$total_orders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$pending_orders = $pdo->query("SELECT COUNT(*) FROM orders WHERE order_status = 'pending'")->fetchColumn();
$processing_orders = $pdo->query("SELECT COUNT(*) FROM orders WHERE order_status = 'processing'")->fetchColumn();
$completed_orders = $pdo->query("SELECT COUNT(*) FROM orders WHERE order_status = 'completed'")->fetchColumn();
$cancelled_orders = $pdo->query("SELECT COUNT(*) FROM orders WHERE order_status = 'cancelled'")->fetchColumn();

// Revenue
$total_revenue = $pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE order_status != 'cancelled'")->fetchColumn();

// Total categories
$total_categories = $pdo->query("SELECT COUNT(*) FROM categories WHERE status = 'active'")->fetchColumn();

// Recent orders (last 5)
$recent_orders = $pdo->query("
    SELECT o.*, u.full_name, u.email,
           COUNT(oi.order_item_id) as item_count
    FROM orders o
    JOIN users u ON o.user_id = u.user_id
    LEFT JOIN order_items oi ON o.order_id = oi.order_id
    GROUP BY o.order_id
    ORDER BY o.order_date DESC
    LIMIT 5
")->fetchAll();

// Low stock products (stock <= 10)
$low_stock_products = $pdo->query("
    SELECT p.*, c.category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.category_id
    WHERE p.stock_quantity <= 10 AND p.status != 'discontinued'
    ORDER BY p.stock_quantity ASC
    LIMIT 5
")->fetchAll();

// Top selling products
$top_products = $pdo->query("
    SELECT p.product_name, p.price, SUM(oi.quantity) as total_sold, 
           SUM(oi.quantity * oi.unit_price) as total_revenue
    FROM order_items oi
    JOIN products p ON oi.product_id = p.product_id
    JOIN orders o ON oi.order_id = o.order_id
    WHERE o.order_status != 'cancelled'
    GROUP BY p.product_id
    ORDER BY total_sold DESC
    LIMIT 5
")->fetchAll();

// Render view
require_once __DIR__ . '/../views/admin/index.view.php';
