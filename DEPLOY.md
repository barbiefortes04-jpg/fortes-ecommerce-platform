# 🚀 Fortes E-commerce Platform - Deployment Guide

## Live Demo
- **GitHub Repository**: https://github.com/barbiefortes04-jpg/fortes-ecommerce-platform
- **Live Site**: Deploy to Vercel using the button below

[![Deploy with Vercel](https://vercel.com/button)](https://vercel.com/new/clone?repository-url=https%3A%2F%2Fgithub.com%2Fbarbiefortes04-jpg%2Ffortes-ecommerce-platform)

## 🛍️ Features

### ✅ Complete E-commerce Platform
- **30 Products** including your personal product images
- **Shopping Cart** with real-time persistence
- **Order Management** with tracking and delivery
- **Address Management** for delivery locations
- **Multiple Payment Methods**: COD, Card, Online Banking, GCash, PayPal
- **Premium Dark Theme** with responsive design
- **RESTful API** with PHP backend
- **MySQL Database Integration** (ready for cloud database)

### 🎯 Technical Stack
- **Frontend**: HTML5, CSS3, JavaScript ES6+
- **Backend**: PHP 8.0+ with RESTful API
- **Database**: MySQL with PDO
- **Deployment**: Vercel-ready with PHP runtime
- **Version Control**: Git & GitHub

## 🚀 Quick Deploy to Vercel

### Option 1: One-Click Deploy
1. Click the "Deploy with Vercel" button above
2. Connect your GitHub account
3. Deploy automatically from your repository

### Option 2: Manual Deploy via Vercel CLI

```bash
# Install Vercel CLI
npm i -g vercel

# Clone your repository
git clone https://github.com/barbiefortes04-jpg/fortes-ecommerce-platform.git
cd fortes-ecommerce-platform

# Deploy to Vercel
vercel --prod
```

### Option 3: Deploy via Vercel Website

1. Go to [vercel.com](https://vercel.com)
2. Sign in with GitHub
3. Click "New Project"
4. Import `barbiefortes04-jpg/fortes-ecommerce-platform`
5. Deploy with default settings

## 🗄️ Database Setup (Optional)

For full functionality with persistent data, set up a cloud MySQL database:

### Option 1: PlanetScale (Recommended)
```bash
# Environment variables to add in Vercel:
DATABASE_URL="mysql://username:password@host:port/database"
```

### Option 2: Railway/Supabase/Digital Ocean
- Create MySQL database instance
- Add connection string to Vercel environment variables
- Database will auto-create tables on first run

## 🔧 Environment Configuration

Add these environment variables in Vercel dashboard:

```env
# Database (Optional - uses file storage by default)
DATABASE_URL=mysql://user:pass@host:port/db

# API Configuration
API_BASE_URL=https://yourapp.vercel.app
```

## 📱 Live Features You Can Test

### 🛒 Shopping Experience
- Browse 30 products across Electronics, Fashion, Home categories
- Add items to cart (persists across sessions)
- Adjust quantities and remove items
- Real-time cart total calculations

### 📦 Order Management
- Place orders with delivery information
- Track order status (Pending → Processing → Shipped → Delivered)
- Order history with detailed item breakdown
- Order confirmation with unique tracking numbers

### 🏠 Address & Payments
- Add multiple delivery addresses
- Set default delivery location
- Choose from 5 payment methods
- Secure checkout process

## 🎨 Design Highlights

- **Dark Premium Theme** with gradient accents
- **Responsive Grid Layout** for all screen sizes
- **Smooth Animations** and hover effects
- **Font Awesome Icons** for better UX
- **Modern Typography** and spacing
- **Mobile-First Design** approach

## 📊 API Endpoints

Your deployed site includes a full RESTful API:

```bash
# Products
GET /api/products              # Get all products
GET /api/products/{id}         # Get specific product
POST /api/products             # Create product (admin)
PUT /api/products/{id}         # Update product (admin)
DELETE /api/products/{id}      # Delete product (admin)

# Shopping Cart
GET /api/cart                  # Get user cart
POST /api/cart                 # Add to cart
PUT /api/cart                  # Update cart item
DELETE /api/cart               # Remove from cart

# Orders
GET /api/orders                # Get user orders
POST /api/orders               # Place new order
GET /api/orders/{id}           # Get order details
PATCH /api/orders/{id}         # Update order status

# Addresses
GET /api/addresses             # Get user addresses
POST /api/addresses            # Add new address
PUT /api/addresses/{id}        # Update address
DELETE /api/addresses/{id}     # Delete address
```

## 🔄 Automatic Deployments

Your repository is configured for automatic deployments:
- **Push to master** → Automatic Vercel deployment
- **Pull Requests** → Preview deployments
- **Environment sync** → Production/staging environments

## 💡 Customization

### Adding Products
1. Edit `public/script.js` products array
2. Add product images to `public/images/`
3. Push changes → Auto-deploy

### Styling Changes
1. Modify `public/styles.css`
2. Update color variables in `:root`
3. Push changes → Auto-deploy

### API Modifications
1. Edit `public/api_mysql.php`
2. Update endpoints or add features
3. Push changes → Auto-deploy

## 📈 Production Features

- **CDN Caching** via Vercel Edge Network
- **SSL Encryption** automatically configured
- **Global Performance** with edge locations worldwide
- **Automatic Scaling** based on traffic
- **Zero-Config Deployments** with instant rollbacks

## 🛠️ Local Development

```bash
# Clone repository
git clone https://github.com/barbiefortes04-jpg/fortes-ecommerce-platform.git
cd fortes-ecommerce-platform

# Start local server
php -S localhost:8000 -t public

# Or use npm script
npm run dev
```

## 📞 Support

- **Repository**: [GitHub Issues](https://github.com/barbiefortes04-jpg/fortes-ecommerce-platform/issues)
- **Documentation**: See README.md files in repository
- **API Testing**: Use `/test_db.php` endpoint

---

**Built with ❤️ by Jherilyn Fortes**

**Ready to launch your e-commerce empire! 🚀🛍️**