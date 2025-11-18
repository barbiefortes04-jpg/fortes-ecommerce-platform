export default async function handler(req, res) {
    // Enable CORS for all requests
    res.setHeader('Access-Control-Allow-Origin', '*');
    res.setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
    res.setHeader('Access-Control-Allow-Headers', 'Content-Type');

    // Handle preflight requests
    if (req.method === 'OPTIONS') {
        return res.status(200).end();
    }

    const { method } = req;
    const url = new URL(req.url || '', `https://${req.headers.host}`);
    const path = url.pathname.replace('/api', '');

    // Sample products data
    const products = [
        { id: 1, name: 'Apple Laptop', price: 1299.99, image: '/images/Apple_Laptop.jpg', category: 'Electronics', description: 'High-performance Apple laptop for professionals', stock: 15 },
        { id: 2, name: 'AirPods Case', price: 49.99, image: '/images/Earpods_case.jpg', category: 'Electronics', description: 'Protective case for your AirPods', stock: 50 },
        { id: 3, name: 'Coffee Mugs', price: 19.99, image: '/images/Mugs.jpg', category: 'Home & Kitchen', description: 'Premium ceramic coffee mugs set', stock: 30 },
        { id: 4, name: 'Cute Plushie', price: 24.99, image: '/images/Plushie.jpg', category: 'Toys & Games', description: 'Adorable soft plushie toy', stock: 20 },
        { id: 5, name: 'Body Suit', price: 79.99, image: '/images/body_suit.jpg', category: 'Fashion', description: 'Stylish and comfortable body suit', stock: 25 },
        { id: 6, name: 'Digital Camera', price: 899.99, image: '/images/digi_cam.jpg', category: 'Electronics', description: 'Professional digital camera with high resolution', stock: 8 },
        { id: 7, name: 'Fashion Rings', price: 129.99, image: '/images/rings.jpg', category: 'Jewelry', description: 'Beautiful fashion rings set', stock: 12 },
        { id: 8, name: 'Gaming Headset', price: 149.99, image: 'https://images.unsplash.com/photo-1599669454699-248893623440?w=400', category: 'Gaming', description: 'High-quality gaming headset with surround sound', stock: 18 },
        { id: 9, name: 'Smart Watch', price: 299.99, image: 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=400', category: 'Electronics', description: 'Feature-rich smartwatch with health tracking', stock: 22 },
        { id: 10, name: 'Wireless Mouse', price: 59.99, image: 'https://images.unsplash.com/photo-1527814050087-3793815479db?w=400', category: 'Electronics', description: 'Ergonomic wireless mouse for productivity', stock: 35 }
    ];

    let cart = [];
    let orders = [];

    try {
        // Routes handling
        if (path === '/products' && method === 'GET') {
            return res.status(200).json({
                success: true,
                data: products,
                message: 'Products loaded successfully'
            });
        }

        if (path === '/products' && method === 'POST') {
            const { name, price, category, description, stock } = req.body;
            const newProduct = {
                id: products.length + 1,
                name,
                price: parseFloat(price),
                category,
                description,
                stock: parseInt(stock),
                image: '/images/placeholder.jpg'
            };
            products.push(newProduct);
            return res.status(201).json({
                success: true,
                data: newProduct,
                message: 'Product added successfully'
            });
        }

        if (path.startsWith('/products/') && method === 'GET') {
            const productId = parseInt(path.split('/')[2]);
            const product = products.find(p => p.id === productId);
            
            if (!product) {
                return res.status(404).json({
                    success: false,
                    message: 'Product not found'
                });
            }
            
            return res.status(200).json({
                success: true,
                data: product,
                message: 'Product found'
            });
        }

        if (path === '/cart' && method === 'GET') {
            return res.status(200).json({
                success: true,
                data: cart,
                message: 'Cart loaded successfully'
            });
        }

        if (path === '/cart' && method === 'POST') {
            const { productId, quantity = 1 } = req.body;
            const product = products.find(p => p.id === parseInt(productId));
            
            if (!product) {
                return res.status(404).json({
                    success: false,
                    message: 'Product not found'
                });
            }

            const existingItem = cart.find(item => item.productId === parseInt(productId));
            
            if (existingItem) {
                existingItem.quantity += parseInt(quantity);
            } else {
                cart.push({
                    id: cart.length + 1,
                    productId: parseInt(productId),
                    quantity: parseInt(quantity),
                    product: product
                });
            }

            return res.status(200).json({
                success: true,
                data: cart,
                message: 'Product added to cart'
            });
        }

        if (path === '/orders' && method === 'GET') {
            return res.status(200).json({
                success: true,
                data: orders,
                message: 'Orders loaded successfully'
            });
        }

        if (path === '/orders' && method === 'POST') {
            const { items, total, shippingAddress, paymentMethod } = req.body;
            const newOrder = {
                id: orders.length + 1,
                items: items || cart,
                total: parseFloat(total),
                shippingAddress,
                paymentMethod,
                status: 'pending',
                createdAt: new Date().toISOString(),
                trackingNumber: 'TRK' + Date.now()
            };
            
            orders.push(newOrder);
            cart = []; // Clear cart after order
            
            return res.status(201).json({
                success: true,
                data: newOrder,
                message: 'Order placed successfully'
            });
        }

        // Status endpoint
        if (path === '/status' && method === 'GET') {
            return res.status(200).json({
                success: true,
                message: 'Fortes E-commerce API is running!',
                timestamp: new Date().toISOString(),
                endpoints: {
                    products: 'GET/POST /api/products',
                    cart: 'GET/POST /api/cart',
                    orders: 'GET/POST /api/orders',
                    status: 'GET /api/status'
                }
            });
        }

        // Default route
        if (path === '/' && method === 'GET') {
            return res.status(200).json({
                success: true,
                message: '🛍️ Welcome to Fortes E-commerce API!',
                version: '1.0.0',
                author: 'Jherilyn Fortes',
                description: 'Complete RESTful API for e-commerce platform',
                endpoints: [
                    'GET /api/products - Get all products',
                    'POST /api/products - Add new product',
                    'GET /api/products/:id - Get specific product',
                    'GET /api/cart - View cart',
                    'POST /api/cart - Add to cart',
                    'GET /api/orders - View orders',
                    'POST /api/orders - Place order',
                    'GET /api/status - API status'
                ]
            });
        }

        // Route not found
        return res.status(404).json({
            success: false,
            message: 'API endpoint not found',
            availableEndpoints: ['/products', '/cart', '/orders', '/status']
        });

    } catch (error) {
        console.error('API Error:', error);
        return res.status(500).json({
            success: false,
            message: 'Internal server error',
            error: error.message
        });
    }
}