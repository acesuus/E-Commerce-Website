<?php
// products.php - Product Listing with Search & Filter
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/session.php';

$pageTitle = 'Products';

// Get filter parameters
$search = trim($_GET['search'] ?? '');
$category_id = intval($_GET['category'] ?? 0);
$sort = $_GET['sort'] ?? 'newest';
$status_filter = $_GET['status'] ?? '';

// Fetch categories for filter dropdown
$categories = $pdo->query("SELECT * FROM categories WHERE status = 'active' ORDER BY category_name")->fetchAll();

// Build product query with filters
$where_clauses = [];
$params = [];

// Search filter
if (!empty($search)) {
    $where_clauses[] = "(p.product_name LIKE ? OR p.description LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

// Category filter
if ($category_id > 0) {
    $where_clauses[] = "p.category_id = ?";
    $params[] = $category_id;
}

// Status filter
if (!empty($status_filter) && in_array($status_filter, ['available', 'out_of_stock'])) {
    $where_clauses[] = "p.status = ?";
    $params[] = $status_filter;
} else {
    // By default, show available and out_of_stock (not discontinued)
    $where_clauses[] = "p.status != 'discontinued'";
}

// Build WHERE clause
$where_sql = !empty($where_clauses) ? 'WHERE ' . implode(' AND ', $where_clauses) : '';

// Sort order
$order_sql = match($sort) {
    'price_low' => 'ORDER BY p.price ASC',
    'price_high' => 'ORDER BY p.price DESC',
    'name_az' => 'ORDER BY p.product_name ASC',
    'name_za' => 'ORDER BY p.product_name DESC',
    default => 'ORDER BY p.created_at DESC'
};

// Execute query
$sql = "SELECT p.*, c.category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.category_id 
        {$where_sql} 
        {$order_sql}";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();
$product_count = count($products);

// Get active category name for display
$active_category_name = '';
if ($category_id > 0) {
    foreach ($categories as $cat) {
        if ($cat['category_id'] == $category_id) {
            $active_category_name = $cat['category_name'];
            break;
        }
    }
}
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
        <!-- Page Header -->
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
                <!-- Category Filter -->
                <select name="category" class="filter-select" onchange="document.getElementById('filterForm').submit()">
                    <option value="0">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['category_id']; ?>" 
                                <?php echo $category_id == $cat['category_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['category_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <!-- Sort Filter -->
                <select name="sort" class="filter-select" onchange="document.getElementById('filterForm').submit()">
                    <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                    <option value="price_low" <?php echo $sort === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                    <option value="price_high" <?php echo $sort === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                    <option value="name_az" <?php echo $sort === 'name_az' ? 'selected' : ''; ?>>Name: A to Z</option>
                    <option value="name_za" <?php echo $sort === 'name_za' ? 'selected' : ''; ?>>Name: Z to A</option>
                </select>

                <!-- Search Button -->
                <button type="submit" class="filter-btn">
                    <i class="fas fa-search"></i> Search
                </button>

                <?php if (!empty($search) || $category_id > 0 || $sort !== 'newest'): ?>
                    <a href="/products.php" class="filter-btn reset">
                        <i class="fas fa-times"></i> Clear
                    </a>
                <?php endif; ?>
            </div>
        </form>

        <!-- Results Info -->
        <div class="results-info">
            <span class="count">
                Showing <strong><?php echo $product_count; ?></strong> product<?php echo $product_count !== 1 ? 's' : ''; ?>
                <?php if (!empty($search)): ?>
                    for "<strong><?php echo htmlspecialchars($search); ?></strong>"
                <?php endif; ?>
            </span>
        </div>

        <!-- Product Grid -->
        <div class="product-grid">
            <?php if ($product_count > 0): ?>
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <a href="/product_detail.php?id=<?php echo $product['product_id']; ?>">
                            <div class="product-image">
                                <?php if (!empty($product['product_image']) && file_exists(__DIR__ . '/' . $product['product_image'])): ?>
                                    <img src="/<?php echo htmlspecialchars($product['product_image']); ?>" 
                                         alt="<?php echo htmlspecialchars($product['product_name']); ?>">
                                <?php else: ?>
                                    <i class="fas fa-box-open placeholder-icon"></i>
                                <?php endif; ?>

                                <!-- Status Badge -->
                                <?php if ($product['status'] === 'out_of_stock'): ?>
                                    <span class="badge badge-out-of-stock">Out of Stock</span>
                                <?php elseif ($product['stock_quantity'] <= 5 && $product['stock_quantity'] > 0): ?>
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
                                <?php if ($product['status'] === 'available'): ?>
                                    <a href="/product_detail.php?id=<?php echo $product['product_id']; ?>" class="btn-view">
                                        View Details
                                    </a>
                                <?php else: ?>
                                    <span class="product-stock out">Out of Stock</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- No Results -->
                <div class="no-results">
                    <i class="fas fa-search"></i>
                    <h3>No products found</h3>
                    <p>
                        <?php if (!empty($search)): ?>
                            No results for "<?php echo htmlspecialchars($search); ?>". Try a different search term.
                        <?php else: ?>
                            No products match your current filters. Try adjusting your criteria.
                        <?php endif; ?>
                    </p>
                    <a href="/products.php" class="filter-btn" style="display:inline-block; margin-top:16px; width:auto;">
                        View All Products
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php require_once __DIR__ . '/includes/footer.php'; ?>
