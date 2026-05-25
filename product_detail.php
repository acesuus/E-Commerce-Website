<?php
// product_detail.php - Product Detail Controller
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/session.php';

$activePage = 'products';
$product_id = intval($_GET['id'] ?? 0);

if ($product_id <= 0) { header('Location: /products.php'); exit; }

$stmt = $pdo->prepare("SELECT p.*, c.category_name FROM products p LEFT JOIN categories c ON p.category_id = c.category_id WHERE p.product_id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) { header('Location: /products.php'); exit; }

$pageTitle = $product['product_name'];

// Fetch related products
$related_stmt = $pdo->prepare("SELECT p.*, c.category_name FROM products p LEFT JOIN categories c ON p.category_id = c.category_id WHERE p.category_id = ? AND p.product_id != ? AND p.status != 'discontinued' ORDER BY RAND() LIMIT 4");
$related_stmt->execute([$product['category_id'], $product_id]);
$related_products = $related_stmt->fetchAll();

// Stock status
$stock_status = 'In Stock';
$stock_class = '';
if ($product['status'] === 'out_of_stock' || $product['stock_quantity'] <= 0) { $stock_status = 'Out of Stock'; $stock_class = 'out'; }
elseif ($product['stock_quantity'] <= 5) { $stock_status = "Only {$product['stock_quantity']} left!"; $stock_class = 'low'; }

// Render view
require_once __DIR__ . '/views/product_detail.view.php';
