<?php
// RESTful API for FORTES E-commerce Platform
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Database connection to your MySQL database
$host = 'localhost';
$dbname = 'fortes_ecommerce';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database connection failed: ' . $e->getMessage()]);
    exit();
}

// Get request method and path
$method = $_SERVER['REQUEST_METHOD'];
$path = $_SERVER['REQUEST_URI'];
$path_parts = explode('/', trim(parse_url($path, PHP_URL_PATH), '/'));

// Remove 'public' from path if present
if ($path_parts[0] === 'public') {
    array_shift($path_parts);
}
if ($path_parts[0] === 'api_mysql.php') {
    array_shift($path_parts);
}

$endpoint = $path_parts[0] ?? '';
$id = $path_parts[1] ?? null;

// Get JSON input for POST/PUT requests
$input = json_decode(file_get_contents('php://input'), true);

// Routing
switch ($endpoint) {
    case 'products':
        handleProducts($pdo, $method, $id, $input);
        break;
    
    case 'cart':
        handleCart($pdo, $method, $id, $input);
        break;
    
    case 'orders':
        handleOrders($pdo, $method, $id, $input);
        break;
    
    case 'categories':
        handleCategories($pdo, $method, $id, $input);
        break;
    
    case 'users':
        handleUsers($pdo, $method, $id, $input);
        break;
    
    case 'test':
        echo json_encode(['success' => true, 'message' => 'PHP API Connected to MySQL!', 'database' => $dbname, 'timestamp' => date('Y-m-d H:i:s')]);
        break;
    
    default:
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Endpoint not found', 'available_endpoints' => ['products', 'cart', 'orders', 'categories', 'users', 'test']]);
        break;
}

// Products endpoint - Full CRUD operations
function handleProducts($pdo, $method, $id, $input) {
    switch ($method) {
        case 'GET':
            if ($id) {
                // Get single product with category name
                $stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
                $stmt->execute([$id]);
                $product = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($product) {
                    echo json_encode(['success' => true, 'data' => $product]);
                } else {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'error' => 'Product not found']);
                }
            } else {
                // Get all products with category names
                $stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id");
                $stmt->execute();
                $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode(['success' => true, 'data' => $products, 'count' => count($products)]);
            }
            break;
            
        case 'POST':
            // Create new product
            $stmt = $pdo->prepare("INSERT INTO products (name, description, price, category_id, image, stock, rating) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $input['name'],
                $input['description'] ?? '',
                $input['price'],
                $input['category_id'] ?? 1,
                $input['image'] ?? null,
                $input['stock'] ?? 0,
                $input['rating'] ?? 4.5
            ]);
            
            $product_id = $pdo->lastInsertId();
            echo json_encode(['success' => true, 'data' => ['id' => $product_id], 'message' => 'Product created successfully']);
            break;
            
        case 'PUT':
            // Update product
            if ($id) {
                $stmt = $pdo->prepare("UPDATE products SET name = ?, description = ?, price = ?, category_id = ?, image = ?, stock = ?, rating = ? WHERE id = ?");
                $stmt->execute([
                    $input['name'],
                    $input['description'],
                    $input['price'],
                    $input['category_id'],
                    $input['image'],
                    $input['stock'],
                    $input['rating'],
                    $id
                ]);
                echo json_encode(['success' => true, 'message' => 'Product updated successfully']);
            }
            break;
            
        case 'DELETE':
            // Delete product
            if ($id) {
                $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
                $stmt->execute([$id]);
                echo json_encode(['success' => true, 'message' => 'Product deleted successfully']);
            }
            break;
    }
}

// Cart endpoint
function handleCart($pdo, $method, $id, $input) {
    switch ($method) {
        case 'GET':
            if ($id) {
                // Get cart for specific user
                $stmt = $pdo->prepare("SELECT c.*, p.name, p.price, p.image FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?");
                $stmt->execute([$id]);
            } else {
                // Get all cart items (for demo)
                $stmt = $pdo->prepare("SELECT c.*, p.name, p.price, p.image FROM cart c JOIN products p ON c.product_id = p.id");
                $stmt->execute();
            }
            $cart = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $cart]);
            break;
            
        case 'POST':
            // Add to cart
            $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)");
            $stmt->execute([
                $input['user_id'] ?? 1,
                $input['product_id'],
                $input['quantity'] ?? 1
            ]);
            echo json_encode(['success' => true, 'message' => 'Product added to cart successfully']);
            break;
            
        case 'PUT':
            // Update cart quantity
            if ($id) {
                $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
                $stmt->execute([$input['quantity'], $id]);
                echo json_encode(['success' => true, 'message' => 'Cart updated successfully']);
            }
            break;
            
        case 'DELETE':
            // Remove from cart
            if ($id) {
                $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ?");
                $stmt->execute([$id]);
                echo json_encode(['success' => true, 'message' => 'Item removed from cart successfully']);
            }
            break;
    }
}

// Orders endpoint
function handleOrders($pdo, $method, $id, $input) {
    switch ($method) {
        case 'GET':
            if ($id) {
                // Get specific order with items and addresses
                $stmt = $pdo->prepare("SELECT o.*, oa.*, pm.method_name FROM orders o LEFT JOIN order_addresses oa ON o.id = oa.order_id LEFT JOIN payment_methods pm ON o.payment_method_id = pm.id WHERE o.id = ?");
                $stmt->execute([$id]);
                $order = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($order) {
                    // Get order items
                    $stmt = $pdo->prepare("SELECT oi.*, p.name, p.image FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
                    $stmt->execute([$id]);
                    $order['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    echo json_encode(['success' => true, 'data' => $order]);
                } else {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'error' => 'Order not found']);
                }
            } else {
                // Get all orders
                $stmt = $pdo->prepare("SELECT o.*, u.name as user_name FROM orders o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC");
                $stmt->execute();
                $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode(['success' => true, 'data' => $orders]);
            }
            break;
            
        case 'POST':
            // Create new order
            try {
                $pdo->beginTransaction();
                
                // Insert order
                $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, status, payment_method_id) VALUES (?, ?, ?, ?)");
                $stmt->execute([
                    $input['user_id'] ?? 1,
                    $input['total_amount'],
                    $input['status'] ?? 'pending',
                    $input['payment_method_id'] ?? 1
                ]);
                
                $order_id = $pdo->lastInsertId();
                
                // Insert order items
                if (isset($input['items'])) {
                    $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
                    foreach ($input['items'] as $item) {
                        $stmt->execute([$order_id, $item['product_id'], $item['quantity'], $item['price']]);
                    }
                }
                
                // Insert address if provided
                if (isset($input['address'])) {
                    $stmt = $pdo->prepare("INSERT INTO order_addresses (order_id, street, city, state, zip_code, country) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([
                        $order_id,
                        $input['address']['street'],
                        $input['address']['city'],
                        $input['address']['state'],
                        $input['address']['zip_code'],
                        $input['address']['country']
                    ]);
                }
                
                $pdo->commit();
                echo json_encode(['success' => true, 'data' => ['order_id' => $order_id], 'message' => 'Order created successfully']);
                
            } catch (Exception $e) {
                $pdo->rollback();
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Failed to create order: ' . $e->getMessage()]);
            }
            break;
            
        case 'PUT':
            // Update order status
            if ($id) {
                $stmt = $pdo->prepare("UPDATE orders SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                $stmt->execute([$input['status'], $id]);
                echo json_encode(['success' => true, 'message' => 'Order status updated successfully']);
            }
            break;
    }
}

// Categories endpoint
function handleCategories($pdo, $method, $id, $input) {
    switch ($method) {
        case 'GET':
            if ($id) {
                $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
                $stmt->execute([$id]);
                $category = $stmt->fetch(PDO::FETCH_ASSOC);
                echo json_encode(['success' => true, 'data' => $category]);
            } else {
                $stmt = $pdo->prepare("SELECT * FROM categories ORDER BY name");
                $stmt->execute();
                $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode(['success' => true, 'data' => $categories]);
            }
            break;
            
        case 'POST':
            $stmt = $pdo->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
            $stmt->execute([$input['name'], $input['description'] ?? null]);
            echo json_encode(['success' => true, 'message' => 'Category created successfully']);
            break;
    }
}

// Users endpoint
function handleUsers($pdo, $method, $id, $input) {
    switch ($method) {
        case 'GET':
            if ($id) {
                $stmt = $pdo->prepare("SELECT id, name, email, created_at FROM users WHERE id = ?");
                $stmt->execute([$id]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                echo json_encode(['success' => true, 'data' => $user]);
            } else {
                $stmt = $pdo->prepare("SELECT id, name, email, created_at FROM users ORDER BY created_at DESC");
                $stmt->execute();
                $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode(['success' => true, 'data' => $users]);
            }
            break;
            
        case 'POST':
            // Register new user
            $hashed_password = password_hash($input['password'], PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$input['name'], $input['email'], $hashed_password]);
            echo json_encode(['success' => true, 'message' => 'User registered successfully']);
            break;
    }
}
?>