<?php
// checkout.php - Checkout Controller
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/session.php';

if (!isLoggedIn()) {
    $_SESSION['redirect_after_login'] = url('/checkout.php');
    setFlashMessage('error', 'Please log in to proceed with checkout.');
    header('Location: ' . url('/user/login.php'));
    exit;
}

$pageTitle = 'Checkout';
$activePage = 'cart';
$errors = [];
$user_id = $_SESSION['user_id'];

$user_stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$user_stmt->execute([$user_id]);
$user = $user_stmt->fetch();

$cart_stmt = $pdo->prepare("SELECT c.cart_id, c.quantity, p.product_id, p.product_name, p.price, p.stock_quantity, p.product_image, p.status, cat.category_name FROM cart c JOIN products p ON c.product_id = p.product_id LEFT JOIN categories cat ON p.category_id = cat.category_id WHERE c.user_id = ? ORDER BY c.added_at DESC");
$cart_stmt->execute([$user_id]);
$cart_items = $cart_stmt->fetchAll();

if (empty($cart_items)) { setFlashMessage('error', 'Your cart is empty.'); header('Location: ' . url('/cart.php')); exit; }

$cart_total = 0; $cart_count = 0; $unavailable_items = [];
foreach ($cart_items as $item) {
    if ($item['status'] !== 'available' || $item['stock_quantity'] <= 0) { $unavailable_items[] = $item['product_name']; }
    else { $cart_total += $item['price'] * $item['quantity']; $cart_count += $item['quantity']; }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) { $errors[] = 'Invalid form submission.'; }
    else {
        $shipping_address = trim($_POST['shipping_address'] ?? '');
        $payment_method = $_POST['payment_method'] ?? 'cash_on_delivery';
        $notes = trim($_POST['notes'] ?? '');
        $allowed_payments = ['cash_on_delivery', 'bank_transfer', 'gcash', 'credit_card'];

        if (empty($shipping_address)) { $errors['shipping_address'] = 'Shipping address is required.'; }
        elseif (strlen($shipping_address) < 10) { $errors['shipping_address'] = 'Please provide a complete shipping address.'; }
        if (!in_array($payment_method, $allowed_payments)) { $errors['payment_method'] = 'Please select a valid payment method.'; }
        if (!empty($unavailable_items)) { $errors[] = 'Some items are unavailable. Please update your cart.'; }

        if (empty($errors)) {
            foreach ($cart_items as $item) { if ($item['quantity'] > $item['stock_quantity']) { $errors[] = htmlspecialchars($item['product_name']) . ' only has ' . $item['stock_quantity'] . ' units available.'; } }
        }

        if (empty($errors)) {
            try {
                $pdo->beginTransaction();
                $order_stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, order_status, shipping_address, payment_method, notes) VALUES (?, ?, 'pending', ?, ?, ?)");
                $order_stmt->execute([$user_id, $cart_total, $shipping_address, $payment_method, $notes ?: null]);
                $order_id = $pdo->lastInsertId();

                $item_stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)");
                $stock_stmt = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE product_id = ? AND stock_quantity >= ?");

                foreach ($cart_items as $item) {
                    if ($item['status'] === 'available' && $item['stock_quantity'] > 0) {
                        $item_stmt->execute([$order_id, $item['product_id'], $item['quantity'], $item['price']]);
                        $stock_stmt->execute([$item['quantity'], $item['product_id'], $item['quantity']]);
                        $remaining = $pdo->prepare("SELECT stock_quantity FROM products WHERE product_id = ?");
                        $remaining->execute([$item['product_id']]);
                        if ($remaining->fetchColumn() <= 0) { $pdo->prepare("UPDATE products SET status = 'out_of_stock' WHERE product_id = ?")->execute([$item['product_id']]); }
                    }
                }
                $pdo->prepare("DELETE FROM cart WHERE user_id = ?")->execute([$user_id]);
                $pdo->commit();
                $_SESSION['last_order_id'] = $order_id;
                header('Location: ' . url('/order_confirmation.php?order=' . $order_id));
                exit;
            } catch (PDOException $e) { $pdo->rollBack(); $errors[] = 'Order could not be processed. Please try again.'; }
        }
    }
}

$csrf_token = generateCSRFToken();

// Render view
require_once __DIR__ . '/views/checkout.view.php';
