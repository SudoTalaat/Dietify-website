<?php
// Premium Healthy Food UI - With Reviews and Features

$products = [
    ["name" => "Salad Bowl", "price" => "120 EGP", "image_url" => "https://images.unsplash.com/photo-1512621776951-a57141f2eefd?q=80&w=400&auto=format&fit=crop"],
    ["name" => "Grilled Chicken", "price" => "180 EGP", "image_url" => "https://images.unsplash.com/photo-1532550907401-a500c9a57435?q=80&w=400&auto=format&fit=crop"],
    ["name" => "Avocado Toast", "price" => "95 EGP", "image_url" => "https://images.unsplash.com/photo-1482049016688-2d3e1b311543?q=80&w=400&auto=format&fit=crop"],
    ["name" => "Fruit Smoothie", "price" => "70 EGP", "image_url" => "https://images.unsplash.com/photo-1502741224143-90386d7f8c82?q=80&w=400&auto=format&fit=crop"]
];

$reviews = [
    ["name" => "Alex Johnson", "text" => "The best healthy meals I've ever had in Egypt. Delivery is super fast!", "img" => "https://i.pravatar.cc/100?u=1"],
    ["name" => "Sarah Emad", "text" => "Fresh ingredients and amazing taste. The Avocado Toast is a must-try!", "img" => "https://i.pravatar.cc/100?u=2"],
    ["name" => "Omar Khaled", "text" => "Finally, a place that balances health and flavor perfectly. 5 stars!", "img" => "https://i.pravatar.cc/100?u=3"]
];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Healthy Food App - Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: #f8f9fb;
            color: #333;
        }

        /* Updated Navbar to match image */
        .navbar {
            background: white;
            padding: 10px 60px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .logo {
            color: #e67e22;
            font-size: 24px;
            font-weight: bold;
            text-decoration: underline;
        }

        .nav-links {
            display: flex;
            align-items: center;
        }

        .nav-links a {
            margin: 0 15px;
            text-decoration: none;
            color: #333;
            font-weight: 500;
            font-size: 16px;
        }

        .cart-icon {
            position: relative;
            margin-right: 20px;
            font-size: 20px;
        }

        .cart-badge {
            position: absolute;
            top: -8px;
            right: -10px;
            background: #e67e22;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 10px;
        }

        /* Banner from Image */
        .banner {
            height: 120px;
            background: #e67e22;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 28px;
            font-weight: bold;
        }

        .container {
            padding: 40px;
            max-width: 1200px;
            margin: auto;
        }

        h2 {
            text-align: center;
            margin-bottom: 40px;
            font-size: 2rem;
            color: #2c3e50;
        }

        /* Features Section */
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin-bottom: 60px;
        }

        .feature-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            border-top: 5px solid #e67e22;
        }

        .feature-card i {
            font-size: 40px;
            display: block;
            margin-bottom: 15px;
        }

        /* Products Grid */
        .products {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 60px;
        }

        .card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
            transition: 0.3s;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .product-image {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }

        .card-details {
            padding: 20px;
        }

        .price {
            color: #27ae60;
            font-weight: bold;
            margin-bottom: 15px;
        }

        .btn {
            background: #e67e22;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            font-weight: 500;
        }

        /* Testimonials Section */
        .reviews {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }

        .review-card {
            background: #fff;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .review-user {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .review-user img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin-right: 15px;
        }

        .review-user h4 {
            margin: 0;
            color: #e67e22;
        }

        .review-text {
            font-style: italic;
            color: #555;
            line-height: 1.6;
        }
    </style>
</head>

<body>

    <div class="navbar">
        <div class="logo">Healthy Food App</div>
        <div class="nav-links">
            <a href="#">Home</a>
            <a href="#">Shop</a>
            <div class="cart-icon">
                <i class="fas fa-shopping-cart"></i>
                <span class="cart-badge">0</span>
            </div>
            <a href="#">Profile</a>
            <a href="#">Chatbot</a>
            <a href="#">Admin</a>
            <a href="#">Logout</a>
        </div>
    </div>

    <div class="banner">Premium Healthy Experience</div>

    <div class="container">

        <h2>Why Choose Us?</h2>
        <div class="features">
            <div class="feature-card">
                <i>🥗</i>
                <h3>100% Organic</h3>
                <p>We use only fresh and organic ingredients directly from local farms.</p>
            </div>
            <div class="feature-card">
                <i>⚡</i>
                <h3>Fast Delivery</h3>
                <p>Your healthy meal will be at your door in less than 30 minutes.</p>
            </div>
            <div class="feature-card">
                <i>👨‍🍳</i>
                <h3>Expert Chefs</h3>
                <p>Crafted by nutritionists and professional chefs for the perfect balance.</p>
            </div>
        </div>

        <h2>Our Popular Meals</h2>
        <div class="products">
            <?php foreach ($products as $product): ?>
                <div class="card">
                    <img src="<?php echo $product['image_url']; ?>" class="product-image">
                    <div class="card-details">
                        <h3><?php echo $product['name']; ?></h3>
                        <div class="price"><?php echo $product['price']; ?></div>
                        <button class="btn">Add to Cart</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <h2>What Our Customers Say</h2>
        <div class="reviews">
            <?php foreach ($reviews as $review): ?>
                <div class="review-card">
                    <div class="review-user">
                        <img src="<?php echo $review['img']; ?>">
                        <h4><?php echo $review['name']; ?></h4>
                    </div>
                    <p class="review-text">"<?php echo $review['text']; ?>"</p>
                </div>
            <?php endforeach; ?>
        </div>

    </div>

</body>

</html>