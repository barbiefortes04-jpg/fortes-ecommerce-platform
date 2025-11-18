<?php

class ProductController {
    private $database;

    public function __construct(Database $database) {
        $this->database = $database;
    }

    public function getProducts() {
        try {
            $filters = [];
            
            // Get query parameters
            if (isset($_GET['category'])) {
                $filters['category'] = $_GET['category'];
            }
            if (isset($_GET['min_price'])) {
                $filters['min_price'] = $_GET['min_price'];
            }
            if (isset($_GET['max_price'])) {
                $filters['max_price'] = $_GET['max_price'];
            }
            if (isset($_GET['limit'])) {
                $filters['limit'] = $_GET['limit'];
            }
            if (isset($_GET['offset'])) {
                $filters['offset'] = $_GET['offset'];
            }

            $result = $this->database->getAllProducts($filters);

            $this->sendResponse(200, [
                'success' => true,
                'data' => $result['products'],
                'total' => $result['total'],
                'offset' => $result['offset'],
                'limit' => $result['limit']
            ]);

        } catch (Exception $e) {
            $this->sendResponse(500, [
                'success' => false,
                'error' => 'Internal server error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function getProduct($id) {
        try {
            $product = $this->database->getProductById($id);

            if (!$product) {
                $this->sendResponse(404, [
                    'success' => false,
                    'error' => 'Product not found'
                ]);
                return;
            }

            $this->sendResponse(200, [
                'success' => true,
                'data' => $product
            ]);

        } catch (Exception $e) {
            $this->sendResponse(500, [
                'success' => false,
                'error' => 'Internal server error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function createProduct() {
        try {
            $input = json_decode(file_get_contents('php://input'), true);

            // Validate required fields
            if (!isset($input['name']) || !isset($input['price']) || 
                !isset($input['stock']) || !isset($input['category'])) {
                $this->sendResponse(400, [
                    'success' => false,
                    'error' => 'Missing required fields: name, price, stock, category'
                ]);
                return;
            }

            // Validate data types
            if (!is_numeric($input['price']) || $input['price'] <= 0) {
                $this->sendResponse(400, [
                    'success' => false,
                    'error' => 'Invalid price: must be a positive number'
                ]);
                return;
            }

            if (!is_int($input['stock']) || $input['stock'] < 0) {
                $this->sendResponse(400, [
                    'success' => false,
                    'error' => 'Invalid stock: must be a non-negative integer'
                ]);
                return;
            }

            if (empty(trim($input['name'])) || empty(trim($input['category']))) {
                $this->sendResponse(400, [
                    'success' => false,
                    'error' => 'Name and category cannot be empty'
                ]);
                return;
            }

            $newProduct = $this->database->createProduct($input);

            $this->sendResponse(201, [
                'success' => true,
                'message' => 'Product created successfully',
                'data' => $newProduct
            ]);

        } catch (Exception $e) {
            $this->sendResponse(500, [
                'success' => false,
                'error' => 'Internal server error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function updateProduct($id) {
        try {
            $input = json_decode(file_get_contents('php://input'), true);

            // Check if product exists
            $existingProduct = $this->database->getProductById($id);
            if (!$existingProduct) {
                $this->sendResponse(404, [
                    'success' => false,
                    'error' => 'Product not found'
                ]);
                return;
            }

            // Validate required fields
            if (!isset($input['name']) || !isset($input['price']) || 
                !isset($input['stock']) || !isset($input['category'])) {
                $this->sendResponse(400, [
                    'success' => false,
                    'error' => 'Missing required fields: name, price, stock, category'
                ]);
                return;
            }

            // Validate data types
            if (!is_numeric($input['price']) || $input['price'] <= 0) {
                $this->sendResponse(400, [
                    'success' => false,
                    'error' => 'Invalid price: must be a positive number'
                ]);
                return;
            }

            if (!is_int($input['stock']) || $input['stock'] < 0) {
                $this->sendResponse(400, [
                    'success' => false,
                    'error' => 'Invalid stock: must be a non-negative integer'
                ]);
                return;
            }

            if (empty(trim($input['name'])) || empty(trim($input['category']))) {
                $this->sendResponse(400, [
                    'success' => false,
                    'error' => 'Name and category cannot be empty'
                ]);
                return;
            }

            $updatedProduct = $this->database->updateProduct($id, $input);

            $this->sendResponse(200, [
                'success' => true,
                'message' => 'Product updated successfully',
                'data' => $updatedProduct
            ]);

        } catch (Exception $e) {
            $this->sendResponse(500, [
                'success' => false,
                'error' => 'Internal server error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function patchProduct($id) {
        try {
            $input = json_decode(file_get_contents('php://input'), true);

            // Check if product exists
            $existingProduct = $this->database->getProductById($id);
            if (!$existingProduct) {
                $this->sendResponse(404, [
                    'success' => false,
                    'error' => 'Product not found'
                ]);
                return;
            }

            // Validate fields if they are provided
            if (isset($input['price'])) {
                if (!is_numeric($input['price']) || $input['price'] <= 0) {
                    $this->sendResponse(400, [
                        'success' => false,
                        'error' => 'Invalid price: must be a positive number'
                    ]);
                    return;
                }
            }

            if (isset($input['stock'])) {
                if (!is_int($input['stock']) || $input['stock'] < 0) {
                    $this->sendResponse(400, [
                        'success' => false,
                        'error' => 'Invalid stock: must be a non-negative integer'
                    ]);
                    return;
                }
            }

            if (isset($input['name'])) {
                if (empty(trim($input['name']))) {
                    $this->sendResponse(400, [
                        'success' => false,
                        'error' => 'Name cannot be empty'
                    ]);
                    return;
                }
            }

            if (isset($input['category'])) {
                if (empty(trim($input['category']))) {
                    $this->sendResponse(400, [
                        'success' => false,
                        'error' => 'Category cannot be empty'
                    ]);
                    return;
                }
            }

            $updatedProduct = $this->database->patchProduct($id, $input);

            $this->sendResponse(200, [
                'success' => true,
                'message' => 'Product updated successfully',
                'data' => $updatedProduct
            ]);

        } catch (Exception $e) {
            $this->sendResponse(500, [
                'success' => false,
                'error' => 'Internal server error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function deleteProduct($id) {
        try {
            $deletedProduct = $this->database->deleteProduct($id);

            if (!$deletedProduct) {
                $this->sendResponse(404, [
                    'success' => false,
                    'error' => 'Product not found'
                ]);
                return;
            }

            $this->sendResponse(200, [
                'success' => true,
                'message' => 'Product deleted successfully',
                'data' => $deletedProduct
            ]);

        } catch (Exception $e) {
            $this->sendResponse(500, [
                'success' => false,
                'error' => 'Internal server error',
                'message' => $e->getMessage()
            ]);
        }
    }

    private function sendResponse($statusCode, $data) {
        http_response_code($statusCode);
        echo json_encode($data, JSON_PRETTY_PRINT);
        exit();
    }
}