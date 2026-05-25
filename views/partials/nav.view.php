    <header class="header">
        <div class="container">
            <a href="<?php echo url('/index.php'); ?>" class="brand"><i class="fas fa-store"></i> E-Store</a>
            <nav class="nav-store">
                <a href="<?php echo url('/index.php'); ?>" <?php echo ($activePage ?? '') === 'home' ? 'class="active"' : ''; ?>>Home</a>
                <a href="<?php echo url('/products.php'); ?>" <?php echo ($activePage ?? '') === 'products' ? 'class="active"' : ''; ?>>Products</a>
                <a href="<?php echo url('/cart.php'); ?>" <?php echo ($activePage ?? '') === 'cart' ? 'class="active"' : ''; ?>><i class="fas fa-shopping-cart"></i> Cart<?php echo isset($cart_count) && $cart_count > 0 ? " ($cart_count)" : ''; ?></a>
                <div class="nav-user">
                    <?php if (isLoggedIn()): ?>
                        <span>Hi, <?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                        <a href="<?php echo url('/user/orders.php'); ?>" <?php echo ($activePage ?? '') === 'orders' ? 'class="active"' : ''; ?>>My Orders</a>
                        <a href="<?php echo url('/user/logout.php'); ?>" class="btn-nav-outline">Logout</a>
                    <?php else: ?>
                        <a href="<?php echo url('/user/login.php'); ?>" class="btn-nav-outline">Sign In</a>
                        <a href="<?php echo url('/user/register.php'); ?>" class="btn-nav">Register</a>
                    <?php endif; ?>
                </div>
            </nav>
        </div>
    </header>
