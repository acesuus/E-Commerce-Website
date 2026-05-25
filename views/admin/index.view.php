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
    <header class="header">
        <div class="container">
            <div class="brand"><i class="fas fa-shield-alt"></i> Admin Panel</div>
            <nav>
                <a href="/admin/index.php">Dashboard</a>
                <a href="/admin/products.php">Products</a>
                <a href="/admin/orders.php">Orders</a>
                <span style="color:#718096; margin-left:16px;">
                    Hello, <?php echo htmlspecialchars($_SESSION['full_name']); ?>
                </span>
                <a href="/admin/logout.php" style="color:#e53e3e;">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </nav>
        </div>
    </header>

    <main style="max-width:1200px; margin:40px auto; padding:0 20px;">
        <h2>Welcome to Admin Dashboard</h2>
        <p style="color:#718096; margin-top:8px;">
            Logged in as: <?php echo htmlspecialchars($_SESSION['email']); ?>
        </p>
    </main>

    <?php require_once __DIR__ . '/../../includes/footer.php'; ?>
</body>
</html>
