<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - E-Commerce Store</title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="logo">
                <i class="fas fa-user-plus"></i>
            </div>
            <h1>Create Account</h1>
            <p class="subtitle">Join us and start shopping today</p>

            <?php if (!empty($errors) && isset($errors[0])): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo $errors[0]; ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="" id="registerForm" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                <!-- Full Name -->
                <div class="form-group">
                    <label for="full_name">Full Name *</label>
                    <div class="input-icon">
                        <i class="fas fa-user"></i>
                        <input type="text" id="full_name" name="full_name" 
                               placeholder="Enter your full name"
                               value="<?php echo htmlspecialchars($old['full_name']); ?>"
                               class="<?php echo isset($errors['full_name']) ? 'error' : ''; ?>"
                               required>
                    </div>
                    <?php if (isset($errors['full_name'])): ?>
                        <div class="error-message show"><i class="fas fa-exclamation-circle"></i> <?php echo $errors['full_name']; ?></div>
                    <?php else: ?>
                        <div class="error-message" id="full_name_error"><i class="fas fa-exclamation-circle"></i> <span></span></div>
                    <?php endif; ?>
                </div>

                <!-- Email -->
                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <div class="input-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" 
                               placeholder="your@email.com"
                               value="<?php echo htmlspecialchars($old['email']); ?>"
                               class="<?php echo isset($errors['email']) ? 'error' : ''; ?>"
                               required>
                    </div>
                    <?php if (isset($errors['email'])): ?>
                        <div class="error-message show"><i class="fas fa-exclamation-circle"></i> <?php echo $errors['email']; ?></div>
                    <?php else: ?>
                        <div class="error-message" id="email_error"><i class="fas fa-exclamation-circle"></i> <span></span></div>
                    <?php endif; ?>
                </div>

                <!-- Phone -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <div class="input-icon">
                            <i class="fas fa-phone"></i>
                            <input type="tel" id="phone" name="phone" 
                                   placeholder="09123456789"
                                   value="<?php echo htmlspecialchars($old['phone']); ?>"
                                   class="<?php echo isset($errors['phone']) ? 'error' : ''; ?>">
                        </div>
                        <?php if (isset($errors['phone'])): ?>
                            <div class="error-message show"><i class="fas fa-exclamation-circle"></i> <?php echo $errors['phone']; ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Address -->
                <div class="form-group">
                    <label for="address">Address</label>
                    <div class="input-icon">
                        <i class="fas fa-map-marker-alt"></i>
                        <input type="text" id="address" name="address" 
                               placeholder="Your delivery address"
                               value="<?php echo htmlspecialchars($old['address']); ?>"
                               class="<?php echo isset($errors['address']) ? 'error' : ''; ?>">
                    </div>
                    <?php if (isset($errors['address'])): ?>
                        <div class="error-message show"><i class="fas fa-exclamation-circle"></i> <?php echo $errors['address']; ?></div>
                    <?php endif; ?>
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label for="password">Password *</label>
                    <div class="input-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" 
                               placeholder="Min. 8 characters"
                               class="<?php echo isset($errors['password']) ? 'error' : ''; ?>"
                               required>
                    </div>
                    <?php if (isset($errors['password'])): ?>
                        <div class="error-message show"><i class="fas fa-exclamation-circle"></i> <?php echo $errors['password']; ?></div>
                    <?php else: ?>
                        <div class="error-message" id="password_error"><i class="fas fa-exclamation-circle"></i> <span></span></div>
                    <?php endif; ?>
                    <div class="password-strength">
                        <div class="strength-bar"><div class="fill" id="strengthFill"></div></div>
                        <span class="strength-text" id="strengthText"></span>
                    </div>
                </div>

                <!-- Confirm Password -->
                <div class="form-group">
                    <label for="confirm_password">Confirm Password *</label>
                    <div class="input-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="confirm_password" name="confirm_password" 
                               placeholder="Re-enter your password"
                               class="<?php echo isset($errors['confirm_password']) ? 'error' : ''; ?>"
                               required>
                    </div>
                    <?php if (isset($errors['confirm_password'])): ?>
                        <div class="error-message show"><i class="fas fa-exclamation-circle"></i> <?php echo $errors['confirm_password']; ?></div>
                    <?php else: ?>
                        <div class="error-message" id="confirm_password_error"><i class="fas fa-exclamation-circle"></i> <span></span></div>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-user-plus"></i> Create Account
                </button>
            </form>

            <div class="auth-footer">
                Already have an account? <a href="/user/login.php">Sign In</a>
            </div>
            <div class="divider"><span>or</span></div>
            <div class="auth-footer">
                <a href="/admin/login.php"><i class="fas fa-shield-alt"></i> Admin Login</a>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('registerForm');
        const passwordInput = document.getElementById('password');
        const confirmInput = document.getElementById('confirm_password');
        const strengthFill = document.getElementById('strengthFill');
        const strengthText = document.getElementById('strengthText');

        passwordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            if (password.length >= 8) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[a-z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;

            strengthFill.className = 'fill';
            if (password.length === 0) { strengthFill.style.width = '0%'; strengthText.textContent = ''; }
            else if (strength <= 2) { strengthFill.className = 'fill weak'; strengthText.textContent = 'Weak password'; strengthText.style.color = '#e53e3e'; }
            else if (strength <= 3) { strengthFill.className = 'fill medium'; strengthText.textContent = 'Medium strength'; strengthText.style.color = '#dd6b20'; }
            else { strengthFill.className = 'fill strong'; strengthText.textContent = 'Strong password'; strengthText.style.color = '#38a169'; }
        });

        form.addEventListener('submit', function(e) {
            let valid = true;
            const name = document.getElementById('full_name');
            if (name.value.trim().length < 3) { showError('full_name_error', 'Full name must be at least 3 characters.'); name.classList.add('error'); valid = false; }
            else { hideError('full_name_error'); name.classList.remove('error'); }

            const email = document.getElementById('email');
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim())) { showError('email_error', 'Please enter a valid email address.'); email.classList.add('error'); valid = false; }
            else { hideError('email_error'); email.classList.remove('error'); }

            if (passwordInput.value.length < 8) { showError('password_error', 'Password must be at least 8 characters.'); passwordInput.classList.add('error'); valid = false; }
            else { hideError('password_error'); passwordInput.classList.remove('error'); }

            if (confirmInput.value !== passwordInput.value) { showError('confirm_password_error', 'Passwords do not match.'); confirmInput.classList.add('error'); valid = false; }
            else { hideError('confirm_password_error'); confirmInput.classList.remove('error'); }

            if (!valid) e.preventDefault();
        });

        function showError(id, msg) { const el = document.getElementById(id); if (el) { el.querySelector('span').textContent = msg; el.classList.add('show'); } }
        function hideError(id) { const el = document.getElementById(id); if (el) el.classList.remove('show'); }
    });
    </script>
</body>
</html>
