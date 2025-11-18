<?php
/**
 * Database Configuration for E-commerce Platform
 * Compatible with XAMPP MySQL/MariaDB
 * 
 * Instructions:
 * 1. Start XAMPP (Apache + MySQL)
 * 2. Open phpMyAdmin (http://localhost/phpmyadmin)
 * 3. Import the ecommerce_database.sql file
 * 4. Update the configuration below if needed
 */

class DatabaseConfig {
    // XAMPP Default Settings
    const DB_HOST = 'localhost';
    const DB_PORT = 3306;
    const DB_NAME = 'fortes_ecommerce';
    const DB_USER = 'root';
    const DB_PASS = ''; // XAMPP default is empty password
    
    // Connection options
    const DB_CHARSET = 'utf8mb4';
    const DB_OPTIONS = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
}

class Database {
    private static $pdo = null;
    
    public static function getConnection() {
        if (self::$pdo === null) {
            try {
                $dsn = sprintf(
                    'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                    DatabaseConfig::DB_HOST,
                    DatabaseConfig::DB_PORT,
                    DatabaseConfig::DB_NAME,
                    DatabaseConfig::DB_CHARSET
                );
                
                self::$pdo = new PDO(
                    $dsn,
                    DatabaseConfig::DB_USER,
                    DatabaseConfig::DB_PASS,
                    DatabaseConfig::DB_OPTIONS
                );
                
            } catch (PDOException $e) {
                throw new Exception('Database connection failed: ' . $e->getMessage());
            }
        }
        
        return self::$pdo;
    }
    
    // Test database connection
    public static function testConnection() {
        try {
            $pdo = self::getConnection();
            $stmt = $pdo->query('SELECT 1');
            return [
                'status' => 'success',
                'message' => 'Database connection successful',
                'server_info' => $pdo->getAttribute(PDO::ATTR_SERVER_VERSION)
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }
    
    // Get all products with category info
    public static function getProducts($limit = null, $offset = 0) {
        try {
            $pdo = self::getConnection();
            $sql = "SELECT * FROM product_catalog ORDER BY featured DESC, id ASC";
            
            if ($limit) {
                $sql .= " LIMIT :limit OFFSET :offset";
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
                $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            } else {
                $stmt = $pdo->prepare($sql);
            }
            
            $stmt->execute();
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            throw new Exception('Failed to fetch products: ' . $e->getMessage());
        }
    }
    
    // Get product by ID
    public static function getProduct($id) {
        try {
            $pdo = self::getConnection();
            $stmt = $pdo->prepare("SELECT * FROM product_catalog WHERE id = :id");
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch();
            
        } catch (Exception $e) {
            throw new Exception('Failed to fetch product: ' . $e->getMessage());
        }
    }
    
    // Get categories
    public static function getCategories() {
        try {
            $pdo = self::getConnection();
            $stmt = $pdo->query("SELECT * FROM categories WHERE status = 'active' ORDER BY sort_order, name");
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            throw new Exception('Failed to fetch categories: ' . $e->getMessage());
        }
    }
    
    // Create new order
    public static function createOrder($orderData) {
        try {
            $pdo = self::getConnection();
            $pdo->beginTransaction();
            
            // Insert order
            $stmt = $pdo->prepare("
                INSERT INTO orders (order_number, user_id, status, payment_method, 
                                  subtotal, shipping_amount, total_amount) 
                VALUES (:order_number, :user_id, :status, :payment_method, 
                        :subtotal, :shipping_amount, :total_amount)
            ");
            
            $stmt->execute([
                ':order_number' => $orderData['order_number'],
                ':user_id' => $orderData['user_id'],
                ':status' => $orderData['status'],
                ':payment_method' => $orderData['payment_method'],
                ':subtotal' => $orderData['subtotal'],
                ':shipping_amount' => $orderData['shipping_amount'],
                ':total_amount' => $orderData['total_amount']
            ]);
            
            $orderId = $pdo->lastInsertId();
            
            // Insert order items
            if (!empty($orderData['items'])) {
                $stmt = $pdo->prepare("
                    INSERT INTO order_items (order_id, product_id, product_name, quantity, unit_price, total_price) 
                    VALUES (:order_id, :product_id, :product_name, :quantity, :unit_price, :total_price)
                ");
                
                foreach ($orderData['items'] as $item) {
                    $stmt->execute([
                        ':order_id' => $orderId,
                        ':product_id' => $item['product_id'],
                        ':product_name' => $item['product_name'],
                        ':quantity' => $item['quantity'],
                        ':unit_price' => $item['unit_price'],
                        ':total_price' => $item['total_price']
                    ]);
                }
            }
            
            $pdo->commit();
            return $orderId;
            
        } catch (Exception $e) {
            $pdo->rollback();
            throw new Exception('Failed to create order: ' . $e->getMessage());
        }
    }
    
    // Get user orders
    public static function getUserOrders($userId) {
        try {
            $pdo = self::getConnection();
            $stmt = $pdo->prepare("
                SELECT o.*, COUNT(oi.id) as item_count 
                FROM orders o 
                LEFT JOIN order_items oi ON o.id = oi.order_id 
                WHERE o.user_id = :user_id 
                GROUP BY o.id 
                ORDER BY o.date_created DESC
            ");
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            throw new Exception('Failed to fetch orders: ' . $e->getMessage());
        }
    }
}

// Usage example and testing
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    header('Content-Type: application/json');
    
    try {
        // Test database connection
        $result = Database::testConnection();
        
        if ($result['status'] === 'success') {
            // Test data fetch
            $products = Database::getProducts(5); // Get first 5 products
            $categories = Database::getCategories();
            
            echo json_encode([
                'status' => 'success',
                'message' => 'Database connection and queries working!',
                'server_info' => $result['server_info'],
                'data' => [
                    'products_count' => count($products),
                    'categories_count' => count($categories),
                    'sample_product' => $products[0] ?? null
                ]
            ], JSON_PRETTY_PRINT);
        } else {
            echo json_encode($result, JSON_PRETTY_PRINT);
        }
        
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ], JSON_PRETTY_PRINT);
    }
}
?>