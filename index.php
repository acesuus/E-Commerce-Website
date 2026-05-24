<?php
// index.php - Homepage with Featured Products
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/session.php';

$pageTitle = 'Home';

// Fetch featured products (newest available products)
$featured_stmt = $pdo->query("
    SELECT p.*, c.category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.category_id 
    WHERE p.status = 'available' 
    ORDER BY p.created_at DESC 
    LIMIT 8
");
$featured_products = $featured_stmt->fetchAll();

// Fetch categories for browsing
$categories = $pdo->query("
    SELECT c.*, COUNT(p.product_id) as product_count 
    FROM categories c 
    LEFT JOIN products p ON c.category_id = p.category_id AND p.status = 'available'
    WHERE c.status = 'active' 
    GROUP BY c.category_id 
    ORDER BY c.category_name
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Commerce Store - Shop the Best Products</title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Header Navigation -->
    <header class="header">
        <div class="container">
            <a href="/index.php" class="brand"><i class="fas fa-store"></i> E-Store</a>
            <nav class="nav-store">
                <a href="/index.php" class="active">Home</a>
                <a href="/products.php">Products</a>
                <a href="/cart.php"><i class="fas fa-shopping-cart"></i> Cart</a>
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

    <!-- Hero Section -->
    <section class="hero-section">
        <h1>Welcome to E-Store</h1>
        <p>Discover amazing products at great prices. Shop electronics, clothing, books, and more.</p>
        <a href="/products.php" class="hero-btn">
            <i class="fas fa-shopping-bag"></i> Shop Now
        </a>
    </section>

    <div class="page-container">
        <!-- Browse by Category -->
        <?php if (!empty($categories)): ?>
            <div style="margin-bottom: 50px;">
                <h2 class="section-title">
                    <i class="fas fa-th-large"></i> Browse by Category
                </h2>
                <div class="product-grid" style="grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));">
                    <?php foreach ($categories as $cat): ?>
                        <a href="/products.php?category=<?php echo $cat['category_id']; ?>" class="product-card" style="text-align:center;">
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
                    <i class="fas fa-star"></i> Featured Products
                </h2>
                <a href="/products.php" class="view-all-link">
                    View All <i class="fas fa-arrow-right"></i>
                </a>
            </div>

            <div class="product-grid">
                <?php if (!empty($featured_products)): ?>
                    <?php foreach ($featured_products as $product): ?>
                        <div class="product-card">
                            <a href="/product_detail.php?id=<?php echo $product['product_id']; ?>">
                                <div class="product-image">
                                    <?php if (!empty($product['product_image']) && file_exists(__DIR__ . '/' . $product['product_image'])): ?>
                                        <img src="/<?php echo htmlspecialchars($product['product_image']); ?>" 
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
                                <span class="product-category">
                                    <?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?>
                                </span>
                                <a href="/product_detail.php?id=<?php echo $product['product_id']; ?>">
                                    <h3 class="product-name"><?php echo htmlspecialchars($product['product_name']); ?></h3>
                                </a>
                                <p class="product-description">
                                    <?php echo htmlspecialchars($product['description'] ?? 'No description available.'); ?>
                                </p>
                                <div class="product-footer">
                                    <span class="product-price">
                                        $<?php echo number_format($product['price'], 2); ?>
                                    </span>
                                    <a href="/product_detail.php?id=<?php echo $product['product_id']; ?>" class="btn-view">
                                        View Details
                                    </a>
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

    <?php require_once __DIR__ . '/includes/footer.php'; ?>
