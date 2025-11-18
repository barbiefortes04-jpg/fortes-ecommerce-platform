<?php
/**
 * Test file for the E-commerce API
 * This file contains sample API calls to test all endpoints
 */

echo "<h1>E-commerce API Test Suite</h1>\n";
echo "<h2>Testing all RESTful endpoints (GET, POST, PUT, DELETE, PATCH)</h2>\n";

$baseUrl = 'http://localhost:8000';

// Helper function to make API calls
function makeRequest($url, $method = 'GET', $data = null) {
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    
    if ($data) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen(json_encode($data))
        ]);
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'status' => $httpCode,
        'body' => json_decode($response, true)
    ];
}

echo "<h3>1. Testing Product Endpoints</h3>\n";

// GET /products
echo "<h4>GET /products - Fetch all products</h4>\n";
$result = makeRequest($baseUrl . '/products');
echo "<pre>Status: " . $result['status'] . "\n";
echo "Response: " . json_encode($result['body'], JSON_PRETTY_PRINT) . "</pre>\n";

// GET /products/1
echo "<h4>GET /products/1 - Fetch specific product</h4>\n";
$result = makeRequest($baseUrl . '/products/1');
echo "<pre>Status: " . $result['status'] . "\n";
echo "Response: " . json_encode($result['body'], JSON_PRETTY_PRINT) . "</pre>\n";

// POST /products
echo "<h4>POST /products - Create new product</h4>\n";
$newProduct = [
    'name' => 'Test Product',
    'price' => 99.99,
    'stock' => 50,
    'category' => 'Test Category'
];
$result = makeRequest($baseUrl . '/products', 'POST', $newProduct);
echo "<pre>Status: " . $result['status'] . "\n";
echo "Request: " . json_encode($newProduct, JSON_PRETTY_PRINT) . "\n";
echo "Response: " . json_encode($result['body'], JSON_PRETTY_PRINT) . "</pre>\n";

// PUT /products/1
echo "<h4>PUT /products/1 - Update complete product</h4>\n";
$updateProduct = [
    'name' => 'Updated Laptop',
    'price' => 1200.00,
    'stock' => 8,
    'category' => 'Electronics'
];
$result = makeRequest($baseUrl . '/products/1', 'PUT', $updateProduct);
echo "<pre>Status: " . $result['status'] . "\n";
echo "Request: " . json_encode($updateProduct, JSON_PRETTY_PRINT) . "\n";
echo "Response: " . json_encode($result['body'], JSON_PRETTY_PRINT) . "</pre>\n";

// PATCH /products/2
echo "<h4>PATCH /products/2 - Partially update product</h4>\n";
$patchProduct = [
    'price' => 550.00,
    'stock' => 12
];
$result = makeRequest($baseUrl . '/products/2', 'PATCH', $patchProduct);
echo "<pre>Status: " . $result['status'] . "\n";
echo "Request: " . json_encode($patchProduct, JSON_PRETTY_PRINT) . "\n";
echo "Response: " . json_encode($result['body'], JSON_PRETTY_PRINT) . "</pre>\n";

echo "<h3>2. Testing Cart Endpoints</h3>\n";

// POST /cart
echo "<h4>POST /cart - Add item to cart</h4>\n";
$cartItem = [
    'product_id' => 1,
    'quantity' => 2,
    'user_id' => 'test_user_123'
];
$result = makeRequest($baseUrl . '/cart', 'POST', $cartItem);
echo "<pre>Status: " . $result['status'] . "\n";
echo "Request: " . json_encode($cartItem, JSON_PRETTY_PRINT) . "\n";
echo "Response: " . json_encode($result['body'], JSON_PRETTY_PRINT) . "</pre>\n";

// Add another item
$cartItem2 = [
    'product_id' => 2,
    'quantity' => 1,
    'user_id' => 'test_user_123'
];
$result2 = makeRequest($baseUrl . '/cart', 'POST', $cartItem2);

// GET /cart/test_user_123
echo "<h4>GET /cart/test_user_123 - Get user's cart</h4>\n";
$result = makeRequest($baseUrl . '/cart/test_user_123');
echo "<pre>Status: " . $result['status'] . "\n";
echo "Response: " . json_encode($result['body'], JSON_PRETTY_PRINT) . "</pre>\n";

echo "<h3>3. Testing Order Endpoints</h3>\n";

// POST /orders
echo "<h4>POST /orders - Place an order</h4>\n";
$order = [
    'user_id' => 'test_user_123',
    'shipping_address' => [
        'street' => '123 Test Street',
        'city' => 'Test City',
        'state' => 'Test State',
        'zip' => '12345',
        'country' => 'Test Country'
    ],
    'payment_method' => 'credit_card'
];
$result = makeRequest($baseUrl . '/orders', 'POST', $order);
echo "<pre>Status: " . $result['status'] . "\n";
echo "Request: " . json_encode($order, JSON_PRETTY_PRINT) . "\n";
echo "Response: " . json_encode($result['body'], JSON_PRETTY_PRINT) . "</pre>\n";

// GET /orders/test_user_123
echo "<h4>GET /orders/test_user_123 - Get user's orders</h4>\n";
$result = makeRequest($baseUrl . '/orders/test_user_123');
echo "<pre>Status: " . $result['status'] . "\n";
echo "Response: " . json_encode($result['body'], JSON_PRETTY_PRINT) . "</pre>\n";

// GET /orders/details/1
echo "<h4>GET /orders/details/1 - Get order details</h4>\n";
$result = makeRequest($baseUrl . '/orders/details/1');
echo "<pre>Status: " . $result['status'] . "\n";
echo "Response: " . json_encode($result['body'], JSON_PRETTY_PRINT) . "</pre>\n";

// PATCH /orders/1
echo "<h4>PATCH /orders/1 - Update order status</h4>\n";
$statusUpdate = [
    'status' => 'shipped'
];
$result = makeRequest($baseUrl . '/orders/1', 'PATCH', $statusUpdate);
echo "<pre>Status: " . $result['status'] . "\n";
echo "Request: " . json_encode($statusUpdate, JSON_PRETTY_PRINT) . "\n";
echo "Response: " . json_encode($result['body'], JSON_PRETTY_PRINT) . "</pre>\n";

echo "<h3>4. Testing Error Cases</h3>\n";

// Test 404 - Non-existent product
echo "<h4>GET /products/999 - Test 404 error</h4>\n";
$result = makeRequest($baseUrl . '/products/999');
echo "<pre>Status: " . $result['status'] . "\n";
echo "Response: " . json_encode($result['body'], JSON_PRETTY_PRINT) . "</pre>\n";

// Test validation error
echo "<h4>POST /products - Test validation error (missing fields)</h4>\n";
$invalidProduct = [
    'name' => 'Incomplete Product'
    // Missing required fields
];
$result = makeRequest($baseUrl . '/products', 'POST', $invalidProduct);
echo "<pre>Status: " . $result['status'] . "\n";
echo "Request: " . json_encode($invalidProduct, JSON_PRETTY_PRINT) . "\n";
echo "Response: " . json_encode($result['body'], JSON_PRETTY_PRINT) . "</pre>\n";

// Test 405 - Method not allowed
echo "<h4>DELETE /cart - Test 405 error (method not allowed)</h4>\n";
$result = makeRequest($baseUrl . '/cart', 'DELETE');
echo "<pre>Status: " . $result['status'] . "\n";
echo "Response: " . json_encode($result['body'], JSON_PRETTY_PRINT) . "</pre>\n";

// Test filtering
echo "<h4>GET /products?category=Electronics - Test filtering</h4>\n";
$result = makeRequest($baseUrl . '/products?category=Electronics');
echo "<pre>Status: " . $result['status'] . "\n";
echo "Response: " . json_encode($result['body'], JSON_PRETTY_PRINT) . "</pre>\n";

// DELETE a product to test DELETE functionality
echo "<h4>DELETE /products/3 - Test delete product</h4>\n";
$result = makeRequest($baseUrl . '/products/3', 'DELETE');
echo "<pre>Status: " . $result['status'] . "\n";
echo "Response: " . json_encode($result['body'], JSON_PRETTY_PRINT) . "</pre>\n";

echo "<h2>Testing Complete!</h2>\n";
echo "<p>All RESTful API endpoints (GET, POST, PUT, DELETE, PATCH) have been tested.</p>\n";
?>