<?php
// Serve the HTML content
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zenon Electronics - Premium Audio Experience</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Splash Screen -->
    <div id="splash-screen" class="splash-screen active">
        <div class="splash-content">
            <div class="logo-container">
                <h1 class="logo">ZENON</h1>
                <p class="tagline">Premium Audio Experience</p>
            </div>
            <div class="loading-bar">
                <div class="loading-progress"></div>
            </div>
        </div>
    </div>

    <!-- Login/Signup Modal -->
    <div id="auth-modal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <div class="auth-container">
                <div class="auth-tabs">
                    <button class="tab-btn active" data-tab="login">Log In</button>
                    <button class="tab-btn" data-tab="signup">Sign Up</button>
                </div>
                
                <!-- Login Form -->
                <div id="login" class="auth-form active">
                    <h2>Welcome Back</h2>
                    <form>
                        <div class="form-group">
                            <input type="email" placeholder="Email" required>
                        </div>
                        <div class="form-group">
                            <input type="password" placeholder="Password" required>
                        </div>
                        <button type="submit" class="btn-primary">Log In</button>
                    </form>
                    <p class="forgot-password">Forgot your password?</p>
                </div>
                
                <!-- Signup Form -->
                <div id="signup" class="auth-form">
                    <h2>Create Account</h2>
                    <form>
                        <div class="form-group">
                            <input type="text" placeholder="Full Name" required>
                        </div>
                        <div class="form-group">
                            <input type="email" placeholder="Email" required>
                        </div>
                        <div class="form-group">
                            <input type="password" placeholder="Password" required>
                        </div>
                        <button type="submit" class="btn-primary">Sign Up</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Main App Container -->
    <div id="app" class="app-container">
        <!-- Header -->
        <header class="header">
            <div class="container">
                <div class="header-content">
                    <div class="logo">ZENON</div>
                    <div class="search-container">
                        <input type="text" placeholder="Search for products..." class="search-input" id="search-input">
                        <button class="search-btn">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    <div class="header-actions">
                        <button class="filter-btn" id="filter-btn">
                            <i class="fas fa-filter"></i>
                        </button>
                        <div class="cart-icon">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="cart-count">2</span>
                        </div>
                        <div class="profile-icon">
                            <i class="fas fa-user"></i>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Home Page -->
            <div id="home-page" class="page active">
                <!-- Offer Banner -->
                <section class="offer-banner">
                    <div class="container">
                        <div class="banner-content">
                            <div class="banner-text">
                                <h1>Premium Audio</h1>
                                <h2>Redefined</h2>
                                <p>Experience crystal-clear sound with our latest collection of wireless headphones</p>
                                <button class="btn-primary">Explore More</button>
                            </div>
                            <div class="banner-image">
                                <img src="https://images.unsplash.com/photo-1484704849700-f032a568e944?w=600&h=400&fit=crop" alt="Premium Headphones">
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Categories -->
                <section class="categories">
                    <div class="container">
                        <h3 class="section-title">Select Categories</h3>
                        <div class="category-grid">
                            <div class="category-card active" data-category="all">
                                <div class="category-icon">
                                    <i class="fas fa-th-large"></i>
                                </div>
                                <span>All Products</span>
                            </div>
                            <div class="category-card" data-category="gadgets">
                                <div class="category-icon">
                                    <i class="fas fa-laptop"></i>
                                </div>
                                <span>Electronics</span>
                            </div>
                            <div class="category-card" data-category="clothes">
                                <div class="category-icon">
                                    <i class="fas fa-tshirt"></i>
                                </div>
                                <span>Fashion</span>
                            </div>
                            <div class="category-card" data-category="furniture">
                                <div class="category-icon">
                                    <i class="fas fa-couch"></i>
                                </div>
                                <span>Home & Decor</span>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Trending Products -->
                <section class="trending-products">
                    <div class="container">
                        <div class="section-header">
                            <h3 class="section-title category-title">Trending Products</h3>
                            <button class="view-all" onclick="app.navigateToPage('search')">View All</button>
                        </div>
                        <div class="product-grid">
                            <!-- Products will be loaded dynamically by JavaScript -->
                        </div>
                    </div>
                </section>
            </div>

            <!-- Product Details Page -->
            <div id="product-details" class="page">
                <div class="container">
                    <div class="product-details-container">
                        <div class="product-gallery">
                            <div class="main-image">
                                <img id="main-product-image" src="https://images.unsplash.com/photo-1546435770-a3e426bf472b?w=500&h=500&fit=crop" alt="Product">
                            </div>
                            <div class="image-thumbnails">
                                <img src="https://images.unsplash.com/photo-1546435770-a3e426bf472b?w=100&h=100&fit=crop" alt="Thumbnail 1" class="thumbnail active">
                                <img src="https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=100&h=100&fit=crop" alt="Thumbnail 2" class="thumbnail">
                                <img src="https://images.unsplash.com/photo-1583394838336-acd977736f90?w=100&h=100&fit=crop" alt="Thumbnail 3" class="thumbnail">
                            </div>
                        </div>

                        <div class="product-info-details">
                            <div class="product-header">
                                <h1 class="product-name">AirBeats Pro</h1>
                                <button class="save-btn-large">
                                    <i class="far fa-heart"></i>
                                </button>
                            </div>
                            
                            <div class="product-rating-details">
                                <div class="stars">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star-half-alt"></i>
                                </div>
                                <span class="rating-value">4.8 (2,847 reviews)</span>
                            </div>

                            <div class="price-section">
                                <div class="current-price">$299</div>
                                <div class="original-price">$399</div>
                                <div class="discount">25% OFF</div>
                            </div>

                            <div class="color-selection">
                                <h4>Choose Color</h4>
                                <div class="color-options">
                                    <div class="color-option active" data-color="black" style="background: #1a1a1a;"></div>
                                    <div class="color-option" data-color="white" style="background: #ffffff;"></div>
                                    <div class="color-option" data-color="blue" style="background: #3b82f6;"></div>
                                    <div class="color-option" data-color="red" style="background: #ef4444;"></div>
                                </div>
                            </div>

                            <div class="features-section">
                                <h4>Key Features</h4>
                                <ul class="features-list">
                                    <li>Active Noise Cancellation</li>
                                    <li>30-hour battery life</li>
                                    <li>Wireless charging case</li>
                                    <li>Premium build quality</li>
                                </ul>
                            </div>

                            <div class="action-buttons">
                                <button class="add-to-cart-btn-large">Add to Cart</button>
                                <button class="buy-now-btn">Buy Now</button>
                            </div>
                        </div>
                    </div>

                    <!-- Product Details Tabs -->
                    <div class="product-tabs">
                        <div class="tab-buttons">
                            <button class="tab-btn active" data-tab="description">Description</button>
                            <button class="tab-btn" data-tab="reviews">Customer Reviews</button>
                            <button class="tab-btn" data-tab="specifications">Specifications</button>
                        </div>

                        <div class="tab-content">
                            <div id="description" class="tab-pane active">
                                <h3>Product Description</h3>
                                <p>Experience the next level of audio excellence with AirBeats Pro. Featuring advanced active noise cancellation technology, these premium wireless headphones deliver crystal-clear sound quality and unparalleled comfort for extended listening sessions.</p>
                                <p>The sleek design combines form and function, making them perfect for daily commutes, work sessions, or relaxing at home. With industry-leading battery life and quick-charge capability, you'll never be without your favorite music.</p>
                            </div>

                            <div id="reviews" class="tab-pane">
                                <div class="reviews-section">
                                    <div class="review-item">
                                        <div class="reviewer-info">
                                            <div class="reviewer-avatar">JD</div>
                                            <div class="reviewer-details">
                                                <h5>John Doe</h5>
                                                <div class="review-rating">
                                                    <i class="fas fa-star"></i>
                                                    <i class="fas fa-star"></i>
                                                    <i class="fas fa-star"></i>
                                                    <i class="fas fa-star"></i>
                                                    <i class="fas fa-star"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <p class="review-text">"Amazing sound quality! The noise cancellation works perfectly and the battery lasts all day. Highly recommended!"</p>
                                        <span class="review-date">2 days ago</span>
                                    </div>

                                    <div class="review-item">
                                        <div class="reviewer-info">
                                            <div class="reviewer-avatar">SM</div>
                                            <div class="reviewer-details">
                                                <h5>Sarah Miller</h5>
                                                <div class="review-rating">
                                                    <i class="fas fa-star"></i>
                                                    <i class="fas fa-star"></i>
                                                    <i class="fas fa-star"></i>
                                                    <i class="fas fa-star"></i>
                                                    <i class="far fa-star"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <p class="review-text">"Great headphones overall. Comfortable to wear for long periods. The only minor issue is the touch controls can be a bit sensitive."</p>
                                        <span class="review-date">1 week ago</span>
                                    </div>
                                </div>
                            </div>

                            <div id="specifications" class="tab-pane">
                                <div class="specs-table">
                                    <div class="spec-row">
                                        <span class="spec-label">Driver Size:</span>
                                        <span class="spec-value">40mm</span>
                                    </div>
                                    <div class="spec-row">
                                        <span class="spec-label">Frequency Response:</span>
                                        <span class="spec-value">20Hz - 20kHz</span>
                                    </div>
                                    <div class="spec-row">
                                        <span class="spec-label">Battery Life:</span>
                                        <span class="spec-value">30 hours</span>
                                    </div>
                                    <div class="spec-row">
                                        <span class="spec-label">Charging Time:</span>
                                        <span class="spec-value">2 hours</span>
                                    </div>
                                    <div class="spec-row">
                                        <span class="spec-label">Weight:</span>
                                        <span class="spec-value">280g</span>
                                    </div>
                                    <div class="spec-row">
                                        <span class="spec-label">Connectivity:</span>
                                        <span class="spec-value">Bluetooth 5.0</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search Results Page -->
            <div id="search-page" class="page">
                <div class="container">
                    <div class="search-header">
                        <h2 id="search-results-title">Search Results</h2>
                        <div class="search-filters">
                            <div class="filter-dropdown">
                                <select id="sort-filter">
                                    <option value="relevance">Sort by Relevance</option>
                                    <option value="price-low">Price: Low to High</option>
                                    <option value="price-high">Price: High to Low</option>
                                    <option value="rating">Rating</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="recent-searches" id="recent-searches">
                        <h4>Recent Searches</h4>
                        <div class="recent-search-tags">
                            <span class="search-tag">wireless headphones</span>
                            <span class="search-tag">gaming audio</span>
                            <span class="search-tag">noise cancelling</span>
                        </div>
                    </div>

                    <div class="search-results-grid" id="search-results-grid">
                        <!-- Search results will be populated here -->
                    </div>
                </div>
            </div>

            <!-- Shopping Cart Page -->
            <div id="cart-page" class="page">
                <div class="container">
                    <h2 class="page-title">My Cart</h2>
                    <div class="cart-container">
                        <div class="cart-items">
                            <div class="cart-item">
                                <div class="item-image">
                                    <img src="https://images.unsplash.com/photo-1546435770-a3e426bf472b?w=100&h=100&fit=crop" alt="AirBeats Pro">
                                </div>
                                <div class="item-details">
                                    <h4>AirBeats Pro</h4>
                                    <p>Color: Black</p>
                                    <div class="item-price">$299</div>
                                </div>
                                <div class="quantity-controls">
                                    <button class="qty-btn minus">-</button>
                                    <span class="quantity">1</span>
                                    <button class="qty-btn plus">+</button>
                                </div>
                                <button class="remove-item">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>

                            <div class="cart-item">
                                <div class="item-image">
                                    <img src="https://images.unsplash.com/photo-1583394838336-acd977736f90?w=100&h=100&fit=crop" alt="Wireless Buds">
                                </div>
                                <div class="item-details">
                                    <h4>Wireless Buds</h4>
                                    <p>Color: White</p>
                                    <div class="item-price">$149</div>
                                </div>
                                <div class="quantity-controls">
                                    <button class="qty-btn minus">-</button>
                                    <span class="quantity">2</span>
                                    <button class="qty-btn plus">+</button>
                                </div>
                                <button class="remove-item">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>

                        <div class="cart-summary">
                            <div class="pricing-details">
                                <h3>Pricing Details</h3>
                                <div class="price-row">
                                    <span>Subtotal:</span>
                                    <span>$0.00</span>
                                </div>
                                <div class="price-row">
                                    <span>Delivery Fee:</span>
                                    <span class="delivery-fee">$0.00</span>
                                </div>
                                <div class="price-row">
                                    <span>Tax (8%):</span>
                                    <span>$0.00</span>
                                </div>
                                <div class="price-row total">
                                    <span>Total Payment:</span>
                                    <span>$0.00</span>
                                </div>
                                <button class="checkout-btn">Proceed to Checkout</button>
                            </div>

                            <div class="promo-code">
                                <h4>Have a promo code?</h4>
                                <div class="promo-input-group">
                                    <input type="text" placeholder="Enter code">
                                    <button>Apply</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- User Profile Page -->
            <div id="profile-page" class="page">
                <div class="container">
                    <div class="profile-header">
                        <div class="profile-avatar">
                            <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=100&h=100&fit=crop&crop=face" alt="Profile">
                        </div>
                        <div class="profile-info">
                            <h2 id="profile-name">John Doe</h2>
                            <p>john.doe@email.com</p>
                            <button class="edit-profile-btn">
                                <i class="fas fa-edit"></i>
                            </button>
                        </div>
                    </div>

                    <div class="profile-menu">
                        <div class="menu-section">
                            <h3>Account</h3>
                            <div class="menu-item" data-action="orders">
                                <i class="fas fa-box"></i>
                                <span>My Orders</span>
                                <i class="fas fa-chevron-right"></i>
                            </div>
                            <div class="menu-item" data-action="addresses">
                                <i class="fas fa-map-marker-alt"></i>
                                <span>My Addresses</span>
                                <i class="fas fa-chevron-right"></i>
                            </div>
                            <div class="menu-item" data-action="payments">
                                <i class="fas fa-credit-card"></i>
                                <span>Payment Methods</span>
                                <i class="fas fa-chevron-right"></i>
                            </div>
                        </div>

                        <div class="menu-section">
                            <h3>Offers & Benefits</h3>
                            <div class="menu-item" data-action="vouchers">
                                <i class="fas fa-gift"></i>
                                <span>Gift Vouchers</span>
                                <i class="fas fa-chevron-right"></i>
                            </div>
                            <div class="menu-item" data-action="notifications">
                                <i class="fas fa-bell"></i>
                                <span>Notifications</span>
                                <i class="fas fa-chevron-right"></i>
                            </div>
                        </div>

                        <div class="menu-section">
                            <h3>Connect</h3>
                            <div class="menu-item" data-action="social">
                                <i class="fas fa-share-alt"></i>
                                <span>Social Media</span>
                                <i class="fas fa-chevron-right"></i>
                            </div>
                        </div>

                        <div class="menu-section">
                            <div class="menu-item logout" data-action="logout">
                                <i class="fas fa-sign-out-alt"></i>
                                <span>Log Out</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Saved Items / Wishlist -->
            <div id="wishlist-page" class="page">
                <div class="container">
                    <h2 class="page-title">Saved Items</h2>
                    <div class="wishlist-grid">
                        <div class="product-card saved">
                            <div class="product-image">
                                <img src="https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=300&h=300&fit=crop" alt="Studio Beats">
                                <button class="save-btn saved">
                                    <i class="fas fa-heart"></i>
                                </button>
                            </div>
                            <div class="product-info">
                                <h4 class="product-name">Studio Beats</h4>
                                <div class="product-rating">
                                    <div class="stars">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="far fa-star"></i>
                                    </div>
                                    <span class="rating-value">4.2</span>
                                </div>
                                <div class="product-price">$199</div>
                                <button class="add-to-cart-btn">Add to Cart</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Bottom Navigation -->
        <nav class="bottom-nav">
            <div class="nav-item active" data-page="home">
                <i class="fas fa-home"></i>
                <span>Home</span>
            </div>
            <div class="nav-item" data-page="search">
                <i class="fas fa-search"></i>
                <span>Search</span>
            </div>
            <div class="nav-item" data-page="cart">
                <i class="fas fa-shopping-cart"></i>
                <span>Cart</span>
            </div>
            <div class="nav-item" data-page="wishlist">
                <i class="fas fa-heart"></i>
                <span>Saved</span>
            </div>
            <div class="nav-item" data-page="profile">
                <i class="fas fa-user"></i>
                <span>Profile</span>
            </div>
        </nav>
    </div>

    <!-- Filter Modal -->
    <div id="filter-modal" class="modal">
        <div class="modal-content filter-modal-content">
            <div class="filter-header">
                <h3>Filter Products</h3>
                <span class="close">&times;</span>
            </div>
            <div class="filter-options">
                <div class="filter-group">
                    <h4>Price Range</h4>
                    <div class="price-range">
                        <input type="range" min="0" max="500" value="250" class="price-slider">
                        <div class="price-labels">
                            <span>$0</span>
                            <span>$500</span>
                        </div>
                    </div>
                </div>
                <div class="filter-group">
                    <h4>Category</h4>
                    <div class="checkbox-group">
                        <label><input type="checkbox" checked> Wireless</label>
                        <label><input type="checkbox"> Over-Ear</label>
                        <label><input type="checkbox"> Earbuds</label>
                        <label><input type="checkbox"> Gaming</label>
                    </div>
                </div>
                <div class="filter-group">
                    <h4>Rating</h4>
                    <div class="checkbox-group">
                        <label><input type="checkbox"> 4+ Stars</label>
                        <label><input type="checkbox"> 3+ Stars</label>
                        <label><input type="checkbox"> 2+ Stars</label>
                    </div>
                </div>
            </div>
            <div class="filter-actions">
                <button class="btn-secondary">Clear All</button>
                <button class="btn-primary">Apply Filters</button>
            </div>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>