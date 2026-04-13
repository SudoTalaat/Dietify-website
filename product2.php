<?php
// بيانات المنتجات (يمكنك ربطها بقاعدة البيانات لاحقاً)
$products = [
    [
        "id" => 1,
        "name" => "Salad Bowl",
        "price" => "120 EGP",
        "category" => "Vegetarian",
        "image" => "https://images.unsplash.com/photo-1512621776951-a57141f2eefd?q=80&w=400&auto=format&fit=crop"
    ],
    [
        "id" => 2,
        "name" => "Grilled Chicken",
        "price" => "180 EGP",
        "category" => "Protein",
        "image" => "https://images.unsplash.com/photo-1532550907401-a500c9a57435?q=80&w=400&auto=format&fit=crop"
    ],
    [
        "id" => 3,
        "name" => "Avocado Toast",
        "price" => "95 EGP",
        "category" => "Breakfast",
        "image" => "https://images.unsplash.com/photo-1482049016688-2d3e1b311543?q=80&w=400&auto=format&fit=crop"
    ],
    [
        "id" => 4,
        "name" => "Fruit Smoothie",
        "price" => "70 EGP",
        "category" => "Drinks",
        "image" => "https://images.unsplash.com/photo-1502741224143-90386d7f8c82?q=80&w=400&auto=format&fit=crop"
    ],
    [
        "id" => 5,
        "name" => "Salmon Steak",
        "price" => "250 EGP",
        "category" => "Seafood",
        "image" => "https://images.unsplash.com/photo-1467003909585-2f8a72700288?q=80&w=400&auto=format&fit=crop"
    ],
    [
        "id" => 6,
        "name" => "Quinoa Salad",
        "price" => "110 EGP",
        "category" => "Vegetarian",
        "image" => "https://images.unsplash.com/photo-1546069901-ba9599a7e63c?q=80&w=400&auto=format&fit=crop"
    ]
];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - Healthy Food App</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #e67e22;
            --secondary-color: #27ae60;
            --bg-color: #f9f9f9;
            --text-dark: #2c3e50;
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg-color);
        }

        /* Navbar - حسب صورتك */
        .navbar {
            background: white;
            padding: 15px 50px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .logo {
            color: var(--primary-color);
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
            transition: 0.3s;
        }

        .nav-links a:hover {
            color: var(--primary-color);
        }

        .cart-container {
            position: relative;
            margin: 0 15px;
            cursor: pointer;
        }

        .cart-badge {
            position: absolute;
            top: -8px;
            right: -10px;
            background: var(--primary-color);
            color: white;
            font-size: 11px;
            padding: 2px 6px;
            border-radius: 50%;
        }

        /* Page Header */
        .header {
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('https://images.unsplash.com/photo-1498837167922-ddd27525d352?q=80&w=1200&auto=format&fit=crop');
            background-size: cover;
            background-position: center;
            height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-align: center;
        }

        /* Products Section */
        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .section-title {
            text-align: left;
            margin-bottom: 30px;
            border-bottom: 2px solid var(--primary-color);
            display: inline-block;
            padding-bottom: 5px;
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 30px;
        }

        .product-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease;
            position: relative;
        }

        .product-card:hover {
            transform: translateY(-10px);
        }

        .product-image-wrapper {
            position: relative;
            height: 200px;
            overflow: hidden;
        }

        .product-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .category-tag {
            position: absolute;
            top: 10px;
            left: 10px;
            background: rgba(255, 255, 255, 0.9);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            color: var(--primary-color);
        }

        .product-info {
            padding: 20px;
        }

        .product-name {
            font-size: 18px;
            color: var(--text-dark);
            margin: 0 0 10px 0;
        }

        .product-price {
            font-size: 20px;
            color: var(--secondary-color);
            font-weight: bold;
            margin-bottom: 15px;
        }

        .add-to-cart-btn {
            background: var(--primary-color);
            color: white;
            border: none;
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: background 0.3s;
        }

        .add-to-cart-btn:hover {
            background: #d35400;
        }

        /* Footer Simple */
        footer {
            text-align: center;
            padding: 40px;
            background: #2c3e50;
            color: white;
            margin-top: 50px;
        }
    </style>
</head>

<body>

    <nav class="navbar">
        <div class="logo">Healthy Food App</div>
        <div class="nav-links">
            <a href="index.php">Home</a>
            <a href="products.php" style="color: var(--primary-color);">Shop</a>
            <div class="cart-container">
                <i class="fas fa-shopping-cart"></i>
                <span class="cart-badge">0</span>
            </div>
            <a href="#">Profile</a>
            <a href="#">Chatbot</a>
            <a href="#">Admin</a>
            <a href="#">Logout</a>
        </div>
    </nav>

    <header class="header">
        <div>
            <h1>Our Healthy Menu</h1>
            <p>Fresh, Organic, and Delivered to Your Door</p>
        </div>
    </header>

    <div class="container">
        <h2 class="section-title">Explore All Meals</h2>

        <div class="products-grid">
            <?php foreach ($products as $item): ?>
                <div class="product-card">
                    <div class="product-image-wrapper">
                        <span class="category-tag"><?php echo $item['category']; ?></span>
                        <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>" class="product-image">
                    </div>
                    <div class="product-info">
                        <h3 class="product-name"><?php echo $item['name']; ?></h3>
                        <div class="product-price"><?php echo $item['price']; ?></div>
                        <button class="add-to-cart-btn">
                            <i class="fas fa-cart-plus"></i> Add to Cart
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <footer>
        <p>&copy; 2024 Healthy Food App. All rights reserved.</p>
    </footer>

</body>

</html>