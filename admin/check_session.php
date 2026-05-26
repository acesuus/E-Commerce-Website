<?php
session_start();
echo "<pre>";
echo "Session ID: " . session_id() . "\n";
echo "User ID: " . ($_SESSION['user_id'] ?? 'NOT SET') . "\n";
echo "User Name: " . ($_SESSION['user_name'] ?? 'NOT SET') . "\n";
echo "User Type: " . ($_SESSION['user_type'] ?? 'NOT SET') . "\n";
echo "</pre>";
?>