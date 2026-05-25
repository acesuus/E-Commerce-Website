<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - E-Commerce Store</title>
    <link rel="stylesheet" href="<?php echo url('/css/style.css'); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="auth-container admin">
        <div class="auth-card admin-card">
            <div class="logo"><i class="fas fa-shield-alt"></i></div>
            <h1>Admin Panel</h1>
            <p class="subtitle">Authorized personnel only</p>

            <?php if ($flash): ?>
                <div class="alert alert-<?php echo $flash['type'] === 'success' ? 'success' : 'error'; ?>">
                    <i class="fas fa-<?php echo $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                    <span><?php echo htmlspecialchars($flash['message']); ?></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($errors) && isset($errors[0])): ?>
                <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <span><?php echo $errors[0]; ?></span></div>
            <?php endif; ?>

            <form method="POST" action="" id="adminLoginForm" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                <div class="form-group">
                    <label for="email">Admin Email</label>
                    <div class="input-icon"><i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" placeholder="admin@ecommerce.com"
                               value="<?php echo htmlspecialchars($old_email); ?>"
                               class="<?php echo isset($errors['email']) ? 'error' : ''; ?>" required autofocus>
                    </div>
                    <?php if (isset($errors['email'])): ?><div class="error-message show"><i class="fas fa-exclamation-circle"></i> <?php echo $errors['email']; ?></div><?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-icon"><i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" placeholder="Enter admin password"
                               class="<?php echo isset($errors['password']) ? 'error' : ''; ?>" required>
                    </div>
                    <?php if (isset($errors['password'])): ?><div class="error-message show"><i class="fas fa-exclamation-circle"></i> <?php echo $errors['password']; ?></div><?php endif; ?>
                </div>

                <button type="submit" class="btn btn-admin"><i class="fas fa-sign-in-alt"></i> Access Admin Panel</button>
            </form>

            <div class="divider"><span>or</span></div>
            <div class="auth-footer"><a href="<?php echo url('/user/login.php'); ?>"><i class="fas fa-arrow-left"></i> Back to Customer Login</a></div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('adminLoginForm').addEventListener('submit', function(e) {
            let v = true;
            const em = document.getElementById('email'), pw = document.getElementById('password');
            if (!em.value.trim() || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(em.value)) { em.classList.add('error'); v=false; } else em.classList.remove('error');
            if (!pw.value.trim()) { pw.classList.add('error'); v=false; } else pw.classList.remove('error');
            if (!v) e.preventDefault();
        });
    });
    </script>
</body>
</html>
