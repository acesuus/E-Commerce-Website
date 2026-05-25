<?php require_once __DIR__ . '/partials/header.view.php'; ?>
<?php require_once __DIR__ . '/partials/nav.view.php'; ?>

    <div class="page-container">
        <div class="page-header">
            <h1>
                <?php if (!empty($active_category_name)): ?>
                    <?php echo htmlspecialchars($active_category_name); ?>
                <?php elseif (!empty($search)): ?>
                    Search: "<?php echo htmlspecialchars($search); ?>"
                <?php else: ?>
                    All Products
                <?php endif; ?>
            </h1>
            <p>Discover our collection of quality products</p>
        </div>

        <!-- Search & Filter Bar -->
        <form method="GET" action="/products.php" class="search-filter-bar" id="filterForm">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" name="search" placeholder="Search products..." 
                       value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="filter-group">
                <select name="category" class="filter-select" onchange="document.getElementById('filterForm').submit()">
                    <option value="0">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['category_id']; ?>" <?php echo $category_id == $cat['category_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['category_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <select name="sort" class="filter-select" onchange="document.getElementById('filterForm').submit()">
                    <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                    <option value="price_low" <?php echo $sort === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                    <option value="price_high" <?php echo $sort === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                    <option value="name_az" <?php echo $sort === 'name_az' ? 'selected' : ''; ?>>Name: A to Z</option>
                    <option value="name_za" <?php echo $sort === 'name_za' ? 'selected' : ''; ?>>Name: Z to A</option>
                </select>
                <button type="submit" class="filter-btn"><i class="fas fa-search"></i> Search</button>
                <?php if (!empty($search) || $category_id > 0 || $sort !== 'newest'): ?>
                    <a href="/products.php" class="filter-btn reset"><i class="fas fa-times"></i> Clear</a>
                <?php endif; ?>
            </div>
        </form>

        <div class="results-info">
            <span class="count">
                Showing <strong><?php echo $product_count; ?></strong> product<?php echo $product_count !== 1 ? 's' : ''; ?>
                <?php if (!empty($search)): ?> for "<strong><?php echo htmlspecialchars($search); ?></strong>"<?php endif; ?>
            </span>
        </div>

        <div class="product-grid">
            <?php if ($product_count > 0): ?>
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <a href="/product_detail.php?id=<?php echo $product['product_id']; ?>">
                            <div class="product-image">
                                <?php if (!empty($product['product_image'])): ?>
                                    <img src="/<?php echo htmlspecialchars($product['product_image']); ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>">
                                <?php else: ?>
                                    <i class="fas fa-box-open placeholder-icon"></i>
                                <?php endif; ?>
                                <?php if ($product['status'] === 'out_of_stock'): ?>
                                    <span class="badge badge-out-of-stock">Out of Stock</span>
                                <?php elseif ($product['stock_quantity'] <= 5 && $product['stock_quantity'] > 0): ?>
                                    <span class="badge badge-available">Only <?php echo $product['stock_quantity']; ?> left</span>
                                <?php endif; ?>
                            </div>
                        </a>
                        <div class="product-info">
                            <span class="product-category"><?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?></span>
                            <a href="/product_detail.php?id=<?php echo $product['product_id']; ?>">
                                <h3 class="product-name"><?php echo htmlspecialchars($product['product_name']); ?></h3>
                            </a>
                            <p class="product-description"><?php echo htmlspecialchars($product['description'] ?? 'No description available.'); ?></p>
                            <div class="product-footer">
                                <span class="product-price">$<?php echo number_format($product['price'], 2); ?></span>
                                <?php if ($product['status'] === 'available'): ?>
                                    <a href="/product_detail.php?id=<?php echo $product['product_id']; ?>" class="btn-view">View Details</a>
                                <?php else: ?>
                                    <span class="product-stock out">Out of Stock</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-results">
                    <i class="fas fa-search"></i>
                    <h3>No products found</h3>
                    <p><?php echo !empty($search) ? 'No results for "' . htmlspecialchars($search) . '". Try a different search term.' : 'No products match your current filters.'; ?></p>
                    <a href="/products.php" class="filter-btn" style="display:inline-block; margin-top:16px; width:auto;">View All Products</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

<?php require_once __DIR__ . '/partials/footer.view.php'; ?>
