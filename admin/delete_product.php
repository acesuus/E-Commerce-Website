<?php
// admin/delete_product.php - Delete Product Controller
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/session.php';

requireAdmin();

$product_id = intval($_GET['id'] ?? 0);
if ($product_id <= 0) { header('Location: ' . url('/admin/products.php')); exit; }

// Fetch product
$stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    setFlashMessage('error', 'Product not found.');
    header('Location: ' . url('/admin/products.php'));
    exit;
}

// Handle POST confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        setFlashMessage('error', 'Invalid form submission.');
        header('Location: ' . url('/admin/products.php'));
        exit;
    }

    if (isset($_POST['confirm_delete']) && $_POST['confirm_delete'] === 'yes') {
        try {
            // Check if product has order items (can't delete if referenced)
            $check = $pdo->prepare("SELECT COUNT(*) FROM order_items WHERE product_id = ?");
            $check->execute([$product_id]);
            
            if ($check->fetchColumn() > 0) {
                // Product has orders - mark as discontinued instead of deleting
                $pdo->prepare("UPDATE products SET status = 'discontinued' WHERE product_id = ?")->execute([$product_id]);
                setFlashMessage('warning', 'Product "' . htmlspecialchars($product['product_name']) . '" has been discontinued (cannot delete: has order history).');
            } else {
                // Delete product image
                if ($product['product_image'] && file_exists(__DIR__ . '/../' . $product['product_image'])) {
                    unlink(__DIR__ . '/../' . $product['product_image']);
                }
                // Remove from cart
                $pdo->prepare("DELETE FROM cart WHERE product_id = ?")->execute([$product_id]);
                // Delete product
                $pdo->prepare("DELETE FROM products WHERE product_id = ?")->execute([$product_id]);
                setFlashMessage('success', 'Product "' . htmlspecialchars($product['product_name']) . '" deleted successfully.');
            }
        } catch (PDOException $e) {
            setFlashMessage('error', 'Failed to delete product.');
        }
    }

    header('Location: ' . url('/admin/products.php'));
    exit;
}

// Show confirmation page
$pageTitle = 'Delete Product';
$activePage = 'products';
$csrf_token = generateCSRFToken();

require_once __DIR__ . '/../views/admin/delete_product.view.php';
