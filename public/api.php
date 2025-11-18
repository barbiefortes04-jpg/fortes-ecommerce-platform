<?php
// Main entry point for the E-commerce API
require_once __DIR__ . '/../src/config/database.php';
require_once __DIR__ . '/../src/routes/api.php';

// Enable CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Basic security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// Rate limiting (basic implementation)
session_start();
$current_time = time();
$rate_limit_window = 900; // 15 minutes
$rate_limit_max = 100; // 100 requests per window

if (!isset($_SESSION['rate_limit'])) {
    $_SESSION['rate_limit'] = ['count' => 0, 'start_time' => $current_time];
}

if ($current_time - $_SESSION['rate_limit']['start_time'] > $rate_limit_window) {
    $_SESSION['rate_limit'] = ['count' => 1, 'start_time' => $current_time];
} else {
    $_SESSION['rate_limit']['count']++;
    if ($_SESSION['rate_limit']['count'] > $rate_limit_max) {
        http_response_code(429);
        echo json_encode([
            'success' => false,
            'error' => 'Too many requests. Please try again later.'
        ]);
        exit();
    }
}

try {
    // Initialize database
    $database = new Database();
    
    // Handle the request
    $router = new Router($database);
    $router->handleRequest();
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Internal server error',
        'message' => $e->getMessage()
    ]);
}