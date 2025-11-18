// E-commerce Website JavaScript - Enhanced User Flow Implementation

class ECommerceApp {
    constructor() {
        this.apiBase = '/api';
        this.currentPage = 'home';
        this.cart = [];
        this.wishlist = JSON.parse(localStorage.getItem('wishlist')) || [];
        this.recentSearches = JSON.parse(localStorage.getItem('recentSearches')) || [];
        this.currentUser = JSON.parse(localStorage.getItem('currentUser')) || null;
        this.orders = [];
        this.addresses = [];
        this.paymentMethods = [];
        this.products = [];
        
        this.init();
    }

    async init() {
        try {
            await this.loadProductsFromAPI();
            await this.loadCartFromAPI();
            await this.loadOrdersFromAPI();
            await this.loadAddressesFromAPI();
            await this.loadPaymentMethodsFromAPI();
        } catch (error) {
            console.error('Failed to load from API, using fallback:', error);
            this.loadSampleProducts();
        }
        
        this.showSplashScreen();
        this.bindEvents();
        this.updateCartCount();
        this.loadRecentSearches();
        this.loadHomePage();
    }

    // API Integration Methods
    async loadProductsFromAPI() {
        try {
            const response = await fetch(`${this.apiBase}/products`);
            if (response.ok) {
                this.products = await response.json();
                console.log('Products loaded from database:', this.products.length);
            } else {
                throw new Error('Failed to load products');
            }
        } catch (error) {
            console.error('Error loading products from API:', error);
            throw error;
        }
    }
    
    async loadCartFromAPI() {
        try {
            const response = await fetch(`${this.apiBase}/cart`);
            if (response.ok) {
                const cartData = await response.json();
                this.cart = cartData.map(item => ({
                    id: parseInt(item.product_id),
                    name: item.name,
                    price: parseFloat(item.price),
                    image: item.image,
                    quantity: parseInt(item.quantity)
                }));
                console.log('Cart loaded from database:', this.cart.length, 'items');
            }
        } catch (error) {
            console.error('Error loading cart from API:', error);
            this.cart = JSON.parse(localStorage.getItem('cart')) || [];
        }
    }
    
    async loadOrdersFromAPI() {
        try {
            const response = await fetch(`${this.apiBase}/orders`);
            if (response.ok) {
                const ordersData = await response.json();
                this.orders = ordersData.map(order => ({
                    id: order.order_number,
                    date: order.date_created.split(' ')[0],
                    total: parseFloat(order.total_amount),
                    status: order.status,
                    items: [] // Will be populated when needed
                }));
                console.log('Orders loaded from database:', this.orders.length);
            }
        } catch (error) {
            console.error('Error loading orders from API:', error);
            this.orders = JSON.parse(localStorage.getItem('orders')) || [];
        }
    }
    
    async loadAddressesFromAPI() {
        try {
            const response = await fetch(`${this.apiBase}/addresses`);
            if (response.ok) {
                const addressesData = await response.json();
                this.addresses = addressesData.map(addr => ({
                    id: addr.id,
                    name: addr.name,
                    type: addr.type,
                    street: addr.address_line_1,
                    city: addr.city,
                    phone: addr.phone,
                    isDefault: addr.is_default == 1
                }));
                console.log('Addresses loaded from database:', this.addresses.length);
            }
        } catch (error) {
            console.error('Error loading addresses from API:', error);
            this.addresses = JSON.parse(localStorage.getItem('addresses')) || [];
        }
    }
    
    async loadPaymentMethodsFromAPI() {
        try {
            const response = await fetch(`${this.apiBase}/payment-methods`);
            if (response.ok) {
                const methodsData = await response.json();
                this.paymentMethods = methodsData.map(method => ({
                    id: method.id,
                    type: method.type,
                    name: method.name,
                    details: method.details,
                    isDefault: method.is_default == 1
                }));
                console.log('Payment methods loaded from database:', this.paymentMethods.length);
            }
        } catch (error) {
            console.error('Error loading payment methods from API:', error);
            this.paymentMethods = JSON.parse(localStorage.getItem('paymentMethods')) || [];
        }
    }

    async syncCartToAPI(productId, quantity, action = 'add') {
        try {
            const method = action === 'remove' ? 'DELETE' : (action === 'update' ? 'PUT' : 'POST');
            const body = { product_id: productId, quantity: quantity };
            
            const response = await fetch(`${this.apiBase}/cart`, {
                method: method,
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(body)
            });
            
            if (!response.ok) {
                throw new Error('Failed to sync cart');
            }
            console.log(`Cart synced: ${action} product ${productId}`);
        } catch (error) {
            console.error('Error syncing cart:', error);
            // Fall back to localStorage
            localStorage.setItem('cart', JSON.stringify(this.cart));
        }
    }

    async clearCartAPI() {
        try {
            const response = await fetch(`${this.apiBase}/cart`, { 
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ clear_all: true })
            });
            
            if (!response.ok) {
                throw new Error('Failed to clear cart');
            }
            console.log('Cart cleared in database');
        } catch (error) {
            console.error('Error clearing cart:', error);
        }
    }

    async syncOrderToAPI(orderData) {
        try {
            const response = await fetch(`${this.apiBase}/orders`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(orderData)
            });
            
            if (response.ok) {
                const result = await response.json();
                console.log('Order created in database:', result.id);
                await this.loadOrdersFromAPI(); // Reload orders
                return result.id;
            } else {
                throw new Error('Failed to create order');
            }
        } catch (error) {
            console.error('Error creating order:', error);
            // Fallback to localStorage
            this.orders.push(orderData);
            localStorage.setItem('orders', JSON.stringify(this.orders));
            return orderData.id;
        }
    }

    async syncAddressToAPI(addressData) {
        try {
            const response = await fetch(`${this.apiBase}/addresses`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    name: addressData.name,
                    type: addressData.type.toLowerCase(),
                    address_line_1: addressData.street,
                    city: addressData.city,
                    phone: addressData.phone,
                    is_default: addressData.isDefault ? 1 : 0
                })
            });
            
            if (response.ok) {
                const result = await response.json();
                console.log('Address created in database:', result.id);
                await this.loadAddressesFromAPI(); // Reload addresses
                return result.id;
            } else {
                throw new Error('Failed to create address');
            }
        } catch (error) {
            console.error('Error creating address:', error);
            return Date.now(); // Fallback ID
        }
    }

    loadHomePage() {
        // Load products on the home page - find the product grid in trending section
        const trendingGrid = document.querySelector('.trending-products .product-grid');
        if (trendingGrid) {
            trendingGrid.innerHTML = ''; // Clear existing content
            // Show first 6 products as trending
            this.products.slice(0, 6).forEach(product => {
                trendingGrid.appendChild(this.createProductCard(product));
            });
        }
    }

    loadSampleProducts() {
        this.products = [
            // Electronics & Gadgets
            { 
                id: 1, 
                name: 'MacBook Pro 16"', 
                price: 2499, 
                image: 'images/Apple_Laptop.jpg',
                rating: 4.9,
                category: 'gadgets',
                description: 'Powerful laptop with M2 Pro chip, perfect for professionals and creatives.',
                features: ['M2 Pro Chip', '16GB RAM', '512GB SSD', 'Retina Display', '22-hour battery'],
                colors: ['space-gray', 'silver'],
                specifications: {
                    'Processor': 'Apple M2 Pro',
                    'Memory': '16GB Unified Memory',
                    'Storage': '512GB SSD',
                    'Display': '16.2-inch Retina',
                    'Weight': '2.15 kg',
                    'Battery': 'Up to 22 hours'
                }
            },
            { 
                id: 2, 
                name: 'Digital Camera Pro', 
                price: 899, 
                image: 'images/digi_cam.jpg',
                rating: 4.6,
                category: 'gadgets',
                description: 'Professional digital camera with 4K video recording and advanced autofocus.',
                features: ['4K Video Recording', '24MP Sensor', 'WiFi Connectivity', 'Image Stabilization'],
                colors: ['black', 'silver'],
                specifications: {
                    'Resolution': '24.2 MP',
                    'Video': '4K at 60fps',
                    'Display': '3.2-inch touchscreen',
                    'Connectivity': 'WiFi, Bluetooth',
                    'Weight': '650g',
                    'Battery Life': '740 shots'
                }
            },
            { 
                id: 3, 
                name: 'AirPods Pro Case', 
                price: 199, 
                image: 'images/Earpods_case.jpg',
                rating: 4.8,
                category: 'gadgets',
                description: 'Premium wireless earbuds with active noise cancellation and spatial audio.',
                features: ['Active Noise Cancellation', 'Spatial Audio', 'Wireless Charging', 'Transparency Mode'],
                colors: ['white'],
                specifications: {
                    'Driver': 'Custom Dynamic Driver',
                    'Battery Life': '6 hours + 24 hours case',
                    'Charging': 'Wireless & Lightning',
                    'Water Resistance': 'IPX4',
                    'Weight': '56g (with case)',
                    'Connectivity': 'Bluetooth 5.3'
                }
            },
            // Fashion & Clothing
            { 
                id: 4, 
                name: 'Premium Body Suit', 
                price: 89, 
                image: 'images/body_suit.jpg',
                rating: 4.4,
                category: 'clothes',
                description: 'Elegant and comfortable body suit perfect for any occasion.',
                features: ['Stretchy Fabric', 'Comfortable Fit', 'Machine Washable', 'Versatile Style'],
                colors: ['black', 'white', 'beige', 'navy'],
                specifications: {
                    'Material': '95% Cotton, 5% Elastane',
                    'Fit': 'Slim Fit',
                    'Care': 'Machine wash cold',
                    'Sizes': 'XS to XL',
                    'Origin': 'Premium Collection',
                    'Features': 'Snap closure'
                }
            },
            { 
                id: 5, 
                name: 'Diamond Rings Set', 
                price: 449, 
                image: 'images/rings.jpg',
                rating: 4.9,
                category: 'clothes',
                description: 'Elegant diamond rings set, perfect for special occasions and daily wear.',
                features: ['Genuine Diamonds', '14K Gold', 'Hypoallergenic', 'Gift Box Included'],
                colors: ['gold', 'silver', 'rose-gold'],
                specifications: {
                    'Material': '14K Gold',
                    'Stone': 'Natural Diamond',
                    'Ring Size': 'Adjustable 6-8',
                    'Carat': '0.25 total weight',
                    'Setting': 'Prong Setting',
                    'Warranty': '2 years'
                }
            },
            // Home & Furniture
            { 
                id: 6, 
                name: 'Designer Coffee Mugs', 
                price: 39, 
                image: 'images/Mugs.jpg',
                rating: 4.3,
                category: 'furniture',
                description: 'Beautiful ceramic coffee mugs with modern design, perfect for your morning coffee.',
                features: ['Ceramic Material', 'Dishwasher Safe', 'Microwave Safe', 'Modern Design'],
                colors: ['white', 'black', 'blue', 'green'],
                specifications: {
                    'Material': 'Premium Ceramic',
                    'Capacity': '350ml (12oz)',
                    'Care': 'Dishwasher & microwave safe',
                    'Design': 'Modern minimalist',
                    'Set': '2 mugs included',
                    'Origin': 'Designer Collection'
                }
            },
            { 
                id: 7, 
                name: 'Cute Plushie Collection', 
                price: 29, 
                image: 'images/Plushie.jpg',
                rating: 4.7,
                category: 'furniture',
                description: 'Adorable plushie toys perfect for decoration or gifting.',
                features: ['Super Soft', 'Hypoallergenic', 'Machine Washable', 'Child Safe'],
                colors: ['pink', 'blue', 'yellow', 'white'],
                specifications: {
                    'Material': 'Premium Plush Fabric',
                    'Size': '25cm height',
                    'Filling': 'Polyester fiber',
                    'Age': '3+ years',
                    'Care': 'Machine washable',
                    'Safety': 'CE certified'
                }
            },
            // Additional popular items
            { 
                id: 8, 
                name: 'Wireless Gaming Headset', 
                price: 249, 
                image: 'https://images.unsplash.com/photo-1487215078519-e21cc028cb29?w=300&h=300&fit=crop',
                rating: 4.7,
                category: 'gadgets',
                description: 'High-performance gaming headset with surround sound and RGB lighting.',
                features: ['7.1 Surround Sound', 'Noise-cancelling microphone', '40-hour battery', 'RGB lighting'],
                colors: ['black', 'red', 'blue'],
                specifications: {
                    'Driver Size': '45mm',
                    'Frequency Response': '12Hz - 28kHz',
                    'Battery Life': '40 hours',
                    'Charging Time': '2 hours',
                    'Weight': '320g',
                    'Connectivity': 'Bluetooth 5.0, USB-C'
                }
            },
            { 
                id: 9, 
                name: 'Smart Fitness Watch', 
                price: 329, 
                image: 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=300&h=300&fit=crop',
                rating: 4.5,
                category: 'gadgets',
                description: 'Advanced fitness tracking with heart rate monitoring and GPS.',
                features: ['Heart Rate Monitor', 'GPS Tracking', 'Sleep Analysis', 'Water Resistant'],
                colors: ['black', 'silver', 'gold'],
                specifications: {
                    'Display': '1.4-inch OLED',
                    'Battery Life': '7 days',
                    'Water Rating': '5ATM',
                    'Sensors': 'Heart rate, GPS, Accelerometer',
                    'Weight': '45g',
                    'Compatibility': 'iOS & Android'
                }
            },
            { 
                id: 10, 
                name: 'Stylish Sneakers', 
                price: 159, 
                image: 'https://images.unsplash.com/photo-1549298916-b41d501d3772?w=300&h=300&fit=crop',
                rating: 4.6,
                category: 'clothes',
                description: 'Comfortable and stylish sneakers perfect for everyday wear.',
                features: ['Breathable Material', 'Cushioned Sole', 'Durable Construction', 'Stylish Design'],
                colors: ['white', 'black', 'gray', 'blue'],
                specifications: {
                    'Material': 'Synthetic Leather & Mesh',
                    'Sole': 'Rubber with Air Cushion',
                    'Sizes': '6-12 US',
                    'Weight': '250g per shoe',
                    'Care': 'Wipe clean',
                    'Style': 'Casual/Athletic'
                }
            },
            // Additional Electronics & Gadgets
            { 
                id: 11, 
                name: 'Wireless Keyboard & Mouse Set', 
                price: 129, 
                image: 'https://images.unsplash.com/photo-1587829741301-dc798b83add3?w=300&h=300&fit=crop',
                rating: 4.4,
                category: 'gadgets',
                description: 'Ergonomic wireless keyboard and mouse combo for productivity.',
                features: ['2.4GHz Wireless', 'Ergonomic Design', '36-month Battery', 'Quiet Keys'],
                colors: ['black', 'white'],
                specifications: {
                    'Connectivity': '2.4GHz Wireless',
                    'Battery': 'AAA (36 months)',
                    'Range': '10 meters',
                    'Compatibility': 'Windows, Mac, Linux',
                    'Weight': '850g (keyboard)',
                    'Type': 'Membrane Keys'
                }
            },
            { 
                id: 12, 
                name: 'USB-C Hub 7-in-1', 
                price: 79, 
                image: 'https://images.unsplash.com/photo-1625842268584-8f3296236761?w=300&h=300&fit=crop',
                rating: 4.5,
                category: 'gadgets',
                description: 'Versatile USB-C hub with multiple ports for all your connectivity needs.',
                features: ['7 Ports', '4K HDMI Output', 'USB 3.0', 'SD Card Reader'],
                colors: ['gray', 'silver'],
                specifications: {
                    'Ports': '7 (HDMI, USB, SD, etc)',
                    'HDMI': '4K@60Hz',
                    'USB': '3.0 (5Gbps)',
                    'Power': '100W Pass-through',
                    'Material': 'Aluminum',
                    'Size': '11.5 x 3.2 x 1.2 cm'
                }
            },
            { 
                id: 13, 
                name: 'Portable Phone Stand', 
                price: 25, 
                image: 'https://images.unsplash.com/photo-1556656793-08538906a9f8?w=300&h=300&fit=crop',
                rating: 4.2,
                category: 'gadgets',
                description: 'Adjustable phone stand perfect for video calls and media viewing.',
                features: ['Adjustable Angle', 'Foldable Design', 'Anti-slip Base', 'Universal Compatibility'],
                colors: ['black', 'white', 'silver'],
                specifications: {
                    'Material': 'Aluminum Alloy',
                    'Angle': '0-90 degrees adjustable',
                    'Compatibility': '4-10 inch devices',
                    'Weight': '180g',
                    'Folded Size': '15 x 8 x 2 cm',
                    'Load': 'Up to 2kg'
                }
            },
            { 
                id: 14, 
                name: 'Wireless Charging Pad', 
                price: 45, 
                image: 'https://images.unsplash.com/photo-1609091839311-d5365f9ff1c5?w=300&h=300&fit=crop',
                rating: 4.3,
                category: 'gadgets',
                description: 'Fast wireless charging pad compatible with all Qi-enabled devices.',
                features: ['Fast Charging', 'LED Indicator', 'Case Friendly', 'Temperature Control'],
                colors: ['black', 'white'],
                specifications: {
                    'Power': '15W Max',
                    'Compatibility': 'Qi-enabled devices',
                    'Charging Distance': 'Up to 8mm',
                    'Input': 'USB-C',
                    'Size': '10 x 10 x 0.8 cm',
                    'Safety': 'Overcharge protection'
                }
            },
            { 
                id: 15, 
                name: 'Bluetooth Speaker Pro', 
                price: 189, 
                image: 'https://images.unsplash.com/photo-1545454675-3531b543be5d?w=300&h=300&fit=crop',
                rating: 4.7,
                category: 'gadgets',
                description: 'High-quality portable Bluetooth speaker with 360-degree sound.',
                features: ['360° Sound', 'Waterproof IPX7', '20h Battery', 'Voice Assistant'],
                colors: ['black', 'blue', 'red'],
                specifications: {
                    'Output': '20W Stereo',
                    'Battery': '20 hours playback',
                    'Bluetooth': '5.0',
                    'Range': '30 meters',
                    'Water Rating': 'IPX7',
                    'Weight': '680g'
                }
            },
            // Fashion & Clothing
            { 
                id: 16, 
                name: 'Designer Sunglasses', 
                price: 199, 
                image: 'https://images.unsplash.com/photo-1511499767150-a48a237f0083?w=300&h=300&fit=crop',
                rating: 4.8,
                category: 'clothes',
                description: 'Premium designer sunglasses with UV protection and polarized lenses.',
                features: ['UV400 Protection', 'Polarized Lenses', 'Designer Frame', 'Scratch Resistant'],
                colors: ['black', 'brown', 'gold'],
                specifications: {
                    'Lens': 'Polarized TAC',
                    'Frame': 'Premium Acetate',
                    'UV Protection': 'UV400',
                    'Width': '145mm',
                    'Bridge': '20mm',
                    'Temple': '135mm'
                }
            },
            { 
                id: 17, 
                name: 'Leather Wallet', 
                price: 89, 
                image: 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=300&h=300&fit=crop',
                rating: 4.6,
                category: 'clothes',
                description: 'Genuine leather wallet with RFID blocking technology.',
                features: ['Genuine Leather', 'RFID Blocking', '8 Card Slots', 'Coin Pocket'],
                colors: ['black', 'brown', 'tan'],
                specifications: {
                    'Material': 'Genuine Leather',
                    'Card Slots': '8',
                    'Size': '11 x 9 x 2 cm',
                    'RFID': 'Blocking Technology',
                    'Weight': '120g',
                    'Origin': 'Handcrafted'
                }
            },
            { 
                id: 18, 
                name: 'Classic Watch', 
                price: 299, 
                image: 'https://images.unsplash.com/photo-1524592094714-0f0654e20314?w=300&h=300&fit=crop',
                rating: 4.9,
                category: 'clothes',
                description: 'Elegant classic watch with automatic movement and sapphire crystal.',
                features: ['Automatic Movement', 'Sapphire Crystal', 'Water Resistant', 'Leather Strap'],
                colors: ['silver', 'gold', 'black'],
                specifications: {
                    'Movement': 'Automatic',
                    'Case': 'Stainless Steel',
                    'Crystal': 'Sapphire',
                    'Water Resistance': '50m',
                    'Diameter': '42mm',
                    'Strap': 'Genuine Leather'
                }
            },
            { 
                id: 19, 
                name: 'Winter Coat', 
                price: 249, 
                image: 'https://images.unsplash.com/photo-1544966503-7cc5ac882d5f?w=300&h=300&fit=crop',
                rating: 4.5,
                category: 'clothes',
                description: 'Warm and stylish winter coat perfect for cold weather.',
                features: ['Water Resistant', 'Insulated Lining', 'Multiple Pockets', 'Adjustable Hood'],
                colors: ['black', 'navy', 'gray'],
                specifications: {
                    'Material': 'Polyester Shell',
                    'Insulation': 'Down Alternative',
                    'Water Rating': 'DWR Coating',
                    'Sizes': 'XS to XXL',
                    'Weight': '850g',
                    'Temperature': '-10°C to 10°C'
                }
            },
            { 
                id: 20, 
                name: 'Running Shoes', 
                price: 179, 
                image: 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=300&h=300&fit=crop',
                rating: 4.7,
                category: 'clothes',
                description: 'High-performance running shoes with advanced cushioning technology.',
                features: ['Energy Return', 'Breathable Upper', 'Lightweight', 'Durable Outsole'],
                colors: ['white', 'black', 'blue', 'red'],
                specifications: {
                    'Midsole': 'Energy Return Foam',
                    'Upper': 'Engineered Mesh',
                    'Drop': '10mm',
                    'Weight': '280g',
                    'Sizes': '5-13 US',
                    'Purpose': 'Road Running'
                }
            },
            // Home & Furniture
            { 
                id: 21, 
                name: 'Smart LED Bulb Set', 
                price: 59, 
                image: 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=300&h=300&fit=crop',
                rating: 4.4,
                category: 'furniture',
                description: 'WiFi-enabled smart LED bulbs with color changing and dimming features.',
                features: ['WiFi Control', '16 Million Colors', 'Voice Control', 'Energy Efficient'],
                colors: ['white'],
                specifications: {
                    'Power': '9W (60W equivalent)',
                    'Brightness': '800 lumens',
                    'Colors': '16 million',
                    'Connectivity': 'WiFi 2.4GHz',
                    'Lifespan': '25,000 hours',
                    'Compatibility': 'Alexa, Google'
                }
            },
            { 
                id: 22, 
                name: 'Decorative Wall Mirror', 
                price: 129, 
                image: 'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?w=300&h=300&fit=crop',
                rating: 4.6,
                category: 'furniture',
                description: 'Elegant round mirror with modern frame perfect for any room.',
                features: ['Modern Design', 'High-Quality Glass', 'Easy Installation', 'Durable Frame'],
                colors: ['gold', 'black', 'silver'],
                specifications: {
                    'Size': '60cm diameter',
                    'Frame': 'Metal',
                    'Glass': 'HD Silver Mirror',
                    'Thickness': '5mm',
                    'Weight': '2.5kg',
                    'Mounting': 'Wall Hanging'
                }
            },
            { 
                id: 23, 
                name: 'Table Lamp Set', 
                price: 89, 
                image: 'https://images.unsplash.com/photo-1507473885765-e6ed057f782c?w=300&h=300&fit=crop',
                rating: 4.3,
                category: 'furniture',
                description: 'Modern table lamps with fabric shade and brass base.',
                features: ['Brass Base', 'Fabric Shade', 'Dimmer Switch', 'USB Charging Port'],
                colors: ['white', 'beige', 'gray'],
                specifications: {
                    'Height': '55cm',
                    'Base': 'Brass',
                    'Shade': 'Fabric',
                    'Bulb': 'E27 LED included',
                    'Power': '15W max',
                    'Features': 'USB charging port'
                }
            },
            { 
                id: 24, 
                name: 'Storage Ottoman', 
                price: 149, 
                image: 'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?w=300&h=300&fit=crop',
                rating: 4.5,
                category: 'furniture',
                description: 'Multi-functional storage ottoman perfect for extra seating and storage.',
                features: ['Hidden Storage', 'Extra Seating', 'Soft Cushioning', 'Durable Fabric'],
                colors: ['gray', 'navy', 'beige'],
                specifications: {
                    'Size': '60 x 40 x 40 cm',
                    'Material': 'Linen Fabric',
                    'Storage': '80L capacity',
                    'Weight Limit': '150kg',
                    'Frame': 'Solid Wood',
                    'Assembly': 'Tool-free'
                }
            },
            { 
                id: 25, 
                name: 'Wall Art Canvas Set', 
                price: 79, 
                image: 'https://images.unsplash.com/photo-1513475382585-d06e58bcb0e0?w=300&h=300&fit=crop',
                rating: 4.4,
                category: 'furniture',
                description: 'Beautiful abstract wall art canvas set of 3 pieces.',
                features: ['Set of 3', 'Ready to Hang', 'High-Quality Print', 'Modern Design'],
                colors: ['multi', 'black-white', 'blue-gold'],
                specifications: {
                    'Size': '30 x 40 cm each',
                    'Material': 'Canvas',
                    'Mounting': 'Pre-installed hooks',
                    'Print': 'Giclee Quality',
                    'Frame': 'Wrapped Canvas',
                    'Theme': 'Abstract Modern'
                }
            },
            { 
                id: 26, 
                name: 'Desk Organizer', 
                price: 35, 
                image: 'https://images.unsplash.com/photo-1554995207-c18c203602cb?w=300&h=300&fit=crop',
                rating: 4.2,
                category: 'furniture',
                description: 'Bamboo desk organizer with multiple compartments for office supplies.',
                features: ['Bamboo Material', 'Multiple Compartments', 'Phone Stand', 'Eco-Friendly'],
                colors: ['natural', 'dark'],
                specifications: {
                    'Material': 'Sustainable Bamboo',
                    'Size': '25 x 15 x 10 cm',
                    'Compartments': '6 sections',
                    'Phone Slot': 'Adjustable',
                    'Weight': '320g',
                    'Finish': 'Natural Oil'
                }
            },
            { 
                id: 27, 
                name: 'Throw Pillow Set', 
                price: 49, 
                image: 'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?w=300&h=300&fit=crop',
                rating: 4.6,
                category: 'furniture',
                description: 'Soft decorative throw pillows set with geometric patterns.',
                features: ['Set of 2', 'Removable Covers', 'Geometric Pattern', 'Machine Washable'],
                colors: ['gray-white', 'blue-gold', 'green-beige'],
                specifications: {
                    'Size': '45 x 45 cm',
                    'Material': 'Cotton Blend',
                    'Fill': 'Polyester Fiber',
                    'Pattern': 'Geometric',
                    'Care': 'Machine Washable',
                    'Closure': 'Hidden Zipper'
                }
            },
            // Additional Gadgets
            { 
                id: 28, 
                name: 'Portable Power Bank', 
                price: 69, 
                image: 'https://images.unsplash.com/photo-1609091839311-d5365f9ff1c5?w=300&h=300&fit=crop',
                rating: 4.5,
                category: 'gadgets',
                description: 'High-capacity portable power bank with fast charging technology.',
                features: ['20000mAh Capacity', 'Fast Charging', 'Multiple Ports', 'LED Display'],
                colors: ['black', 'white', 'blue'],
                specifications: {
                    'Capacity': '20000mAh',
                    'Input': 'USB-C 18W',
                    'Output': '3 ports (USB-A, USB-C)',
                    'Fast Charge': 'PD 3.0, QC 3.0',
                    'Weight': '420g',
                    'Size': '15 x 7 x 2.5 cm'
                }
            },
            { 
                id: 29, 
                name: 'Webcam HD 1080p', 
                price: 95, 
                image: 'https://images.unsplash.com/photo-1587829741301-dc798b83add3?w=300&h=300&fit=crop',
                rating: 4.4,
                category: 'gadgets',
                description: 'High-definition webcam perfect for video calls and streaming.',
                features: ['1080p Full HD', 'Auto Focus', 'Built-in Microphone', 'Privacy Cover'],
                colors: ['black'],
                specifications: {
                    'Resolution': '1080p@30fps',
                    'Field of View': '90 degrees',
                    'Focus': 'Auto Focus',
                    'Microphone': 'Built-in stereo',
                    'Connection': 'USB 2.0',
                    'Compatibility': 'Windows, Mac, Linux'
                }
            },
            { 
                id: 30, 
                name: 'Gaming Mouse RGB', 
                price: 79, 
                image: 'https://images.unsplash.com/photo-1527814050087-3793815479db?w=300&h=300&fit=crop',
                rating: 4.7,
                category: 'gadgets',
                description: 'High-precision gaming mouse with customizable RGB lighting.',
                features: ['16000 DPI', 'RGB Lighting', 'Programmable Buttons', 'Ergonomic Design'],
                colors: ['black', 'white'],
                specifications: {
                    'DPI': '16000 max',
                    'Buttons': '7 programmable',
                    'Lighting': '16.7M color RGB',
                    'Sensor': 'Optical PMW3360',
                    'Weight': '95g',
                    'Cable': '1.8m braided'
                }
            }
        ];
    }

    showSplashScreen() {
        const splashScreen = document.getElementById('splash-screen');
        splashScreen.classList.add('active');
        
        // Simulate loading and auto-progress to login
        setTimeout(() => {
            splashScreen.classList.remove('active');
            if (!this.currentUser) {
                this.showAuthModal();
            } else {
                this.showApp();
            }
        }, 3000);
    }

    showAuthModal() {
        const modal = document.getElementById('auth-modal');
        modal.classList.add('active');
    }

    showApp() {
        const app = document.getElementById('app');
        app.classList.add('active');
        
        // Load products immediately when app shows
        this.loadHomePage();
        this.updateCartCount();
    }

    bindEvents() {
        // Auth Modal Events
        this.bindAuthEvents();
        
        // Navigation Events
        this.bindNavigationEvents();
        
        // Search Events
        this.bindSearchEvents();
        
        // Product Events
        this.bindProductEvents();
        
        // Cart Events
        this.bindCartEvents();
        
        // Profile Events
        this.bindProfileEvents();
        
        // Filter Events
        this.bindFilterEvents();
        
        // Tab Events
        this.bindTabEvents();
    }

    bindAuthEvents() {
        const authModal = document.getElementById('auth-modal');
        const tabBtns = authModal.querySelectorAll('.tab-btn');
        const authForms = authModal.querySelectorAll('.auth-form');
        const closeBtn = authModal.querySelector('.close');

        // Tab switching
        tabBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const tabId = btn.dataset.tab;
                
                // Update active tab
                tabBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                
                // Update active form
                authForms.forEach(form => form.classList.remove('active'));
                document.getElementById(tabId).classList.add('active');
            });
        });

        // Close modal
        closeBtn.addEventListener('click', () => {
            authModal.classList.remove('active');
        });

        // Form submissions
        authForms.forEach(form => {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleAuth(form.id);
            });
        });
    }

    handleAuth(type) {
        // Simulate authentication
        const userData = {
            name: 'John Doe',
            email: 'john.doe@email.com',
            id: 'user123'
        };
        
        this.currentUser = userData;
        localStorage.setItem('currentUser', JSON.stringify(userData));
        
        document.getElementById('auth-modal').classList.remove('active');
        this.showApp();
        this.updateProfileDisplay();
        
        // Show success message
        this.showNotification(`${type === 'login' ? 'Logged in' : 'Account created'} successfully!`);
    }

    bindNavigationEvents() {
        const navItems = document.querySelectorAll('.nav-item');
        const profileIcon = document.querySelector('.profile-icon');
        const cartIcon = document.querySelector('.cart-icon');

        navItems.forEach(item => {
            item.addEventListener('click', () => {
                const page = item.dataset.page;
                this.navigateToPage(page);
            });
        });

        // Header icons
        profileIcon?.addEventListener('click', () => {
            this.navigateToPage('profile');
        });

        cartIcon?.addEventListener('click', () => {
            this.navigateToPage('cart');
        });
    }

    navigateToPage(page) {
        // Update active nav item
        document.querySelectorAll('.nav-item').forEach(item => {
            item.classList.remove('active');
        });
        document.querySelector(`[data-page="${page}"]`)?.classList.add('active');

        // Hide all pages
        document.querySelectorAll('.page').forEach(p => {
            p.classList.remove('active');
        });

        // Show target page
        document.getElementById(`${page}-page`).classList.add('active');
        this.currentPage = page;

        // Load page-specific data
        switch(page) {
            case 'home':
                this.loadHomePage();
                break;
            case 'search':
                this.loadSearchPage();
                break;
            case 'cart':
                this.updateCartDisplay();
                break;
            case 'profile':
                this.updateProfileDisplay();
                break;
            case 'wishlist':
                this.updateWishlistDisplay();
                break;
        }
    }

    bindSearchEvents() {
        const searchInput = document.getElementById('search-input');
        const searchBtn = document.querySelector('.search-btn');
        const filterBtn = document.getElementById('filter-btn');

        searchInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                this.performSearch(searchInput.value);
            }
        });

        searchBtn.addEventListener('click', () => {
            this.performSearch(searchInput.value);
        });

        searchInput.addEventListener('focus', () => {
            if (this.recentSearches.length > 0) {
                this.showRecentSearches();
            }
        });

        filterBtn.addEventListener('click', () => {
            this.showFilterModal();
        });

        // Category selection
        document.querySelectorAll('.category-card').forEach(card => {
            card.addEventListener('click', () => {
                document.querySelectorAll('.category-card').forEach(c => c.classList.remove('active'));
                card.classList.add('active');
                
                const category = card.dataset.category;
                this.filterProductsByCategory(category);
            });
        });
    }

    performSearch(query) {
        if (!query.trim()) return;

        // Add to recent searches
        if (!this.recentSearches.includes(query)) {
            this.recentSearches.unshift(query);
            this.recentSearches = this.recentSearches.slice(0, 5);
            localStorage.setItem('recentSearches', JSON.stringify(this.recentSearches));
        }

        // Navigate to search page and display results
        this.navigateToPage('search');
        this.displaySearchResults(query);
        
        // Update search input on search page
        document.getElementById('search-results-title').textContent = `Results for "${query}"`;
        
        // Clear the search input after search
        const searchInput = document.getElementById('search-input');
        if (searchInput) searchInput.value = '';
    }

    displaySearchResults(query) {
        const resultsGrid = document.getElementById('search-results-grid');
        const filteredProducts = this.products.filter(product => 
            product.name.toLowerCase().includes(query.toLowerCase()) ||
            product.category.toLowerCase().includes(query.toLowerCase()) ||
            product.description.toLowerCase().includes(query.toLowerCase()) ||
            product.features.some(feature => feature.toLowerCase().includes(query.toLowerCase()))
        );

        resultsGrid.innerHTML = '';
        
        if (filteredProducts.length === 0) {
            // Show suggestions when no results found
            const suggestions = this.getSuggestions(query);
            resultsGrid.innerHTML = `
                <div style="grid-column: 1 / -1; text-align: center; padding: 40px;">
                    <h3 style="color: #888; margin-bottom: 20px;">No products found for "${query}"</h3>
                    <p style="color: #666; margin-bottom: 20px;">Try searching for:</p>
                    <div style="display: flex; flex-wrap: wrap; gap: 10px; justify-content: center;">
                        ${suggestions.map(suggestion => `
                            <span class="search-tag" onclick="app.performSearch('${suggestion}')" style="cursor: pointer;">
                                ${suggestion}
                            </span>
                        `).join('')}
                    </div>
                </div>
            `;
        } else {
            filteredProducts.forEach(product => {
                resultsGrid.appendChild(this.createProductCard(product));
            });
        }
        
        // Update results count
        const resultsCount = document.createElement('div');
        resultsCount.style.cssText = 'grid-column: 1 / -1; text-align: center; padding: 20px; color: #888;';
        resultsCount.textContent = `${filteredProducts.length} product${filteredProducts.length !== 1 ? 's' : ''} found`;
        resultsGrid.insertBefore(resultsCount, resultsGrid.firstChild);
    }
    
    getSuggestions(query) {
        // Provide smart suggestions based on categories and popular items
        const suggestions = [
            'laptop', 'camera', 'headphones', 'keyboard', 'mouse', 'speaker', 'watch', 'powerbank',
            'clothing', 'sneakers', 'sunglasses', 'wallet', 'coat', 'shoes',
            'furniture', 'lamp', 'mirror', 'ottoman', 'pillow', 'desk', 'bulb', 'organizer',
            'gadgets', 'wireless', 'bluetooth', 'usb', 'charging', 'led', 'smart'
        ];
        
        // Filter out the current query and return 6 suggestions
        return suggestions
            .filter(s => s.toLowerCase() !== query.toLowerCase() && !s.includes(query.toLowerCase()))
            .slice(0, 6);
    }

    loadSearchPage() {
        this.loadRecentSearches();
    }

    loadRecentSearches() {
        const recentSearchContainer = document.getElementById('recent-searches');
        const searchTags = recentSearchContainer.querySelector('.recent-search-tags');
        
        if (this.recentSearches.length > 0) {
            recentSearchContainer.style.display = 'block';
            searchTags.innerHTML = '';
            
            this.recentSearches.forEach(search => {
                const tag = document.createElement('span');
                tag.className = 'search-tag';
                tag.textContent = search;
                tag.addEventListener('click', () => {
                    this.performSearch(search);
                });
                searchTags.appendChild(tag);
            });
        } else {
            recentSearchContainer.style.display = 'none';
        }
    }

    filterProductsByCategory(category) {
        let filteredProducts;
        
        if (category === 'all') {
            filteredProducts = this.products;
        } else {
            filteredProducts = this.products.filter(product => 
                product.category === category
            );
        }
        
        this.updateProductGrid(filteredProducts);
        
        // Update category title
        const categoryTitle = document.querySelector('.category-title');
        if (categoryTitle) {
            const categoryNames = {
                'all': 'All Products',
                'gadgets': 'Electronics & Gadgets',
                'clothes': 'Fashion & Clothing',
                'furniture': 'Home & Furniture'
            };
            categoryTitle.textContent = categoryNames[category] || 'Products';
        }
    }

    updateProductGrid(products = this.products) {
        // Try to find the trending products grid first, then fall back to any product grid
        let grid = document.querySelector('.trending-products .product-grid');
        if (!grid) {
            grid = document.querySelector('.product-grid');
        }
        
        if (grid) {
            grid.innerHTML = '';
            products.forEach(product => {
                grid.appendChild(this.createProductCard(product));
            });
        }
    }

    createProductCard(product) {
        const card = document.createElement('div');
        card.className = 'product-card';
        card.dataset.productId = product.id;

        const isWished = this.wishlist.includes(product.id);
        
        card.innerHTML = `
            <div class="product-image">
                <img src="${product.image}" alt="${product.name}">
                <button class="save-btn ${isWished ? 'saved' : ''}">
                    <i class="${isWished ? 'fas' : 'far'} fa-heart"></i>
                </button>
            </div>
            <div class="product-info">
                <h4 class="product-name">${product.name}</h4>
                <div class="product-rating">
                    <div class="stars">
                        ${this.generateStars(product.rating)}
                    </div>
                    <span class="rating-value">${product.rating}</span>
                </div>
                <div class="product-price">$${product.price}</div>
                <button class="add-to-cart-btn">Add to Cart</button>
            </div>
        `;

        // Bind events
        this.bindProductCardEvents(card, product);
        return card;
    }

    bindProductCardEvents(card, product) {
        const saveBtn = card.querySelector('.save-btn');
        const addToCartBtn = card.querySelector('.add-to-cart-btn');
        const productImage = card.querySelector('.product-image img');

        // Save/Wishlist functionality
        saveBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            this.toggleWishlist(product.id);
            this.updateSaveButton(saveBtn, this.wishlist.includes(product.id));
        });

        // Add to cart
        addToCartBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            this.addToCart(product);
        });

        // View product details
        productImage.addEventListener('click', () => {
            this.viewProductDetails(product);
        });

        card.addEventListener('click', () => {
            this.viewProductDetails(product);
        });
    }

    generateStars(rating) {
        const fullStars = Math.floor(rating);
        const hasHalfStar = rating % 1 !== 0;
        let starsHTML = '';

        for (let i = 0; i < fullStars; i++) {
            starsHTML += '<i class="fas fa-star"></i>';
        }

        if (hasHalfStar) {
            starsHTML += '<i class="fas fa-star-half-alt"></i>';
        }

        const remainingStars = 5 - Math.ceil(rating);
        for (let i = 0; i < remainingStars; i++) {
            starsHTML += '<i class="far fa-star"></i>';
        }

        return starsHTML;
    }

    bindProductEvents() {
        // This will be called when product detail events are needed
        const thumbnails = document.querySelectorAll('.thumbnail');
        thumbnails.forEach(thumb => {
            thumb.addEventListener('click', () => {
                this.changeMainImage(thumb.src);
                thumbnails.forEach(t => t.classList.remove('active'));
                thumb.classList.add('active');
            });
        });

        // Color selection
        document.querySelectorAll('.color-option').forEach(option => {
            option.addEventListener('click', () => {
                document.querySelectorAll('.color-option').forEach(o => o.classList.remove('active'));
                option.classList.add('active');
            });
        });
    }

    viewProductDetails(product) {
        // Navigate to product details page
        this.navigateToPage('product-details');
        
        // Update product details
        this.updateProductDetails(product);
    }

    updateProductDetails(product) {
        // Update main image
        document.getElementById('main-product-image').src = product.image;
        
        // Update product info
        document.querySelector('#product-details .product-name').textContent = product.name;
        document.querySelector('#product-details .current-price').textContent = `$${product.price}`;
        document.querySelector('#product-details .product-rating-details .rating-value').textContent = `${product.rating} (2,847 reviews)`;
        
        // Update stars
        document.querySelector('#product-details .stars').innerHTML = this.generateStars(product.rating);
        
        // Update features
        const featuresList = document.querySelector('.features-list');
        featuresList.innerHTML = '';
        product.features.forEach(feature => {
            const li = document.createElement('li');
            li.textContent = feature;
            featuresList.appendChild(li);
        });
        
        // Update color options
        const colorOptions = document.querySelector('.color-options');
        colorOptions.innerHTML = '';
        product.colors.forEach((color, index) => {
            const colorDiv = document.createElement('div');
            colorDiv.className = `color-option ${index === 0 ? 'active' : ''}`;
            colorDiv.dataset.color = color;
            colorDiv.style.background = this.getColorValue(color);
            colorOptions.appendChild(colorDiv);
        });
        
        // Update description
        document.querySelector('#description p').textContent = product.description;
        
        // Update specifications
        const specsTable = document.querySelector('.specs-table');
        specsTable.innerHTML = '';
        Object.entries(product.specifications).forEach(([key, value]) => {
            const specRow = document.createElement('div');
            specRow.className = 'spec-row';
            specRow.innerHTML = `
                <span class="spec-label">${key}:</span>
                <span class="spec-value">${value}</span>
            `;
            specsTable.appendChild(specRow);
        });
        
        // Update save button
        const saveBtn = document.querySelector('.save-btn-large');
        const isWished = this.wishlist.includes(product.id);
        this.updateSaveButton(saveBtn, isWished);
        
        // Bind save functionality
        saveBtn.onclick = () => {
            this.toggleWishlist(product.id);
            this.updateSaveButton(saveBtn, this.wishlist.includes(product.id));
        };
        
        // Bind add to cart functionality
        document.querySelector('.add-to-cart-btn-large').onclick = () => {
            this.addToCart(product);
        };
        
        // Re-bind product events
        this.bindProductEvents();
    }

    getColorValue(colorName) {
        const colors = {
            'black': '#1a1a1a',
            'white': '#ffffff',
            'blue': '#3b82f6',
            'red': '#ef4444',
            'silver': '#c0c0c0'
        };
        return colors[colorName] || '#333333';
    }

    changeMainImage(src) {
        document.getElementById('main-product-image').src = src;
    }

    toggleWishlist(productId) {
        const index = this.wishlist.indexOf(productId);
        if (index > -1) {
            this.wishlist.splice(index, 1);
            this.showNotification('Removed from wishlist');
        } else {
            this.wishlist.push(productId);
            this.showNotification('Added to wishlist');
        }
        localStorage.setItem('wishlist', JSON.stringify(this.wishlist));
    }

    updateSaveButton(button, isSaved) {
        if (isSaved) {
            button.classList.add('saved');
            button.querySelector('i').className = 'fas fa-heart';
        } else {
            button.classList.remove('saved');
            button.querySelector('i').className = 'far fa-heart';
        }
    }

    addToCart(product) {
        const existingItem = this.cart.find(item => item.id === product.id);
        
        if (existingItem) {
            existingItem.quantity += 1;
            this.syncCartToAPI(product.id, existingItem.quantity, 'update');
        } else {
            const cartItem = {
                id: product.id,
                name: product.name,
                price: product.price,
                image: product.image,
                quantity: 1,
                color: 'black' // Default color
            };
            this.cart.push(cartItem);
            this.syncCartToAPI(product.id, 1, 'add');
        }
        
        this.updateCartCount();
        this.showNotification(`${product.name} added to cart!`);
    }

    bindCartEvents() {
        // These events will be bound when cart page loads
    }

    updateCartDisplay() {
        const cartItems = document.querySelector('.cart-items');
        const subtotalEl = document.querySelector('.price-row:first-child span:last-child');
        const taxEl = document.querySelector('.price-row:nth-child(3) span:last-child');
        const totalEl = document.querySelector('.price-row.total span:last-child');
        
        if (!cartItems) return;
        
        cartItems.innerHTML = '';
        
        if (this.cart.length === 0) {
            cartItems.innerHTML = '<div style="text-align: center; color: #888; padding: 40px;">Your cart is empty</div>';
            return;
        }
        
        let subtotal = 0;
        
        this.cart.forEach(item => {
            const itemTotal = item.price * item.quantity;
            subtotal += itemTotal;
            
            const cartItemEl = document.createElement('div');
            cartItemEl.className = 'cart-item';
            cartItemEl.innerHTML = `
                <div class="item-image">
                    <img src="${item.image}" alt="${item.name}">
                </div>
                <div class="item-details">
                    <h4>${item.name}</h4>
                    <p>Color: ${item.color}</p>
                    <div class="item-price">$${item.price}</div>
                </div>
                <div class="quantity-controls">
                    <button class="qty-btn minus" data-id="${item.id}" ${item.quantity <= 1 ? 'disabled' : ''}>-</button>
                    <input type="number" class="quantity-input" value="${item.quantity}" min="1" max="99" data-id="${item.id}">
                    <button class="qty-btn plus" data-id="${item.id}">+</button>
                </div>
                <div class="item-total">
                    <div class="item-total-price">$${(item.price * item.quantity).toFixed(2)}</div>
                </div>
                <button class="remove-item" data-id="${item.id}">
                    <i class="fas fa-trash"></i>
                </button>
            `;
            
            cartItems.appendChild(cartItemEl);
        });
        
        // Bind cart item events
        this.bindCartItemEvents();
        
        // Update pricing with delivery fee
        const deliveryFee = subtotal > 100 ? 0 : 15; // Free delivery over $100
        const tax = subtotal * 0.08;
        const total = subtotal + tax + deliveryFee;
        
        if (subtotalEl) subtotalEl.textContent = `$${subtotal.toFixed(2)}`;
        if (taxEl) taxEl.textContent = `$${tax.toFixed(2)}`;
        if (totalEl) totalEl.textContent = `$${total.toFixed(2)}`;
        
        // Update delivery fee display
        const deliveryEl = document.querySelector('.delivery-fee');
        if (deliveryEl) deliveryEl.textContent = deliveryFee === 0 ? 'FREE' : `$${deliveryFee.toFixed(2)}`;
    }

    bindCartItemEvents() {
        document.querySelectorAll('.qty-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const productId = parseInt(btn.dataset.id);
                const isPlus = btn.classList.contains('plus');
                this.updateCartQuantity(productId, isPlus ? 1 : -1);
            });
        });
        
        document.querySelectorAll('.quantity-input').forEach(input => {
            input.addEventListener('change', (e) => {
                const productId = parseInt(input.dataset.id);
                const newQuantity = parseInt(e.target.value);
                if (newQuantity > 0 && newQuantity <= 99) {
                    this.setCartQuantity(productId, newQuantity);
                } else {
                    e.target.value = this.cart.find(item => item.id === productId)?.quantity || 1;
                }
            });
            
            input.addEventListener('blur', (e) => {
                if (e.target.value === '' || parseInt(e.target.value) < 1) {
                    e.target.value = 1;
                    this.setCartQuantity(parseInt(input.dataset.id), 1);
                }
            });
        });
        
        document.querySelectorAll('.remove-item').forEach(btn => {
            btn.addEventListener('click', () => {
                const productId = parseInt(btn.dataset.id);
                this.removeFromCart(productId);
            });
        });
        
        document.querySelector('.checkout-btn')?.addEventListener('click', () => {
            this.proceedToCheckout();
        });
    }

    setCartQuantity(productId, quantity) {
        const item = this.cart.find(item => item.id === productId);
        if (item) {
            item.quantity = quantity;
            this.syncCartToAPI(productId, quantity, 'update');
            this.updateCartDisplay();
            this.updateCartCount();
        }
    }

    proceedToCheckout() {
        if (this.cart.length === 0) {
            this.showNotification('Your cart is empty!');
            return;
        }
        
        const modal = document.createElement('div');
        modal.className = 'modal active';
        modal.innerHTML = `
            <div class="modal-content large-modal">
                <span class="close">&times;</span>
                <div style="padding: 30px;">
                    <h3>Checkout</h3>
                    <div class="checkout-sections">
                        <div class="checkout-section">
                            <h4>Select Payment Method</h4>
                            <div class="payment-methods-grid">
                                <div class="payment-method" data-method="cod">
                                    <i class="fas fa-money-bill-wave"></i>
                                    <span>Cash on Delivery</span>
                                </div>
                                <div class="payment-method" data-method="card">
                                    <i class="fas fa-credit-card"></i>
                                    <span>Credit/Debit Card</span>
                                </div>
                                <div class="payment-method" data-method="banking">
                                    <i class="fas fa-university"></i>
                                    <span>Online Banking</span>
                                </div>
                                <div class="payment-method" data-method="gcash">
                                    <i class="fas fa-mobile-alt"></i>
                                    <span>GCash</span>
                                </div>
                                <div class="payment-method" data-method="paypal">
                                    <i class="fab fa-paypal"></i>
                                    <span>PayPal</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="checkout-section">
                            <h4>Delivery Address</h4>
                            <div id="address-selection">
                                ${this.renderAddressSelection()}
                            </div>
                        </div>
                        
                        <div class="checkout-section">
                            <h4>Order Summary</h4>
                            <div class="order-summary-checkout">
                                ${this.renderCheckoutSummary()}
                            </div>
                        </div>
                    </div>
                    
                    <div class="checkout-actions">
                        <button class="place-order-btn" onclick="app.placeOrder()">Place Order</button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        modal.querySelector('.close').addEventListener('click', () => {
            document.body.removeChild(modal);
        });
        
        // Payment method selection
        modal.querySelectorAll('.payment-method').forEach(method => {
            method.addEventListener('click', () => {
                modal.querySelectorAll('.payment-method').forEach(m => m.classList.remove('selected'));
                method.classList.add('selected');
                this.selectedPaymentMethod = method.dataset.method;
            });
        });
    }

    updateCartQuantity(productId, change) {
        const item = this.cart.find(item => item.id === productId);
        if (item) {
            item.quantity += change;
            if (item.quantity <= 0) {
                this.removeFromCart(productId);
            } else {
                this.syncCartToAPI(productId, item.quantity, 'update');
                this.updateCartDisplay();
                this.updateCartCount();
            }
        }
    }

    removeFromCart(productId) {
        this.cart = this.cart.filter(item => item.id !== productId);
        this.syncCartToAPI(productId, 0, 'remove');
        this.updateCartDisplay();
        this.updateCartCount();
        this.showNotification('Item removed from cart');
    }

    updateCartCount() {
        const cartCount = document.querySelector('.cart-count');
        const totalItems = this.cart.reduce((sum, item) => sum + item.quantity, 0);
        if (cartCount) {
            cartCount.textContent = totalItems;
            cartCount.style.display = totalItems > 0 ? 'flex' : 'none';
        }
    }

    bindProfileEvents() {
        document.querySelectorAll('.menu-item').forEach(item => {
            item.addEventListener('click', () => {
                const action = item.dataset.action;
                this.handleProfileAction(action);
            });
        });
        
        document.querySelector('.edit-profile-btn')?.addEventListener('click', () => {
            this.showNotification('Profile edit functionality would be implemented here');
        });
    }

    handleProfileAction(action) {
        switch(action) {
            case 'orders':
                this.showOrdersModal();
                break;
            case 'addresses':
                this.showAddressesModal();
                break;
            case 'payments':
                this.showPaymentsModal();
                break;
            case 'vouchers':
                this.showVoucherModal();
                break;
            case 'notifications':
                this.showNotification('Notification settings would be configured here');
                break;
            case 'social':
                this.showNotification('Social media connections would be managed here');
                break;
            case 'logout':
                this.logout();
                break;
        }
    }

    showVoucherModal() {
        // Create and show voucher modal with "Add Gift Promo Code" functionality
        const modal = document.createElement('div');
        modal.className = 'modal active';
        modal.innerHTML = `
            <div class="modal-content">
                <span class="close">&times;</span>
                <div style="padding: 30px;">
                    <h3>Gift Vouchers</h3>
                    <div style="margin: 20px 0;">
                        <h4>Add Gift Promo Code</h4>
                        <div class="promo-input-group">
                            <input type="text" placeholder="Enter promo code" id="promo-code-input">
                            <button id="add-promo-btn">Add Code</button>
                        </div>
                    </div>
                    <div style="margin-top: 30px;">
                        <h4>Your Vouchers</h4>
                        <div style="color: #888; padding: 20px 0;">No vouchers available</div>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        // Bind events
        modal.querySelector('.close').addEventListener('click', () => {
            document.body.removeChild(modal);
        });
        
        modal.querySelector('#add-promo-btn').addEventListener('click', () => {
            const code = modal.querySelector('#promo-code-input').value;
            if (code.trim()) {
                this.showNotification(`Promo code "${code}" added successfully!`);
                modal.querySelector('#promo-code-input').value = '';
            }
        });
    }

    showOrdersModal() {
        // Initialize sample orders if none exist
        if (this.orders.length === 0) {
            this.initializeSampleOrders();
        }
        
        const modal = document.createElement('div');
        modal.className = 'modal active';
        modal.innerHTML = `
            <div class="modal-content large-modal">
                <span class="close">&times;</span>
                <div style="padding: 30px;">
                    <h3>My Orders</h3>
                    <div class="orders-tabs">
                        <button class="order-tab-btn active" data-status="all">All Orders</button>
                        <button class="order-tab-btn" data-status="pending">Pending</button>
                        <button class="order-tab-btn" data-status="shipped">Shipped</button>
                        <button class="order-tab-btn" data-status="delivered">Delivered</button>
                    </div>
                    <div class="orders-list" id="orders-list">
                        ${this.renderOrdersList('all')}
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        // Bind events
        modal.querySelector('.close').addEventListener('click', () => {
            document.body.removeChild(modal);
        });
        
        // Tab switching
        modal.querySelectorAll('.order-tab-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                modal.querySelectorAll('.order-tab-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                const status = btn.dataset.status;
                modal.querySelector('#orders-list').innerHTML = this.renderOrdersList(status);
            });
        });
    }

    initializeSampleOrders() {
        this.orders = [
            {
                id: 'ORD001',
                date: new Date(Date.now() - 86400000).toISOString(),
                status: 'delivered',
                total: 358.99,
                estimatedDelivery: 'Delivered',
                items: [
                    { id: 1, name: 'MacBook Pro 16"', price: 2499, quantity: 1, image: 'images/Apple_Laptop.jpg' }
                ]
            },
            {
                id: 'ORD002',
                date: new Date(Date.now() - 172800000).toISOString(),
                status: 'shipped',
                total: 127.99,
                estimatedDelivery: 'Nov 20, 2025',
                items: [
                    { id: 2, name: 'Digital Camera Pro', price: 899, quantity: 1, image: 'images/digi_cam.jpg' }
                ]
            },
            {
                id: 'ORD003',
                date: new Date().toISOString(),
                status: 'pending',
                total: 89.99,
                estimatedDelivery: 'Nov 22, 2025',
                items: [
                    { id: 4, name: 'Premium Body Suit', price: 89, quantity: 1, image: 'images/body_suit.jpg' }
                ]
            }
        ];
        localStorage.setItem('orders', JSON.stringify(this.orders));
    }

    renderOrdersList(status) {
        let ordersToShow = this.orders;
        if (status !== 'all') {
            ordersToShow = this.orders.filter(order => order.status === status);
        }
        
        if (ordersToShow.length === 0) {
            return '<div style="text-align: center; color: #888; padding: 40px;">No orders found</div>';
        }
        
        return ordersToShow.map(order => `
            <div class="order-item">
                <div class="order-header">
                    <div class="order-id">#${order.id}</div>
                    <div class="order-status status-${order.status}">${order.status.toUpperCase()}</div>
                </div>
                <div class="order-details">
                    <div class="order-date">Ordered on ${new Date(order.date).toLocaleDateString()}</div>
                    <div class="order-total">Total: $${order.total.toFixed(2)}</div>
                </div>
                <div class="order-items">
                    ${order.items.map(item => `
                        <div class="order-item-detail">
                            <img src="${item.image}" alt="${item.name}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                            <div>
                                <div style="font-weight: 500;">${item.name}</div>
                                <div style="color: #666;">Qty: ${item.quantity} × $${item.price}</div>
                            </div>
                        </div>
                    `).join('')}
                </div>
                <div class="order-actions">
                    <button onclick="app.trackOrder('${order.id}')" class="track-btn">Track Order</button>
                    ${order.status === 'delivered' ? '<button onclick="app.reorderItems(\'' + order.id + '\')" class="reorder-btn">Reorder</button>' : ''}
                </div>
            </div>
        `).join('');
    }

    showAddressesModal() {
        // Initialize sample addresses if none exist
        if (this.addresses.length === 0) {
            this.initializeSampleAddresses();
        }
        
        const modal = document.createElement('div');
        modal.className = 'modal active';
        modal.innerHTML = `
            <div class="modal-content large-modal">
                <span class="close">&times;</span>
                <div style="padding: 30px;">
                    <h3>Delivery Addresses</h3>
                    <button class="add-address-btn" onclick="app.showAddAddressForm()">+ Add New Address</button>
                    <div class="addresses-list" id="addresses-list">
                        ${this.renderAddressesList()}
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        modal.querySelector('.close').addEventListener('click', () => {
            document.body.removeChild(modal);
        });
    }

    initializeSampleAddresses() {
        this.addresses = [
            {
                type: 'Home',
                recipientName: 'John Doe',
                street: '123 Main Street, Apt 4B',
                city: 'New York',
                state: 'NY',
                zipCode: '10001',
                country: 'United States',
                phone: '+1 (555) 123-4567',
                isDefault: true
            }
        ];
        localStorage.setItem('addresses', JSON.stringify(this.addresses));
    }

    renderAddressesList() {
        if (this.addresses.length === 0) {
            return '<div style="text-align: center; color: #888; padding: 40px;">No addresses saved</div>';
        }
        
        return this.addresses.map((address, index) => `
            <div class="address-item ${address.isDefault ? 'default-address' : ''}">
                <div class="address-header">
                    <div class="address-type">${address.type}</div>
                    ${address.isDefault ? '<span class="default-label">DEFAULT</span>' : ''}
                </div>
                <div class="address-details">
                    <div class="recipient-name">${address.recipientName}</div>
                    <div class="address-line">${address.street}</div>
                    <div class="address-line">${address.city}, ${address.state} ${address.zipCode}</div>
                    <div class="address-line">${address.country}</div>
                    <div class="phone-number">${address.phone}</div>
                </div>
                <div class="address-actions">
                    <button onclick="app.editAddress(${index})" class="edit-btn">Edit</button>
                    <button onclick="app.deleteAddress(${index})" class="delete-btn">Delete</button>
                    ${!address.isDefault ? `<button onclick="app.setDefaultAddress(${index})" class="default-btn">Set as Default</button>` : ''}
                </div>
            </div>
        `).join('');
    }

    showPaymentsModal() {
        const modal = document.createElement('div');
        modal.className = 'modal active';
        modal.innerHTML = `
            <div class="modal-content large-modal">
                <span class="close">&times;</span>
                <div style="padding: 30px;">
                    <h3>Payment Methods</h3>
                    <div class="payment-options">
                        <div class="payment-section">
                            <h4>Available Payment Methods</h4>
                            <div class="payment-methods-grid">
                                <div class="payment-method" data-method="cod">
                                    <i class="fas fa-money-bill-wave"></i>
                                    <span>Cash on Delivery</span>
                                    <small>Pay when you receive</small>
                                </div>
                                <div class="payment-method" data-method="card">
                                    <i class="fas fa-credit-card"></i>
                                    <span>Credit/Debit Card</span>
                                    <small>Visa, Mastercard, etc.</small>
                                </div>
                                <div class="payment-method" data-method="banking">
                                    <i class="fas fa-university"></i>
                                    <span>Online Banking</span>
                                    <small>Bank transfer</small>
                                </div>
                                <div class="payment-method" data-method="gcash">
                                    <i class="fas fa-mobile-alt"></i>
                                    <span>GCash</span>
                                    <small>Mobile wallet</small>
                                </div>
                                <div class="payment-method" data-method="paypal">
                                    <i class="fab fa-paypal"></i>
                                    <span>PayPal</span>
                                    <small>Secure online payments</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        modal.querySelector('.close').addEventListener('click', () => {
            document.body.removeChild(modal);
        });
        
        // Payment method selection
        modal.querySelectorAll('.payment-method').forEach(method => {
            method.addEventListener('click', () => {
                modal.querySelectorAll('.payment-method').forEach(m => m.classList.remove('selected'));
                method.classList.add('selected');
                this.selectedPaymentMethod = method.dataset.method;
                this.showNotification(`${method.querySelector('span').textContent} selected as payment method`);
            });
        });
    }

    trackOrder(orderId) {
        const order = this.orders.find(o => o.id === orderId);
        if (!order) return;
        
        const modal = document.createElement('div');
        modal.className = 'modal active';
        modal.innerHTML = `
            <div class="modal-content">
                <span class="close">&times;</span>
                <div style="padding: 30px;">
                    <h3>Track Order #${orderId}</h3>
                    <div class="tracking-info">
                        <div class="order-progress">
                            <div class="progress-step ${['confirmed', 'processing', 'shipped', 'delivered'].indexOf(order.status) >= 0 ? 'completed' : ''}">
                                <i class="fas fa-check-circle"></i>
                                <span>Order Confirmed</span>
                                <small>Nov 15, 2025</small>
                            </div>
                            <div class="progress-step ${['processing', 'shipped', 'delivered'].indexOf(order.status) >= 0 ? 'completed' : ''}">
                                <i class="fas fa-cog"></i>
                                <span>Processing</span>
                                <small>Nov 16, 2025</small>
                            </div>
                            <div class="progress-step ${['shipped', 'delivered'].indexOf(order.status) >= 0 ? 'completed' : ''}">
                                <i class="fas fa-shipping-fast"></i>
                                <span>Shipped</span>
                                <small>${order.status === 'shipped' || order.status === 'delivered' ? 'Nov 17, 2025' : 'Pending'}</small>
                            </div>
                            <div class="progress-step ${order.status === 'delivered' ? 'completed' : ''}">
                                <i class="fas fa-box-open"></i>
                                <span>Delivered</span>
                                <small>${order.status === 'delivered' ? 'Nov 18, 2025' : 'Pending'}</small>
                            </div>
                        </div>
                        <div class="order-summary">
                            <h4>Order Summary</h4>
                            <p><strong>Order Date:</strong> ${new Date(order.date).toLocaleDateString()}</p>
                            <p><strong>Total:</strong> $${order.total.toFixed(2)}</p>
                            <p><strong>Status:</strong> ${order.status.toUpperCase()}</p>
                            <p><strong>Estimated Delivery:</strong> ${order.estimatedDelivery || 'TBD'}</p>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        modal.querySelector('.close').addEventListener('click', () => {
            document.body.removeChild(modal);
        });
    }

    showAddAddressForm() {
        const modal = document.createElement('div');
        modal.className = 'modal active';
        modal.innerHTML = `
            <div class="modal-content">
                <span class="close">&times;</span>
                <div style="padding: 30px;">
                    <h3>Add New Address</h3>
                    <form id="address-form">
                        <div class="form-row">
                            <input type="text" placeholder="Full Name" name="recipientName" required>
                        </div>
                        <div class="form-row">
                            <input type="text" placeholder="Street Address" name="street" required>
                        </div>
                        <div class="form-row">
                            <input type="text" placeholder="City" name="city" required>
                            <input type="text" placeholder="State" name="state" required>
                        </div>
                        <div class="form-row">
                            <input type="text" placeholder="ZIP Code" name="zipCode" required>
                            <input type="text" placeholder="Country" name="country" required>
                        </div>
                        <div class="form-row">
                            <input type="tel" placeholder="Phone Number" name="phone" required>
                        </div>
                        <div class="form-row">
                            <select name="type" required>
                                <option value="Home">Home</option>
                                <option value="Office">Office</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="form-row">
                            <label>
                                <input type="checkbox" name="isDefault"> Set as default address
                            </label>
                        </div>
                        <button type="submit" class="btn-primary">Save Address</button>
                    </form>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        modal.querySelector('.close').addEventListener('click', () => {
            document.body.removeChild(modal);
        });
        
        modal.querySelector('#address-form').addEventListener('submit', (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const address = Object.fromEntries(formData);
            address.isDefault = formData.has('isDefault');
            
            // If setting as default, remove default from others
            if (address.isDefault) {
                this.addresses.forEach(addr => addr.isDefault = false);
            }
            
            this.addresses.push(address);
            this.syncAddressToAPI(address);
            this.showNotification('Address added successfully!');
            document.body.removeChild(modal);
        });
    }

    updateProfileDisplay() {
        if (this.currentUser) {
            document.getElementById('profile-name').textContent = this.currentUser.name;
        }
    }
    
    renderAddressSelection() {
        if (this.addresses.length === 0) {
            return '<p>No saved addresses. <a href="#" onclick="app.showAddressesModal()">Add an address</a></p>';
        }
        
        return this.addresses.map((address, index) => `
            <div class="address-option" data-index="${index}">
                <input type="radio" name="delivery-address" value="${index}" ${index === 0 ? 'checked' : ''}>
                <label>
                    <strong>${address.name}</strong> - ${address.type}<br>
                    ${address.street}, ${address.city}<br>
                    ${address.phone}
                </label>
            </div>
        `).join('') + '<p><a href="#" onclick="app.showAddressesModal()">Manage addresses</a></p>';
    }
    
    renderCheckoutSummary() {
        const subtotal = this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        const deliveryFee = subtotal >= 100 ? 0 : 15;
        const total = subtotal + deliveryFee;
        
        return `
            <div class="cart-items-summary">
                ${this.cart.map(item => `
                    <div class="summary-item">
                        <span>${item.name} (${item.quantity}x)</span>
                        <span>$${(item.price * item.quantity).toFixed(2)}</span>
                    </div>
                `).join('')}
            </div>
            <div class="summary-totals">
                <div class="summary-line">
                    <span>Subtotal:</span>
                    <span>$${subtotal.toFixed(2)}</span>
                </div>
                <div class="summary-line">
                    <span>Delivery:</span>
                    <span>${deliveryFee === 0 ? 'FREE' : '$' + deliveryFee.toFixed(2)}</span>
                </div>
                <div class="summary-line total-line">
                    <span>Total:</span>
                    <span>$${total.toFixed(2)}</span>
                </div>
            </div>
        `;
    }
    
    async placeOrder() {
        if (!this.selectedPaymentMethod) {
            this.showNotification('Please select a payment method');
            return;
        }
        
        const selectedAddress = document.querySelector('input[name="delivery-address"]:checked');
        if (!selectedAddress && this.addresses.length > 0) {
            this.showNotification('Please select a delivery address');
            return;
        }
        
        // Generate order ID
        const orderId = 'ORD' + Date.now().toString().slice(-6);
        
        // Calculate totals
        const subtotal = this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        const deliveryFee = subtotal >= 100 ? 0 : 15;
        const total = subtotal + deliveryFee;
        
        // Create order data
        const orderData = {
            order_number: orderId,
            items: this.cart.map(item => ({
                product_id: item.id,
                quantity: item.quantity,
                price: item.price
            })),
            total_amount: total,
            payment_method_id: this.selectedPaymentMethod.id || 1,
            delivery_address_id: selectedAddress ? selectedAddress.value : null,
            status: 'pending'
        };
        
        try {
            // Save order to database
            const orderDbId = await this.syncOrderToAPI(orderData);
            
            // Clear cart in database
            await this.clearCartAPI();
            
            // Clear local cart
            this.cart = [];
            
            // Close modal and show success
            document.querySelectorAll('.modal').forEach(modal => {
                if (modal.parentNode) modal.parentNode.removeChild(modal);
            });
            
            this.updateCartCount();
            this.updateCartDisplay();
            
            this.showNotification(`Order ${orderId} placed successfully!`);
            
            // Show order confirmation
            setTimeout(() => {
                this.showOrderConfirmation({
                    id: orderId,
                    date: new Date().toLocaleDateString(),
                    items: [...this.cart],
                    total: total,
                    status: 'pending'
                });
            }, 1000);
            
        } catch (error) {
            console.error('Order placement error:', error);
            this.showNotification('Error placing order. Please try again.');
        }
    }

    showOrderConfirmation(order) {
        const modal = document.createElement('div');
        modal.className = 'modal active';
        modal.innerHTML = `
            <div class="modal-content">
                <span class="close">&times;</span>
                <div style="text-align: center; padding: 30px;">
                    <i class="fas fa-check-circle" style="font-size: 48px; color: #4CAF50; margin-bottom: 20px;"></i>
                    <h3>Order Confirmed!</h3>
                    <p>Your order <strong>${order.id}</strong> has been placed successfully.</p>
                    <p>You will receive updates on your order status.</p>
                    <div style="margin-top: 30px;">
                        <button onclick="app.showOrdersModal()" style="margin-right: 10px;">Track Order</button>
                        <button onclick="document.body.removeChild(this.closest('.modal'))">Continue Shopping</button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        modal.querySelector('.close').addEventListener('click', () => {
            document.body.removeChild(modal);
        });
    }

    logout() {
        this.currentUser = null;
        localStorage.removeItem('currentUser');
        localStorage.removeItem('cart');
        localStorage.removeItem('wishlist');
        localStorage.removeItem('recentSearches');
        
        this.cart = [];
        this.wishlist = [];
        this.recentSearches = [];
        
        // Reset app state
        document.getElementById('app').classList.remove('active');
        this.showAuthModal();
        
        this.showNotification('Logged out successfully');
    }

    updateWishlistDisplay() {
        const wishlistGrid = document.querySelector('.wishlist-grid');
        if (!wishlistGrid) return;
        
        wishlistGrid.innerHTML = '';
        
        if (this.wishlist.length === 0) {
            wishlistGrid.innerHTML = '<div style="text-align: center; color: #888; padding: 40px; grid-column: 1 / -1;">No saved items</div>';
            return;
        }
        
        const savedProducts = this.products.filter(product => 
            this.wishlist.includes(product.id)
        );
        
        savedProducts.forEach(product => {
            const card = this.createProductCard(product);
            card.classList.add('saved');
            wishlistGrid.appendChild(card);
        });
    }

    bindFilterEvents() {
        const filterBtn = document.getElementById('filter-btn');
        const filterModal = document.getElementById('filter-modal');
        const closeBtn = filterModal?.querySelector('.close');
        
        filterBtn?.addEventListener('click', () => {
            filterModal.classList.add('active');
        });
        
        closeBtn?.addEventListener('click', () => {
            filterModal.classList.remove('active');
        });
        
        filterModal?.addEventListener('click', (e) => {
            if (e.target === filterModal) {
                filterModal.classList.remove('active');
            }
        });
    }

    showFilterModal() {
        const filterModal = document.getElementById('filter-modal');
        filterModal.classList.add('active');
    }

    bindTabEvents() {
        document.querySelectorAll('.product-tabs .tab-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const tabId = btn.dataset.tab;
                
                // Update active tab
                document.querySelectorAll('.product-tabs .tab-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                
                // Update active content
                document.querySelectorAll('.tab-pane').forEach(pane => pane.classList.remove('active'));
                document.getElementById(tabId).classList.add('active');
            });
        });
    }

    showNotification(message) {
        // Create notification element
        const notification = document.createElement('div');
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: #6366f1;
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            z-index: 10001;
            animation: slideIn 0.3s ease-out;
            max-width: 300px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        `;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        // Remove after 3 seconds
        setTimeout(() => {
            notification.style.animation = 'slideOut 0.3s ease-in';
            setTimeout(() => {
                if (document.body.contains(notification)) {
                    document.body.removeChild(notification);
                }
            }, 300);
        }, 3000);
    }
}

// CSS animations for notifications
const notificationStyles = document.createElement('style');
notificationStyles.textContent = `
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    
    @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
`;
document.head.appendChild(notificationStyles);

// Initialize the app when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.app = new ECommerceApp(); // Make app globally accessible
});

// Export for potential module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ECommerceApp;
}