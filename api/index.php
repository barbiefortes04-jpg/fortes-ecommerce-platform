<?php
// API entry point for Vercel deployment
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS, PATCH');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Get request path
$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);

// Remove /api from path if present
$path = preg_replace('#^/api#', '', $path);

// Set up the path info for the included file
$_SERVER['PATH_INFO'] = $path;

// Include required files
require_once __DIR__ . '/../src/Database.php';

// Simple in-memory data for demonstration (Vercel serverless)
$products = [
    ['id' => 1, 'name' => 'Apple Laptop', 'price' => 1299.99, 'image' => 'Apple_Laptop.jpg', 'category' => 'Electronics', 'stock' => 10],
    ['id' => 2, 'name' => 'AirPods Case', 'price' => 49.99, 'image' => 'Earpods_case.jpg', 'category' => 'Electronics', 'stock' => 25],
    ['id' => 3, 'name' => 'Coffee Mugs', 'price' => 19.99, 'image' => 'Mugs.jpg', 'category' => 'Home', 'stock' => 50],
    ['id' => 4, 'name' => 'Cute Plushie', 'price' => 24.99, 'image' => 'Plushie.jpg', 'category' => 'Home', 'stock' => 30],
    ['id' => 5, 'name' => 'Body Suit', 'price' => 79.99, 'image' => 'body_suit.jpg', 'category' => 'Fashion', 'stock' => 15],
    ['id' => 6, 'name' => 'Digital Camera', 'price' => 899.99, 'image' => 'digi_cam.jpg', 'category' => 'Electronics', 'stock' => 8],
    ['id' => 7, 'name' => 'Fashion Rings', 'price' => 129.99, 'image' => 'rings.jpg', 'category' => 'Fashion', 'stock' => 20],
    // Additional products...
    ['id' => 8, 'name' => 'Gaming Headset', 'price' => 149.99, 'image' => 'https://via.placeholder.com/300x300/333/fff?text=Gaming+Headset', 'category' => 'Electronics', 'stock' => 12],
    ['id' => 9, 'name' => 'Smart Watch', 'price' => 299.99, 'image' => 'https://via.placeholder.com/300x300/333/fff?text=Smart+Watch', 'category' => 'Electronics', 'stock' => 18],
    ['id' => 10, 'name' => 'Wireless Mouse', 'price' => 59.99, 'image' => 'https://via.placeholder.com/300x300/333/fff?text=Wireless+Mouse', 'category' => 'Electronics', 'stock' => 35]
];

// Simple routing
$method = $_SERVER['REQUEST_METHOD'];

switch ($path) {
    case '/products':
    case '':
        if ($method === 'GET') {
            echo json_encode($products);
        }
        break;
        
    case '/cart':
        if ($method === 'GET') {
            echo json_encode([]);
        } elseif ($method === 'POST') {
            echo json_encode(['success' => true, 'message' => 'Item added to cart']);
        } elseif ($method === 'PUT') {
            echo json_encode(['success' => true, 'message' => 'Cart updated']);
        } elseif ($method === 'DELETE') {
            echo json_encode(['success' => true, 'message' => 'Cart cleared']);
        }
        break;
        
    case '/orders':
        if ($method === 'GET') {
            echo json_encode([]);
        } elseif ($method === 'POST') {
            echo json_encode(['success' => true, 'id' => 'ORD' . time(), 'message' => 'Order placed']);
        }
        break;
        
    case '/addresses':
        if ($method === 'GET') {
            echo json_encode([]);
        } elseif ($method === 'POST') {
            echo json_encode(['success' => true, 'id' => time(), 'message' => 'Address saved']);
        }
        break;
        
    case '/payment-methods':
        if ($method === 'GET') {
            echo json_encode([]);
        }
        break;
        
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint not found']);
        break;
}
?>