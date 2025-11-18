# E-commerce RESTful API - PHP Implementation

## Overview
This is a complete RESTful API implementation for an e-commerce application built with PHP. The API provides endpoints for managing products, shopping carts, and orders.

## Features
- **Product Management**: Create, read, update, delete products (CRUD)
- **Shopping Cart**: Add items, view cart, update quantities, remove items
- **Order Processing**: Place orders, view order history, update order status
- **Security**: CORS support, rate limiting, input validation
- **Error Handling**: Comprehensive error responses

## Installation & Setup

### Prerequisites
- PHP 7.4 or higher
- Composer (for dependency management)

### Installation
1. Clone or download the project
2. Install dependencies:
   ```bash
   composer install
   ```
3. Start the development server:
   ```bash
   composer serve
   ```
   Or manually:
   ```bash
   php -S localhost:8000 -t public
   ```

The API will be available at `http://localhost:8000`

## API Endpoints

### Products

#### GET /products
Fetch all products with optional filtering and pagination.

**Query Parameters:**
- `category` (optional): Filter by product category
- `min_price` (optional): Minimum price filter
- `max_price` (optional): Maximum price filter
- `limit` (optional): Number of products to return
- `offset` (optional): Number of products to skip

**Example Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "Laptop",
            "price": 1000,
            "stock": 10,
            "category": "Electronics"
        },
        {
            "id": 2,
            "name": "Smartphone",
            "price": 500,
            "stock": 15,
            "category": "Electronics"
        }
    ],
    "total": 2,
    "offset": 0,
    "limit": 10
}
```

#### GET /products/{id}
Fetch a specific product by ID.

**Example Response:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "Laptop",
        "price": 1000,
        "stock": 10,
        "category": "Electronics"
    }
}
```

#### POST /products
Create a new product.

**Request Body:**
```json
{
    "name": "New Product",
    "price": 99.99,
    "stock": 50,
    "category": "Electronics"
}
```

#### PUT /products/{id}
Update a complete product (all fields required).

**Request Body:**
```json
{
    "name": "Updated Product",
    "price": 149.99,
    "stock": 30,
    "category": "Electronics"
}
```

#### PATCH /products/{id}
Partially update a product (only provided fields will be updated).

**Request Body:**
```json
{
    "price": 129.99,
    "stock": 25
}
```

#### DELETE /products/{id}
Delete a product.

### Shopping Cart

#### GET /cart/{user_id}
Get user's shopping cart.

**Example Response:**
```json
{
    "success": true,
    "data": {
        "cart_id": "user123",
        "items": [
            {
                "id": "unique-item-id",
                "product_id": 1,
                "product_name": "Laptop",
                "price": 1000,
                "quantity": 2,
                "added_at": "2024-01-01T12:00:00Z"
            }
        ],
        "total_items": 2,
        "total_amount": 2000
    }
}
```

#### POST /cart
Add an item to the shopping cart.

**Request Body:**
```json
{
    "product_id": 1,
    "quantity": 2,
    "user_id": "user123"
}
```

#### PUT /cart/{user_id}/{item_id}
Update cart item quantity.

**Request Body:**
```json
{
    "quantity": 3
}
```

#### DELETE /cart/{user_id}/{item_id}
Remove an item from the cart.

### Orders

#### POST /orders
Place an order with items from the cart.

**Request Body:**
```json
{
    "user_id": "user123",
    "shipping_address": {
        "street": "123 Main St",
        "city": "Anytown",
        "state": "CA",
        "zip": "12345",
        "country": "USA"
    },
    "payment_method": "credit_card"
}
```

**Example Response:**
```json
{
    "success": true,
    "message": "Order placed successfully",
    "data": {
        "id": 1,
        "user_id": "user123",
        "items": [...],
        "total_amount": 2000,
        "shipping_address": {...},
        "payment_method": "credit_card",
        "status": "pending",
        "order_date": "2024-01-01T12:00:00Z"
    }
}
```

#### GET /orders/{user_id}
Get all orders for a user.

#### GET /orders/details/{order_id}
Get detailed information about a specific order.

#### PATCH /orders/{order_id}
Update order status.

**Request Body:**
```json
{
    "status": "shipped"
}
```

**Valid Status Values:** `pending`, `confirmed`, `shipped`, `delivered`, `cancelled`

## Error Handling

All endpoints return consistent error responses:

```json
{
    "success": false,
    "error": "Error description",
    "message": "Detailed error message (if applicable)"
}
```

**Common HTTP Status Codes:**
- `200`: Success
- `201`: Created
- `400`: Bad Request (validation errors)
- `404`: Not Found
- `405`: Method Not Allowed
- `429`: Too Many Requests (rate limit exceeded)
- `500`: Internal Server Error

## Security Features

1. **CORS Support**: Cross-origin requests allowed
2. **Rate Limiting**: 100 requests per 15-minute window per session
3. **Input Validation**: All inputs are validated and sanitized
4. **Security Headers**: XSS protection, content type options, frame options

## Testing

You can test the API using tools like:
- **Postman**: Import the endpoints and test manually
- **cURL**: Command-line testing
- **Browser**: For GET requests

### Example cURL Commands

```bash
# Get all products
curl -X GET http://localhost:8000/products

# Get specific product
curl -X GET http://localhost:8000/products/1

# Add item to cart
curl -X POST http://localhost:8000/cart \
  -H "Content-Type: application/json" \
  -d '{"product_id": 1, "quantity": 2, "user_id": "user123"}'

# Place an order
curl -X POST http://localhost:8000/orders \
  -H "Content-Type: application/json" \
  -d '{
    "user_id": "user123",
    "shipping_address": {
      "street": "123 Main St",
      "city": "Anytown",
      "state": "CA",
      "zip": "12345"
    },
    "payment_method": "credit_card"
  }'
```

## Project Structure

```
fortes_e-commerce/
├── public/
│   └── index.php              # Main entry point
├── src/
│   ├── config/
│   │   └── database.php       # Database class and data management
│   ├── controllers/
│   │   ├── ProductController.php
│   │   ├── CartController.php
│   │   └── OrderController.php
│   └── routes/
│       └── api.php            # Router class and route handling
├── composer.json              # PHP dependencies and scripts
└── README.md                  # This documentation
```

## Development Notes

- This implementation uses in-memory data storage for demonstration purposes
- In production, replace the Database class with actual database connections (MySQL, PostgreSQL, etc.)
- Add authentication and authorization for secure user management
- Implement proper logging and monitoring
- Add unit tests using PHPUnit

## Author
Jherilyn Fortes - Practical Exam Implementation