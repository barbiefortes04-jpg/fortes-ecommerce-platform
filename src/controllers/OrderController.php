<?php

class OrderController {
    private $database;

    public function __construct(Database $database) {
        $this->database = $database;
    }

    public function createOrder() {
        try {
            $input = json_decode(file_get_contents('php://input'), true);

            // Validate required fields
            if (!isset($input['user_id']) || !isset($input['shipping_address']) || !isset($input['payment_method'])) {
                $this->sendResponse(400, [
                    'success' => false,
                    'error' => 'Missing required fields: user_id, shipping_address, payment_method'
                ]);
                return;
            }

            // Validate that cart is not empty
            $cart = $this->database->getCart($input['user_id']);
            if (empty($cart)) {
                $this->sendResponse(400, [
                    'success' => false,
                    'error' => 'Cart is empty'
                ]);
                return;
            }

            // Validate stock availability for all items
            foreach ($cart as $cartItem) {
                $product = $this->database->getProductById($cartItem['product_id']);
                if (!$product) {
                    $this->sendResponse(400, [
                        'success' => false,
                        'error' => "Product {$cartItem['product_name']} is no longer available"
                    ]);
                    return;
                }
                
                if ($product['stock'] < $cartItem['quantity']) {
                    $this->sendResponse(400, [
                        'success' => false,
                        'error' => "Insufficient stock for {$cartItem['product_name']}. Available: {$product['stock']}"
                    ]);
                    return;
                }
            }

            // Update product stock
            foreach ($cart as $cartItem) {
                $this->database->updateProductStock($cartItem['product_id'], $cartItem['quantity']);
            }

            // Create order
            $order = $this->database->createOrder(
                $input['user_id'],
                $input['shipping_address'],
                $input['payment_method']
            );

            if ($order) {
                $this->sendResponse(201, [
                    'success' => true,
                    'message' => 'Order placed successfully',
                    'data' => $order
                ]);
            } else {
                $this->sendResponse(500, [
                    'success' => false,
                    'error' => 'Failed to create order'
                ]);
            }

        } catch (Exception $e) {
            $this->sendResponse(500, [
                'success' => false,
                'error' => 'Internal server error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function getUserOrders($userId) {
        try {
            $orders = $this->database->getOrdersByUser($userId);

            $this->sendResponse(200, [
                'success' => true,
                'data' => array_values($orders), // Reindex array
                'total' => count($orders)
            ]);

        } catch (Exception $e) {
            $this->sendResponse(500, [
                'success' => false,
                'error' => 'Internal server error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function getOrderDetails($orderId) {
        try {
            $order = $this->database->getOrderById($orderId);

            if (!$order) {
                $this->sendResponse(404, [
                    'success' => false,
                    'error' => 'Order not found'
                ]);
                return;
            }

            $this->sendResponse(200, [
                'success' => true,
                'data' => $order
            ]);

        } catch (Exception $e) {
            $this->sendResponse(500, [
                'success' => false,
                'error' => 'Internal server error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function updateOrderStatus($orderId) {
        try {
            $input = json_decode(file_get_contents('php://input'), true);

            // Validate required field
            if (!isset($input['status'])) {
                $this->sendResponse(400, [
                    'success' => false,
                    'error' => 'Missing required field: status'
                ]);
                return;
            }

            // Validate status value
            $validStatuses = ['pending', 'confirmed', 'shipped', 'delivered', 'cancelled'];
            if (!in_array($input['status'], $validStatuses)) {
                $this->sendResponse(400, [
                    'success' => false,
                    'error' => 'Invalid status. Must be one of: ' . implode(', ', $validStatuses)
                ]);
                return;
            }

            // Check if order exists
            $existingOrder = $this->database->getOrderById($orderId);
            if (!$existingOrder) {
                $this->sendResponse(404, [
                    'success' => false,
                    'error' => 'Order not found'
                ]);
                return;
            }

            // Update order status
            $updatedOrder = $this->database->updateOrderStatus($orderId, $input['status']);

            if ($updatedOrder) {
                $this->sendResponse(200, [
                    'success' => true,
                    'message' => 'Order status updated successfully',
                    'data' => $updatedOrder
                ]);
            } else {
                $this->sendResponse(500, [
                    'success' => false,
                    'error' => 'Failed to update order status'
                ]);
            }

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