<?php
// cart.php - Shopping Cart System
// Supports: add, update quantity, remove, display cart with totals
// Uses database cart for logged-in users, session cart for guests
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/session.php';

$pageTitle = 'Shopping Cart';
$flash = getFlashMessage();

// =============================================
// CART ACTION HANDLER (POST requests)
// =============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $product_id = intval($_POST['product_id'] ?? 0);
    $quantity = intval($_POST['quantity'] ?? 1);

    // Validate product exists and is available
    if ($product_id > 0 && in_array($action, ['add', 'update', 'remove'])) {
        
        $product_stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = ?");
        $product_stmt->execute([$product_id]);
        $product = $product_stmt->fetch();

        if ($product) {
            // ===== LOGGED-IN USER: Database Cart =====
            if (isLoggedIn()) {
                $user_id = $_SESSION['user_id'];

                switch ($action) {
                    case 'add':
                        // Check stock availability
                        if ($product['status'] !== 'available' || $product['stock_quantity'] <= 0) {
                            setFlashMessage('error', 'This product is currently out of stock.');
                            break;
                        }

                        // Check if already in cart (prevent duplicates - use UPSERT)
                        $existing = $pdo->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
                        $existing->execute([$user_id, $product_id]);
                        $cart_item = $existing->fetch();

                        if ($cart_item) {
                            // Product already in cart - increment quantity
                            $new_qty = $cart_item['quantity'] + $quantity;
                            // Cap at available stock
                            if ($new_qty > $product['stock_quantity']) {
                                $new_qty = $product['stock_quantity'];
                                setFlashMessage('warning', 'Quantity adjusted to maximum available stock (' . $new_qty . ').');
                            } else {
                                setFlashMessage('success', htmlspecialchars($product['product_name']) . ' quantity updated in cart.');
                            }
                            $update = $pdo->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ?");
                            $update->execute([$new_qty, $cart_item['cart_id']]);
                        } else {
                            // New item - insert into cart
                            $qty = min($quantity, $product['stock_quantity']);
                            $insert = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
                            $insert->execute([$user_id, $product_id, $qty]);
                            setFlashMessage('success', htmlspecialchars($product['product_name']) . ' added to cart!');
                        }
                        break;

                    case 'update':
                        if ($quantity <= 0) {
                            // If quantity is 0 or less, remove item
                            $delete = $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
                            $delete->execute([$user_id, $product_id]);
                            setFlashMessage('success', 'Item removed from cart.');
                        } else {
                            // Cap at stock
                            $qty = min($quantity, $product['stock_quantity']);
                            if ($qty < $quantity) {
                                setFlashMessage('warning', 'Quantity adjusted to available stock (' . $qty . ').');
                            } else {
                                setFlashMessage('success', 'Cart updated successfully.');
                            }
                            $update = $pdo->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
                            $update->execute([$qty, $user_id, $product_id]);
                        }
                        break;

                    case 'remove':
                        $delete = $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
                        $delete->execute([$user_id, $product_id]);
                        setFlashMessage('success', htmlspecialchars($product['product_name']) . ' removed from cart.');
                        break;
                }

            // ===== GUEST USER: Session Cart =====
            } else {
                if (!isset($_SESSION['cart'])) {
                    $_SESSION['cart'] = [];
                }

                switch ($action) {
                    case 'add':
                        if ($product['status'] !== 'available' || $product['stock_quantity'] <= 0) {
                            setFlashMessage('error', 'This product is currently out of stock.');
                            break;
                        }

                        if (isset($_SESSION['cart'][$product_id])) {
                            // Already in cart - increment
                            $new_qty = $_SESSION['cart'][$product_id] + $quantity;
                            if ($new_qty > $product['stock_quantity']) {
                                $new_qty = $product['stock_quantity'];
                                setFlashMessage('warning', 'Quantity adjusted to maximum available stock (' . $new_qty . ').');
                            } else {
                                setFlashMessage('success', htmlspecialchars($product['product_name']) . ' quantity updated in cart.');
                            }
                            $_SESSION['cart'][$product_id] = $new_qty;
                        } else {
                            $qty = min($quantity, $product['stock_quantity']);
                            $_SESSION['cart'][$product_id] = $qty;
                            setFlashMessage('success', htmlspecialchars($product['product_name']) . ' added to cart!');
                        }
                        break;

                    case 'update':
                        if ($quantity <= 0) {
                            unset($_SESSION['cart'][$product_id]);
                            setFlashMessage('success', 'Item removed from cart.');
                        } else {
                            $qty = min($quantity, $product['stock_quantity']);
                            if ($qty < $quantity) {
                                setFlashMessage('warning', 'Quantity adjusted to available stock (' . $qty . ').');
                            } else {
                                setFlashMessage('success', 'Cart updated successfully.');
                            }
                            $_SESSION['cart'][$product_id] = $qty;
                        }
                        break;

                    case 'remove':
                        unset($_SESSION['cart'][$product_id]);
                        setFlashMessage('success', htmlspecialchars($product['product_name']) . ' removed from cart.');
                        break;
                }
            }
        } else {
            setFlashMessage('error', 'Product not found.');
        }
    }

    // Clear cart action
    if ($action === 'clear') {
        if (isLoggedIn()) {
            $pdo->prepare("DELETE FROM cart WHERE user_id = ?")->execute([$_SESSION['user_id']]);
        } else {
            $_SESSION['cart'] = [];
        }
        setFlashMessage('success', 'Cart cleared successfully.');
    }

    // Redirect to prevent form resubmission (PRG pattern)
    header('Location: /cart.php');
    exit;
}

// =============================================
// FETCH CART ITEMS FOR DISPLAY
// =============================================
$cart_items = [];
$cart_total = 0;
$cart_count = 0;

if (isLoggedIn()) {
    // Database cart for logged-in users
    $stmt = $pdo->prepare("
        SELECT c.cart_id, c.quantity, c.added_at,
               p.product_id, p.product_name, p.price, p.stock_quantity, 
               p.product_image, p.status,
               cat.category_name
        FROM cart c
        JOIN products p ON c.product_id = p.product_id
        LEFT JOIN categories cat ON p.category_id = cat.category_id
        WHERE c.user_id = ?
        ORDER BY c.added_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $cart_items = $stmt->fetchAll();
} else {
    // Session cart for guests
    if (!empty($_SESSION['cart'])) {
        $product_ids = array_keys($_SESSION['cart']);
        $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
        
        $stmt = $pdo->prepare("
            SELECT p.product_id, p.product_name, p.price, p.stock_quantity, 
                   p.product_image, p.status,
                   cat.category_name
            FROM products p
            LEFT JOIN categories cat ON p.category_id = cat.category_id
            WHERE p.product_id IN ($placeholders)
        ");
        $stmt->execute($product_ids);
        $products = $stmt->fetchAll();

        foreach ($products as $p) {
            $p['quantity'] = $_SESSION['cart'][$p['product_id']];
            $p['added_at'] = null;
            $cart_items[] = $p;
        }
    }
}

// Calculate totals
foreach ($cart_items as $item) {
    $subtotal = $item['price'] * $item['quantity'];
    $cart_total += $subtotal;
    $cart_count += $item['quantity'];
}

// Re-fetch flash after POST redirect
$flash = $flash ?: getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - E-Commerce Store</title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Header Navigation -->
    <header class="header">
        <div class="container">
            <a href="/index.php" class="brand"><i class="fas fa-store"></i> E-Store</a>
            <nav class="nav-store">
                <a href="/index.php">Home</a>
                <a href="/products.php">Products</a>
                <a href="/cart.php" class="active"><i class="fas fa-shopping-cart"></i> Cart (<?php echo $cart_count; ?>)</a>
                <div class="nav-user">
                    <?php if (isLoggedIn()): ?>
                        <span>Hi, <?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                        <a href="/user/orders.php">My Orders</a>
                        <a href="/user/logout.php" class="btn-nav-outline">Logout</a>
                    <?php else: ?>
                        <a href="/user/login.php" class="btn-nav-outline">Sign In</a>
                        <a href="/user/register.php" class="btn-nav">Register</a>
                    <?php endif; ?>
                </div>
            </nav>
        </div>
    </header>

    <div class="page-container">
        <!-- Page Header -->
        <div class="page-header">
            <h1><i class="fas fa-shopping-cart" style="color:#667eea;"></i> Shopping Cart</h1>
            <p>
                <?php if ($cart_count > 0): ?>
                    You have <?php echo $cart_count; ?> item<?php echo $cart_count !== 1 ? 's' : ''; ?> in your cart
                <?php else: ?>
                    Your cart is empty
                <?php endif; ?>
            </p>
        </div>

        <!-- Flash Messages -->
        <?php if ($flash): ?>
            <div class="alert alert-<?php echo $flash['type']; ?>">
                <i class="fas fa-<?php echo $flash['type'] === 'success' ? 'check-circle' : ($flash['type'] === 'warning' ? 'exclamation-triangle' : 'exclamation-circle'); ?>"></i>
                <span><?php echo $flash['message']; ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($cart_items)): ?>
            <div class="cart-layout">
                <!-- Cart Items Table -->
                <div class="cart-items-section">
                    <table class="cart-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Subtotal</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cart_items as $item): ?>
                                <?php $subtotal = $item['price'] * $item['quantity']; ?>
                                <tr class="cart-row <?php echo ($item['status'] !== 'available') ? 'unavailable' : ''; ?>">
                                    <!-- Product Info -->
                                    <td class="cart-product">
                                        <div class="cart-product-inner">
                                            <div class="cart-thumb">
                                                <?php if (!empty($item['product_image']) && file_exists(__DIR__ . '/' . $item['product_image'])): ?>
                                                    <img src="/<?php echo htmlspecialchars($item['product_image']); ?>" 
                                                         alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                                                <?php else: ?>
                                                    <i class="fas fa-box-open"></i>
                                                <?php endif; ?>
                                            </div>
                                            <div class="cart-product-details">
                                                <a href="/product_detail.php?id=<?php echo $item['product_id']; ?>" class="cart-product-name">
                                                    <?php echo htmlspecialchars($item['product_name']); ?>
                                                </a>
                                                <span class="cart-product-category">
                                                    <?php echo htmlspecialchars($item['category_name'] ?? 'Uncategorized'); ?>
                                                </span>
                                                <?php if ($item['status'] !== 'available'): ?>
                                                    <span class="cart-unavailable-badge">Unavailable</span>
                                                <?php elseif ($item['quantity'] > $item['stock_quantity']): ?>
                                                    <span class="cart-unavailable-badge" style="background:#fefcbf;color:#975a16;">
                                                        Only <?php echo $item['stock_quantity']; ?> in stock
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Unit Price -->
                                    <td class="cart-price" data-label="Price">
                                        $<?php echo number_format($item['price'], 2); ?>
                                    </td>

                                    <!-- Quantity Control -->
                                    <td class="cart-quantity" data-label="Quantity">
                                        <form method="POST" action="/cart.php" class="qty-form">
                                            <input type="hidden" name="action" value="update">
                                            <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                            <div class="qty-control">
                                                <button type="button" class="qty-btn qty-minus" onclick="changeQty(this, -1)">
                                                    <i class="fas fa-minus"></i>
                                                </button>
                                                <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" 
                                                       min="1" max="<?php echo $item['stock_quantity']; ?>" 
                                                       class="qty-input" onchange="this.form.submit()">
                                                <button type="button" class="qty-btn qty-plus" onclick="changeQty(this, 1)">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                            </div>
                                        </form>
                                    </td>

                                    <!-- Subtotal -->
                                    <td class="cart-subtotal" data-label="Subtotal">
                                        <strong>$<?php echo number_format($subtotal, 2); ?></strong>
                                    </td>

                                    <!-- Remove Button -->
                                    <td class="cart-actions" data-label="Actions">
                                        <form method="POST" action="/cart.php" style="display:inline;">
                                            <input type="hidden" name="action" value="remove">
                                            <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                            <button type="submit" class="btn-remove" title="Remove item" 
                                                    onclick="return confirm('Remove this item from cart?')">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <!-- Cart Actions -->
                    <div class="cart-bottom-actions">
                        <a href="/products.php" class="btn-continue-shopping">
                            <i class="fas fa-arrow-left"></i> Continue Shopping
                        </a>
                        <form method="POST" action="/cart.php" style="display:inline;">
                            <input type="hidden" name="action" value="clear">
                            <button type="submit" class="btn-clear-cart" onclick="return confirm('Clear all items from cart?')">
                                <i class="fas fa-trash"></i> Clear Cart
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Cart Summary / Totals -->
                <div class="cart-summary">
                    <h3><i class="fas fa-receipt"></i> Order Summary</h3>
                    
                    <div class="summary-row">
                        <span>Items (<?php echo $cart_count; ?>)</span>
                        <span>$<?php echo number_format($cart_total, 2); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Shipping</span>
                        <span class="free-shipping">Free</span>
                    </div>
                    <div class="summary-divider"></div>
                    <div class="summary-row summary-total">
                        <span>Total</span>
                        <span>$<?php echo number_format($cart_total, 2); ?></span>
                    </div>

                    <?php if (isLoggedIn()): ?>
                        <a href="/checkout.php" class="btn-checkout">
                            <i class="fas fa-lock"></i> Proceed to Checkout
                        </a>
                    <?php else: ?>
                        <a href="/user/login.php" class="btn-checkout">
                            <i class="fas fa-sign-in-alt"></i> Sign In to Checkout
                        </a>
                        <p class="checkout-note">
                            <i class="fas fa-info-circle"></i>
                            Please sign in to complete your purchase. Your cart will be saved.
                        </p>
                    <?php endif; ?>
                </div>
            </div>

        <?php else: ?>
            <!-- Empty Cart -->
            <div class="empty-cart">
                <i class="fas fa-shopping-cart"></i>
                <h3>Your cart is empty</h3>
                <p>Looks like you haven't added any products yet. Start shopping!</p>
                <a href="/products.php" class="btn-shop-now">
                    <i class="fas fa-shopping-bag"></i> Browse Products
                </a>
            </div>
        <?php endif; ?>
    </div>

    <?php require_once __DIR__ . '/includes/footer.php'; ?>

    <!-- Quantity Control Script -->
    <script>
    function changeQty(btn, delta) {
        const form = btn.closest('.qty-form');
        const input = form.querySelector('.qty-input');
        let current = parseInt(input.value) || 1;
        let newVal = current + delta;
        const max = parseInt(input.max) || 999;
        const min = parseInt(input.min) || 1;

        if (newVal < min) newVal = min;
        if (newVal > max) newVal = max;

        if (newVal !== current) {
            input.value = newVal;
            form.submit();
        }
    }
    </script>
</body>
</html>
