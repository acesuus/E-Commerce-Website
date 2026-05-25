<?php
// index.php - Homepage Controller
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/session.php';

$pageTitle = 'Home';
$activePage = 'home';

// Fetch featured products (newest available)
$featured_stmt = $pdo->query("
    SELECT p.*, c.category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.category_id 
    WHERE p.status = 'available' 
    ORDER BY p.created_at DESC 
    LIMIT 8
");
$featured_products = $featured_stmt->fetchAll();

// Fetch categories for browsing
$categories = $pdo->query("
    SELECT c.*, COUNT(p.product_id) as product_count 
    FROM categories c 
    LEFT JOIN products p ON c.category_id = p.category_id AND p.status = 'available'
    WHERE c.status = 'active' 
    GROUP BY c.category_id 
    ORDER BY c.category_name
")->fetchAll();

// Render view
require_once __DIR__ . '/views/home.view.php';
