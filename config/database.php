<?php
// config/database.php

// Base URL - change this to match your deployment
// If project is at http://localhost/E-Commerce-Website/ set to '/E-Commerce-Website'
// If project is at http://localhost/ (document root) set to ''
define('BASE_URL', '');

$host = '127.0.0.1';
$dbname = 'ecommerce_website';
$username = 'root';
$password = 'root';

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