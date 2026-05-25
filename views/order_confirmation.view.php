<?php require_once __DIR__ . '/partials/header.view.php'; ?>
<?php require_once __DIR__ . '/partials/nav.view.php'; ?>

    <div class="page-container">
        <div class="checkout-progress">
            <div class="progress-step completed"><i class="fas fa-shopping-cart"></i> Cart</div>
            <div class="progress-line active"></div>
            <div class="progress-step completed"><i class="fas fa-clipboard-list"></i> Checkout</div>
            <div class="progress-line active"></div>
            <div class="progress-step completed"><i class="fas fa-check-circle"></i> Confirmation</div>
        </div>

        <div class="order-success-banner">
            <div class="success-icon"><i class="fas fa-check-circle"></i></div>
            <h1>Order Placed Successfully!</h1>
            <p>Thank you for your purchase. Your order has been received and is being processed.</p>
            <div class="order-number">Order #<?php echo str_pad($order_id, 6, '0', STR_PAD_LEFT); ?></div>
        </div>

        <div class="confirmation-layout">
            <div class="confirmation-card">
                <h2><i class="fas fa-info-circle"></i> Order Details</h2>
                <div class="order-detail-grid">
                    <div class="order-detail-item"><span class="label">Order Number</span><span class="value">#<?php echo str_pad($order_id, 6, '0', STR_PAD_LEFT); ?></span></div>
                    <div class="order-detail-item"><span class="label">Order Date</span><span class="value"><?php echo date('F j, Y \a\t g:i A', strtotime($order['order_date'])); ?></span></div>
                    <div class="order-detail-item"><span class="label">Status</span><span class="value"><span class="order-status <?php echo $status_class; ?>"><?php echo ucfirst($order['order_status']); ?></span></span></div>
                    <div class="order-detail-item"><span class="label">Payment Method</span><span class="value"><?php echo htmlspecialchars($payment_display); ?></span></div>
                    <div class="order-detail-item"><span class="label">Shipping Address</span><span class="value"><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></span></div>
                    <?php if (!empty($order['notes'])): ?><div class="order-detail-item"><span class="label">Notes</span><span class="value"><?php echo htmlspecialchars($order['notes']); ?></span></div><?php endif; ?>
                </div>
            </div>

            <div class="confirmation-card">
                <h2><i class="fas fa-box"></i> Items Ordered</h2>
                <div class="confirmation-items">
                    <?php foreach ($order_items as $item): ?>
                        <div class="confirmation-item">
                            <div class="conf-item-thumb"><?php if (!empty($item['product_image'])): ?><img src="<?php echo url('/' . htmlspecialchars($item['product_image'])); ?>" alt=""><?php else: ?><i class="fas fa-box-open"></i><?php endif; ?></div>
                            <div class="conf-item-info"><span class="conf-item-name"><?php echo htmlspecialchars($item['product_name']); ?></span><span class="conf-item-meta"><?php echo htmlspecialchars($item['category_name'] ?? ''); ?> | Qty: <?php echo $item['quantity']; ?> x $<?php echo number_format($item['unit_price'], 2); ?></span></div>
                            <div class="conf-item-price">$<?php echo number_format($item['quantity'] * $item['unit_price'], 2); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="summary-divider"></div>
                <div class="summary-row summary-total"><span>Order Total</span><span>$<?php echo number_format($order['total_amount'], 2); ?></span></div>
            </div>
        </div>

        <div class="confirmation-actions">
            <a href="<?php echo url('/user/orders.php'); ?>" class="btn-view-orders"><i class="fas fa-list"></i> View All Orders</a>
            <a href="<?php echo url('/products.php'); ?>" class="btn-continue-shopping-conf"><i class="fas fa-shopping-bag"></i> Continue Shopping</a>
        </div>
    </div>

<?php require_once __DIR__ . '/partials/footer.view.php'; ?>
