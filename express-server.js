const express = require('express');
const path = require('path');
const app = express();
const PORT = 5000;

// Enable CORS
app.use((req, res, next) => {
    res.setHeader('Access-Control-Allow-Origin', '*');
    res.setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
    res.setHeader('Access-Control-Allow-Headers', 'Content-Type');
    next();
});

// Serve static files from public directory
app.use('/public', express.static(path.join(__dirname, 'public')));
app.use('/photo_ecommerce', express.static(path.join(__dirname, 'photo_ecommerce')));
app.use('/images', express.static(path.join(__dirname, 'photo_ecommerce')));

// Products data matching your images
const products = [
    { id: 1, name: 'Apple MacBook Pro', price: 2499.99, image: 'Apple_Laptop.jpg', category: 'Electronics', rating: 4.8, stock: 10 },
    { id: 2, name: 'Digital Camera Pro', price: 899.99, image: 'digi_cam.jpg', category: 'Electronics', rating: 4.7, stock: 15 },
    { id: 3, name: 'Premium AirPods Case', price: 49.99, image: 'Earpods_case.jpg', category: 'Electronics', rating: 4.5, stock: 50 },
    { id: 4, name: 'Designer Body Suit', price: 89.99, image: 'body_suit.jpg', category: 'Fashion', rating: 4.6, stock: 25 },
    { id: 5, name: 'Golden Wedding Rings', price: 1299.99, image: 'rings.jpg', category: 'Fashion', rating: 4.9, stock: 8 },
    { id: 6, name: 'Custom Coffee Mugs', price: 24.99, image: 'Mugs.jpg', category: 'Home', rating: 4.4, stock: 100 },
    { id: 7, name: 'Cute Plushie Collection', price: 19.99, image: 'Plushie.jpg', category: 'Home', rating: 4.7, stock: 75 }
];

// API Routes
app.get('/api/products', (req, res) => {
    res.json(products);
});

app.get('/api/cart', (req, res) => {
    res.json([]);
});

app.get('/api/orders', (req, res) => {
    res.json([]);
});

app.get('/api/addresses', (req, res) => {
    res.json([]);
});

app.get('/api/status', (req, res) => {
    res.json({ 
        success: true, 
        message: 'Fortes E-commerce API is running!',
        timestamp: new Date().toISOString()
    });
});

// Serve main page
app.get('/', (req, res) => {
    res.sendFile(path.join(__dirname, 'simple-index.html'));
});

app.listen(PORT, () => {
    console.log(`🚀 Fortes E-commerce Server running perfectly at:`);
    console.log(`📱 Local: http://localhost:${PORT}`);
    console.log(`✅ Your complete original design is now working!`);
    console.log(`🛍️ Open http://localhost:${PORT} in your browser`);
});