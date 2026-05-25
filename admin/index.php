<?php
// admin/index.php - Admin Dashboard Controller
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/session.php';

// Require admin access
requireAdmin();

$pageTitle = 'Admin Dashboard';

// Render view
require_once __DIR__ . '/../views/admin/index.view.php';
