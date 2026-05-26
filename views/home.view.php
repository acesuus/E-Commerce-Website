<?php require_once __DIR__ . '/partials/header.view.php'; ?>
<?php require_once __DIR__ . '/partials/nav.view.php'; ?>

    <!-- Hero Section -->
    <section class="hero-section">
        <h1>Welcome to E-Store</h1>
        <p>Discover amazing products at great prices. Shop electronics, clothing, books, and more.</p>
        <a href="<?php echo url('/products.php'); ?>" class="hero-btn">
            Shop Now
        </a>
    </section>

    <div class="page-container">
        <!-- Browse by Category -->
        <?php if (!empty($categories)): ?>
            <div style="margin-bottom: 50px;">
                <h2 class="section-title">
                    Browse by Category
                </h2>
                <div class="product-grid" style="grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));">
                    <?php foreach ($categories as $cat): ?>
                        <a href="<?php echo url('/products.php?category=' . $cat['category_id']); ?>" class="product-card" style="text-align:center;">
                            <div class="product-info" style="padding:30px 20px;">
                                <h3 class="product-name" style="font-size:18px; margin-bottom:8px;">
                                    <?php echo htmlspecialchars($cat['category_name']); ?>
                                </h3>
                                <p class="product-description" style="-webkit-line-clamp:1;">
                                    <?php echo $cat['product_count']; ?> product<?php echo $cat['product_count'] != 1 ? 's' : ''; ?>
                                </p>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Featured Products -->
        <div style="margin-bottom: 40px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
                <h2 class="section-title" style="margin-bottom:0;">
                    Featured Products
                </h2>
                <a href="<?php echo url('/products.php'); ?>" class="view-all-link">
                    View All
                </a>
            </div>

            <div class="product-grid">
                <?php if (!empty($featured_products)): ?>
                    <?php foreach ($featured_products as $product): ?>
                        <div class="product-card">
                            <a href="<?php echo url('/product_detail.php?id=' . $product['product_id']); ?>">
                                <div class="product-image">
                                    <?php if (!empty($product['product_image']) && file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . $product['product_image'])): ?>
                                        <img src="<?php echo url('/' . htmlspecialchars($product['product_image'])); ?>" 
                                             alt="<?php echo htmlspecialchars($product['product_name']); ?>">
                                    <?php else: ?>
                                        <i class="fas fa-box-open placeholder-icon"></i>
                                    <?php endif; ?>
                                    <?php if ($product['stock_quantity'] <= 5 && $product['stock_quantity'] > 0): ?>
                                        <span class="badge badge-available">Only <?php echo $product['stock_quantity']; ?> left</span>
                                    <?php endif; ?>
                                </div>
                            </a>
                            <div class="product-info">
                                <span class="product-category"><?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?></span>
                                <a href="<?php echo url('/product_detail.php?id=' . $product['product_id']); ?>">
                                    <h3 class="product-name"><?php echo htmlspecialchars($product['product_name']); ?></h3>
                                </a>
                                <p class="product-description"><?php echo htmlspecialchars($product['description'] ?? 'No description available.'); ?></p>
                                <div class="product-footer">
                                    <span class="product-price">$<?php echo number_format($product['price'], 2); ?></span>
                                    <a href="<?php echo url('/product_detail.php?id=' . $product['product_id']); ?>" class="btn-view">View Details</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-results">
                        <i class="fas fa-box-open"></i>
                        <h3>No products available yet</h3>
                        <p>Check back soon for new arrivals!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

<?php require_once __DIR__ . '/partials/footer.view.php'; ?>
