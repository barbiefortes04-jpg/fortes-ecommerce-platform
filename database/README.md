# 🗄️ E-commerce Database Setup Guide for XAMPP

## 📋 Quick Setup Instructions

### Step 1: Start XAMPP
1. Open XAMPP Control Panel
2. Start **Apache** and **MySQL** services
3. Ensure both services are running (green status)

### Step 2: Import Database
1. Open your browser and go to: `http://localhost/phpmyadmin`
2. Click on **"New"** in the left sidebar to create a new database
3. Or simply click **"Import"** tab (the SQL file will create the database automatically)
4. Choose **"Choose File"** and select: `database/ecommerce_database.sql`
5. Click **"Go"** to import

### Step 3: Verify Setup
1. After import, you should see `fortes_ecommerce` database in the left sidebar
2. The database contains 11 tables with sample data
3. Test the connection by visiting: `http://localhost:8000/src/Database.php`

## 📊 Database Structure

### Core Tables
- **`users`** - Customer accounts and authentication
- **`categories`** - Product categories (Electronics, Fashion, Home)
- **`products`** - Product catalog (30 products including your images)
- **`orders`** - Order management and tracking
- **`order_items`** - Individual order line items
- **`addresses`** - Customer delivery addresses
- **`payment_methods`** - Saved payment methods
- **`cart`** - Shopping cart persistence
- **`wishlist`** - Saved favorite products

### Utility Tables
- **`order_addresses`** - Order-specific shipping/billing addresses

## 🎯 Sample Data Included

### Your Product Images (7 items):
- Apple MacBook Pro ($2,499) - `Apple_Laptop.jpg`
- Digital Camera Pro ($899) - `digi_cam.jpg`
- Premium AirPods Case ($49.99) - `Earpods_case.jpg`
- Designer Body Suit ($89.99) - `body_suit.jpg`
- Golden Wedding Rings ($1,299.99) - `rings.jpg`
- Custom Coffee Mugs ($24.99) - `Mugs.jpg`
- Cute Plushie Collection ($19.99) - `Plushie.jpg`

### Additional Products (23 items):
- Electronics: Gaming laptops, keyboards, headphones, smartwatches
- Fashion: Sneakers, handbags, blazers, sunglasses
- Home & Garden: Coffee machines, air purifiers, garden tools

### Sample User:
- **Email:** john@example.com
- **Password:** password (hashed)
- **Sample Orders:** 3 orders with different statuses

## ⚙️ Configuration

### Default XAMPP Settings:
- **Host:** localhost
- **Port:** 3306
- **Database:** fortes_ecommerce
- **Username:** root
- **Password:** (empty)

### If you need to change settings:
Edit `src/Database.php` and update the `DatabaseConfig` class constants.

## 🔗 API Integration

The database is ready to work with your existing PHP API. Update your controllers to use the new `Database` class:

```php
// Example usage
require_once 'src/Database.php';

// Get all products
$products = Database::getProducts();

// Get specific product
$product = Database::getProduct(1);

// Create order
$orderId = Database::createOrder($orderData);
```

## 🚀 Testing

1. **Connection Test:** Visit `http://localhost:8000/src/Database.php`
2. **View Data:** Use phpMyAdmin to browse tables and data
3. **API Test:** Your existing endpoints should work with the new database

## 📝 SQL Features

- **Views:** `product_catalog`, `order_summary` for optimized queries
- **Indexes:** Performance indexes on frequently queried columns
- **Foreign Keys:** Data integrity constraints
- **UTF8MB4:** Full Unicode support including emojis

## 🛠️ Troubleshooting

### Common Issues:
1. **Import Error:** Ensure MySQL is running in XAMPP
2. **Connection Failed:** Check if database name matches `fortes_ecommerce`
3. **Permission Error:** Ensure XAMPP MySQL user has proper permissions

### Reset Database:
If you need to reset, simply re-import the SQL file - it will drop and recreate everything.

---

**🎉 Your database is now ready for production use with XAMPP!**

The database includes all the features from your e-commerce platform:
- ✅ User management with authentication
- ✅ Product catalog with categories
- ✅ Shopping cart and wishlist
- ✅ Order management and tracking
- ✅ Multiple addresses per user
- ✅ Payment method storage
- ✅ Complete order history

Simply copy-paste the SQL file into phpMyAdmin and you're ready to go!