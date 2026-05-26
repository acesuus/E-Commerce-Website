<?php
// admin/edit_product.php - Edit Product Controller
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/session.php';

requireAdmin();

$pageTitle = 'Edit Product';
$activePage = 'products';
$errors = [];

$product_id = intval($_GET['id'] ?? 0);
if ($product_id <= 0) { header('Location: ' . url('/admin/products.php')); exit; }

// Fetch product
$stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();
if (!$product) { setFlashMessage('error', 'Product not found.'); header('Location: ' . url('/admin/products.php')); exit; }

// Fetch categories
$categories = $pdo->query("SELECT * FROM categories WHERE status = 'active' ORDER BY category_name")->fetchAll();

$old = [
    'product_name' => $product['product_name'],
    'description' => $product['description'],
    'price' => $product['price'],
    'stock_quantity' => $product['stock_quantity'],
    'category_id' => $product['category_id'],
    'status' => $product['status']
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $errors[] = 'Invalid form submission.';
    } else {
        $product_name = trim($_POST['product_name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price = $_POST['price'] ?? '';
        $stock_quantity = intval($_POST['stock_quantity'] ?? 0);
        $category_id = intval($_POST['category_id'] ?? 0);
        $status = $_POST['status'] ?? 'available';

        $old = compact('product_name', 'description', 'price', 'stock_quantity', 'category_id', 'status');

        // Validation
        if (empty($product_name)) { $errors['product_name'] = 'Product name is required.'; }
        elseif (strlen($product_name) > 200) { $errors['product_name'] = 'Name cannot exceed 200 characters.'; }

        if (empty($price) || !is_numeric($price) || floatval($price) < 0) { $errors['price'] = 'Valid price is required.'; }

        if ($stock_quantity < 0) { $errors['stock_quantity'] = 'Stock cannot be negative.'; }

        if (!in_array($status, ['available', 'out_of_stock', 'discontinued'])) { $errors['status'] = 'Invalid status.'; }

        // Image upload (optional on edit)
        $product_image = $product['product_image']; // keep existing
        if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['product_image'];
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $max_size = 5 * 1024 * 1024;

            if (!in_array($file['type'], $allowed_types)) {
                $errors['product_image'] = 'Only JPG, PNG, GIF, WEBP images allowed.';
            } elseif ($file['size'] > $max_size) {
                $errors['product_image'] = 'Image must be under 5MB.';
            } else {
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = 'product_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                $upload_dir = __DIR__ . '/../uploads/products/';
                
                if (!is_dir($upload_dir)) { mkdir($upload_dir, 0755, true); }
                
                if (move_uploaded_file($file['tmp_name'], $upload_dir . $filename)) {
                    // Delete old image if exists
                    if ($product['product_image'] && file_exists(__DIR__ . '/../' . $product['product_image'])) {
                        unlink(__DIR__ . '/../' . $product['product_image']);
                    }
                    $product_image = 'uploads/products/' . $filename;
                } else {
                    $errors['product_image'] = 'Failed to upload image.';
                }
            }
        }

        // Remove image if requested
        if (isset($_POST['remove_image']) && $_POST['remove_image'] === '1') {
            if ($product['product_image'] && file_exists(__DIR__ . '/../' . $product['product_image'])) {
                unlink(__DIR__ . '/../' . $product['product_image']);
            }
            $product_image = null;
        }

        // Update product
        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("
                    UPDATE products SET product_name=?, description=?, price=?, stock_quantity=?, product_image=?, category_id=?, status=?
                    WHERE product_id=?
                ");
                $stmt->execute([
                    $product_name,
                    $description ?: null,
                    floatval($price),
                    $stock_quantity,
                    $product_image,
                    $category_id > 0 ? $category_id : null,
                    $status,
                    $product_id
                ]);

                setFlashMessage('success', 'Product "' . htmlspecialchars($product_name) . '" updated successfully!');
                header('Location: ' . url('/admin/products.php'));
                exit;
            } catch (PDOException $e) {
                $errors[] = 'Failed to update product. Please try again.';
            }
        }
    }
}

$csrf_token = generateCSRFToken();

require_once __DIR__ . '/../views/admin/edit_product.view.php';
