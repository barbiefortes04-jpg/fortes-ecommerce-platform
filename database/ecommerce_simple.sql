-- ========================================
-- E-COMMERCE DATABASE - SIMPLE IMPORT VERSION
-- Created: November 17, 2025
-- For XAMPP phpMyAdmin Import
-- ========================================

-- Drop database if exists and create new one
DROP DATABASE IF EXISTS `fortes_ecommerce`;
CREATE DATABASE `fortes_ecommerce` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `fortes_ecommerce`;

-- ========================================
-- TABLE CREATION
-- ========================================

-- Users table
CREATE TABLE `users` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL,
    `email` varchar(150) NOT NULL UNIQUE,
    `password` varchar(255) NOT NULL,
    `phone` varchar(20) DEFAULT NULL,
    `avatar` varchar(255) DEFAULT NULL,
    `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `last_login` timestamp NULL DEFAULT NULL,
    `status` enum('active','inactive','suspended') DEFAULT 'active',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Categories table
CREATE TABLE `categories` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL,
    `slug` varchar(100) NOT NULL UNIQUE,
    `description` text,
    `image` varchar(255) DEFAULT NULL,
    `status` enum('active','inactive') DEFAULT 'active',
    `sort_order` int(11) DEFAULT 0,
    `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Products table
CREATE TABLE `products` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(200) NOT NULL,
    `slug` varchar(200) NOT NULL UNIQUE,
    `description` text,
    `short_description` varchar(500),
    `price` decimal(10,2) NOT NULL,
    `compare_price` decimal(10,2) DEFAULT NULL,
    `cost_price` decimal(10,2) DEFAULT NULL,
    `sku` varchar(100) UNIQUE,
    `barcode` varchar(100),
    `quantity` int(11) NOT NULL DEFAULT 0,
    `min_quantity` int(11) DEFAULT 1,
    `track_quantity` tinyint(1) DEFAULT 1,
    `category_id` int(11),
    `brand` varchar(100),
    `weight` decimal(8,2) DEFAULT NULL,
    `dimensions` varchar(100),
    `image` varchar(255),
    `gallery` text,
    `featured` tinyint(1) DEFAULT 0,
    `status` enum('active','inactive','draft') DEFAULT 'active',
    `seo_title` varchar(200),
    `seo_description` varchar(500),
    `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `date_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Orders table
CREATE TABLE `orders` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `order_number` varchar(20) NOT NULL UNIQUE,
    `user_id` int(11) NOT NULL,
    `status` enum('pending','confirmed','processing','shipped','delivered','cancelled','refunded') DEFAULT 'pending',
    `payment_status` enum('pending','paid','failed','refunded','partial_refund') DEFAULT 'pending',
    `payment_method` enum('card','banking','gcash','paypal','cod') NOT NULL,
    `subtotal` decimal(10,2) NOT NULL,
    `tax_amount` decimal(10,2) DEFAULT 0.00,
    `shipping_amount` decimal(10,2) DEFAULT 0.00,
    `discount_amount` decimal(10,2) DEFAULT 0.00,
    `total_amount` decimal(10,2) NOT NULL,
    `currency` varchar(3) DEFAULT 'USD',
    `notes` text,
    `shipped_date` timestamp NULL DEFAULT NULL,
    `delivered_date` timestamp NULL DEFAULT NULL,
    `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `date_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Order items table
CREATE TABLE `order_items` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `order_id` int(11) NOT NULL,
    `product_id` int(11) NOT NULL,
    `product_name` varchar(200) NOT NULL,
    `product_sku` varchar(100),
    `product_image` varchar(255),
    `quantity` int(11) NOT NULL,
    `unit_price` decimal(10,2) NOT NULL,
    `total_price` decimal(10,2) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Addresses table
CREATE TABLE `addresses` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `name` varchar(100) NOT NULL,
    `type` enum('home','office','other') DEFAULT 'home',
    `company` varchar(100),
    `address_line_1` varchar(200) NOT NULL,
    `address_line_2` varchar(200),
    `city` varchar(100) NOT NULL,
    `state` varchar(100),
    `postal_code` varchar(20),
    `country` varchar(100) NOT NULL DEFAULT 'Philippines',
    `phone` varchar(20),
    `is_default` tinyint(1) DEFAULT 0,
    `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `date_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Payment methods table
CREATE TABLE `payment_methods` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `type` enum('card','banking','gcash','paypal','cod') NOT NULL,
    `name` varchar(100) NOT NULL,
    `details` varchar(255),
    `card_last_four` varchar(4),
    `card_brand` varchar(50),
    `bank_name` varchar(100),
    `account_number` varchar(100),
    `is_default` tinyint(1) DEFAULT 0,
    `is_verified` tinyint(1) DEFAULT 0,
    `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `date_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Order addresses table
CREATE TABLE `order_addresses` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `order_id` int(11) NOT NULL,
    `type` enum('billing','shipping') NOT NULL,
    `name` varchar(100) NOT NULL,
    `company` varchar(100),
    `address_line_1` varchar(200) NOT NULL,
    `address_line_2` varchar(200),
    `city` varchar(100) NOT NULL,
    `state` varchar(100),
    `postal_code` varchar(20),
    `country` varchar(100) NOT NULL,
    `phone` varchar(20),
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Cart table
CREATE TABLE `cart` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11),
    `session_id` varchar(128),
    `product_id` int(11) NOT NULL,
    `quantity` int(11) NOT NULL DEFAULT 1,
    `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `date_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Wishlist table
CREATE TABLE `wishlist` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `product_id` int(11) NOT NULL,
    `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_user_product` (`user_id`, `product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================
-- SAMPLE DATA INSERTION
-- ========================================

-- Insert Categories
INSERT INTO `categories` (`name`, `slug`, `description`, `status`) VALUES
('Electronics', 'electronics', 'Electronic gadgets and devices', 'active'),
('Fashion', 'fashion', 'Clothing and fashion accessories', 'active'),
('Home & Garden', 'home-garden', 'Home appliances and garden tools', 'active');

-- Insert Sample User
INSERT INTO `users` (`name`, `email`, `password`, `phone`, `status`) VALUES
('John Doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+1234567890', 'active');

-- Insert Products (Your uploaded images + additional products)
INSERT INTO `products` (`name`, `slug`, `description`, `price`, `category_id`, `image`, `quantity`, `featured`, `status`) VALUES
-- Your uploaded products
('Apple MacBook Pro', 'apple-macbook-pro', 'High-performance laptop for professionals', 2499.00, 1, 'Apple_Laptop.jpg', 10, 1, 'active'),
('Digital Camera Pro', 'digital-camera-pro', 'Professional digital camera with 4K recording', 899.00, 1, 'digi_cam.jpg', 15, 1, 'active'),
('Premium AirPods Case', 'premium-airpods-case', 'Protective case for AirPods with wireless charging', 49.99, 1, 'Earpods_case.jpg', 50, 0, 'active'),
('Designer Body Suit', 'designer-body-suit', 'Elegant designer body suit for special occasions', 89.99, 2, 'body_suit.jpg', 25, 1, 'active'),
('Golden Wedding Rings', 'golden-wedding-rings', 'Beautiful 18k gold wedding ring set', 1299.99, 2, 'rings.jpg', 8, 1, 'active'),
('Custom Coffee Mugs', 'custom-coffee-mugs', 'Personalized ceramic coffee mugs set of 2', 24.99, 3, 'Mugs.jpg', 100, 0, 'active'),
('Cute Plushie Collection', 'cute-plushie-collection', 'Adorable soft plushie toy for kids and adults', 19.99, 3, 'Plushie.jpg', 75, 0, 'active'),

-- Additional Electronics
('Gaming Laptop RTX 4080', 'gaming-laptop-rtx-4080', 'High-end gaming laptop with RTX 4080 graphics', 1999.99, 1, 'laptop-gaming.jpg', 5, 1, 'active'),
('Wireless Gaming Mouse', 'wireless-gaming-mouse', 'RGB wireless gaming mouse with precision sensor', 79.99, 1, 'mouse-gaming.jpg', 30, 0, 'active'),
('Mechanical Keyboard', 'mechanical-keyboard', 'RGB mechanical keyboard with cherry switches', 129.99, 1, 'keyboard-mechanical.jpg', 20, 0, 'active'),
('4K Webcam Pro', 'webcam-4k-pro', 'Professional 4K webcam for streaming and meetings', 199.99, 1, 'webcam-4k.jpg', 15, 0, 'active'),
('Bluetooth Headphones', 'bluetooth-headphones', 'Premium noise-cancelling Bluetooth headphones', 249.99, 1, 'headphones-bt.jpg', 25, 1, 'active'),
('Smart Watch Series 9', 'smart-watch-series-9', 'Latest smartwatch with health monitoring', 399.99, 1, 'smartwatch-s9.jpg', 12, 1, 'active'),
('Portable Power Bank', 'portable-power-bank', '20000mAh fast charging power bank', 49.99, 1, 'powerbank-20k.jpg', 40, 0, 'active'),
('USB-C Hub 7-in-1', 'usb-c-hub-7in1', 'Multi-port USB-C hub with HDMI and charging', 69.99, 1, 'usb-hub-7in1.jpg', 35, 0, 'active'),

-- Fashion Items
('Designer Sneakers', 'designer-sneakers', 'Limited edition designer sneakers', 299.99, 2, 'sneakers-designer.jpg', 18, 1, 'active'),
('Leather Handbag', 'leather-handbag', 'Genuine leather luxury handbag', 189.99, 2, 'handbag-leather.jpg', 22, 0, 'active'),
('Silk Scarf Collection', 'silk-scarf-collection', 'Premium silk scarves in various colors', 59.99, 2, 'scarf-silk.jpg', 45, 0, 'active'),
('Men''s Dress Shirt', 'mens-dress-shirt', 'Cotton blend formal dress shirt', 49.99, 2, 'shirt-dress.jpg', 60, 0, 'active'),
('Women''s Blazer', 'womens-blazer', 'Professional women''s blazer jacket', 99.99, 2, 'blazer-womens.jpg', 28, 0, 'active'),
('Designer Sunglasses', 'designer-sunglasses', 'UV protection designer sunglasses', 149.99, 2, 'sunglasses-designer.jpg', 32, 0, 'active'),
('Casual T-Shirt Pack', 'casual-tshirt-pack', 'Comfortable cotton t-shirts 3-pack', 39.99, 2, 'tshirt-pack.jpg', 80, 0, 'active'),

-- Home & Garden
('Coffee Machine Deluxe', 'coffee-machine-deluxe', 'Professional espresso coffee machine', 699.99, 3, 'coffee-machine.jpg', 8, 1, 'active'),
('Air Purifier HEPA', 'air-purifier-hepa', 'High-efficiency air purifier with HEPA filter', 299.99, 3, 'air-purifier.jpg', 15, 0, 'active'),
('Robot Vacuum Cleaner', 'robot-vacuum-cleaner', 'Smart robot vacuum with app control', 449.99, 3, 'robot-vacuum.jpg', 12, 1, 'active'),
('Garden Tool Set', 'garden-tool-set', 'Complete gardening tool set with storage bag', 89.99, 3, 'garden-tools.jpg', 25, 0, 'active'),
('Indoor Plant Collection', 'indoor-plant-collection', 'Set of 3 air-purifying indoor plants', 79.99, 3, 'plants-indoor.jpg', 20, 0, 'active'),
('LED Desk Lamp', 'led-desk-lamp', 'Adjustable LED desk lamp with USB charging', 59.99, 3, 'lamp-desk.jpg', 40, 0, 'active'),
('Throw Pillow Set', 'throw-pillow-set', 'Decorative throw pillows set of 4', 34.99, 3, 'pillows-throw.jpg', 50, 0, 'active'),
('Essential Oil Diffuser', 'essential-oil-diffuser', 'Ultrasonic aromatherapy diffuser', 39.99, 3, 'diffuser-oil.jpg', 35, 0, 'active'),
('Kitchen Knife Set', 'kitchen-knife-set', 'Professional chef knife set with wooden block', 149.99, 3, 'knives-kitchen.jpg', 18, 0, 'active');

-- Insert Sample Addresses
INSERT INTO `addresses` (`user_id`, `name`, `type`, `address_line_1`, `city`, `state`, `postal_code`, `country`, `phone`, `is_default`) VALUES
(1, 'Home Address', 'home', '123 Main Street, Apt 4B', 'New York', 'NY', '10001', 'United States', '+1 555-123-4567', 1),
(1, 'Office Address', 'office', '456 Business Ave, Suite 200', 'New York', 'NY', '10005', 'United States', '+1 555-987-6543', 0);

-- Insert Sample Payment Methods
INSERT INTO `payment_methods` (`user_id`, `type`, `name`, `details`, `card_last_four`, `card_brand`, `is_default`) VALUES
(1, 'card', 'Visa ending in 1234', 'Personal Credit Card', '1234', 'Visa', 1),
(1, 'gcash', 'GCash Wallet', '+63 9XX XXX 1234', NULL, NULL, 0),
(1, 'paypal', 'PayPal Account', 'john@example.com', NULL, NULL, 0);

-- Insert Sample Orders
INSERT INTO `orders` (`order_number`, `user_id`, `status`, `payment_method`, `subtotal`, `shipping_amount`, `total_amount`) VALUES
('ORD001', 1, 'delivered', 'card', 199.99, 15.00, 214.99),
('ORD002', 1, 'shipped', 'gcash', 451.97, 0.00, 451.97),
('ORD003', 1, 'pending', 'cod', 1299.99, 0.00, 1299.99);

-- Insert Sample Order Items
INSERT INTO `order_items` (`order_id`, `product_id`, `product_name`, `quantity`, `unit_price`, `total_price`) VALUES
(1, 12, 'Bluetooth Headphones', 1, 199.99, 199.99),
(2, 13, 'Smart Watch Series 9', 1, 399.99, 399.99),
(2, 4, 'Designer Body Suit', 1, 51.98, 51.98),
(3, 1, 'Apple MacBook Pro', 1, 1299.99, 1299.99);

-- ========================================
-- ADD INDEXES AND CONSTRAINTS
-- ========================================

-- Add foreign key constraints
ALTER TABLE `products` ADD CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;
ALTER TABLE `addresses` ADD CONSTRAINT `fk_addresses_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
ALTER TABLE `payment_methods` ADD CONSTRAINT `fk_payment_methods_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
ALTER TABLE `orders` ADD CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
ALTER TABLE `order_items` ADD CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;
ALTER TABLE `order_items` ADD CONSTRAINT `fk_order_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
ALTER TABLE `order_addresses` ADD CONSTRAINT `fk_order_addresses_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;
ALTER TABLE `cart` ADD CONSTRAINT `fk_cart_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
ALTER TABLE `cart` ADD CONSTRAINT `fk_cart_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
ALTER TABLE `wishlist` ADD CONSTRAINT `fk_wishlist_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
ALTER TABLE `wishlist` ADD CONSTRAINT `fk_wishlist_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

-- Add performance indexes
CREATE INDEX `idx_email` ON `users` (`email`);
CREATE INDEX `idx_status` ON `users` (`status`);
CREATE INDEX `idx_slug` ON `categories` (`slug`);
CREATE INDEX `idx_category_status` ON `categories` (`status`);
CREATE INDEX `idx_product_slug` ON `products` (`slug`);
CREATE INDEX `idx_product_category` ON `products` (`category_id`);
CREATE INDEX `idx_product_status` ON `products` (`status`);
CREATE INDEX `idx_product_featured` ON `products` (`featured`);
CREATE INDEX `idx_product_price` ON `products` (`price`);
CREATE INDEX `idx_order_number` ON `orders` (`order_number`);
CREATE INDEX `idx_order_user` ON `orders` (`user_id`);
CREATE INDEX `idx_order_status` ON `orders` (`status`);
CREATE INDEX `idx_order_payment_status` ON `orders` (`payment_status`);
CREATE INDEX `idx_order_date` ON `orders` (`date_created`);
CREATE INDEX `idx_address_user` ON `addresses` (`user_id`);
CREATE INDEX `idx_address_default` ON `addresses` (`is_default`);
CREATE INDEX `idx_payment_user` ON `payment_methods` (`user_id`);
CREATE INDEX `idx_payment_type` ON `payment_methods` (`type`);
CREATE INDEX `idx_payment_default` ON `payment_methods` (`is_default`);
CREATE INDEX `idx_cart_user` ON `cart` (`user_id`);
CREATE INDEX `idx_cart_session` ON `cart` (`session_id`);
CREATE INDEX `idx_cart_product` ON `cart` (`product_id`);
CREATE INDEX `idx_wishlist_user` ON `wishlist` (`user_id`);
CREATE INDEX `idx_wishlist_product` ON `wishlist` (`product_id`);

-- ========================================
-- CREATE VIEWS
-- ========================================

-- Product catalog view
CREATE VIEW `product_catalog` AS
SELECT 
    p.id,
    p.name,
    p.slug,
    p.description,
    p.short_description,
    p.price,
    p.compare_price,
    p.quantity,
    p.image,
    p.featured,
    p.status,
    p.date_created,
    c.name as category_name,
    c.slug as category_slug
FROM products p
LEFT JOIN categories c ON p.category_id = c.id
WHERE p.status = 'active';

-- Order summary view
CREATE VIEW `order_summary` AS
SELECT 
    o.id,
    o.order_number,
    o.status,
    o.payment_status,
    o.payment_method,
    o.subtotal,
    o.shipping_amount,
    o.total_amount,
    o.date_created,
    u.name as customer_name,
    u.email as customer_email,
    COUNT(oi.id) as item_count
FROM orders o
LEFT JOIN users u ON o.user_id = u.id
LEFT JOIN order_items oi ON o.id = oi.order_id
GROUP BY o.id;

-- ========================================
-- SETUP COMPLETE MESSAGE
-- ========================================
SELECT 'Database setup completed successfully!' as message,
       (SELECT COUNT(*) FROM categories) as categories_count,
       (SELECT COUNT(*) FROM products) as products_count,
       (SELECT COUNT(*) FROM users) as users_count,
       (SELECT COUNT(*) FROM orders) as orders_count;