<?php
require_once __DIR__ . '/init.php';
include __DIR__ . '/header.php';

// Fetch featured products (active and in stock)
$featured = $conn->query("SELECT * FROM products WHERE status = 'active' AND stock > 0 ORDER BY created_at DESC LIMIT 6");
?>

<section class="hero">
    <div class="hero-content">
        <h1>Fresh & Healthy Food Delivered to Your Door</h1>
        <p>Discover our curated selection of organic, nutritious meals and refreshing drinks.</p>
        <div class="hero-buttons">
            <a href="shop.php" class="btn-primary">Shop Now</a>
            <a href="#featured" class="btn-secondary">View Featured</a>
        </div>
    </div>
</section>

<section class="features" id="featured">
    <div class="container">
        <h2>Our Featured Products</h2>
        <div class="features-grid">
            <?php if ($featured->num_rows > 0): ?>
                <?php while ($product = $featured->fetch_assoc()): ?>
                    <div class="feature-card">
                        <img src="<?php echo $product['image_path'] ?: 'assets/images/placeholder-300x300.png'; ?>"
                            alt="<?php echo htmlspecialchars($product['name']); ?>"
                            style="width: 100%; height: 200px; object-fit: cover; border-radius: 8px; margin-bottom: 20px;">
                        <h3>
                            <?php echo htmlspecialchars($product['name']); ?>
                        </h3>
                        <p>
                            <?php echo substr(htmlspecialchars($product['description']), 0, 80) . '...'; ?>
                        </p>
                        <div style="margin-top: 15px; font-weight: 700; color: #ff6b35; font-size: 1.25rem;">$
                            <?php echo number_format($product['price'], 2); ?>
                        </div>
                        <a href="product.php?id=<?php echo $product['id']; ?>" class="btn-secondary"
                            style="display: block; margin-top: 15px; border-color: #ff6b35; color: #ff6b35;">View Details</a>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="text-align: center; grid-column: 1/-1;">No products found. Start by adding some in the admin
                    panel!</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="features" style="background: white;">
    <div class="container">
        <h2>Why Choose Us?</h2>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">🥗</div>
                <h3>100% Organic</h3>
                <p>We use only the freshest, locally sourced organic ingredients for all our meals.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🚀</div>
                <h3>Fast Delivery</h3>
                <p>Hot and fresh food delivered to your doorstep within 30 minutes of preparation.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">💳</div>
                <h3>Secure Payment</h3>
                <p>Safe and seamless payments integrated with Stripe for your peace of mind.</p>
            </div>
        </div>
    </div>
</section>

</main>
</body>

</html>