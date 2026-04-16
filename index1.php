<?php
require_once __DIR__ . '/init.php';
include __DIR__ . '/header.php';
// it show frist 3 items the are active and in stock
// Fetch featured products (active and in stock)

$featured = $conn->query("SELECT * FROM products WHERE status = 'active' AND stock > 0 ORDER BY created_at DESC LIMIT 3");

// Fetch 3 top-rated reviews (5 stars)
$reviews = $conn->query("
    SELECT r.*, u.username, u.avatar, p.name as product_name 
    FROM reviews r 
    JOIN users u ON r.user_id = u.id 
    JOIN products p ON r.product_id = p.id
    WHERE r.rating = 5 
    ORDER BY r.created_at DESC 
    LIMIT 3
");
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


<!-- Customer Reviews Section -->
<section class="features" style="background: #f8f9fb; border-top: 1px solid #eee; border-bottom: 1px solid #eee;">
    <div class="container">
        <h2 style="margin-bottom: 40px;">What Our Customers Say</h2>
        <div class="features-grid">
            <?php if ($reviews && $reviews->num_rows > 0): ?>
                <?php while ($review = $reviews->fetch_assoc()): ?>
                    <div class="feature-card" style="text-align: left; padding: 30px;">
                        <div style="color: #f1c40f; margin-bottom: 15px;">
                            <?php for ($i = 0; $i < 5; $i++): ?>
                                <i class="fas fa-star"></i>
                            <?php endfor; ?>
                        </div>
                        <p style="font-style: italic; color: #555; line-height: 1.6; margin-bottom: 20px;">
                            "<?php echo htmlspecialchars($review['comment']); ?>"
                        </p>
                        <div
                            style="display: flex; align-items: center; gap: 12px; border-top: 1px solid #eee; padding-top: 15px;">
                            <?php if (!empty($review['avatar']) && $review['avatar'] !== 'assets/images/default_avatar.png'): ?>
                                <img src="<?php echo htmlspecialchars($review['avatar']); ?>" alt="Avatar"
                                    style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                            <?php else: ?>
                                <div
                                    style="width: 40px; height: 40px; background: linear-gradient(135deg, #ff6b35, #ff9f1c); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 0.9rem; flex-shrink: 0;">
                                    <?php echo strtoupper(substr($review['username'], 0, 1)); ?>
                                </div>
                            <?php endif; ?>
                            <div>
                                <h4 style="margin: 0; font-size: 1rem; color: #333;">
                                    <?php echo htmlspecialchars($review['username']); ?></h4>
                                <small style="color: #888;">on <?php echo htmlspecialchars($review['product_name']); ?></small>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div
                    style="grid-column: 1/-1; text-align: center; padding: 40px; background: white; border-radius: 15px; color: #888;">
                    <i class="fas fa-quote-left"
                        style="font-size: 2rem; color: #eee; margin-bottom: 15px; display: block;"></i>
                    No 5-star reviews yet. Be the first to leave a review!
                </div>
            <?php endif; ?>
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
                        <img src="<?php echo getImageUrl($product['image_path']) ?: 'assets/images/placeholder-300x300.png'; ?>"
                            alt="<?php echo htmlspecialchars($product['name']); ?>"
                            style="width: 100%; height: 200px; object-fit: cover; border-radius: 8px; margin-bottom: 20px;">
                        <h3>
                            <?php echo htmlspecialchars($product['name']); ?>
                        </h3>
                        <p>
                            <?php echo substr(htmlspecialchars($product['description']), 0, 80) . '...'; ?>
                        </p>
                        <div style="margin-top: 15px; font-weight: 700; color: #ff6b35; font-size: 1.25rem;">
                            <?php echo CURRENCY_SYMBOL; ?>         <?php echo number_format($product['price'], 2); ?>
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