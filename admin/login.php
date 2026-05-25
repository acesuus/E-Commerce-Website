<?php
// admin/login.php - Admin Login Controller
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/session.php';

// Redirect if already logged in as admin
if (isAdmin()) {
    header('Location: /admin/index.php');
    exit;
}

$errors = [];
$old_email = '';
$flash = getFlashMessage();

// Process Login Form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $errors[] = 'Invalid form submission. Please try again.';
    } else {
        $email = trim(strtolower($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';
        $old_email = $email;

        if (empty($email)) { $errors['email'] = 'Admin email is required.'; }
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors['email'] = 'Please enter a valid email address.'; }

        if (empty($password)) { $errors['password'] = 'Password is required.'; }

        // Authenticate Admin
        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND user_type = 'admin'");
                $stmt->execute([$email]);
                $admin = $stmt->fetch();

                if ($admin) {
                    $password_valid = false;
                    if (password_get_info($admin['password_hash'])['algo'] !== null) {
                        $password_valid = password_verify($password, $admin['password_hash']);
                    } else {
                        $password_valid = ($password === $admin['password_hash']);
                    }

                    if ($password_valid) {
                        session_regenerate_id(true);
                        $_SESSION['user_id'] = $admin['user_id'];
                        $_SESSION['full_name'] = $admin['full_name'];
                        $_SESSION['email'] = $admin['email'];
                        $_SESSION['user_type'] = 'admin';
                        $_SESSION['logged_in_at'] = time();

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

// Render view
require_once __DIR__ . '/../views/admin/login.view.php';
