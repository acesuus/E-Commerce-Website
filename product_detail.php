<?php
// product_detail.php - Single Product Detail Page
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/session.php';

// Get product ID from URL
$product_id = intval($_GET['id'] ?? 0);

if ($product_id <= 0) {
    header('Location: /products.php');
    exit;
}

// Fetch product with category info
$stmt = $pdo->prepare("
    SELECT p.*, c.category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.category_id 
    WHERE p.product_id = ?
");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

// If product not found, redirect
if (!$product) {
    header('Location: /products.php');
    exit;
}

$pageTitle = $product['product_name'];

// Fetch related products (same category, excluding current)
$related_stmt = $pdo->prepare("
    SELECT p.*, c.category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.category_id 
    WHERE p.category_id = ? AND p.product_id != ? AND p.status != 'discontinued'
    ORDER BY RAND()
    LIMIT 4
");
$related_stmt->execute([$product['category_id'], $product_id]);
$related_products = $related_stmt->fetchAll();

// Determine stock status
$stock_status = 'In Stock';
$stock_class = '';
if ($product['status'] === 'out_of_stock' || $product['stock_quantity'] <= 0) {
    $stock_status = 'Out of Stock';
    $stock_class = 'out';
} elseif ($product['stock_quantity'] <= 5) {
    $stock_status = "Only {$product['stock_quantity']} left!";
    $stock_class = 'low';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> - E-Commerce Store</title>
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
                <a href="/products.php" class="active">Products</a>
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

    <div class="page-container">
        <!-- Back Button -->
        <a href="/products.php" class="btn-back">
            <i class="fas fa-arrow-left"></i> Back to Products
        </a>

        <!-- Product Detail -->
        <div class="product-detail">
            <!-- Product Image -->
            <div class="product-gallery">
                <?php if (!empty($product['product_image']) && file_exists(__DIR__ . '/' . $product['product_image'])): ?>
                    <img src="/<?php echo htmlspecialchars($product['product_image']); ?>" 
                         alt="<?php echo htmlspecialchars($product['product_name']); ?>">
                <?php else: ?>
                    <i class="fas fa-box-open placeholder-icon"></i>
                <?php endif; ?>
            </div>

            <!-- Product Info -->
            <div class="detail-info">
                <span class="detail-category">
                    <i class="fas fa-tag"></i> <?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?>
                </span>

                <h1><?php echo htmlspecialchars($product['product_name']); ?></h1>

                <div class="detail-price">$<?php echo number_format($product['price'], 2); ?></div>

                <p class="detail-description">
                    <?php echo nl2br(htmlspecialchars($product['description'] ?? 'No description available for this product.')); ?>
                </p>

                <!-- Product Meta Info -->
                <div class="detail-meta">
                    <div class="meta-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Availability:</span>
                        <span class="value product-stock <?php echo $stock_class; ?>">
                            <?php echo $stock_status; ?>
                        </span>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-boxes-stacked"></i>
                        <span>Stock:</span>
                        <span class="value"><?php echo $product['stock_quantity']; ?> units</span>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-folder"></i>
                        <span>Category:</span>
                        <span class="value">
                            <a href="/products.php?category=<?php echo $product['category_id']; ?>" style="color:#667eea;">
                                <?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?>
                            </a>
                        </span>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-calendar"></i>
                        <span>Added:</span>
                        <span class="value"><?php echo date('M d, Y', strtotime($product['created_at'])); ?></span>
                    </div>
                </div>

                <!-- Add to Cart Button -->
                <?php if ($product['status'] === 'available' && $product['stock_quantity'] > 0): ?>
                    <form method="POST" action="/cart.php" style="display:inline;">
                        <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                        <input type="hidden" name="action" value="add">
                        <button type="submit" class="btn-add-cart">
                            <i class="fas fa-cart-plus"></i> Add to Cart
                        </button>
                    </form>
                <?php else: ?>
                    <button class="btn-add-cart" disabled>
                        <i class="fas fa-ban"></i> Out of Stock
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Related Products -->
        <?php if (!empty($related_products)): ?>
            <div style="margin-top: 60px;">
                <h2 class="section-title">
                    <i class="fas fa-th-large"></i> Related Products
                </h2>
                <div class="product-grid">
                    <?php foreach ($related_products as $related): ?>
                        <div class="product-card">
                            <a href="/product_detail.php?id=<?php echo $related['product_id']; ?>">
                                <div class="product-image">
                                    <?php if (!empty($related['product_image']) && file_exists(__DIR__ . '/' . $related['product_image'])): ?>
                                        <img src="/<?php echo htmlspecialchars($related['product_image']); ?>" 
                                             alt="<?php echo htmlspecialchars($related['product_name']); ?>">
                                    <?php else: ?>
                                        <i class="fas fa-box-open placeholder-icon"></i>
                                    <?php endif; ?>
                                </div>
                            </a>
                            <div class="product-info">
                                <span class="product-category">
                                    <?php echo htmlspecialchars($related['category_name'] ?? 'Uncategorized'); ?>
                                </span>
                                <a href="/product_detail.php?id=<?php echo $related['product_id']; ?>">
                                    <h3 class="product-name"><?php echo htmlspecialchars($related['product_name']); ?></h3>
                                </a>
                                <p class="product-description">
                                    <?php echo htmlspecialchars($related['description'] ?? ''); ?>
                                </p>
                                <div class="product-footer">
                                    <span class="product-price">$<?php echo number_format($related['price'], 2); ?></span>
                                    <a href="/product_detail.php?id=<?php echo $related['product_id']; ?>" class="btn-view">
                                        View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php require_once __DIR__ . '/includes/footer.php'; ?>
