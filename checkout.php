<?php
// checkout.php - Checkout Process (Simulated - No Payment Integration)
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/session.php';

// Require login to checkout
if (!isLoggedIn()) {
    $_SESSION['redirect_after_login'] = '/checkout.php';
    setFlashMessage('error', 'Please log in to proceed with checkout.');
    header('Location: /user/login.php');
    exit;
}

$pageTitle = 'Checkout';
$errors = [];
$user_id = $_SESSION['user_id'];

// Fetch user info for pre-filling
$user_stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$user_stmt->execute([$user_id]);
$user = $user_stmt->fetch();

// Fetch cart items
$cart_stmt = $pdo->prepare("
    SELECT c.cart_id, c.quantity,
           p.product_id, p.product_name, p.price, p.stock_quantity, 
           p.product_image, p.status, cat.category_name
    FROM cart c
    JOIN products p ON c.product_id = p.product_id
    LEFT JOIN categories cat ON p.category_id = cat.category_id
    WHERE c.user_id = ?
    ORDER BY c.added_at DESC
");
$cart_stmt->execute([$user_id]);
$cart_items = $cart_stmt->fetchAll();

// If cart is empty, redirect back
if (empty($cart_items)) {
    setFlashMessage('error', 'Your cart is empty. Add some products before checking out.');
    header('Location: /cart.php');
    exit;
}

// Calculate totals
$cart_total = 0;
$cart_count = 0;
$unavailable_items = [];

foreach ($cart_items as $item) {
    if ($item['status'] !== 'available' || $item['stock_quantity'] <= 0) {
        $unavailable_items[] = $item['product_name'];
    } else {
        $cart_total += $item['price'] * $item['quantity'];
        $cart_count += $item['quantity'];
    }
}

// =============================================
// PROCESS CHECKOUT (POST)
// =============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // CSRF validation
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $errors[] = 'Invalid form submission. Please try again.';
    } else {
        // Get form data
        $shipping_address = trim($_POST['shipping_address'] ?? '');
        $payment_method = $_POST['payment_method'] ?? 'cash_on_delivery';
        $notes = trim($_POST['notes'] ?? '');

        // Allowed payment methods
        $allowed_payments = ['cash_on_delivery', 'bank_transfer', 'gcash', 'credit_card'];

        // Validation
        if (empty($shipping_address)) {
            $errors['shipping_address'] = 'Shipping address is required.';
        } elseif (strlen($shipping_address) < 10) {
            $errors['shipping_address'] = 'Please provide a complete shipping address.';
        } elseif (strlen($shipping_address) > 500) {
            $errors['shipping_address'] = 'Address cannot exceed 500 characters.';
        }

        if (!in_array($payment_method, $allowed_payments)) {
            $errors['payment_method'] = 'Please select a valid payment method.';
        }

        if (strlen($notes) > 500) {
            $errors['notes'] = 'Notes cannot exceed 500 characters.';
        }

        // Check for unavailable items
        if (!empty($unavailable_items)) {
            $errors[] = 'Some items in your cart are unavailable: ' . implode(', ', $unavailable_items) . '. Please remove them first.';
        }

        // Re-validate stock for each item
        if (empty($errors)) {
            foreach ($cart_items as $item) {
                if ($item['quantity'] > $item['stock_quantity']) {
                    $errors[] = htmlspecialchars($item['product_name']) . ' only has ' . $item['stock_quantity'] . ' units available.';
                }
            }
        }

        // Process order if no errors
        if (empty($errors)) {
            try {
                // Begin transaction
                $pdo->beginTransaction();

                // 1. Create order record
                $order_stmt = $pdo->prepare("
                    INSERT INTO orders (user_id, total_amount, order_status, shipping_address, payment_method, notes)
                    VALUES (?, ?, 'pending', ?, ?, ?)
                ");
                $order_stmt->execute([$user_id, $cart_total, $shipping_address, $payment_method, $notes ?: null]);
                $order_id = $pdo->lastInsertId();

                // 2. Insert order items and reduce stock
                $item_stmt = $pdo->prepare("
                    INSERT INTO order_items (order_id, product_id, quantity, unit_price)
                    VALUES (?, ?, ?, ?)
                ");
                $stock_stmt = $pdo->prepare("
                    UPDATE products SET stock_quantity = stock_quantity - ? WHERE product_id = ? AND stock_quantity >= ?
                ");

                foreach ($cart_items as $item) {
                    if ($item['status'] === 'available' && $item['stock_quantity'] > 0) {
                        // Insert order item
                        $item_stmt->execute([$order_id, $item['product_id'], $item['quantity'], $item['price']]);
                        
                        // Reduce stock
                        $stock_stmt->execute([$item['quantity'], $item['product_id'], $item['quantity']]);
                        
                        // Check if stock reduced to 0, update status
                        $check_stock = $pdo->prepare("SELECT stock_quantity FROM products WHERE product_id = ?");
                        $check_stock->execute([$item['product_id']]);
                        $remaining = $check_stock->fetchColumn();
                        if ($remaining <= 0) {
                            $pdo->prepare("UPDATE products SET status = 'out_of_stock' WHERE product_id = ?")->execute([$item['product_id']]);
                        }
                    }
                }

                // 3. Clear cart
                $pdo->prepare("DELETE FROM cart WHERE user_id = ?")->execute([$user_id]);

                // Commit transaction
                $pdo->commit();

                // Redirect to order confirmation
                $_SESSION['last_order_id'] = $order_id;
                header('Location: /order_confirmation.php?order=' . $order_id);
                exit;

            } catch (PDOException $e) {
                $pdo->rollBack();
                $errors[] = 'Order could not be processed. Please try again.';
                // error_log($e->getMessage());
            }
        }
    }
}

$csrf_token = generateCSRFToken();
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
    <!-- Header -->
    <header class="header">
        <div class="container">
            <a href="/index.php" class="brand"><i class="fas fa-store"></i> E-Store</a>
            <nav class="nav-store">
                <a href="/index.php">Home</a>
                <a href="/products.php">Products</a>
                <a href="/cart.php"><i class="fas fa-shopping-cart"></i> Cart</a>
                <div class="nav-user">
                    <span>Hi, <?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                    <a href="/user/orders.php">My Orders</a>
                    <a href="/user/logout.php" class="btn-nav-outline">Logout</a>
                </div>
            </nav>
        </div>
    </header>

    <div class="page-container">
        <!-- Checkout Progress -->
        <div class="checkout-progress">
            <div class="progress-step completed"><i class="fas fa-shopping-cart"></i> Cart</div>
            <div class="progress-line active"></div>
            <div class="progress-step active"><i class="fas fa-clipboard-list"></i> Checkout</div>
            <div class="progress-line"></div>
            <div class="progress-step"><i class="fas fa-check-circle"></i> Confirmation</div>
        </div>

        <h1 class="checkout-title">Checkout</h1>

        <!-- Error Messages -->
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <div>
                    <?php if (isset($errors[0])): ?>
                        <span><?php echo $errors[0]; ?></span>
                    <?php else: ?>
                        <span>Please fix the errors below.</span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Unavailable Items Warning -->
        <?php if (!empty($unavailable_items)): ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                <span>Some items are unavailable: <?php echo implode(', ', array_map('htmlspecialchars', $unavailable_items)); ?>. 
                Please <a href="/cart.php" style="color:#975a16;font-weight:600;">update your cart</a>.</span>
            </div>
        <?php endif; ?>

        <form method="POST" action="/checkout.php" id="checkoutForm" novalidate>
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

            <div class="checkout-layout">
                <!-- Left: Shipping & Payment Form -->
                <div class="checkout-form-section">
                    
                    <!-- Shipping Information -->
                    <div class="checkout-card">
                        <h2><i class="fas fa-truck"></i> Shipping Information</h2>
                        
                        <div class="form-group">
                            <label for="full_name_display">Full Name</label>
                            <input type="text" id="full_name_display" 
                                   value="<?php echo htmlspecialchars($user['full_name']); ?>" 
                                   disabled class="disabled-input">
                        </div>

                        <div class="form-group">
                            <label for="email_display">Email</label>
                            <input type="email" id="email_display" 
                                   value="<?php echo htmlspecialchars($user['email']); ?>" 
                                   disabled class="disabled-input">
                        </div>

                        <div class="form-group">
                            <label for="phone_display">Phone</label>
                            <input type="tel" id="phone_display" 
                                   value="<?php echo htmlspecialchars($user['phone'] ?? 'Not provided'); ?>" 
                                   disabled class="disabled-input">
                        </div>

                        <div class="form-group">
                            <label for="shipping_address">Shipping Address *</label>
                            <textarea id="shipping_address" name="shipping_address" rows="3" 
                                      placeholder="Enter your complete shipping address (street, city, zip code)"
                                      class="<?php echo isset($errors['shipping_address']) ? 'error' : ''; ?>"
                                      required><?php echo htmlspecialchars($_POST['shipping_address'] ?? $user['address'] ?? ''); ?></textarea>
                            <?php if (isset($errors['shipping_address'])): ?>
                                <div class="error-message show">
                                    <i class="fas fa-exclamation-circle"></i>
                                    <?php echo $errors['shipping_address']; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div class="checkout-card">
                        <h2><i class="fas fa-credit-card"></i> Payment Method</h2>
                        <p class="card-subtitle">Select your preferred payment method (simulated - no real payment)</p>

                        <div class="payment-options">
                            <label class="payment-option <?php echo ($_POST['payment_method'] ?? 'cash_on_delivery') === 'cash_on_delivery' ? 'selected' : ''; ?>">
                                <input type="radio" name="payment_method" value="cash_on_delivery" 
                                       <?php echo ($_POST['payment_method'] ?? 'cash_on_delivery') === 'cash_on_delivery' ? 'checked' : ''; ?>>
                                <div class="payment-icon"><i class="fas fa-money-bill-wave"></i></div>
                                <div class="payment-info">
                                    <strong>Cash on Delivery</strong>
                                    <span>Pay when you receive your order</span>
                                </div>
                            </label>

                            <label class="payment-option <?php echo ($_POST['payment_method'] ?? '') === 'bank_transfer' ? 'selected' : ''; ?>">
                                <input type="radio" name="payment_method" value="bank_transfer"
                                       <?php echo ($_POST['payment_method'] ?? '') === 'bank_transfer' ? 'checked' : ''; ?>>
                                <div class="payment-icon"><i class="fas fa-university"></i></div>
                                <div class="payment-info">
                                    <strong>Bank Transfer</strong>
                                    <span>Direct bank deposit</span>
                                </div>
                            </label>

                            <label class="payment-option <?php echo ($_POST['payment_method'] ?? '') === 'gcash' ? 'selected' : ''; ?>">
                                <input type="radio" name="payment_method" value="gcash"
                                       <?php echo ($_POST['payment_method'] ?? '') === 'gcash' ? 'checked' : ''; ?>>
                                <div class="payment-icon"><i class="fas fa-mobile-alt"></i></div>
                                <div class="payment-info">
                                    <strong>GCash</strong>
                                    <span>Pay via GCash e-wallet</span>
                                </div>
                            </label>

                            <label class="payment-option <?php echo ($_POST['payment_method'] ?? '') === 'credit_card' ? 'selected' : ''; ?>">
                                <input type="radio" name="payment_method" value="credit_card"
                                       <?php echo ($_POST['payment_method'] ?? '') === 'credit_card' ? 'checked' : ''; ?>>
                                <div class="payment-icon"><i class="fas fa-credit-card"></i></div>
                                <div class="payment-info">
                                    <strong>Credit/Debit Card</strong>
                                    <span>Visa, Mastercard (simulated)</span>
                                </div>
                            </label>
                        </div>
                        <?php if (isset($errors['payment_method'])): ?>
                            <div class="error-message show" style="margin-top:8px;">
                                <i class="fas fa-exclamation-circle"></i>
                                <?php echo $errors['payment_method']; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Order Notes -->
                    <div class="checkout-card">
                        <h2><i class="fas fa-sticky-note"></i> Order Notes (Optional)</h2>
                        <div class="form-group" style="margin-bottom:0;">
                            <textarea id="notes" name="notes" rows="3" 
                                      placeholder="Any special instructions for delivery..."
                                      class="<?php echo isset($errors['notes']) ? 'error' : ''; ?>"
                            ><?php echo htmlspecialchars($_POST['notes'] ?? ''); ?></textarea>
                            <?php if (isset($errors['notes'])): ?>
                                <div class="error-message show">
                                    <i class="fas fa-exclamation-circle"></i>
                                    <?php echo $errors['notes']; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Right: Order Summary -->
                <div class="checkout-summary">
                    <div class="checkout-card sticky-summary">
                        <h2><i class="fas fa-receipt"></i> Order Summary</h2>

                        <!-- Items List -->
                        <div class="checkout-items">
                            <?php foreach ($cart_items as $item): ?>
                                <?php if ($item['status'] === 'available'): ?>
                                <div class="checkout-item">
                                    <div class="checkout-item-thumb">
                                        <?php if (!empty($item['product_image']) && file_exists(__DIR__ . '/' . $item['product_image'])): ?>
                                            <img src="/<?php echo htmlspecialchars($item['product_image']); ?>" alt="">
                                        <?php else: ?>
                                            <i class="fas fa-box-open"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="checkout-item-info">
                                        <span class="checkout-item-name"><?php echo htmlspecialchars($item['product_name']); ?></span>
                                        <span class="checkout-item-qty">Qty: <?php echo $item['quantity']; ?></span>
                                    </div>
                                    <div class="checkout-item-price">
                                        $<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>

                        <!-- Totals -->
                        <div class="summary-divider"></div>
                        <div class="summary-row">
                            <span>Subtotal (<?php echo $cart_count; ?> items)</span>
                            <span>$<?php echo number_format($cart_total, 2); ?></span>
                        </div>
                        <div class="summary-row">
                            <span>Shipping</span>
                            <span class="free-shipping">Free</span>
                        </div>
                        <div class="summary-row">
                            <span>Tax</span>
                            <span>$0.00</span>
                        </div>
                        <div class="summary-divider"></div>
                        <div class="summary-row summary-total">
                            <span>Total</span>
                            <span>$<?php echo number_format($cart_total, 2); ?></span>
                        </div>

                        <!-- Place Order Button -->
                        <button type="submit" class="btn-place-order" <?php echo !empty($unavailable_items) ? 'disabled' : ''; ?>>
                            <i class="fas fa-check-circle"></i> Place Order
                        </button>

                        <p class="order-note">
                            <i class="fas fa-shield-alt"></i>
                            By placing this order, you agree to our terms. No real payment will be processed.
                        </p>

                        <!-- Back to Cart -->
                        <a href="/cart.php" class="btn-back-to-cart">
                            <i class="fas fa-arrow-left"></i> Back to Cart
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <?php require_once __DIR__ . '/includes/footer.php'; ?>

    <!-- Payment option selection script -->
    <script>
    document.querySelectorAll('.payment-option input[type="radio"]').forEach(radio => {
        radio.addEventListener('change', function() {
            document.querySelectorAll('.payment-option').forEach(opt => opt.classList.remove('selected'));
            this.closest('.payment-option').classList.add('selected');
        });
    });
    </script>
</body>
</html>
