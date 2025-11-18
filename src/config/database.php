<?php

class Database {
    private $products = [];
    private $carts = [];
    private $orders = [];
    private $nextOrderId = 1;

    public function __construct() {
        $this->initializeData();
    }

    private function initializeData() {
        // Initialize sample products
        $this->products = [
            [
                'id' => 1,
                'name' => 'Laptop',
                'price' => 1000.00,
                'stock' => 10,
                'category' => 'Electronics'
            ],
            [
                'id' => 2,
                'name' => 'Smartphone',
                'price' => 500.00,
                'stock' => 15,
                'category' => 'Electronics'
            ],
            [
                'id' => 3,
                'name' => 'Headphones',
                'price' => 100.00,
                'stock' => 20,
                'category' => 'Electronics'
            ],
            [
                'id' => 4,
                'name' => 'Book',
                'price' => 25.00,
                'stock' => 50,
                'category' => 'Books'
            ],
            [
                'id' => 5,
                'name' => 'Coffee Mug',
                'price' => 15.00,
                'stock' => 30,
                'category' => 'Home & Kitchen'
            ]
        ];
    }

    // Product methods
    public function getAllProducts($filters = []) {
        $products = $this->products;

        // Apply filters
        if (!empty($filters['category'])) {
            $products = array_filter($products, function($product) use ($filters) {
                return stripos($product['category'], $filters['category']) !== false;
            });
        }

        if (!empty($filters['min_price'])) {
            $products = array_filter($products, function($product) use ($filters) {
                return $product['price'] >= (float)$filters['min_price'];
            });
        }

        if (!empty($filters['max_price'])) {
            $products = array_filter($products, function($product) use ($filters) {
                return $product['price'] <= (float)$filters['max_price'];
            });
        }

        // Apply pagination
        $offset = isset($filters['offset']) ? (int)$filters['offset'] : 0;
        $limit = isset($filters['limit']) ? (int)$filters['limit'] : count($products);
        
        $totalCount = count($products);
        $paginatedProducts = array_slice(array_values($products), $offset, $limit);

        return [
            'products' => $paginatedProducts,
            'total' => $totalCount,
            'offset' => $offset,
            'limit' => $limit
        ];
    }

    public function getProductById($id) {
        foreach ($this->products as $product) {
            if ($product['id'] == $id) {
                return $product;
            }
        }
        return null;
    }

    public function createProduct($data) {
        $newId = empty($this->products) ? 1 : max(array_column($this->products, 'id')) + 1;
        
        $newProduct = [
            'id' => $newId,
            'name' => trim($data['name']),
            'price' => (float)$data['price'],
            'stock' => (int)$data['stock'],
            'category' => trim($data['category'])
        ];

        $this->products[] = $newProduct;
        return $newProduct;
    }

    public function updateProduct($id, $data) {
        foreach ($this->products as &$product) {
            if ($product['id'] == $id) {
                $product['name'] = trim($data['name']);
                $product['price'] = (float)$data['price'];
                $product['stock'] = (int)$data['stock'];
                $product['category'] = trim($data['category']);
                return $product;
            }
        }
        return null;
    }

    public function patchProduct($id, $data) {
        foreach ($this->products as &$product) {
            if ($product['id'] == $id) {
                if (isset($data['name'])) {
                    $product['name'] = trim($data['name']);
                }
                if (isset($data['price'])) {
                    $product['price'] = (float)$data['price'];
                }
                if (isset($data['stock'])) {
                    $product['stock'] = (int)$data['stock'];
                }
                if (isset($data['category'])) {
                    $product['category'] = trim($data['category']);
                }
                return $product;
            }
        }
        return null;
    }

    public function deleteProduct($id) {
        foreach ($this->products as $key => $product) {
            if ($product['id'] == $id) {
                $deletedProduct = $product;
                unset($this->products[$key]);
                $this->products = array_values($this->products); // Reindex array
                return $deletedProduct;
            }
        }
        return null;
    }

    public function updateProductStock($id, $quantity) {
        foreach ($this->products as &$product) {
            if ($product['id'] == $id) {
                $product['stock'] -= $quantity;
                return true;
            }
        }
        return false;
    }

    // Cart methods
    public function getCart($userId) {
        if (!isset($this->carts[$userId])) {
            return [];
        }
        return $this->carts[$userId];
    }

    public function addToCart($userId, $productId, $quantity) {
        if (!isset($this->carts[$userId])) {
            $this->carts[$userId] = [];
        }

        $product = $this->getProductById($productId);
        if (!$product) {
            return false;
        }

        // Check if item already exists in cart
        foreach ($this->carts[$userId] as &$cartItem) {
            if ($cartItem['product_id'] == $productId) {
                $cartItem['quantity'] += $quantity;
                return true;
            }
        }

        // Add new item to cart
        $this->carts[$userId][] = [
            'id' => uniqid(),
            'product_id' => $productId,
            'product_name' => $product['name'],
            'price' => $product['price'],
            'quantity' => $quantity,
            'added_at' => date('c')
        ];

        return true;
    }

    public function updateCartItem($userId, $itemId, $quantity) {
        if (!isset($this->carts[$userId])) {
            return false;
        }

        foreach ($this->carts[$userId] as &$cartItem) {
            if ($cartItem['id'] === $itemId) {
                $cartItem['quantity'] = $quantity;
                return true;
            }
        }

        return false;
    }

    public function removeFromCart($userId, $itemId) {
        if (!isset($this->carts[$userId])) {
            return false;
        }

        foreach ($this->carts[$userId] as $key => $cartItem) {
            if ($cartItem['id'] === $itemId) {
                $removedItem = $cartItem;
                unset($this->carts[$userId][$key]);
                $this->carts[$userId] = array_values($this->carts[$userId]); // Reindex array
                return $removedItem;
            }
        }

        return false;
    }

    public function clearCart($userId) {
        $this->carts[$userId] = [];
    }

    public function getCartSummary($userId) {
        $cart = $this->getCart($userId);
        $totalItems = array_sum(array_column($cart, 'quantity'));
        $totalAmount = array_sum(array_map(function($item) {
            return $item['price'] * $item['quantity'];
        }, $cart));

        return [
            'cart_id' => $userId,
            'items' => $cart,
            'total_items' => $totalItems,
            'total_amount' => $totalAmount
        ];
    }

    // Order methods
    public function createOrder($userId, $shippingAddress, $paymentMethod) {
        $cart = $this->getCart($userId);
        if (empty($cart)) {
            return false;
        }

        $totalAmount = array_sum(array_map(function($item) {
            return $item['price'] * $item['quantity'];
        }, $cart));

        $order = [
            'id' => $this->nextOrderId++,
            'user_id' => $userId,
            'items' => $cart,
            'total_amount' => $totalAmount,
            'shipping_address' => $shippingAddress,
            'payment_method' => $paymentMethod,
            'status' => 'pending',
            'order_date' => date('c')
        ];

        $this->orders[] = $order;
        $this->clearCart($userId);

        return $order;
    }

    public function getOrdersByUser($userId) {
        return array_filter($this->orders, function($order) use ($userId) {
            return $order['user_id'] === $userId;
        });
    }

    public function getOrderById($orderId) {
        foreach ($this->orders as $order) {
            if ($order['id'] == $orderId) {
                return $order;
            }
        }
        return null;
    }

    public function updateOrderStatus($orderId, $status) {
        foreach ($this->orders as &$order) {
            if ($order['id'] == $orderId) {
                $order['status'] = $status;
                $order['updated_at'] = date('c');
                return $order;
            }
        }
        return null;
    }
}