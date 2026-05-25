<?php
// cart.php - Shopping Cart Controller
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/session.php';

$pageTitle = 'Shopping Cart';
$activePage = 'cart';
$flash = getFlashMessage();

// CART ACTION HANDLER (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $product_id = intval($_POST['product_id'] ?? 0);
    $quantity = intval($_POST['quantity'] ?? 1);

    if ($product_id > 0 && in_array($action, ['add', 'update', 'remove'])) {
        $product_stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = ?");
        $product_stmt->execute([$product_id]);
        $product = $product_stmt->fetch();

        if ($product) {
            if (isLoggedIn()) {
                $user_id = $_SESSION['user_id'];
                switch ($action) {
                    case 'add':
                        if ($product['status'] !== 'available' || $product['stock_quantity'] <= 0) { setFlashMessage('error', 'This product is currently out of stock.'); break; }
                        $existing = $pdo->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
                        $existing->execute([$user_id, $product_id]);
                        $cart_item = $existing->fetch();
                        if ($cart_item) {
                            $new_qty = min($cart_item['quantity'] + $quantity, $product['stock_quantity']);
                            $pdo->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ?")->execute([$new_qty, $cart_item['cart_id']]);
                            setFlashMessage('success', htmlspecialchars($product['product_name']) . ' quantity updated in cart.');
                        } else {
                            $qty = min($quantity, $product['stock_quantity']);
                            $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)")->execute([$user_id, $product_id, $qty]);
                            setFlashMessage('success', htmlspecialchars($product['product_name']) . ' added to cart!');
                        }
                        break;
                    case 'update':
                        if ($quantity <= 0) { $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?")->execute([$user_id, $product_id]); setFlashMessage('success', 'Item removed from cart.'); }
                        else { $qty = min($quantity, $product['stock_quantity']); $pdo->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?")->execute([$qty, $user_id, $product_id]); setFlashMessage('success', 'Cart updated successfully.'); }
                        break;
                    case 'remove':
                        $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?")->execute([$user_id, $product_id]);
                        setFlashMessage('success', htmlspecialchars($product['product_name']) . ' removed from cart.');
                        break;
                }
            } else {
                if (!isset($_SESSION['cart'])) { $_SESSION['cart'] = []; }
                switch ($action) {
                    case 'add':
                        if ($product['status'] !== 'available' || $product['stock_quantity'] <= 0) { setFlashMessage('error', 'This product is currently out of stock.'); break; }
                        $_SESSION['cart'][$product_id] = min(($_SESSION['cart'][$product_id] ?? 0) + $quantity, $product['stock_quantity']);
                        setFlashMessage('success', htmlspecialchars($product['product_name']) . ' added to cart!');
                        break;
                    case 'update':
                        if ($quantity <= 0) { unset($_SESSION['cart'][$product_id]); setFlashMessage('success', 'Item removed from cart.'); }
                        else { $_SESSION['cart'][$product_id] = min($quantity, $product['stock_quantity']); setFlashMessage('success', 'Cart updated successfully.'); }
                        break;
                    case 'remove':
                        unset($_SESSION['cart'][$product_id]);
                        setFlashMessage('success', htmlspecialchars($product['product_name']) . ' removed from cart.');
                        break;
                }
            }
        } else { setFlashMessage('error', 'Product not found.'); }
    }

    if (($action ?? '') === 'clear') {
        if (isLoggedIn()) { $pdo->prepare("DELETE FROM cart WHERE user_id = ?")->execute([$_SESSION['user_id']]); }
        else { $_SESSION['cart'] = []; }
        setFlashMessage('success', 'Cart cleared successfully.');
    }

    header('Location: /cart.php');
    exit;
}

// FETCH CART ITEMS
$cart_items = [];
$cart_total = 0;
$cart_count = 0;

if (isLoggedIn()) {
    $stmt = $pdo->prepare("SELECT c.cart_id, c.quantity, c.added_at, p.product_id, p.product_name, p.price, p.stock_quantity, p.product_image, p.status, cat.category_name FROM cart c JOIN products p ON c.product_id = p.product_id LEFT JOIN categories cat ON p.category_id = cat.category_id WHERE c.user_id = ? ORDER BY c.added_at DESC");
    $stmt->execute([$_SESSION['user_id']]);
    $cart_items = $stmt->fetchAll();
} else {
    if (!empty($_SESSION['cart'])) {
        $product_ids = array_keys($_SESSION['cart']);
        $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
        $stmt = $pdo->prepare("SELECT p.product_id, p.product_name, p.price, p.stock_quantity, p.product_image, p.status, cat.category_name FROM products p LEFT JOIN categories cat ON p.category_id = cat.category_id WHERE p.product_id IN ($placeholders)");
        $stmt->execute($product_ids);
        foreach ($stmt->fetchAll() as $p) { $p['quantity'] = $_SESSION['cart'][$p['product_id']]; $p['added_at'] = null; $cart_items[] = $p; }
    }
}

foreach ($cart_items as $item) { $cart_total += $item['price'] * $item['quantity']; $cart_count += $item['quantity']; }
$flash = $flash ?: getFlashMessage();

// Render view
require_once __DIR__ . '/views/cart.view.php';
