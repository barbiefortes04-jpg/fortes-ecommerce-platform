export default function handler(req, res) {
  res.setHeader('Access-Control-Allow-Origin', '*');
  res.setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE');
  res.setHeader('Access-Control-Allow-Headers', 'Content-Type');

  const products = [
    { id: 1, name: 'Apple Laptop', price: 1299.99, image: 'Apple_Laptop.jpg', category: 'Electronics', stock: 10 },
    { id: 2, name: 'AirPods Case', price: 49.99, image: 'Earpods_case.jpg', category: 'Electronics', stock: 25 },
    { id: 3, name: 'Coffee Mugs', price: 19.99, image: 'Mugs.jpg', category: 'Home', stock: 50 },
    { id: 4, name: 'Cute Plushie', price: 24.99, image: 'Plushie.jpg', category: 'Home', stock: 30 },
    { id: 5, name: 'Body Suit', price: 79.99, image: 'body_suit.jpg', category: 'Fashion', stock: 15 },
    { id: 6, name: 'Digital Camera', price: 899.99, image: 'digi_cam.jpg', category: 'Electronics', stock: 8 },
    { id: 7, name: 'Fashion Rings', price: 129.99, image: 'rings.jpg', category: 'Fashion', stock: 20 }
  ];

  if (req.method === 'GET') {
    return res.status(200).json(products);
  }

  if (req.method === 'POST') {
    return res.status(200).json({ success: true });
  }

  return res.status(405).json({ error: 'Method not allowed' });
}