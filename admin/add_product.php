<?php
// admin/add_product.php - Add Product Controller
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/session.php';

requireAdmin();

$pageTitle = 'Add Product';
$activePage = 'products';
$errors = [];
$old = ['product_name' => '', 'description' => '', 'price' => '', 'stock_quantity' => '', 'category_id' => '', 'status' => 'available'];

// Fetch categories
$categories = $pdo->query("SELECT * FROM categories WHERE status = 'active' ORDER BY category_name")->fetchAll();

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

        // Image upload
        $product_image = null;
        if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['product_image'];
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $max_size = 5 * 1024 * 1024; // 5MB

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
                    $product_image = 'uploads/products/' . $filename;
                } else {
                    $errors['product_image'] = 'Failed to upload image.';
                }
            }
        }

        // Insert product
        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO products (product_name, description, price, stock_quantity, product_image, category_id, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $product_name,
                    $description ?: null,
                    floatval($price),
                    $stock_quantity,
                    $product_image,
                    $category_id > 0 ? $category_id : null,
                    $status
                ]);

                setFlashMessage('success', 'Product "' . htmlspecialchars($product_name) . '" added successfully!');
                header('Location: ' . url('/admin/products.php'));
                exit;
            } catch (PDOException $e) {
                $errors[] = 'Failed to add product. Please try again.';
            }
        }
    }
}

$csrf_token = generateCSRFToken();

require_once __DIR__ . '/../views/admin/add_product.view.php';
