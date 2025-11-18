const http = require('http');
const fs = require('fs');
const path = require('path');
const url = require('url');

const PORT = 9000;

// Product data
const products = [
    { id: 1, name: 'Apple Laptop', price: 1299.99, image: 'Apple_Laptop.jpg', category: 'Electronics', stock: 10 },
    { id: 2, name: 'AirPods Case', price: 49.99, image: 'Earpods_case.jpg', category: 'Electronics', stock: 25 },
    { id: 3, name: 'Coffee Mugs', price: 19.99, image: 'Mugs.jpg', category: 'Home', stock: 50 },
    { id: 4, name: 'Cute Plushie', price: 24.99, image: 'Plushie.jpg', category: 'Home', stock: 30 },
    { id: 5, name: 'Body Suit', price: 79.99, image: 'body_suit.jpg', category: 'Fashion', stock: 15 },
    { id: 6, name: 'Digital Camera', price: 899.99, image: 'digi_cam.jpg', category: 'Electronics', stock: 8 },
    { id: 7, name: 'Fashion Rings', price: 129.99, image: 'rings.jpg', category: 'Fashion', stock: 20 }
];

const server = http.createServer((req, res) => {
    const parsedUrl = url.parse(req.url, true);
    const pathname = parsedUrl.pathname;
    
    // CORS headers
    res.setHeader('Access-Control-Allow-Origin', '*');
    res.setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
    res.setHeader('Access-Control-Allow-Headers', 'Content-Type');
    
    if (req.method === 'OPTIONS') {
        res.writeHead(200);
        res.end();
        return;
    }
    
    // API endpoints
    if (pathname.startsWith('/api/')) {
        res.setHeader('Content-Type', 'application/json');
        
        if (pathname === '/api/' && req.method === 'GET') {
            res.writeHead(200);
            res.end(JSON.stringify(products));
            return;
        }
        
        if (pathname === '/api/products' && req.method === 'GET') {
            res.writeHead(200);
            res.end(JSON.stringify({ success: true, data: products }));
            return;
        }
        
        if (pathname === '/api/status' && req.method === 'GET') {
            res.writeHead(200);
            res.end(JSON.stringify({ 
                success: true, 
                message: 'Fortes E-commerce API is running!',
                timestamp: new Date().toISOString()
            }));
            return;
        }
        
        // API not found
        res.writeHead(404);
        res.end(JSON.stringify({ error: 'API endpoint not found' }));
        return;
    }
    
    // Serve static files
    let filePath = '';
    
    if (pathname === '/' || pathname === '/index.html') {
        filePath = path.join(__dirname, 'index.html');
    } else if (pathname.startsWith('/images/')) {
        filePath = path.join(__dirname, 'photo_ecommerce', pathname.replace('/images/', ''));
    } else {
        filePath = path.join(__dirname, pathname.substring(1));
    }
    
    // Get file extension for content type
    const ext = path.extname(filePath).toLowerCase();
    const mimeTypes = {
        '.html': 'text/html',
        '.js': 'text/javascript',
        '.css': 'text/css',
        '.json': 'application/json',
        '.png': 'image/png',
        '.jpg': 'image/jpeg',
        '.jpeg': 'image/jpeg',
        '.gif': 'image/gif',
        '.ico': 'image/x-icon'
    };
    
    const contentType = mimeTypes[ext] || 'application/octet-stream';
    
    fs.readFile(filePath, (err, content) => {
        if (err) {
            if (err.code === 'ENOENT') {
                res.writeHead(404, { 'Content-Type': 'text/html' });
                res.end('<h1>404 - File Not Found</h1>');
            } else {
                res.writeHead(500);
                res.end('Server Error');
            }
        } else {
            res.writeHead(200, { 'Content-Type': contentType });
            res.end(content);
        }
    });
});

server.listen(PORT, () => {
    console.log(`🚀 Fortes E-commerce Server running at:`);
    console.log(`📱 Local:    http://localhost:${PORT}`);
    console.log(`🌐 Network:  http://localhost:${PORT}`);
    console.log(`\n✅ Your e-commerce site is ready to preview!`);
    console.log(`🛍️ Open http://localhost:${PORT} in your browser`);
});