<?php
// includes/session.php - Session management utilities

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Base URL constant - set in config/database.php, fallback if not defined
if (!defined('BASE_URL')) {
    define('BASE_URL', '');
}

/**
 * Generate a URL relative to the project base
 */
if (!function_exists('url')) {
    function url($path = '') {
        return BASE_URL . $path;
    }
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if logged-in user is admin
 */
function isAdmin() {
    return isLoggedIn() && isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin';
}

/**
 * Check if logged-in user is customer
 */
function isCustomer() {
    return isLoggedIn() && isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'customer';
}

/**
 * Require user to be logged in, redirect to login if not
 */
function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['error'] = 'Please log in to access this page.';
        header('Location: ' . url('/user/login.php'));
        exit;
    }
}

/**
 * Require admin access, redirect if not admin
 */
function requireAdmin() {
    if (!isAdmin()) {
        $_SESSION['error'] = 'Access denied. Admin privileges required.';
        header('Location: ' . url('/admin/login.php'));
        exit;
    }
}

/**
 * Redirect logged-in users away from login/register pages
 */
function redirectIfLoggedIn() {
    if (isLoggedIn()) {
        if (isAdmin()) {
            header('Location: ' . url('/admin/index.php'));
        } else {
            header('Location: ' . url('/index.php'));
        }
        exit;
    }
}

/**
 * Set flash message
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get and clear flash message
 */
function getFlashMessage() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token
 */
function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>
