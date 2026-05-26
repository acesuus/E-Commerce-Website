<?php
// admin/products.php - Admin Products Management Controller
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/session.php';

requireAdmin();

$pageTitle = 'Manage Products';
$activePage = 'products';

// Fetch all products with category
$products = $pdo->query("
    SELECT p.*, c.category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.category_id 
    ORDER BY p.created_at DESC
")->fetchAll();

$categories = $pdo->query("SELECT * FROM categories WHERE status = 'active' ORDER BY category_name")->fetchAll();

// Render view
require_once __DIR__ . '/../views/admin/products.view.php';
