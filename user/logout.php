<?php
// user/logout.php - Logout & Session Destroy
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

// Start a new session for flash message
session_start();
setFlashMessage('success', 'You have been logged out successfully.');

// Redirect to login page
header('Location: ' . url('/user/login.php'));
exit;
?>
