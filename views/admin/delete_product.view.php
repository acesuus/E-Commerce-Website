<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - E-Commerce Store</title>
    <link rel="stylesheet" href="<?php echo url('/css/style.css'); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php require_once __DIR__ . '/partials/nav.view.php'; ?>

    <div class="admin-container">
        <a href="<?php echo url('/admin/products.php'); ?>" class="btn-back"><i class="fas fa-arrow-left"></i> Back to Products</a>

        <div class="delete-confirm-card">
            <div class="delete-icon"><i class="fas fa-exclamation-triangle"></i></div>
            <h2>Delete Product</h2>
            <p>Are you sure you want to delete this product?</p>

            <div class="delete-product-info">
                <div class="delete-thumb">
                    <?php if ($product['product_image']): ?>
                        <img src="<?php echo url('/' . htmlspecialchars($product['product_image'])); ?>" alt="">
                    <?php else: ?>
                        <i class="fas fa-box-open"></i>
                    <?php endif; ?>
                </div>
                <div>
                    <strong><?php echo htmlspecialchars($product['product_name']); ?></strong>
                    <span>$<?php echo number_format($product['price'], 2); ?> | Stock: <?php echo $product['stock_quantity']; ?></span>
                </div>
            </div>

            <p class="delete-warning"><i class="fas fa-info-circle"></i> If this product has order history, it will be marked as "discontinued" instead of deleted.</p>

            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="confirm_delete" value="yes">
                <div class="delete-actions">
                    <button type="submit" class="btn btn-danger"><i class="fas fa-trash"></i> Yes, Delete</button>
                    <a href="<?php echo url('/admin/products.php'); ?>" class="btn btn-cancel">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <?php require_once __DIR__ . '/../../includes/footer.php'; ?>
</body>
</html>
