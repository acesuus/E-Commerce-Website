<?php
// admin/login.php - Admin Login Authentication
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/session.php';

// Redirect if already logged in as admin
if (isAdmin()) {
    header('Location: /admin/index.php');
    exit;
}

$errors = [];
$old_email = '';

// Get flash messages
$flash = getFlashMessage();

// Process Login Form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Validate CSRF Token
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $errors[] = 'Invalid form submission. Please try again.';
    } else {
        $email = trim(strtolower($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';
        $old_email = $email;

        // Validation
        if (empty($email)) {
            $errors['email'] = 'Admin email is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address.';
        }

        if (empty($password)) {
            $errors['password'] = 'Password is required.';
        }


        // Authenticate Admin
        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND user_type = 'admin'");
                $stmt->execute([$email]);
                $admin = $stmt->fetch();

                if ($admin) {
                    $password_valid = false;
                    
                    // Check if password is bcrypt hashed
                    if (password_get_info($admin['password_hash'])['algo'] !== null) {
                        $password_valid = password_verify($password, $admin['password_hash']);
                    } else {
                        // Legacy plain text comparison (for sample data)
                        $password_valid = ($password === $admin['password_hash']);
                    }

                    if ($password_valid) {
                        // Regenerate session ID
                        session_regenerate_id(true);

                        // Set admin session
                        $_SESSION['user_id'] = $admin['user_id'];
                        $_SESSION['full_name'] = $admin['full_name'];
                        $_SESSION['email'] = $admin['email'];
                        $_SESSION['user_type'] = 'admin';
                        $_SESSION['logged_in_at'] = time();

                        // Upgrade plain text password to hash
                        if (password_get_info($admin['password_hash'])['algo'] === null) {
                            $new_hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
                            $update = $pdo->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
                            $update->execute([$new_hash, $admin['user_id']]);
                        }

                        header('Location: /admin/index.php');
                        exit;
                    } else {
                        $errors[] = 'Invalid admin credentials. Access denied.';
                    }
                } else {
                    $errors[] = 'Invalid admin credentials. Access denied.';
                }
            } catch (PDOException $e) {
                $errors[] = 'Login failed. Please try again later.';
            }
        }
    }
}

$csrf_token = generateCSRFToken();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - E-Commerce Store</title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="auth-container admin">
        <div class="auth-card admin-card">
            <!-- Logo & Title -->
            <div class="logo">
                <i class="fas fa-shield-alt"></i>
            </div>
            <h1>Admin Panel</h1>
            <p class="subtitle">Authorized personnel only</p>

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


            <!-- Admin Login Form -->
            <form method="POST" action="" id="adminLoginForm" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                <!-- Email -->
                <div class="form-group">
                    <label for="email">Admin Email</label>
                    <div class="input-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" 
                               placeholder="admin@ecommerce.com"
                               value="<?php echo htmlspecialchars($old_email); ?>"
                               class="<?php echo isset($errors['email']) ? 'error' : ''; ?>"
                               required autofocus>
                    </div>
                    <?php if (isset($errors['email'])): ?>
                        <div class="error-message show">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php echo $errors['email']; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" 
                               placeholder="Enter admin password"
                               class="<?php echo isset($errors['password']) ? 'error' : ''; ?>"
                               required>
                    </div>
                    <?php if (isset($errors['password'])): ?>
                        <div class="error-message show">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php echo $errors['password']; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-admin">
                    <i class="fas fa-sign-in-alt"></i> Access Admin Panel
                </button>
            </form>

            <!-- Back to store link -->
            <div class="divider"><span>or</span></div>
            <div class="auth-footer">
                <a href="/user/login.php"><i class="fas fa-arrow-left"></i> Back to Customer Login</a>
            </div>
        </div>
    </div>

    <!-- Client-side Validation -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('adminLoginForm');
        form.addEventListener('submit', function(e) {
            const email = document.getElementById('email');
            const password = document.getElementById('password');
            let valid = true;

            if (email.value.trim() === '' || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
                email.classList.add('error');
                valid = false;
            } else {
                email.classList.remove('error');
            }

            if (password.value.trim() === '') {
                password.classList.add('error');
                valid = false;
            } else {
                password.classList.remove('error');
            }

            if (!valid) e.preventDefault();
        });
    });
    </script>
</body>
</html>
