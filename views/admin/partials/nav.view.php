    <header class="admin-header">
        <div class="admin-header-inner">
            <a href="<?php echo url('/admin/index.php'); ?>" class="admin-brand">
                <i class="fas fa-shield-alt"></i> Admin Panel
            </a>
            <nav class="admin-nav">
                <a href="<?php echo url('/admin/index.php'); ?>" class="<?php echo ($activePage ?? '') === 'dashboard' ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="<?php echo url('/admin/products.php'); ?>" class="<?php echo ($activePage ?? '') === 'products' ? 'active' : ''; ?>">
                    <i class="fas fa-box"></i> Products
                </a>
                <a href="<?php echo url('/admin/categories.php'); ?>" class="<?php echo ($activePage ?? '') === 'categories' ? 'active' : ''; ?>">
                    <i class="fas fa-folder-open"></i> Categories
                </a>
                <a href="<?php echo url('/admin/orders.php'); ?>" class="<?php echo ($activePage ?? '') === 'orders' ? 'active' : ''; ?>">
                    <i class="fas fa-shopping-bag"></i> Orders
                </a>
            </nav>
            <div class="admin-user">
                <span><i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                <a href="<?php echo url('/admin/logout.php'); ?>" class="admin-logout">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </header>
