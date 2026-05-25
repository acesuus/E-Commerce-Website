<?php
// user/register.php - Customer Registration Controller
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/session.php';

// Redirect if already logged in
redirectIfLoggedIn();

$errors = [];
$old = [
    'full_name' => '',
    'email' => '',
    'phone' => '',
    'address' => ''
];

// Process Registration Form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $errors[] = 'Invalid form submission. Please try again.';
    } else {
        $full_name = trim(htmlspecialchars($_POST['full_name'] ?? ''));
        $email = trim(strtolower($_POST['email'] ?? ''));
        $phone = trim(htmlspecialchars($_POST['phone'] ?? ''));
        $address = trim(htmlspecialchars($_POST['address'] ?? ''));
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        $old = compact('full_name', 'email', 'phone', 'address');

        // Full Name validation
        if (empty($full_name)) { $errors['full_name'] = 'Full name is required.'; }
        elseif (strlen($full_name) < 3) { $errors['full_name'] = 'Full name must be at least 3 characters.'; }
        elseif (strlen($full_name) > 100) { $errors['full_name'] = 'Full name cannot exceed 100 characters.'; }
        elseif (!preg_match('/^[a-zA-Z\s\.\-]+$/', $full_name)) { $errors['full_name'] = 'Full name can only contain letters, spaces, dots, and hyphens.'; }

        // Email validation
        if (empty($email)) { $errors['email'] = 'Email address is required.'; }
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors['email'] = 'Please enter a valid email address.'; }
        elseif (strlen($email) > 100) { $errors['email'] = 'Email cannot exceed 100 characters.'; }
        else {
            $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) { $errors['email'] = 'This email is already registered. Please login instead.'; }
        }

        // Phone validation
        if (!empty($phone) && !preg_match('/^[0-9\+\-\(\)\s]{7,20}$/', $phone)) {
            $errors['phone'] = 'Please enter a valid phone number.';
        }

        // Address validation
        if (!empty($address) && strlen($address) > 500) {
            $errors['address'] = 'Address cannot exceed 500 characters.';
        }

        // Password validation
        if (empty($password)) { $errors['password'] = 'Password is required.'; }
        elseif (strlen($password) < 8) { $errors['password'] = 'Password must be at least 8 characters long.'; }
        elseif (strlen($password) > 72) { $errors['password'] = 'Password cannot exceed 72 characters.'; }
        elseif (!preg_match('/[A-Z]/', $password)) { $errors['password'] = 'Password must contain at least one uppercase letter.'; }
        elseif (!preg_match('/[a-z]/', $password)) { $errors['password'] = 'Password must contain at least one lowercase letter.'; }
        elseif (!preg_match('/[0-9]/', $password)) { $errors['password'] = 'Password must contain at least one number.'; }

        // Confirm Password
        if (empty($confirm_password)) { $errors['confirm_password'] = 'Please confirm your password.'; }
        elseif ($password !== $confirm_password) { $errors['confirm_password'] = 'Passwords do not match.'; }

        // Register user
        if (empty($errors)) {
            try {
                $password_hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
                $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password_hash, phone, address, user_type) VALUES (?, ?, ?, ?, ?, 'customer')");
                $stmt->execute([$full_name, $email, $password_hash, $phone ?: null, $address ?: null]);

                setFlashMessage('success', 'Registration successful! Please log in with your credentials.');
                header('Location: ' . url('/user/login.php'));
                exit;
            } catch (PDOException $e) {
                $errors[] = 'Registration failed. Please try again later.';
            }
        }
    }
}

$csrf_token = generateCSRFToken();

// Render view
require_once __DIR__ . '/../views/user/register.view.php';
