<?php
echo "<h1>Database Connection Test</h1>";

try {
    // Test basic PHP
    echo "<p>✓ PHP is working</p>";
    
    // Test database connection
    require_once 'src/Database.php';
    $db = new Database();
    $connection = $db->getConnection();
    
    echo "<p>✓ Database connection successful</p>";
    
    // Test products table
    $stmt = $connection->query("SELECT COUNT(*) as count FROM products");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p>✓ Products table has " . $result['count'] . " records</p>";
    
    // Test cart table
    $stmt = $connection->query("SELECT COUNT(*) as count FROM cart");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p>✓ Cart table has " . $result['count'] . " records</p>";
    
    // Test API endpoint
    echo "<h2>Testing API Endpoints</h2>";
    
    // Test products API
    $productsUrl = "http://localhost:8000/api_mysql.php/products";
    $products = file_get_contents($productsUrl);
    if ($products) {
        $productsData = json_decode($products, true);
        echo "<p>✓ Products API returns " . count($productsData) . " products</p>";
    } else {
        echo "<p>✗ Products API failed</p>";
    }
    
} catch (Exception $e) {
    echo "<p>✗ Error: " . $e->getMessage() . "</p>";
}

echo "<h2>Next Steps</h2>";
echo "<ol>";
echo "<li>Import the database schema: <code>database/ecommerce_simple.sql</code> into phpMyAdmin</li>";
echo "<li>Access your e-commerce site at: <a href='http://localhost:8000' target='_blank'>http://localhost:8000</a></li>";
echo "<li>All cart, order, and user data will now sync with your MySQL database in XAMPP</li>";
echo "</ol>";
?>