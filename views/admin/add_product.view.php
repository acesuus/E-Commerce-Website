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

        <div class="admin-welcome">
            <div><h1><i class="fas fa-plus-circle" style="color:#38a169;"></i> Add New Product</h1><p>Fill in the details to add a new product to your store</p></div>
        </div>

        <?php if (!empty($errors) && isset($errors[0])): ?>
            <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <span><?php echo $errors[0]; ?></span></div>
        <?php endif; ?>

        <div class="admin-card product-form-card">
            <form method="POST" action="" enctype="multipart/form-data" id="productForm" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                <div class="product-form-grid">
                    <!-- Left Column -->
                    <div class="product-form-main">
                        <div class="form-group">
                            <label for="product_name">Product Name *</label>
                            <input type="text" id="product_name" name="product_name" placeholder="Enter product name"
                                   value="<?php echo htmlspecialchars($old['product_name']); ?>"
                                   class="<?php echo isset($errors['product_name']) ? 'error' : ''; ?>" required>
                            <?php if (isset($errors['product_name'])): ?>
                                <div class="error-message show"><i class="fas fa-exclamation-circle"></i> <?php echo $errors['product_name']; ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" rows="5" placeholder="Describe the product..."><?php echo htmlspecialchars($old['description']); ?></textarea>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="price">Price ($) *</label>
                                <input type="number" id="price" name="price" step="0.01" min="0" placeholder="0.00"
                                       value="<?php echo htmlspecialchars($old['price']); ?>"
                                       class="<?php echo isset($errors['price']) ? 'error' : ''; ?>" required>
                                <?php if (isset($errors['price'])): ?>
                                    <div class="error-message show"><i class="fas fa-exclamation-circle"></i> <?php echo $errors['price']; ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="form-group">
                                <label for="stock_quantity">Stock Quantity *</label>
                                <input type="number" id="stock_quantity" name="stock_quantity" min="0" placeholder="0"
                                       value="<?php echo htmlspecialchars($old['stock_quantity']); ?>"
                                       class="<?php echo isset($errors['stock_quantity']) ? 'error' : ''; ?>" required>
                                <?php if (isset($errors['stock_quantity'])): ?>
                                    <div class="error-message show"><i class="fas fa-exclamation-circle"></i> <?php echo $errors['stock_quantity']; ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="category_id">Category</label>
                                <select id="category_id" name="category_id" class="form-select">
                                    <option value="0">-- No Category --</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat['category_id']; ?>" <?php echo $old['category_id'] == $cat['category_id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($cat['category_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select id="status" name="status" class="form-select">
                                    <option value="available" <?php echo $old['status'] === 'available' ? 'selected' : ''; ?>>Available</option>
                                    <option value="out_of_stock" <?php echo $old['status'] === 'out_of_stock' ? 'selected' : ''; ?>>Out of Stock</option>
                                    <option value="discontinued" <?php echo $old['status'] === 'discontinued' ? 'selected' : ''; ?>>Discontinued</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column - Image Upload -->
                    <div class="product-form-sidebar">
                        <div class="form-group">
                            <label>Product Image</label>
                            <div class="image-upload-area" id="imageUploadArea">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <p>Click or drag to upload</p>
                                <span>JPG, PNG, GIF, WEBP (max 5MB)</span>
                                <input type="file" name="product_image" id="product_image" accept="image/*">
                            </div>
                            <div class="image-preview" id="imagePreview" style="display:none;">
                                <img id="previewImg" src="" alt="Preview">
                                <button type="button" class="remove-preview" onclick="removePreview()"><i class="fas fa-times"></i></button>
                            </div>
                            <?php if (isset($errors['product_image'])): ?>
                                <div class="error-message show"><i class="fas fa-exclamation-circle"></i> <?php echo $errors['product_image']; ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Add Product</button>
                            <a href="<?php echo url('/admin/products.php'); ?>" class="btn btn-cancel">Cancel</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php require_once __DIR__ . '/../../includes/footer.php'; ?>

    <script>
    const fileInput = document.getElementById('product_image');
    const uploadArea = document.getElementById('imageUploadArea');
    const previewDiv = document.getElementById('imagePreview');
    const previewImg = document.getElementById('previewImg');

    uploadArea.addEventListener('click', () => fileInput.click());
    uploadArea.addEventListener('dragover', e => { e.preventDefault(); uploadArea.classList.add('dragover'); });
    uploadArea.addEventListener('dragleave', () => uploadArea.classList.remove('dragover'));
    uploadArea.addEventListener('drop', e => { e.preventDefault(); uploadArea.classList.remove('dragover'); fileInput.files = e.dataTransfer.files; showPreview(); });
    fileInput.addEventListener('change', showPreview);

    function showPreview() {
        if (fileInput.files && fileInput.files[0]) {
            const reader = new FileReader();
            reader.onload = e => { previewImg.src = e.target.result; previewDiv.style.display = 'block'; uploadArea.style.display = 'none'; };
            reader.readAsDataURL(fileInput.files[0]);
        }
    }
    function removePreview() { previewImg.src = ''; previewDiv.style.display = 'none'; uploadArea.style.display = 'flex'; fileInput.value = ''; }
    </script>
</body>
</html>
