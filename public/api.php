<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Content-Type: application/json');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fortes_ecommerce";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(["error" => "Connection failed: " . $e->getMessage()]);
    exit();
}

// Get the request method and URI
$method = $_SERVER['REQUEST_METHOD'];
$request = $_SERVER['REQUEST_URI'];

// Parse the request
$path = parse_url($request, PHP_URL_PATH);
$path = trim($path, '/');
$path_parts = explode('/', $path);

// Remove 'api' and 'public' from path if present
if (in_array('api', $path_parts)) {
    $path_parts = array_values(array_diff($path_parts, ['api']));
}
if (in_array('public', $path_parts)) {
    $path_parts = array_values(array_diff($path_parts, ['public']));
}
if (in_array('api.php', $path_parts)) {
    $path_parts = array_values(array_diff($path_parts, ['api.php']));
}

$endpoint = isset($path_parts[0]) ? $path_parts[0] : '';
$id = isset($path_parts[1]) ? $path_parts[1] : null;

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Routes
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
    case 'users':
        handleUsers($pdo, $method, $id, $input);
        break;
    default:
        http_response_code(404);
        echo json_encode(["error" => "Endpoint not found"]);
        break;
}

function handleProducts($pdo, $method, $id, $input) {
    switch ($method) {
        case 'GET':
            if ($id) {
                $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
                $stmt->execute([$id]);
                $product = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($product) {
                    echo json_encode($product);
                } else {
                    http_response_code(404);
                    echo json_encode(["error" => "Product not found"]);
                }
            } else {
                $stmt = $pdo->query("SELECT * FROM products");
                $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode($products);
            }
            break;
            
        case 'POST':
            $stmt = $pdo->prepare("INSERT INTO products (name, description, price, category_id, image, stock) VALUES (?, ?, ?, ?, ?, ?)");
            $result = $stmt->execute([$input['name'], $input['description'], $input['price'], $input['category_id'], $input['image'], $input['stock']]);
            if ($result) {
                echo json_encode(["message" => "Product created successfully", "id" => $pdo->lastInsertId()]);
            } else {
                http_response_code(500);
                echo json_encode(["error" => "Failed to create product"]);
            }
            break;
            
        case 'PUT':
            if ($id) {
                $stmt = $pdo->prepare("UPDATE products SET name = ?, description = ?, price = ?, category_id = ?, image = ?, stock = ? WHERE id = ?");
                $result = $stmt->execute([$input['name'], $input['description'], $input['price'], $input['category_id'], $input['image'], $input['stock'], $id]);
                if ($result) {
                    echo json_encode(["message" => "Product updated successfully"]);
                } else {
                    http_response_code(500);
                    echo json_encode(["error" => "Failed to update product"]);
                }
            }
            break;
            
        case 'DELETE':
            if ($id) {
                $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
                $result = $stmt->execute([$id]);
                if ($result) {
                    echo json_encode(["message" => "Product deleted successfully"]);
                } else {
                    http_response_code(500);
                    echo json_encode(["error" => "Failed to delete product"]);
                }
            }
            break;
    }
}

function handleCart($pdo, $method, $id, $input) {
    switch ($method) {
        case 'GET':
            $stmt = $pdo->query("SELECT c.*, p.name, p.price, p.image FROM cart c JOIN products p ON c.product_id = p.id");
            $cart = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($cart);
            break;
            
        case 'POST':
            // Check if item already exists in cart
            $stmt = $pdo->prepare("SELECT * FROM cart WHERE product_id = ?");
            $stmt->execute([$input['product_id']]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existing) {
                // Update quantity
                $new_quantity = $existing['quantity'] + ($input['quantity'] ?? 1);
                $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
                $result = $stmt->execute([$new_quantity, $existing['id']]);
            } else {
                // Insert new item
                $stmt = $pdo->prepare("INSERT INTO cart (product_id, quantity, user_id) VALUES (?, ?, ?)");
                $result = $stmt->execute([$input['product_id'], $input['quantity'] ?? 1, 1]);
            }
            
            if ($result) {
                echo json_encode(["message" => "Item added to cart"]);
            } else {
                http_response_code(500);
                echo json_encode(["error" => "Failed to add item to cart"]);
            }
            break;
            
        case 'DELETE':
            if ($id) {
                $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ?");
                $result = $stmt->execute([$id]);
                if ($result) {
                    echo json_encode(["message" => "Item removed from cart"]);
                } else {
                    http_response_code(500);
                    echo json_encode(["error" => "Failed to remove item"]);
                }
            }
            break;
    }
}

function handleOrders($pdo, $method, $id, $input) {
    switch ($method) {
        case 'GET':
            if ($id) {
                $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
                $stmt->execute([$id]);
                $order = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($order) {
                    // Get order items
                    $stmt = $pdo->prepare("SELECT oi.*, p.name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
                    $stmt->execute([$id]);
                    $order['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    echo json_encode($order);
                } else {
                    http_response_code(404);
                    echo json_encode(["error" => "Order not found"]);
                }
            } else {
                $stmt = $pdo->query("SELECT * FROM orders ORDER BY created_at DESC");
                $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode($orders);
            }
            break;
            
        case 'POST':
            try {
                $pdo->beginTransaction();
                
                // Create order
                $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, status) VALUES (?, ?, ?)");
                $stmt->execute([1, $input['total'], 'pending']);
                $order_id = $pdo->lastInsertId();
                
                // Add order items
                if (isset($input['items'])) {
                    $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
                    foreach ($input['items'] as $item) {
                        $stmt->execute([$order_id, $item['product_id'], $item['quantity'], $item['price']]);
                    }
                }
                
                $pdo->commit();
                echo json_encode(["message" => "Order created successfully", "order_id" => $order_id]);
                
            } catch (Exception $e) {
                $pdo->rollback();
                http_response_code(500);
                echo json_encode(["error" => "Failed to create order: " . $e->getMessage()]);
            }
            break;
    }
}

function handleUsers($pdo, $method, $id, $input) {
    switch ($method) {
        case 'GET':
            if ($id) {
                $stmt = $pdo->prepare("SELECT id, name, email, created_at FROM users WHERE id = ?");
                $stmt->execute([$id]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($user) {
                    echo json_encode($user);
                } else {
                    http_response_code(404);
                    echo json_encode(["error" => "User not found"]);
                }
            } else {
                $stmt = $pdo->query("SELECT id, name, email, created_at FROM users");
                $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode($users);
            }
            break;
            
        case 'POST':
            $hashed_password = password_hash($input['password'], PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            $result = $stmt->execute([$input['name'], $input['email'], $hashed_password]);
            
            if ($result) {
                echo json_encode(["message" => "User created successfully", "id" => $pdo->lastInsertId()]);
            } else {
                http_response_code(500);
                echo json_encode(["error" => "Failed to create user"]);
            }
            break;
    }
}
?>