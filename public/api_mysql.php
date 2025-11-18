<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Include database connection
require_once '../src/Database.php';

// Get request data
$requestMethod = $_SERVER['REQUEST_METHOD'];
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestUri = str_replace('/api.php', '', $requestUri);
$inputData = json_decode(file_get_contents('php://input'), true);

try {
    // Route handling
    switch ($requestUri) {
        case '/products':
            handleProducts($requestMethod, $inputData);
            break;
        case '/cart':
            handleCart($requestMethod, $inputData);
            break;
        case '/orders':
            handleOrders($requestMethod, $inputData);
            break;
        case '/users':
            handleUsers($requestMethod, $inputData);
            break;
        case '/addresses':
            handleAddresses($requestMethod, $inputData);
            break;
        case '/payment-methods':
            handlePaymentMethods($requestMethod, $inputData);
            break;
        case '/categories':
            handleCategories($requestMethod, $inputData);
            break;
        default:
            if (preg_match('/\/products\/(\d+)/', $requestUri, $matches)) {
                handleProductById($requestMethod, $matches[1], $inputData);
            } elseif (preg_match('/\/orders\/(\d+)/', $requestUri, $matches)) {
                handleOrderById($requestMethod, $matches[1], $inputData);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Endpoint not found']);
            }
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

// Product handlers
function handleProducts($method, $data) {
    switch ($method) {
        case 'GET':
            $products = Database::getProducts();
            echo json_encode($products);
            break;
        case 'POST':
            if (!$data) {
                http_response_code(400);
                echo json_encode(['error' => 'No data provided']);
                return;
            }
            $productId = createProduct($data);
            echo json_encode(['id' => $productId, 'message' => 'Product created']);
            break;
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }
}

function handleProductById($method, $id, $data) {
    switch ($method) {
        case 'GET':
            $product = Database::getProduct($id);
            if ($product) {
                echo json_encode($product);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Product not found']);
            }
            break;
        case 'PUT':
        case 'PATCH':
            updateProduct($id, $data);
            echo json_encode(['message' => 'Product updated']);
            break;
        case 'DELETE':
            deleteProduct($id);
            echo json_encode(['message' => 'Product deleted']);
            break;
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }
}

// Cart handlers
function handleCart($method, $data) {
    session_start();
    $sessionId = session_id();
    $userId = $_SESSION['user_id'] ?? null;
    
    switch ($method) {
        case 'GET':
            $cartItems = getCart($userId, $sessionId);
            echo json_encode($cartItems);
            break;
        case 'POST':
            if (!$data || !isset($data['product_id'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Product ID required']);
                return;
            }
            addToCart($data['product_id'], $data['quantity'] ?? 1, $userId, $sessionId);
            echo json_encode(['message' => 'Item added to cart']);
            break;
        case 'PUT':
            if (!$data || !isset($data['product_id'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Product ID required']);
                return;
            }
            updateCartItem($data['product_id'], $data['quantity'], $userId, $sessionId);
            echo json_encode(['message' => 'Cart updated']);
            break;
        case 'DELETE':
            if (!$data || !isset($data['product_id'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Product ID required']);
                return;
            }
            removeFromCart($data['product_id'], $userId, $sessionId);
            echo json_encode(['message' => 'Item removed from cart']);
            break;
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }
}

// Order handlers
function handleOrders($method, $data) {
    session_start();
    $userId = $_SESSION['user_id'] ?? 1; // Default to user 1 for demo
    
    switch ($method) {
        case 'GET':
            $orders = Database::getUserOrders($userId);
            echo json_encode($orders);
            break;
        case 'POST':
            if (!$data) {
                http_response_code(400);
                echo json_encode(['error' => 'Order data required']);
                return;
            }
            $orderId = createOrder($data, $userId);
            echo json_encode(['id' => $orderId, 'message' => 'Order created']);
            break;
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }
}

function handleOrderById($method, $id, $data) {
    switch ($method) {
        case 'GET':
            $order = getOrderById($id);
            if ($order) {
                echo json_encode($order);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Order not found']);
            }
            break;
        case 'PUT':
        case 'PATCH':
            updateOrder($id, $data);
            echo json_encode(['message' => 'Order updated']);
            break;
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }
}

// User handlers
function handleUsers($method, $data) {
    switch ($method) {
        case 'POST':
            if ($data && isset($data['action']) && $data['action'] === 'register') {
                $userId = registerUser($data);
                session_start();
                $_SESSION['user_id'] = $userId;
                echo json_encode(['id' => $userId, 'message' => 'User registered']);
            } elseif ($data && isset($data['action']) && $data['action'] === 'login') {
                $user = loginUser($data);
                if ($user) {
                    session_start();
                    $_SESSION['user_id'] = $user['id'];
                    echo json_encode(['user' => $user, 'message' => 'Login successful']);
                } else {
                    http_response_code(401);
                    echo json_encode(['error' => 'Invalid credentials']);
                }
            }
            break;
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }
}

// Address handlers
function handleAddresses($method, $data) {
    session_start();
    $userId = $_SESSION['user_id'] ?? 1;
    
    switch ($method) {
        case 'GET':
            $addresses = getUserAddresses($userId);
            echo json_encode($addresses);
            break;
        case 'POST':
            $addressId = createAddress($data, $userId);
            echo json_encode(['id' => $addressId, 'message' => 'Address created']);
            break;
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }
}

// Payment method handlers
function handlePaymentMethods($method, $data) {
    session_start();
    $userId = $_SESSION['user_id'] ?? 1;
    
    switch ($method) {
        case 'GET':
            $paymentMethods = getUserPaymentMethods($userId);
            echo json_encode($paymentMethods);
            break;
        case 'POST':
            $paymentMethodId = createPaymentMethod($data, $userId);
            echo json_encode(['id' => $paymentMethodId, 'message' => 'Payment method created']);
            break;
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }
}

// Category handlers
function handleCategories($method, $data) {
    switch ($method) {
        case 'GET':
            $categories = Database::getCategories();
            echo json_encode($categories);
            break;
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }
}

// Database functions
function createProduct($data) {
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("
        INSERT INTO products (name, slug, description, price, category_id, image, quantity, featured, status) 
        VALUES (:name, :slug, :description, :price, :category_id, :image, :quantity, :featured, :status)
    ");
    
    $slug = strtolower(str_replace(' ', '-', $data['name']));
    
    $stmt->execute([
        ':name' => $data['name'],
        ':slug' => $slug,
        ':description' => $data['description'] ?? '',
        ':price' => $data['price'],
        ':category_id' => $data['category_id'] ?? 1,
        ':image' => $data['image'] ?? '',
        ':quantity' => $data['quantity'] ?? 0,
        ':featured' => $data['featured'] ?? 0,
        ':status' => 'active'
    ]);
    
    return $pdo->lastInsertId();
}

function updateProduct($id, $data) {
    $pdo = Database::getConnection();
    $updates = [];
    $params = [':id' => $id];
    
    foreach (['name', 'description', 'price', 'quantity', 'featured'] as $field) {
        if (isset($data[$field])) {
            $updates[] = "$field = :$field";
            $params[":$field"] = $data[$field];
        }
    }
    
    if (!empty($updates)) {
        $sql = "UPDATE products SET " . implode(', ', $updates) . ", date_modified = CURRENT_TIMESTAMP WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
    }
}

function deleteProduct($id) {
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("UPDATE products SET status = 'inactive' WHERE id = :id");
    $stmt->execute([':id' => $id]);
}

function getCart($userId, $sessionId) {
    $pdo = Database::getConnection();
    $whereClause = $userId ? "c.user_id = :user_id" : "c.session_id = :session_id";
    $param = $userId ? [':user_id' => $userId] : [':session_id' => $sessionId];
    
    $stmt = $pdo->prepare("
        SELECT c.*, p.name, p.price, p.image 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE $whereClause
    ");
    $stmt->execute($param);
    return $stmt->fetchAll();
}

function addToCart($productId, $quantity, $userId, $sessionId) {
    $pdo = Database::getConnection();
    
    // Check if item already exists
    $whereClause = $userId ? "user_id = :user_id" : "session_id = :session_id";
    $param = $userId ? [':user_id' => $userId] : [':session_id' => $sessionId];
    $param[':product_id'] = $productId;
    
    $stmt = $pdo->prepare("SELECT * FROM cart WHERE $whereClause AND product_id = :product_id");
    $stmt->execute($param);
    $existingItem = $stmt->fetch();
    
    if ($existingItem) {
        // Update quantity
        $stmt = $pdo->prepare("UPDATE cart SET quantity = quantity + :quantity WHERE id = :id");
        $stmt->execute([':quantity' => $quantity, ':id' => $existingItem['id']]);
    } else {
        // Insert new item
        $stmt = $pdo->prepare("
            INSERT INTO cart (user_id, session_id, product_id, quantity) 
            VALUES (:user_id, :session_id, :product_id, :quantity)
        ");
        $stmt->execute([
            ':user_id' => $userId,
            ':session_id' => $sessionId,
            ':product_id' => $productId,
            ':quantity' => $quantity
        ]);
    }
}

function updateCartItem($productId, $quantity, $userId, $sessionId) {
    $pdo = Database::getConnection();
    $whereClause = $userId ? "user_id = :user_id" : "session_id = :session_id";
    $param = $userId ? [':user_id' => $userId] : [':session_id' => $sessionId];
    $param[':product_id'] = $productId;
    $param[':quantity'] = $quantity;
    
    $stmt = $pdo->prepare("UPDATE cart SET quantity = :quantity WHERE $whereClause AND product_id = :product_id");
    $stmt->execute($param);
}

function removeFromCart($productId, $userId, $sessionId) {
    $pdo = Database::getConnection();
    $whereClause = $userId ? "user_id = :user_id" : "session_id = :session_id";
    $param = $userId ? [':user_id' => $userId] : [':session_id' => $sessionId];
    $param[':product_id'] = $productId;
    
    $stmt = $pdo->prepare("DELETE FROM cart WHERE $whereClause AND product_id = :product_id");
    $stmt->execute($param);
}

function createOrder($data, $userId) {
    $pdo = Database::getConnection();
    $pdo->beginTransaction();
    
    try {
        // Create order
        $orderNumber = 'ORD' . date('YmdHis') . rand(100, 999);
        
        $stmt = $pdo->prepare("
            INSERT INTO orders (order_number, user_id, status, payment_method, subtotal, shipping_amount, total_amount) 
            VALUES (:order_number, :user_id, :status, :payment_method, :subtotal, :shipping_amount, :total_amount)
        ");
        
        $stmt->execute([
            ':order_number' => $orderNumber,
            ':user_id' => $userId,
            ':status' => 'pending',
            ':payment_method' => $data['payment_method'] ?? 'cod',
            ':subtotal' => $data['subtotal'],
            ':shipping_amount' => $data['shipping_amount'] ?? 0,
            ':total_amount' => $data['total_amount']
        ]);
        
        $orderId = $pdo->lastInsertId();
        
        // Add order items
        if (isset($data['items'])) {
            $stmt = $pdo->prepare("
                INSERT INTO order_items (order_id, product_id, product_name, quantity, unit_price, total_price) 
                VALUES (:order_id, :product_id, :product_name, :quantity, :unit_price, :total_price)
            ");
            
            foreach ($data['items'] as $item) {
                $stmt->execute([
                    ':order_id' => $orderId,
                    ':product_id' => $item['id'],
                    ':product_name' => $item['name'],
                    ':quantity' => $item['quantity'],
                    ':unit_price' => $item['price'],
                    ':total_price' => $item['price'] * $item['quantity']
                ]);
            }
        }
        
        // Clear cart after order
        $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $userId]);
        
        $pdo->commit();
        return $orderId;
        
    } catch (Exception $e) {
        $pdo->rollback();
        throw $e;
    }
}

function getOrderById($id) {
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("
        SELECT o.*, 
               JSON_ARRAYAGG(
                   JSON_OBJECT('id', oi.product_id, 'name', oi.product_name, 'quantity', oi.quantity, 'price', oi.unit_price)
               ) as items
        FROM orders o 
        LEFT JOIN order_items oi ON o.id = oi.order_id 
        WHERE o.id = :id
        GROUP BY o.id
    ");
    $stmt->execute([':id' => $id]);
    return $stmt->fetch();
}

function updateOrder($id, $data) {
    $pdo = Database::getConnection();
    $updates = [];
    $params = [':id' => $id];
    
    foreach (['status', 'payment_status', 'notes'] as $field) {
        if (isset($data[$field])) {
            $updates[] = "$field = :$field";
            $params[":$field"] = $data[$field];
        }
    }
    
    if (!empty($updates)) {
        $sql = "UPDATE orders SET " . implode(', ', $updates) . ", date_modified = CURRENT_TIMESTAMP WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
    }
}

function registerUser($data) {
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("
        INSERT INTO users (name, email, password, phone) 
        VALUES (:name, :email, :password, :phone)
    ");
    
    $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
    
    $stmt->execute([
        ':name' => $data['name'],
        ':email' => $data['email'],
        ':password' => $hashedPassword,
        ':phone' => $data['phone'] ?? null
    ]);
    
    return $pdo->lastInsertId();
}

function loginUser($data) {
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email AND status = 'active'");
    $stmt->execute([':email' => $data['email']]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($data['password'], $user['password'])) {
        // Update last login
        $stmt = $pdo->prepare("UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = :id");
        $stmt->execute([':id' => $user['id']]);
        
        unset($user['password']); // Don't return password
        return $user;
    }
    
    return false;
}

function getUserAddresses($userId) {
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("SELECT * FROM addresses WHERE user_id = :user_id ORDER BY is_default DESC, date_created DESC");
    $stmt->execute([':user_id' => $userId]);
    return $stmt->fetchAll();
}

function createAddress($data, $userId) {
    $pdo = Database::getConnection();
    
    // If this is default, unset other defaults
    if (!empty($data['is_default'])) {
        $stmt = $pdo->prepare("UPDATE addresses SET is_default = 0 WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $userId]);
    }
    
    $stmt = $pdo->prepare("
        INSERT INTO addresses (user_id, name, type, address_line_1, city, state, postal_code, country, phone, is_default) 
        VALUES (:user_id, :name, :type, :address_line_1, :city, :state, :postal_code, :country, :phone, :is_default)
    ");
    
    $stmt->execute([
        ':user_id' => $userId,
        ':name' => $data['name'],
        ':type' => $data['type'] ?? 'home',
        ':address_line_1' => $data['address_line_1'],
        ':city' => $data['city'],
        ':state' => $data['state'] ?? '',
        ':postal_code' => $data['postal_code'] ?? '',
        ':country' => $data['country'] ?? 'Philippines',
        ':phone' => $data['phone'] ?? '',
        ':is_default' => $data['is_default'] ?? 0
    ]);
    
    return $pdo->lastInsertId();
}

function getUserPaymentMethods($userId) {
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("SELECT * FROM payment_methods WHERE user_id = :user_id ORDER BY is_default DESC, date_created DESC");
    $stmt->execute([':user_id' => $userId]);
    return $stmt->fetchAll();
}

function createPaymentMethod($data, $userId) {
    $pdo = Database::getConnection();
    
    // If this is default, unset other defaults
    if (!empty($data['is_default'])) {
        $stmt = $pdo->prepare("UPDATE payment_methods SET is_default = 0 WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $userId]);
    }
    
    $stmt = $pdo->prepare("
        INSERT INTO payment_methods (user_id, type, name, details, card_last_four, card_brand, is_default) 
        VALUES (:user_id, :type, :name, :details, :card_last_four, :card_brand, :is_default)
    ");
    
    $stmt->execute([
        ':user_id' => $userId,
        ':type' => $data['type'],
        ':name' => $data['name'],
        ':details' => $data['details'] ?? '',
        ':card_last_four' => $data['card_last_four'] ?? null,
        ':card_brand' => $data['card_brand'] ?? null,
        ':is_default' => $data['is_default'] ?? 0
    ]);
    
    return $pdo->lastInsertId();
}
?>