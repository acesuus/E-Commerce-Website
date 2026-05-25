<?php
// user/login.php - Customer Login Controller
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
                    $password_valid = false;
                    
                    if (password_get_info($user['password_hash'])['algo'] !== null) {
                        $password_valid = password_verify($password, $user['password_hash']);
                    } else {
                        $password_valid = ($password === $user['password_hash']);
                    }

                    if ($password_valid) {
                        session_regenerate_id(true);

                        $_SESSION['user_id'] = $user['user_id'];
                        $_SESSION['full_name'] = $user['full_name'];
                        $_SESSION['email'] = $user['email'];
                        $_SESSION['user_type'] = $user['user_type'];
                        $_SESSION['logged_in_at'] = time();

                        if (password_get_info($user['password_hash'])['algo'] === null) {
                            $new_hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
                            $update = $pdo->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
                            $update->execute([$new_hash, $user['user_id']]);
                        }

                        $redirect = $_SESSION['redirect_after_login'] ?? url('/index.php');
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
            }
        }
    }
}

// Generate CSRF token
$csrf_token = generateCSRFToken();

// Render view
require_once __DIR__ . '/../views/user/login.view.php';
