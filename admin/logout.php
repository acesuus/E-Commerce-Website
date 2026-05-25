<?php
// admin/logout.php - Admin Logout
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/session.php';

// Unset all session variables
$_SESSION = [];

// Destroy session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Start new session for flash message
session_start();
setFlashMessage('success', 'Admin logged out successfully.');

// Redirect to admin login
header('Location: ' . url('/admin/login.php'));
exit;
?>
