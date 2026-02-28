<?php
require_once __DIR__ . '/init.php';

$type = $_GET['type'] ?? '';
$query = "SELECT * FROM products WHERE status = 'active'";
if ($type) {
    $query .= " AND type = '" . $conn->real_escape_string($type) . "'";
}
$query .= " ORDER BY created_at DESC";
$result = $conn->query($query);

include __DIR__ . '/header.php';
?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <h1>The Healthy Shop</h1>
        <p>Browse our selection of nutritious food and drinks.</p>

        <div style="margin-top: 20px;">
            <a href="shop.php" class="<?php echo !$type ? 'btn-login' : 'btn-secondary'; ?>"
                style="padding: 8px 15px; text-decoration: none; margin-right: 10px; border-radius: 20px; border-color: #ff6b35; <?php echo !$type ? '' : 'color: #ff6b35;'; ?>">All</a>
            <a href="shop.php?type=food" class="<?php echo $type === 'food' ? 'btn-login' : 'btn-secondary'; ?>"
                style="padding: 8px 15px; text-decoration: none; margin-right: 10px; border-radius: 20px; border-color: #ff6b35; <?php echo $type === 'food' ? '' : 'color: #ff6b35;'; ?>">Food</a>
            <a href="shop.php?type=drink" class="<?php echo $type === 'drink' ? 'btn-login' : 'btn-secondary'; ?>"
                style="padding: 8px 15px; text-decoration: none; border-radius: 20px; border-color: #ff6b35; <?php echo $type === 'drink' ? '' : 'color: #ff6b35;'; ?>">Drinks</a>
        </div>
    </div>

    <div class="features-grid">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($product = $result->fetch_assoc()): ?>
                <div class="feature-card" style="text-align: left;">
                    <img src="<?php echo $product['image_path'] ?: 'assets/images/placeholder-300x300.png'; ?>"
                        alt="<?php echo htmlspecialchars($product['name']); ?>"
                        style="width: 100%; height: 200px; object-fit: cover; border-radius: 8px; margin-bottom: 20px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 0.8rem; text-transform: uppercase; color: #ff6b35; font-weight: 700;">
                            <?php echo $product['type']; ?>
                        </span>
                        <span style="font-size: 0.9rem; color: #666;"><i class="fas fa-box"></i>
                            <?php echo $product['stock']; ?> left
                        </span>
                    </div>
                    <h3 style="margin: 10px 0;">
                        <?php echo htmlspecialchars($product['name']); ?>
                    </h3>
                    <div style="font-weight: 700; color: #333; font-size: 1.25rem;">$
                        <?php echo number_format($product['price'], 2); ?>
                    </div>
                    <a href="product.php?id=<?php echo $product['id']; ?>" class="login-btn"
                        style="display: block; text-align: center; margin-top: 15px; text-decoration: none;">View Details</a>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="text-align: center; grid-column: 1/-1;">No products found matching your criteria.</p>
        <?php endif; ?>
    </div>
</div>

</main>
</body>

</html>