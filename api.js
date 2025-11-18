// Node.js API for Vercel deployment
const products = [
    { id: 1, name: 'Apple Laptop', price: 1299.99, image: 'Apple_Laptop.jpg', category: 'Electronics', stock: 10 },
    { id: 2, name: 'AirPods Case', price: 49.99, image: 'Earpods_case.jpg', category: 'Electronics', stock: 25 },
    { id: 3, name: 'Coffee Mugs', price: 19.99, image: 'Mugs.jpg', category: 'Home', stock: 50 },
    { id: 4, name: 'Cute Plushie', price: 24.99, image: 'Plushie.jpg', category: 'Home', stock: 30 },
    { id: 5, name: 'Body Suit', price: 79.99, image: 'body_suit.jpg', category: 'Fashion', stock: 15 },
    { id: 6, name: 'Digital Camera', price: 899.99, image: 'digi_cam.jpg', category: 'Electronics', stock: 8 },
    { id: 7, name: 'Fashion Rings', price: 129.99, image: 'rings.jpg', category: 'Fashion', stock: 20 },
    { id: 8, name: 'Gaming Headset', price: 149.99, image: 'https://via.placeholder.com/300x300/333/fff?text=Gaming+Headset', category: 'Electronics', stock: 12 },
    { id: 9, name: 'Smart Watch', price: 299.99, image: 'https://via.placeholder.com/300x300/333/fff?text=Smart+Watch', category: 'Electronics', stock: 18 },
    { id: 10, name: 'Wireless Mouse', price: 59.99, image: 'https://via.placeholder.com/300x300/333/fff?text=Wireless+Mouse', category: 'Electronics', stock: 35 },
    { id: 11, name: 'Bluetooth Speaker', price: 89.99, image: 'https://via.placeholder.com/300x300/333/fff?text=Bluetooth+Speaker', category: 'Electronics', stock: 22 },
    { id: 12, name: 'Fitness Tracker', price: 199.99, image: 'https://via.placeholder.com/300x300/333/fff?text=Fitness+Tracker', category: 'Electronics', stock: 16 },
    { id: 13, name: 'Wireless Earbuds', price: 179.99, image: 'https://via.placeholder.com/300x300/333/fff?text=Wireless+Earbuds', category: 'Electronics', stock: 28 },
    { id: 14, name: 'Tablet Stand', price: 39.99, image: 'https://via.placeholder.com/300x300/333/fff?text=Tablet+Stand', category: 'Electronics', stock: 45 },
    { id: 15, name: 'Phone Case', price: 24.99, image: 'https://via.placeholder.com/300x300/333/fff?text=Phone+Case', category: 'Electronics', stock: 60 },
    { id: 16, name: 'Leather Jacket', price: 159.99, image: 'https://via.placeholder.com/300x300/333/fff?text=Leather+Jacket', category: 'Fashion', stock: 12 },
    { id: 17, name: 'Designer Sunglasses', price: 199.99, image: 'https://via.placeholder.com/300x300/333/fff?text=Designer+Sunglasses', category: 'Fashion', stock: 18 },
    { id: 18, name: 'Silk Scarf', price: 79.99, image: 'https://via.placeholder.com/300x300/333/fff?text=Silk+Scarf', category: 'Fashion', stock: 25 },
    { id: 19, name: 'Designer Handbag', price: 299.99, image: 'https://via.placeholder.com/300x300/333/fff?text=Designer+Handbag', category: 'Fashion', stock: 8 },
    { id: 20, name: 'Casual Sneakers', price: 129.99, image: 'https://via.placeholder.com/300x300/333/fff?text=Casual+Sneakers', category: 'Fashion', stock: 32 },
    { id: 21, name: 'Throw Pillow', price: 34.99, image: 'https://via.placeholder.com/300x300/333/fff?text=Throw+Pillow', category: 'Home', stock: 40 },
    { id: 22, name: 'Table Lamp', price: 89.99, image: 'https://via.placeholder.com/300x300/333/fff?text=Table+Lamp', category: 'Home', stock: 15 },
    { id: 23, name: 'Wall Art', price: 59.99, image: 'https://via.placeholder.com/300x300/333/fff?text=Wall+Art', category: 'Home', stock: 20 },
    { id: 24, name: 'Candle Set', price: 49.99, image: 'https://via.placeholder.com/300x300/333/fff?text=Candle+Set', category: 'Home', stock: 35 },
    { id: 25, name: 'Storage Basket', price: 39.99, image: 'https://via.placeholder.com/300x300/333/fff?text=Storage+Basket', category: 'Home', stock: 28 },
    { id: 26, name: 'Essential Oil Diffuser', price: 69.99, image: 'https://via.placeholder.com/300x300/333/fff?text=Essential+Oil+Diffuser', category: 'Home', stock: 22 },
    { id: 27, name: 'Kitchen Scale', price: 44.99, image: 'https://via.placeholder.com/300x300/333/fff?text=Kitchen+Scale', category: 'Home', stock: 18 },
    { id: 28, name: 'Bamboo Cutting Board', price: 29.99, image: 'https://via.placeholder.com/300x300/333/fff?text=Bamboo+Cutting+Board', category: 'Home', stock: 42 },
    { id: 29, name: 'French Press', price: 54.99, image: 'https://via.placeholder.com/300x300/333/fff?text=French+Press', category: 'Home', stock: 26 },
    { id: 30, name: 'Yoga Mat', price: 79.99, image: 'https://via.placeholder.com/300x300/333/fff?text=Yoga+Mat', category: 'Home', stock: 31 }
];

let cart = [];
let orders = [];

module.exports = (req, res) => {
    // Enable CORS
    res.setHeader('Access-Control-Allow-Origin', '*');
    res.setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
    res.setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
    
    if (req.method === 'OPTIONS') {
        res.status(200).end();
        return;
    }

    const { url, method } = req;
    const path = url.replace('/api', '');

    try {
        switch (path) {
            case '/products':
            case '':
                if (method === 'GET') {
                    res.status(200).json(products);
                }
                break;
                
            case '/cart':
                if (method === 'GET') {
                    res.status(200).json(cart);
                } else if (method === 'POST') {
                    const { product_id, quantity } = req.body || {};
                    const product = products.find(p => p.id === product_id);
                    if (product) {
                        const existingItem = cart.find(item => item.product_id === product_id);
                        if (existingItem) {
                            existingItem.quantity = quantity;
                        } else {
                            cart.push({
                                product_id,
                                name: product.name,
                                price: product.price,
                                image: product.image,
                                quantity
                            });
                        }
                    }
                    res.status(200).json({ success: true, message: 'Cart updated' });
                } else if (method === 'PUT') {
                    const { product_id, quantity } = req.body || {};
                    const item = cart.find(item => item.product_id === product_id);
                    if (item) {
                        item.quantity = quantity;
                    }
                    res.status(200).json({ success: true, message: 'Cart updated' });
                } else if (method === 'DELETE') {
                    const { product_id, clear_all } = req.body || {};
                    if (clear_all) {
                        cart = [];
                    } else if (product_id) {
                        cart = cart.filter(item => item.product_id !== product_id);
                    }
                    res.status(200).json({ success: true, message: 'Cart updated' });
                }
                break;
                
            case '/orders':
                if (method === 'GET') {
                    res.status(200).json(orders);
                } else if (method === 'POST') {
                    const orderData = req.body || {};
                    const orderId = 'ORD' + Date.now().toString().slice(-6);
                    const order = {
                        id: orderId,
                        order_number: orderData.order_number || orderId,
                        date_created: new Date().toISOString(),
                        total_amount: orderData.total_amount || 0,
                        status: orderData.status || 'pending',
                        items: orderData.items || []
                    };
                    orders.push(order);
                    res.status(200).json({ success: true, id: orderId, message: 'Order created' });
                }
                break;
                
            case '/addresses':
                if (method === 'GET') {
                    res.status(200).json([]);
                } else if (method === 'POST') {
                    res.status(200).json({ success: true, id: Date.now(), message: 'Address saved' });
                }
                break;
                
            case '/payment-methods':
                if (method === 'GET') {
                    res.status(200).json([]);
                }
                break;
                
            default:
                res.status(404).json({ error: 'Endpoint not found' });
                break;
        }
    } catch (error) {
        res.status(500).json({ error: 'Internal server error', message: error.message });
    }
};