<?php require_once __DIR__ . '/partials/header.view.php'; ?>
<?php require_once __DIR__ . '/partials/nav.view.php'; ?>

    <div class="page-container">
        <a href="<?php echo url('/products.php'); ?>" class="btn-back"><i class="fas fa-arrow-left"></i> Back to Products</a>

        <div class="product-detail">
            <div class="product-gallery">
                <?php if (!empty($product['product_image'])): ?>
                    <img src="<?php echo url('/' . htmlspecialchars($product['product_image'])); ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>">
                <?php else: ?>
                    <i class="fas fa-box-open placeholder-icon"></i>
                <?php endif; ?>
            </div>

            <div class="detail-info">
                <span class="detail-category"><i class="fas fa-tag"></i> <?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?></span>
                <h1><?php echo htmlspecialchars($product['product_name']); ?></h1>
                <div class="detail-price">$<?php echo number_format($product['price'], 2); ?></div>
                <p class="detail-description"><?php echo nl2br(htmlspecialchars($product['description'] ?? 'No description available.')); ?></p>

                <div class="detail-meta">
                    <div class="meta-item"><i class="fas fa-check-circle"></i> <span>Availability:</span> <span class="value product-stock <?php echo $stock_class; ?>"><?php echo $stock_status; ?></span></div>
                    <div class="meta-item"><i class="fas fa-boxes-stacked"></i> <span>Stock:</span> <span class="value"><?php echo $product['stock_quantity']; ?> units</span></div>
                    <div class="meta-item"><i class="fas fa-folder"></i> <span>Category:</span> <span class="value"><a href="<?php echo url('/products.php?category=' . $product['category_id']); ?>" style="color:#667eea;"><?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?></a></span></div>
                    <div class="meta-item"><i class="fas fa-calendar"></i> <span>Added:</span> <span class="value"><?php echo date('M d, Y', strtotime($product['created_at'])); ?></span></div>
                </div>

                <?php if ($product['status'] === 'available' && $product['stock_quantity'] > 0): ?>
                    <form method="POST" action="<?php echo url('/cart.php'); ?>" style="display:inline;">
                        <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                        <input type="hidden" name="action" value="add">
                        <button type="submit" class="btn-add-cart"><i class="fas fa-cart-plus"></i> Add to Cart</button>
                    </form>
                <?php else: ?>
                    <button class="btn-add-cart" disabled><i class="fas fa-ban"></i> Out of Stock</button>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($related_products)): ?>
            <div style="margin-top: 60px;">
                <h2 class="section-title"><i class="fas fa-th-large"></i> Related Products</h2>
                <div class="product-grid">
                    <?php foreach ($related_products as $related): ?>
                        <div class="product-card">
                            <a href="<?php echo url('/product_detail.php?id=' . $related['product_id']); ?>">
                                <div class="product-image">
                                    <?php if (!empty($related['product_image'])): ?>
                                        <img src="<?php echo url('/' . htmlspecialchars($related['product_image'])); ?>" alt="<?php echo htmlspecialchars($related['product_name']); ?>">
                                    <?php else: ?>
                                        <i class="fas fa-box-open placeholder-icon"></i>
                                    <?php endif; ?>
                                </div>
                            </a>
                            <div class="product-info">
                                <span class="product-category"><?php echo htmlspecialchars($related['category_name'] ?? 'Uncategorized'); ?></span>
                                <a href="<?php echo url('/product_detail.php?id=' . $related['product_id']); ?>"><h3 class="product-name"><?php echo htmlspecialchars($related['product_name']); ?></h3></a>
                                <p class="product-description"><?php echo htmlspecialchars($related['description'] ?? ''); ?></p>
                                <div class="product-footer">
                                    <span class="product-price">$<?php echo number_format($related['price'], 2); ?></span>
                                    <a href="<?php echo url('/product_detail.php?id=' . $related['product_id']); ?>" class="btn-view">View Details</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

<?php require_once __DIR__ . '/partials/footer.view.php'; ?>
