<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - E-Commerce Store</title>
    <link rel="stylesheet" href="<?php echo url('/css/style.css'); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="logo"><i class="fas fa-user-plus"></i></div>
            <h1>Create Account</h1>
            <p class="subtitle">Join us and start shopping today</p>

            <?php if (!empty($errors) && isset($errors[0])): ?>
                <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <span><?php echo $errors[0]; ?></span></div>
            <?php endif; ?>

            <form method="POST" action="" id="registerForm" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                <div class="form-group">
                    <label for="full_name">Full Name *</label>
                    <div class="input-icon"><i class="fas fa-user"></i>
                        <input type="text" id="full_name" name="full_name" placeholder="Enter your full name" value="<?php echo htmlspecialchars($old['full_name']); ?>" class="<?php echo isset($errors['full_name']) ? 'error' : ''; ?>" required>
                    </div>
                    <?php if (isset($errors['full_name'])): ?><div class="error-message show"><i class="fas fa-exclamation-circle"></i> <?php echo $errors['full_name']; ?></div>
                    <?php else: ?><div class="error-message" id="full_name_error"><i class="fas fa-exclamation-circle"></i> <span></span></div><?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <div class="input-icon"><i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" placeholder="your@email.com" value="<?php echo htmlspecialchars($old['email']); ?>" class="<?php echo isset($errors['email']) ? 'error' : ''; ?>" required>
                    </div>
                    <?php if (isset($errors['email'])): ?><div class="error-message show"><i class="fas fa-exclamation-circle"></i> <?php echo $errors['email']; ?></div>
                    <?php else: ?><div class="error-message" id="email_error"><i class="fas fa-exclamation-circle"></i> <span></span></div><?php endif; ?>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <div class="input-icon"><i class="fas fa-phone"></i>
                            <input type="tel" id="phone" name="phone" placeholder="09123456789" value="<?php echo htmlspecialchars($old['phone']); ?>" class="<?php echo isset($errors['phone']) ? 'error' : ''; ?>">
                        </div>
                        <?php if (isset($errors['phone'])): ?><div class="error-message show"><i class="fas fa-exclamation-circle"></i> <?php echo $errors['phone']; ?></div><?php endif; ?>
                    </div>
                </div>

                <div class="form-group">
                    <label for="address">Address</label>
                    <div class="input-icon"><i class="fas fa-map-marker-alt"></i>
                        <input type="text" id="address" name="address" placeholder="Your delivery address" value="<?php echo htmlspecialchars($old['address']); ?>" class="<?php echo isset($errors['address']) ? 'error' : ''; ?>">
                    </div>
                    <?php if (isset($errors['address'])): ?><div class="error-message show"><i class="fas fa-exclamation-circle"></i> <?php echo $errors['address']; ?></div><?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="password">Password *</label>
                    <div class="input-icon"><i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" placeholder="Min. 8 characters" class="<?php echo isset($errors['password']) ? 'error' : ''; ?>" required>
                    </div>
                    <?php if (isset($errors['password'])): ?><div class="error-message show"><i class="fas fa-exclamation-circle"></i> <?php echo $errors['password']; ?></div>
                    <?php else: ?><div class="error-message" id="password_error"><i class="fas fa-exclamation-circle"></i> <span></span></div><?php endif; ?>
                    <div class="password-strength"><div class="strength-bar"><div class="fill" id="strengthFill"></div></div><span class="strength-text" id="strengthText"></span></div>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password *</label>
                    <div class="input-icon"><i class="fas fa-lock"></i>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-enter your password" class="<?php echo isset($errors['confirm_password']) ? 'error' : ''; ?>" required>
                    </div>
                    <?php if (isset($errors['confirm_password'])): ?><div class="error-message show"><i class="fas fa-exclamation-circle"></i> <?php echo $errors['confirm_password']; ?></div>
                    <?php else: ?><div class="error-message" id="confirm_password_error"><i class="fas fa-exclamation-circle"></i> <span></span></div><?php endif; ?>
                </div>

                <button type="submit" class="btn btn-primary"><i class="fas fa-user-plus"></i> Create Account</button>
            </form>

            <div class="auth-footer">Already have an account? <a href="<?php echo url('/user/login.php'); ?>">Sign In</a></div>
            <div class="divider"><span>or</span></div>
            <div class="auth-footer"><a href="<?php echo url('/admin/login.php'); ?>"><i class="fas fa-shield-alt"></i> Admin Login</a></div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('registerForm');
        const pw = document.getElementById('password'), cpw = document.getElementById('confirm_password');
        const sf = document.getElementById('strengthFill'), st = document.getElementById('strengthText');
        pw.addEventListener('input', function() {
            let s = 0; const v = this.value;
            if (v.length >= 8) s++; if (/[A-Z]/.test(v)) s++; if (/[a-z]/.test(v)) s++; if (/[0-9]/.test(v)) s++; if (/[^A-Za-z0-9]/.test(v)) s++;
            sf.className = 'fill';
            if (!v.length) { sf.style.width='0%'; st.textContent=''; }
            else if (s<=2) { sf.className='fill weak'; st.textContent='Weak'; st.style.color='#e53e3e'; }
            else if (s<=3) { sf.className='fill medium'; st.textContent='Medium'; st.style.color='#dd6b20'; }
            else { sf.className='fill strong'; st.textContent='Strong'; st.style.color='#38a169'; }
        });
        form.addEventListener('submit', function(e) {
            let v = true;
            if (document.getElementById('full_name').value.trim().length < 3) { showE('full_name_error','Min 3 chars'); v=false; } else hideE('full_name_error');
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(document.getElementById('email').value)) { showE('email_error','Invalid email'); v=false; } else hideE('email_error');
            if (pw.value.length < 8) { showE('password_error','Min 8 chars'); v=false; } else hideE('password_error');
            if (cpw.value !== pw.value) { showE('confirm_password_error','No match'); v=false; } else hideE('confirm_password_error');
            if (!v) e.preventDefault();
        });
        function showE(id,m){const e=document.getElementById(id);if(e){e.querySelector('span').textContent=m;e.classList.add('show');}}
        function hideE(id){const e=document.getElementById(id);if(e)e.classList.remove('show');}
    });
    </script>
</body>
</html>
