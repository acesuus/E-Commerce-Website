<?php
// config/database.php

// Auto-detect Base URL from the project directory
// Works whether project is at document root or in a subdirectory (e.g. /E-Commerce-Website)
if (!defined('BASE_URL')) {
    $baseUrl = '';
    if (isset($_SERVER['SCRIPT_NAME'])) {
        // Find the project root relative to the document root
        $projectDir = dirname(__DIR__); // e.g. E:\xampp\htdocs\E-Commerce-Website
        $docRoot = realpath($_SERVER['DOCUMENT_ROOT']); // e.g. E:\xampp\htdocs
        if ($docRoot && strpos(realpath($projectDir), $docRoot) === 0) {
            $baseUrl = str_replace('\\', '/', substr(realpath($projectDir), strlen($docRoot)));
        }
    }
    define('BASE_URL', rtrim($baseUrl, '/'));
}

$host = '127.0.0.1';
$dbname = 'ecommerce_website';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;port=3306;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

/**
 * Generate a URL relative to the project base
 * Usage: url('/products.php') or url('/css/style.css')
 */
function url($path = '') {
    return BASE_URL . $path;
}
?>