<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - E-Commerce Store</title>
    <link rel="stylesheet" href="<?php echo url('/css/style.css'); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="logo"><i class="fas fa-shopping-bag"></i></div>
            <h1>Welcome Back</h1>
            <p class="subtitle">Sign in to your account to continue</p>

            <?php if ($flash): ?>
                <div class="alert alert-<?php echo $flash['type'] === 'success' ? 'success' : 'error'; ?>">
                    <i class="fas fa-<?php echo $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                    <span><?php echo htmlspecialchars($flash['message']); ?></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($errors) && isset($errors[0])): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo $errors[0]; ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="" id="loginForm" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <div class="input-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" placeholder="your@email.com"
                               value="<?php echo htmlspecialchars($old_email); ?>"
                               class="<?php echo isset($errors['email']) ? 'error' : ''; ?>" required autofocus>
                    </div>
                    <?php if (isset($errors['email'])): ?>
                        <div class="error-message show"><i class="fas fa-exclamation-circle"></i> <?php echo $errors['email']; ?></div>
                    <?php else: ?>
                        <div class="error-message" id="email_error"><i class="fas fa-exclamation-circle"></i> <span></span></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" placeholder="Enter your password"
                               class="<?php echo isset($errors['password']) ? 'error' : ''; ?>" required>
                    </div>
                    <?php if (isset($errors['password'])): ?>
                        <div class="error-message show"><i class="fas fa-exclamation-circle"></i> <?php echo $errors['password']; ?></div>
                    <?php else: ?>
                        <div class="error-message" id="password_error"><i class="fas fa-exclamation-circle"></i> <span></span></div>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn btn-primary"><i class="fas fa-sign-in-alt"></i> Sign In</button>
            </form>

            <div class="auth-footer">Don't have an account? <a href="<?php echo url('/user/register.php'); ?>">Create Account</a></div>
            <div class="divider"><span>or</span></div>
            <div class="auth-footer"><a href="<?php echo url('/admin/login.php'); ?>"><i class="fas fa-shield-alt"></i> Admin Login</a></div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('loginForm');
        form.addEventListener('submit', function(e) {
            let valid = true;
            const email = document.getElementById('email');
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim())) { showError('email_error', 'Please enter a valid email.'); email.classList.add('error'); valid = false; }
            else { hideError('email_error'); email.classList.remove('error'); }
            const password = document.getElementById('password');
            if (password.value.trim().length === 0) { showError('password_error', 'Password is required.'); password.classList.add('error'); valid = false; }
            else { hideError('password_error'); password.classList.remove('error'); }
            if (!valid) e.preventDefault();
        });
        function showError(id, msg) { const el = document.getElementById(id); if (el) { el.querySelector('span').textContent = msg; el.classList.add('show'); } }
        function hideError(id) { const el = document.getElementById(id); if (el) el.classList.remove('show'); }
    });
    </script>
</body>
</html>
