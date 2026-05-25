<?php
// test_db.php - Test database connection (Fixed version)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Direct connection (same as simple_test.php)
$host = '127.0.0.1';
$dbname = 'ecommerce_website';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;port=3306;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Connection Test</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 50px;
            background: #f5f5f5;
        }
        .container {
            max-width: 1000px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #28a745;
            border-bottom: 3px solid #28a745;
            padding-bottom: 10px;
        }
        h2 {
            color: #333;
            margin-top: 30px;
        }
        .success {
            color: green;
            font-weight: bold;
            background: #d4edda;
            padding: 10px;
            border-radius: 5px;
        }
        .error {
            color: red;
            font-weight: bold;
            background: #f8d7da;
            padding: 10px;
            border-radius: 5px;
        }
        .stats {
            background: #e9ecef;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #28a745;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🛒 E-Commerce Website - Database Test</h1>
        
        <?php
        try {
            // Test 1: Simple query to check connection
            $testQuery = $pdo->query("SELECT 1");
            echo '<div class="success">✅ Database connection successful!</div>';
            
            // Get database statistics
            echo '<div class="stats">';
            echo '<h3>📊 Database Statistics:</h3>';
            echo '<ul>';
            
            $tables = ['users', 'categories', 'products', 'orders', 'order_items'];
            foreach($tables as $table) {
                $count = $pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn();
                echo "<li><strong>$table:</strong> $count records</li>";
            }
            echo '</ul>';
            echo '</div>';
            
            // Display users
            $users = $pdo->query("SELECT user_id, full_name, email, user_type FROM users")->fetchAll();
            echo '<h2>👥 Users Table</h2>';
            echo '<table>';
            echo '<tr><th>ID</th><th>Full Name</th><th>Email</th><th>User Type</th></tr>';
            foreach($users as $user) {
                echo '<tr>';
                echo '<td>' . $user['user_id'] . '</td>';
                echo '<td>' . htmlspecialchars($user['full_name']) . '</td>';
                echo '<td>' . htmlspecialchars($user['email']) . '</td>';
                echo '<td>' . $user['user_type'] . '</td>';
                echo '</tr>';
            }
            echo '</table>';
            
            // Display products with categories
            $products = $pdo->query("
                SELECT p.product_id, p.product_name, p.price, p.stock_quantity, c.category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.category_id 
                LIMIT 5
            ")->fetchAll();
            
            echo '<h2>📦 Products (Sample of 5)</h2>';
            echo '<table>';
            echo '<tr><th>ID</th><th>Product Name</th><th>Price</th><th>Stock</th><th>Category</th></tr>';
            foreach($products as $product) {
                echo '<tr>';
                echo '<td>' . $product['product_id'] . '</td>';
                echo '<td>' . htmlspecialchars($product['product_name']) . '</td>';
                echo '<td>$' . number_format($product['price'], 2) . '</td>';
                echo '<td>' . $product['stock_quantity'] . '</td>';
                echo '<td>' . htmlspecialchars($product['category_name']) . '</td>';
                echo '</tr>';
            }
            echo '</table>';
            
            echo '<br><div class="success">🎉 Database is fully functional! Ready to build the website.</div>';
            
        } catch(PDOException $e) {
            echo '<div class="error">❌ Database Error: ' . $e->getMessage() . '</div>';
        } catch(Exception $e) {
            echo '<div class="error">❌ General Error: ' . $e->getMessage() . '</div>';
        }
        ?>
    </div>
</body>
</html>