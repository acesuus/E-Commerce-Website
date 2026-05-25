<?php
// admin/categories.php - Category Management Controller
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/session.php';

requireAdmin();

$pageTitle = 'Manage Categories';
$activePage = 'categories';
$errors = [];
$edit_category = null;
$flash = getFlashMessage();

// Determine action
$action = $_GET['action'] ?? 'list';
$category_id = intval($_GET['id'] ?? 0);

// Fetch category for edit
if ($action === 'edit' && $category_id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE category_id = ?");
    $stmt->execute([$category_id]);
    $edit_category = $stmt->fetch();
    if (!$edit_category) {
        setFlashMessage('error', 'Category not found.');
        header('Location: ' . url('/admin/categories.php'));
        exit;
    }
}

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $errors[] = 'Invalid form submission.';
    } else {
        $post_action = $_POST['action'] ?? '';

        // ADD CATEGORY
        if ($post_action === 'add') {
            $category_name = trim($_POST['category_name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $status = $_POST['status'] ?? 'active';

            if (empty($category_name)) {
                $errors['category_name'] = 'Category name is required.';
            } elseif (strlen($category_name) > 50) {
                $errors['category_name'] = 'Name cannot exceed 50 characters.';
            } else {
                // Check duplicate
                $check = $pdo->prepare("SELECT category_id FROM categories WHERE category_name = ?");
                $check->execute([$category_name]);
                if ($check->fetch()) {
                    $errors['category_name'] = 'This category name already exists.';
                }
            }

            if (!in_array($status, ['active', 'inactive'])) {
                $errors['status'] = 'Invalid status.';
            }

            if (empty($errors)) {
                $stmt = $pdo->prepare("INSERT INTO categories (category_name, description, status) VALUES (?, ?, ?)");
                $stmt->execute([$category_name, $description ?: null, $status]);
                setFlashMessage('success', 'Category "' . htmlspecialchars($category_name) . '" added successfully!');
                header('Location: ' . url('/admin/categories.php'));
                exit;
            }
        }

        // EDIT CATEGORY
        if ($post_action === 'edit') {
            $edit_id = intval($_POST['category_id'] ?? 0);
            $category_name = trim($_POST['category_name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $status = $_POST['status'] ?? 'active';

            if (empty($category_name)) {
                $errors['category_name'] = 'Category name is required.';
            } elseif (strlen($category_name) > 50) {
                $errors['category_name'] = 'Name cannot exceed 50 characters.';
            } else {
                $check = $pdo->prepare("SELECT category_id FROM categories WHERE category_name = ? AND category_id != ?");
                $check->execute([$category_name, $edit_id]);
                if ($check->fetch()) {
                    $errors['category_name'] = 'This category name already exists.';
                }
            }

            if (!in_array($status, ['active', 'inactive'])) {
                $errors['status'] = 'Invalid status.';
            }

            if (empty($errors)) {
                $stmt = $pdo->prepare("UPDATE categories SET category_name = ?, description = ?, status = ? WHERE category_id = ?");
                $stmt->execute([$category_name, $description ?: null, $status, $edit_id]);
                setFlashMessage('success', 'Category "' . htmlspecialchars($category_name) . '" updated successfully!');
                header('Location: ' . url('/admin/categories.php'));
                exit;
            } else {
                // Re-fetch for the edit form
                $edit_category = $pdo->prepare("SELECT * FROM categories WHERE category_id = ?");
                $edit_category->execute([$edit_id]);
                $edit_category = $edit_category->fetch();
            }
        }

        // DELETE CATEGORY
        if ($post_action === 'delete') {
            $delete_id = intval($_POST['category_id'] ?? 0);
            if ($delete_id > 0) {
                // Check if products are assigned
                $product_count = $pdo->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
                $product_count->execute([$delete_id]);
                $count = $product_count->fetchColumn();

                $cat_stmt = $pdo->prepare("SELECT category_name FROM categories WHERE category_id = ?");
                $cat_stmt->execute([$delete_id]);
                $cat_name = $cat_stmt->fetchColumn();

                if ($count > 0) {
                    // Has products - just deactivate
                    $pdo->prepare("UPDATE categories SET status = 'inactive' WHERE category_id = ?")->execute([$delete_id]);
                    setFlashMessage('warning', 'Category "' . htmlspecialchars($cat_name) . '" deactivated (has ' . $count . ' products assigned).');
                } else {
                    // No products - safe to delete
                    $pdo->prepare("DELETE FROM categories WHERE category_id = ?")->execute([$delete_id]);
                    setFlashMessage('success', 'Category "' . htmlspecialchars($cat_name) . '" deleted successfully.');
                }
            }
            header('Location: ' . url('/admin/categories.php'));
            exit;
        }
    }
}

// Fetch all categories with product counts
$categories = $pdo->query("
    SELECT c.*, COUNT(p.product_id) as product_count
    FROM categories c
    LEFT JOIN products p ON c.category_id = p.category_id
    GROUP BY c.category_id
    ORDER BY c.category_name ASC
")->fetchAll();

$csrf_token = generateCSRFToken();

// Render view
require_once __DIR__ . '/../views/admin/categories.view.php';
