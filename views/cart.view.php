<?php require_once __DIR__ . '/partials/header.view.php'; ?>
<?php require_once __DIR__ . '/partials/nav.view.php'; ?>

    <div class="page-container">
        <div class="page-header">
            <h1><i class="fas fa-shopping-cart" style="color:#667eea;"></i> Shopping Cart</h1>
            <p><?php echo $cart_count > 0 ? "You have {$cart_count} item" . ($cart_count !== 1 ? 's' : '') . " in your cart" : 'Your cart is empty'; ?></p>
        </div>

        <?php if ($flash): ?>
            <div class="alert alert-<?php echo $flash['type']; ?>">
                <i class="fas fa-<?php echo $flash['type'] === 'success' ? 'check-circle' : ($flash['type'] === 'warning' ? 'exclamation-triangle' : 'exclamation-circle'); ?>"></i>
                <span><?php echo $flash['message']; ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($cart_items)): ?>
            <div class="cart-layout">
                <div class="cart-items-section">
                    <table class="cart-table">
                        <thead><tr><th>Product</th><th>Price</th><th>Quantity</th><th>Subtotal</th><th>Actions</th></tr></thead>
                        <tbody>
                            <?php foreach ($cart_items as $item): ?>
                                <?php $subtotal = $item['price'] * $item['quantity']; ?>
                                <tr class="cart-row <?php echo ($item['status'] !== 'available') ? 'unavailable' : ''; ?>">
                                    <td class="cart-product">
                                        <div class="cart-product-inner">
                                            <div class="cart-thumb">
                                                <?php if (!empty($item['product_image'])): ?><img src="<?php echo url('/' . htmlspecialchars($item['product_image'])); ?>" alt="">
                                                <?php else: ?><i class="fas fa-box-open"></i><?php endif; ?>
                                            </div>
                                            <div class="cart-product-details">
                                                <a href="<?php echo url('/product_detail.php?id=' . $item['product_id']); ?>" class="cart-product-name"><?php echo htmlspecialchars($item['product_name']); ?></a>
                                                <span class="cart-product-category"><?php echo htmlspecialchars($item['category_name'] ?? 'Uncategorized'); ?></span>
                                                <?php if ($item['status'] !== 'available'): ?><span class="cart-unavailable-badge">Unavailable</span><?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="cart-price" data-label="Price">$<?php echo number_format($item['price'], 2); ?></td>
                                    <td class="cart-quantity" data-label="Quantity">
                                        <form method="POST" action="<?php echo url('/cart.php'); ?>" class="qty-form">
                                            <input type="hidden" name="action" value="update">
                                            <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                            <div class="qty-control">
                                                <button type="button" class="qty-btn qty-minus" onclick="changeQty(this, -1)"><i class="fas fa-minus"></i></button>
                                                <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo $item['stock_quantity']; ?>" class="qty-input" onchange="this.form.submit()">
                                                <button type="button" class="qty-btn qty-plus" onclick="changeQty(this, 1)"><i class="fas fa-plus"></i></button>
                                            </div>
                                        </form>
                                    </td>
                                    <td class="cart-subtotal" data-label="Subtotal"><strong>$<?php echo number_format($subtotal, 2); ?></strong></td>
                                    <td class="cart-actions" data-label="Actions">
                                        <form method="POST" action="<?php echo url('/cart.php'); ?>" style="display:inline;">
                                            <input type="hidden" name="action" value="remove">
                                            <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                            <button type="submit" class="btn-remove" onclick="return confirm('Remove this item?')"><i class="fas fa-trash-alt"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div class="cart-bottom-actions">
                        <a href="<?php echo url('/products.php'); ?>" class="btn-continue-shopping"><i class="fas fa-arrow-left"></i> Continue Shopping</a>
                        <form method="POST" action="<?php echo url('/cart.php'); ?>" style="display:inline;">
                            <input type="hidden" name="action" value="clear">
                            <button type="submit" class="btn-clear-cart" onclick="return confirm('Clear all items?')"><i class="fas fa-trash"></i> Clear Cart</button>
                        </form>
                    </div>
                </div>

                <div class="cart-summary">
                    <h3><i class="fas fa-receipt"></i> Order Summary</h3>
                    <div class="summary-row"><span>Items (<?php echo $cart_count; ?>)</span><span>$<?php echo number_format($cart_total, 2); ?></span></div>
                    <div class="summary-row"><span>Shipping</span><span class="free-shipping">Free</span></div>
                    <div class="summary-divider"></div>
                    <div class="summary-row summary-total"><span>Total</span><span>$<?php echo number_format($cart_total, 2); ?></span></div>
                    <?php if (isLoggedIn()): ?>
                        <a href="<?php echo url('/checkout.php'); ?>" class="btn-checkout"><i class="fas fa-lock"></i> Proceed to Checkout</a>
                    <?php else: ?>
                        <a href="<?php echo url('/user/login.php'); ?>" class="btn-checkout"><i class="fas fa-sign-in-alt"></i> Sign In to Checkout</a>
                        <p class="checkout-note"><i class="fas fa-info-circle"></i> Please sign in to complete your purchase.</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="empty-cart">
                <i class="fas fa-shopping-cart"></i>
                <h3>Your cart is empty</h3>
                <p>Looks like you haven't added any products yet. Start shopping!</p>
                <a href="<?php echo url('/products.php'); ?>" class="btn-shop-now"><i class="fas fa-shopping-bag"></i> Browse Products</a>
            </div>
        <?php endif; ?>
    </div>

<?php require_once __DIR__ . '/partials/footer.view.php'; ?>

    <script>
    function changeQty(btn, delta) {
        const form = btn.closest('.qty-form');
        const input = form.querySelector('.qty-input');
        let newVal = Math.min(Math.max((parseInt(input.value) || 1) + delta, parseInt(input.min) || 1), parseInt(input.max) || 999);
        if (newVal !== parseInt(input.value)) { input.value = newVal; form.submit(); }
    }
    </script>
