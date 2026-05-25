<?php require_once __DIR__ . '/partials/header.view.php'; ?>
<?php require_once __DIR__ . '/partials/nav.view.php'; ?>

    <div class="page-container">
        <div class="checkout-progress">
            <div class="progress-step completed"><i class="fas fa-shopping-cart"></i> Cart</div>
            <div class="progress-line active"></div>
            <div class="progress-step active"><i class="fas fa-clipboard-list"></i> Checkout</div>
            <div class="progress-line"></div>
            <div class="progress-step"><i class="fas fa-check-circle"></i> Confirmation</div>
        </div>

        <h1 class="checkout-title">Checkout</h1>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <span><?php echo $errors[0] ?? 'Please fix the errors below.'; ?></span></div>
        <?php endif; ?>
        <?php if (!empty($unavailable_items)): ?>
            <div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> <span>Some items are unavailable. Please <a href="/cart.php" style="color:#975a16;font-weight:600;">update your cart</a>.</span></div>
        <?php endif; ?>

        <form method="POST" action="/checkout.php" id="checkoutForm" novalidate>
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <div class="checkout-layout">
                <div class="checkout-form-section">
                    <div class="checkout-card">
                        <h2><i class="fas fa-truck"></i> Shipping Information</h2>
                        <div class="form-group"><label>Full Name</label><input type="text" value="<?php echo htmlspecialchars($user['full_name']); ?>" disabled class="disabled-input"></div>
                        <div class="form-group"><label>Email</label><input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled class="disabled-input"></div>
                        <div class="form-group"><label>Phone</label><input type="tel" value="<?php echo htmlspecialchars($user['phone'] ?? 'Not provided'); ?>" disabled class="disabled-input"></div>
                        <div class="form-group">
                            <label for="shipping_address">Shipping Address *</label>
                            <textarea id="shipping_address" name="shipping_address" rows="3" placeholder="Enter your complete shipping address" class="<?php echo isset($errors['shipping_address']) ? 'error' : ''; ?>" required><?php echo htmlspecialchars($_POST['shipping_address'] ?? $user['address'] ?? ''); ?></textarea>
                            <?php if (isset($errors['shipping_address'])): ?><div class="error-message show"><i class="fas fa-exclamation-circle"></i> <?php echo $errors['shipping_address']; ?></div><?php endif; ?>
                        </div>
                    </div>

                    <div class="checkout-card">
                        <h2><i class="fas fa-credit-card"></i> Payment Method</h2>
                        <p class="card-subtitle">Select your preferred payment method (simulated)</p>
                        <div class="payment-options">
                            <?php $pm = $_POST['payment_method'] ?? 'cash_on_delivery'; ?>
                            <label class="payment-option <?php echo $pm === 'cash_on_delivery' ? 'selected' : ''; ?>"><input type="radio" name="payment_method" value="cash_on_delivery" <?php echo $pm === 'cash_on_delivery' ? 'checked' : ''; ?>><div class="payment-icon"><i class="fas fa-money-bill-wave"></i></div><div class="payment-info"><strong>Cash on Delivery</strong><span>Pay when you receive your order</span></div></label>
                            <label class="payment-option <?php echo $pm === 'bank_transfer' ? 'selected' : ''; ?>"><input type="radio" name="payment_method" value="bank_transfer" <?php echo $pm === 'bank_transfer' ? 'checked' : ''; ?>><div class="payment-icon"><i class="fas fa-university"></i></div><div class="payment-info"><strong>Bank Transfer</strong><span>Direct bank deposit</span></div></label>
                            <label class="payment-option <?php echo $pm === 'gcash' ? 'selected' : ''; ?>"><input type="radio" name="payment_method" value="gcash" <?php echo $pm === 'gcash' ? 'checked' : ''; ?>><div class="payment-icon"><i class="fas fa-mobile-alt"></i></div><div class="payment-info"><strong>GCash</strong><span>Pay via GCash e-wallet</span></div></label>
                            <label class="payment-option <?php echo $pm === 'credit_card' ? 'selected' : ''; ?>"><input type="radio" name="payment_method" value="credit_card" <?php echo $pm === 'credit_card' ? 'checked' : ''; ?>><div class="payment-icon"><i class="fas fa-credit-card"></i></div><div class="payment-info"><strong>Credit/Debit Card</strong><span>Visa, Mastercard (simulated)</span></div></label>
                        </div>
                    </div>

                    <div class="checkout-card">
                        <h2><i class="fas fa-sticky-note"></i> Order Notes (Optional)</h2>
                        <div class="form-group" style="margin-bottom:0;"><textarea name="notes" rows="3" placeholder="Any special instructions..."><?php echo htmlspecialchars($_POST['notes'] ?? ''); ?></textarea></div>
                    </div>
                </div>

                <div class="checkout-summary">
                    <div class="checkout-card sticky-summary">
                        <h2><i class="fas fa-receipt"></i> Order Summary</h2>
                        <div class="checkout-items">
                            <?php foreach ($cart_items as $item): ?>
                                <?php if ($item['status'] === 'available'): ?>
                                <div class="checkout-item">
                                    <div class="checkout-item-thumb"><?php if (!empty($item['product_image'])): ?><img src="/<?php echo htmlspecialchars($item['product_image']); ?>" alt=""><?php else: ?><i class="fas fa-box-open"></i><?php endif; ?></div>
                                    <div class="checkout-item-info"><span class="checkout-item-name"><?php echo htmlspecialchars($item['product_name']); ?></span><span class="checkout-item-qty">Qty: <?php echo $item['quantity']; ?></span></div>
                                    <div class="checkout-item-price">$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></div>
                                </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                        <div class="summary-divider"></div>
                        <div class="summary-row"><span>Subtotal (<?php echo $cart_count; ?> items)</span><span>$<?php echo number_format($cart_total, 2); ?></span></div>
                        <div class="summary-row"><span>Shipping</span><span class="free-shipping">Free</span></div>
                        <div class="summary-divider"></div>
                        <div class="summary-row summary-total"><span>Total</span><span>$<?php echo number_format($cart_total, 2); ?></span></div>
                        <button type="submit" class="btn-place-order" <?php echo !empty($unavailable_items) ? 'disabled' : ''; ?>><i class="fas fa-check-circle"></i> Place Order</button>
                        <p class="order-note"><i class="fas fa-shield-alt"></i> No real payment will be processed.</p>
                        <a href="/cart.php" class="btn-back-to-cart"><i class="fas fa-arrow-left"></i> Back to Cart</a>
                    </div>
                </div>
            </div>
        </form>
    </div>

<?php require_once __DIR__ . '/partials/footer.view.php'; ?>
    <script>
    document.querySelectorAll('.payment-option input[type="radio"]').forEach(radio => {
        radio.addEventListener('change', function() { document.querySelectorAll('.payment-option').forEach(o => o.classList.remove('selected')); this.closest('.payment-option').classList.add('selected'); });
    });
    </script>
