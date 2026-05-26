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
        <div class="admin-welcome">
            <div><h1>Categories</h1><p>Manage product categories</p></div>
            <span class="admin-badge"><?php echo count($categories); ?> categories</span>
        </div>

        <?php if ($flash): ?>
            <div class="alert alert-<?php echo $flash['type']; ?>">
                <span><?php echo $flash['message']; ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors) && isset($errors[0])): ?>
            <div class="alert alert-error"><span><?php echo $errors[0]; ?></span></div>
        <?php endif; ?>

        <div class="category-layout">
            <!-- Left: Category List -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h3>All Categories</h3>
                </div>
                <table class="admin-table">
                    <thead>
                        <tr><th>Name</th><th>Description</th><th>Products</th><th>Status</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($categories)): ?>
                            <?php foreach ($categories as $cat): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($cat['category_name']); ?></strong></td>
                                    <td><span class="cat-desc"><?php echo htmlspecialchars($cat['description'] ?? '—'); ?></span></td>
                                    <td><span class="list-badge badge-success"><?php echo $cat['product_count']; ?></span></td>
                                    <td>
                                        <span class="order-status <?php echo $cat['status'] === 'active' ? 'status-completed' : 'status-cancelled'; ?>">
                                            <?php echo ucfirst($cat['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="table-actions">
                                            <a href="<?php echo url('/admin/categories.php?action=edit&id=' . $cat['category_id']); ?>" class="action-btn action-edit" title="Edit">Edit</a>
                                            <form method="POST" action="<?php echo url('/admin/categories.php'); ?>" style="display:inline;" onsubmit="return confirm('Delete this category?');">
                                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="category_id" value="<?php echo $cat['category_id']; ?>">
                                                <button type="submit" class="action-btn action-delete" title="Delete">Del</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="admin-empty">No categories yet. Add one using the form.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Right: Add/Edit Form -->
            <div class="category-form-panel">
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h3><?php echo $edit_category ? 'Edit Category' : 'Add Category'; ?></h3>
                        <?php if ($edit_category): ?>
                            <a href="<?php echo url('/admin/categories.php'); ?>" class="card-link">Cancel</a>
                        <?php endif; ?>
                    </div>
                    <div class="category-form-body">
                        <form method="POST" action="<?php echo url('/admin/categories.php'); ?>" novalidate>
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                            <input type="hidden" name="action" value="<?php echo $edit_category ? 'edit' : 'add'; ?>">
                            <?php if ($edit_category): ?>
                                <input type="hidden" name="category_id" value="<?php echo $edit_category['category_id']; ?>">
                            <?php endif; ?>

                            <div class="form-group">
                                <label for="category_name">Category Name *</label>
                                <input type="text" id="category_name" name="category_name" placeholder="e.g. Electronics"
                                       value="<?php echo htmlspecialchars($edit_category ? $edit_category['category_name'] : ($_POST['category_name'] ?? '')); ?>"
                                       class="<?php echo isset($errors['category_name']) ? 'error' : ''; ?>" required>
                                <?php if (isset($errors['category_name'])): ?>
                                    <div class="error-message show"><?php echo $errors['category_name']; ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea id="description" name="description" rows="3" placeholder="Brief description of category..."><?php echo htmlspecialchars($edit_category ? $edit_category['description'] : ($_POST['description'] ?? '')); ?></textarea>
                            </div>

                            <div class="form-group">
                                <label for="status">Status</label>
                                <select id="status" name="status" class="form-select">
                                    <?php $current_status = $edit_category ? $edit_category['status'] : ($_POST['status'] ?? 'active'); ?>
                                    <option value="active" <?php echo $current_status === 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo $current_status === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <?php echo $edit_category ? 'Save Changes' : 'Add Category'; ?>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php require_once __DIR__ . '/../../includes/footer.php'; ?>
</body>
</html>
