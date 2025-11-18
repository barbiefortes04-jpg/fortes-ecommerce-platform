<?php

require_once __DIR__ . '/../controllers/ProductController.php';
require_once __DIR__ . '/../controllers/CartController.php';
require_once __DIR__ . '/../controllers/OrderController.php';

class Router {
    private $database;

    public function __construct(Database $database) {
        $this->database = $database;
    }

    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Remove leading slash and split path
        $path = trim($path, '/');
        $segments = explode('/', $path);

        // Route to appropriate controller
        try {
            switch ($segments[0]) {
                case 'products':
                    $this->handleProductRoutes($method, $segments);
                    break;
                case 'cart':
                    $this->handleCartRoutes($method, $segments);
                    break;
                case 'orders':
                    $this->handleOrderRoutes($method, $segments);
                    break;
                default:
                    $this->sendResponse(404, [
                        'success' => false,
                        'error' => 'Endpoint not found'
                    ]);
                    break;
            }
        } catch (Exception $e) {
            $this->sendResponse(500, [
                'success' => false,
                'error' => 'Internal server error',
                'message' => $e->getMessage()
            ]);
        }
    }

    private function handleProductRoutes($method, $segments) {
        $controller = new ProductController($this->database);

        switch ($method) {
            case 'GET':
                if (isset($segments[1]) && is_numeric($segments[1])) {
                    // GET /products/{id}
                    $controller->getProduct($segments[1]);
                } else {
                    // GET /products
                    $controller->getProducts();
                }
                break;
            case 'POST':
                // POST /products
                $controller->createProduct();
                break;
            case 'PUT':
                if (isset($segments[1]) && is_numeric($segments[1])) {
                    // PUT /products/{id}
                    $controller->updateProduct($segments[1]);
                } else {
                    $this->sendResponse(400, [
                        'success' => false,
                        'error' => 'Product ID required'
                    ]);
                }
                break;
            case 'PATCH':
                if (isset($segments[1]) && is_numeric($segments[1])) {
                    // PATCH /products/{id}
                    $controller->patchProduct($segments[1]);
                } else {
                    $this->sendResponse(400, [
                        'success' => false,
                        'error' => 'Product ID required'
                    ]);
                }
                break;
            case 'DELETE':
                if (isset($segments[1]) && is_numeric($segments[1])) {
                    // DELETE /products/{id}
                    $controller->deleteProduct($segments[1]);
                } else {
                    $this->sendResponse(400, [
                        'success' => false,
                        'error' => 'Product ID required'
                    ]);
                }
                break;
            default:
                $this->sendResponse(405, [
                    'success' => false,
                    'error' => 'Method not allowed'
                ]);
                break;
        }
    }

    private function handleCartRoutes($method, $segments) {
        $controller = new CartController($this->database);

        switch ($method) {
            case 'GET':
                if (isset($segments[1])) {
                    // GET /cart/{user_id}
                    $controller->getCart($segments[1]);
                } else {
                    $this->sendResponse(400, [
                        'success' => false,
                        'error' => 'User ID required'
                    ]);
                }
                break;
            case 'POST':
                // POST /cart
                $controller->addToCart();
                break;
            case 'PUT':
                if (isset($segments[1]) && isset($segments[2])) {
                    // PUT /cart/{user_id}/{item_id}
                    $controller->updateCartItem($segments[1], $segments[2]);
                } else {
                    $this->sendResponse(400, [
                        'success' => false,
                        'error' => 'User ID and Item ID required'
                    ]);
                }
                break;
            case 'DELETE':
                if (isset($segments[1]) && isset($segments[2])) {
                    // DELETE /cart/{user_id}/{item_id}
                    $controller->removeFromCart($segments[1], $segments[2]);
                } else {
                    $this->sendResponse(400, [
                        'success' => false,
                        'error' => 'User ID and Item ID required'
                    ]);
                }
                break;
            default:
                $this->sendResponse(405, [
                    'success' => false,
                    'error' => 'Method not allowed'
                ]);
                break;
        }
    }

    private function handleOrderRoutes($method, $segments) {
        $controller = new OrderController($this->database);

        switch ($method) {
            case 'GET':
                if (isset($segments[1])) {
                    if ($segments[1] === 'details' && isset($segments[2])) {
                        // GET /orders/details/{order_id}
                        $controller->getOrderDetails($segments[2]);
                    } else {
                        // GET /orders/{user_id}
                        $controller->getUserOrders($segments[1]);
                    }
                } else {
                    $this->sendResponse(400, [
                        'success' => false,
                        'error' => 'User ID or order details required'
                    ]);
                }
                break;
            case 'POST':
                // POST /orders
                $controller->createOrder();
                break;
            case 'PATCH':
                if (isset($segments[1]) && is_numeric($segments[1])) {
                    // PATCH /orders/{order_id}
                    $controller->updateOrderStatus($segments[1]);
                } else {
                    $this->sendResponse(400, [
                        'success' => false,
                        'error' => 'Order ID required'
                    ]);
                }
                break;
            default:
                $this->sendResponse(405, [
                    'success' => false,
                    'error' => 'Method not allowed'
                ]);
                break;
        }
    }

    private function sendResponse($statusCode, $data) {
        http_response_code($statusCode);
        echo json_encode($data, JSON_PRETTY_PRINT);
        exit();
    }
}