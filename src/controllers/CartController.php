<?php

class CartController {
    private $database;

    public function __construct(Database $database) {
        $this->database = $database;
    }

    public function getCart($userId) {
        try {
            $cartSummary = $this->database->getCartSummary($userId);

            $this->sendResponse(200, [
                'success' => true,
                'data' => $cartSummary
            ]);

        } catch (Exception $e) {
            $this->sendResponse(500, [
                'success' => false,
                'error' => 'Internal server error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function addToCart() {
        try {
            $input = json_decode(file_get_contents('php://input'), true);

            // Validate required fields
            if (!isset($input['product_id']) || !isset($input['quantity']) || !isset($input['user_id'])) {
                $this->sendResponse(400, [
                    'success' => false,
                    'error' => 'Missing required fields: product_id, quantity, user_id'
                ]);
                return;
            }

            // Validate data types
            if (!is_int($input['product_id']) || $input['product_id'] <= 0) {
                $this->sendResponse(400, [
                    'success' => false,
                    'error' => 'Invalid product_id: must be a positive integer'
                ]);
                return;
            }

            if (!is_int($input['quantity']) || $input['quantity'] <= 0) {
                $this->sendResponse(400, [
                    'success' => false,
                    'error' => 'Invalid quantity: must be a positive integer'
                ]);
                return;
            }

            // Check if product exists
            $product = $this->database->getProductById($input['product_id']);
            if (!$product) {
                $this->sendResponse(404, [
                    'success' => false,
                    'error' => 'Product not found'
                ]);
                return;
            }

            // Check stock availability
            $currentCart = $this->database->getCart($input['user_id']);
            $currentQuantityInCart = 0;
            
            foreach ($currentCart as $cartItem) {
                if ($cartItem['product_id'] == $input['product_id']) {
                    $currentQuantityInCart = $cartItem['quantity'];
                    break;
                }
            }

            $totalRequestedQuantity = $currentQuantityInCart + $input['quantity'];
            
            if ($product['stock'] < $totalRequestedQuantity) {
                $this->sendResponse(400, [
                    'success' => false,
                    'error' => "Insufficient stock. Available: {$product['stock']}, Requested total: {$totalRequestedQuantity}"
                ]);
                return;
            }

            // Add to cart
            $success = $this->database->addToCart(
                $input['user_id'], 
                $input['product_id'], 
                $input['quantity']
            );

            if ($success) {
                $cartSummary = $this->database->getCartSummary($input['user_id']);

                $this->sendResponse(201, [
                    'success' => true,
                    'message' => 'Item added to cart successfully',
                    'data' => $cartSummary
                ]);
            } else {
                $this->sendResponse(500, [
                    'success' => false,
                    'error' => 'Failed to add item to cart'
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

    public function updateCartItem($userId, $itemId) {
        try {
            $input = json_decode(file_get_contents('php://input'), true);

            // Validate required fields
            if (!isset($input['quantity'])) {
                $this->sendResponse(400, [
                    'success' => false,
                    'error' => 'Missing required field: quantity'
                ]);
                return;
            }

            // Validate data type
            if (!is_int($input['quantity']) || $input['quantity'] <= 0) {
                $this->sendResponse(400, [
                    'success' => false,
                    'error' => 'Invalid quantity: must be a positive integer'
                ]);
                return;
            }

            // Check if cart exists
            $cart = $this->database->getCart($userId);
            if (empty($cart)) {
                $this->sendResponse(404, [
                    'success' => false,
                    'error' => 'Cart not found'
                ]);
                return;
            }

            // Find the cart item
            $cartItem = null;
            foreach ($cart as $item) {
                if ($item['id'] === $itemId) {
                    $cartItem = $item;
                    break;
                }
            }

            if (!$cartItem) {
                $this->sendResponse(404, [
                    'success' => false,
                    'error' => 'Item not found in cart'
                ]);
                return;
            }

            // Check stock availability
            $product = $this->database->getProductById($cartItem['product_id']);
            if ($product['stock'] < $input['quantity']) {
                $this->sendResponse(400, [
                    'success' => false,
                    'error' => "Insufficient stock. Available: {$product['stock']}"
                ]);
                return;
            }

            // Update cart item
            $success = $this->database->updateCartItem($userId, $itemId, $input['quantity']);

            if ($success) {
                $cartSummary = $this->database->getCartSummary($userId);

                $this->sendResponse(200, [
                    'success' => true,
                    'message' => 'Cart item updated successfully',
                    'data' => $cartSummary
                ]);
            } else {
                $this->sendResponse(500, [
                    'success' => false,
                    'error' => 'Failed to update cart item'
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

    public function removeFromCart($userId, $itemId) {
        try {
            // Check if cart exists
            $cart = $this->database->getCart($userId);
            if (empty($cart)) {
                $this->sendResponse(404, [
                    'success' => false,
                    'error' => 'Cart not found'
                ]);
                return;
            }

            // Remove item from cart
            $removedItem = $this->database->removeFromCart($userId, $itemId);

            if ($removedItem) {
                $cartSummary = $this->database->getCartSummary($userId);

                $this->sendResponse(200, [
                    'success' => true,
                    'message' => 'Item removed from cart successfully',
                    'data' => [
                        'removed_item' => $removedItem,
                        'cart' => $cartSummary
                    ]
                ]);
            } else {
                $this->sendResponse(404, [
                    'success' => false,
                    'error' => 'Item not found in cart'
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