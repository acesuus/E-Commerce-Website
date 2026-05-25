<?php
// products.php - Product Listing Controller
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/session.php';

$pageTitle = 'Products';
$activePage = 'products';

// Get filter parameters
$search = trim($_GET['search'] ?? '');
$category_id = intval($_GET['category'] ?? 0);
$sort = $_GET['sort'] ?? 'newest';
$status_filter = $_GET['status'] ?? '';

// Fetch categories for filter dropdown
$categories = $pdo->query("SELECT * FROM categories WHERE status = 'active' ORDER BY category_name")->fetchAll();

// Build product query
$where_clauses = [];
$params = [];

if (!empty($search)) {
    $where_clauses[] = "(p.product_name LIKE ? OR p.description LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}
if ($category_id > 0) {
    $where_clauses[] = "p.category_id = ?";
    $params[] = $category_id;
}
if (!empty($status_filter) && in_array($status_filter, ['available', 'out_of_stock'])) {
    $where_clauses[] = "p.status = ?";
    $params[] = $status_filter;
} else {
    $where_clauses[] = "p.status != 'discontinued'";
}

$where_sql = !empty($where_clauses) ? 'WHERE ' . implode(' AND ', $where_clauses) : '';

$order_sql = match($sort) {
    'price_low' => 'ORDER BY p.price ASC',
    'price_high' => 'ORDER BY p.price DESC',
    'name_az' => 'ORDER BY p.product_name ASC',
    'name_za' => 'ORDER BY p.product_name DESC',
    default => 'ORDER BY p.created_at DESC'
};

$stmt = $pdo->prepare("SELECT p.*, c.category_name FROM products p LEFT JOIN categories c ON p.category_id = c.category_id {$where_sql} {$order_sql}");
$stmt->execute($params);
$products = $stmt->fetchAll();
$product_count = count($products);

$active_category_name = '';
if ($category_id > 0) {
    foreach ($categories as $cat) {
        if ($cat['category_id'] == $category_id) { $active_category_name = $cat['category_name']; break; }
    }
}

// Render view
require_once __DIR__ . '/views/products.view.php';
