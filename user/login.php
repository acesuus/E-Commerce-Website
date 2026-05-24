<?php
// user/login.php - Customer Login
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/session.php';

// Redirect if already logged in
redirectIfLoggedIn();

$errors = [];
$old_email = '';

// Get flash messages (e.g., from successful registration)
$flash = getFlashMessage();

// Process Login Form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Validate CSRF Token
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $errors[] = 'Invalid form submission. Please try again.';
    } else {
        // Sanitize inputs
        $email = trim(strtolower($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';
        $old_email = $email;

        // ===== VALIDATION =====

        if (empty($email)) {
            $errors['email'] = 'Email address is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address.';
        }

        if (empty($password)) {
            $errors['password'] = 'Password is required.';
        }

        // ===== AUTHENTICATE USER =====
        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND user_type = 'customer'");
                $stmt->execute([$email]);
                $user = $stmt->fetch();

                if ($user) {
                    // Verify password (supports both hashed and plain text for legacy data)
                    $password_valid = false;
                    
                    // Check if password is bcrypt hashed
                    if (password_get_info($user['password_hash'])['algo'] !== null) {
                        $password_valid = password_verify($password, $user['password_hash']);
                    } else {
                        // Legacy plain text comparison (for sample data)
                        $password_valid = ($password === $user['password_hash']);
                    }

                    if ($password_valid) {
                        // Regenerate session ID to prevent session fixation
                        session_regenerate_id(true);

                        // Set session variables
                        $_SESSION['user_id'] = $user['user_id'];
                        $_SESSION['full_name'] = $user['full_name'];
                        $_SESSION['email'] = $user['email'];
                        $_SESSION['user_type'] = $user['user_type'];
                        $_SESSION['logged_in_at'] = time();

                        // If password was plain text, upgrade to hashed
                        if (password_get_info($user['password_hash'])['algo'] === null) {
                            $new_hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
                            $update = $pdo->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
                            $update->execute([$new_hash, $user['user_id']]);
                        }

                        // Redirect to homepage or intended page
                        $redirect = $_SESSION['redirect_after_login'] ?? '/index.php';
                        unset($_SESSION['redirect_after_login']);
                        header('Location: ' . $redirect);
                        exit;
                    } else {
                        $errors[] = 'Invalid email or password. Please try again.';
                    }
                } else {
                    $errors[] = 'Invalid email or password. Please try again.';
                }
            } catch (PDOException $e) {
                $errors[] = 'Login failed. Please try again later.';
                // Log error in production: error_log($e->getMessage());
            }
        }
    }
}

// Generate CSRF token
$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - E-Commerce Store</title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <!-- Logo & Title -->
            <div class="logo">
                <i class="fas fa-shopping-bag"></i>
            </div>
            <h1>Welcome Back</h1>
            <p class="subtitle">Sign in to your account to continue</p>

            <!-- Flash Messages -->
            <?php if ($flash): ?>
                <div class="alert alert-<?php echo $flash['type'] === 'success' ? 'success' : 'error'; ?>">
                    <i class="fas fa-<?php echo $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                    <span><?php echo htmlspecialchars($flash['message']); ?></span>
                </div>
            <?php endif; ?>

            <!-- Error Messages -->
            <?php if (!empty($errors) && isset($errors[0])): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo $errors[0]; ?></span>
                </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form method="POST" action="" id="loginForm" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                <!-- Email -->
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <div class="input-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" 
                               placeholder="your@email.com"
                               value="<?php echo htmlspecialchars($old_email); ?>"
                               class="<?php echo isset($errors['email']) ? 'error' : ''; ?>"
                               required autofocus>
                    </div>
                    <?php if (isset($errors['email'])): ?>
                        <div class="error-message show">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php echo $errors['email']; ?>
                        </div>
                    <?php else: ?>
                        <div class="error-message" id="email_error">
                            <i class="fas fa-exclamation-circle"></i>
                            <span></span>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" 
                               placeholder="Enter your password"
                               class="<?php echo isset($errors['password']) ? 'error' : ''; ?>"
                               required>
                    </div>
                    <?php if (isset($errors['password'])): ?>
                        <div class="error-message show">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php echo $errors['password']; ?>
                        </div>
                    <?php else: ?>
                        <div class="error-message" id="password_error">
                            <i class="fas fa-exclamation-circle"></i>
                            <span></span>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt"></i> Sign In
                </button>
            </form>

            <!-- Register Link -->
            <div class="auth-footer">
                Don't have an account? <a href="/user/register.php">Create Account</a>
            </div>

            <div class="divider"><span>or</span></div>

            <div class="auth-footer">
                <a href="/admin/login.php"><i class="fas fa-shield-alt"></i> Admin Login</a>
            </div>
        </div>
    </div>

    <!-- Client-side Validation Script -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('loginForm');

        form.addEventListener('submit', function(e) {
            let valid = true;

            // Email
            const email = document.getElementById('email');
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email.value.trim())) {
                showError('email_error', 'Please enter a valid email address.');
                email.classList.add('error');
                valid = false;
            } else {
                hideError('email_error');
                email.classList.remove('error');
            }

            // Password
            const password = document.getElementById('password');
            if (password.value.trim().length === 0) {
                showError('password_error', 'Password is required.');
                password.classList.add('error');
                valid = false;
            } else {
                hideError('password_error');
                password.classList.remove('error');
            }

            if (!valid) e.preventDefault();
        });

        function showError(id, msg) {
            const el = document.getElementById(id);
            if (el) {
                el.querySelector('span').textContent = msg;
                el.classList.add('show');
            }
        }

        function hideError(id) {
            const el = document.getElementById(id);
            if (el) el.classList.remove('show');
        }
    });
    </script>
</body>
</html>
