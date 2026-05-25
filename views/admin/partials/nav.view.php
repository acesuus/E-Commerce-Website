    <header class="admin-header">
        <div class="admin-header-inner">
            <a href="<?php echo url('/admin/index.php'); ?>" class="admin-brand">
                <i class="fas fa-shield-alt"></i> Admin Panel
            </a>
            <nav class="admin-nav">
                <a href="<?php echo url('/admin/index.php'); ?>" class="<?php echo ($activePage ?? '') === 'dashboard' ? 'active' : ''; ?>">
                    Dashboard
                </a>
                <a href="<?php echo url('/admin/products.php'); ?>" class="<?php echo ($activePage ?? '') === 'products' ? 'active' : ''; ?>">
                    Products
                </a>
                <a href="<?php echo url('/admin/categories.php'); ?>" class="<?php echo ($activePage ?? '') === 'categories' ? 'active' : ''; ?>">
                    Categories
                </a>
                <a href="<?php echo url('/admin/orders.php'); ?>" class="<?php echo ($activePage ?? '') === 'orders' ? 'active' : ''; ?>">
                    Orders
                </a>
            </nav>
            <div class="admin-user">
                <span><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                <a href="<?php echo url('/admin/logout.php'); ?>" class="admin-logout">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </header>
