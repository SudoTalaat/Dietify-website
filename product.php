<?php
require_once __DIR__ . '/init.php';

$id = $_GET['id'] ?? 0;
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ? AND status = 'active'");
$stmt->bind_param("i", $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    header('Location: shop.php');
    exit();
}

// Fetch Reviews
$reviewStmt = $conn->prepare("SELECT r.*, u.username FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.product_id = ? ORDER BY r.created_at DESC");
$reviewStmt->bind_param("i", $id);
$reviewStmt->execute();
$reviews = $reviewStmt->get_result();

// Calculate Average Rating
$avgRatingStmt = $conn->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as count FROM reviews WHERE product_id = ?");
$avgRatingStmt->bind_param("i", $id);
$avgRatingStmt->execute();
$ratingData = $avgRatingStmt->get_result()->fetch_assoc();
$avgRating = round($ratingData['avg_rating'], 1) ?: 0;
$reviewCount = $ratingData['count'] ?: 0;

// Determine if User can run a review
$is_eligible_to_review = false;
$remaining_reviews = 0;
if (isLoggedIn()) {
    $user_id = $_SESSION['user_id'];

    // 1. How many of this product did the user buy in completed/paid orders?
    $purchase_stmt = $conn->prepare("
        SELECT SUM(oi.quantity) as total_bought
        FROM order_items oi
        JOIN orders o ON oi.order_id = o.id
        WHERE o.user_id = ? AND oi.product_id = ? AND o.status = 'paid'
    ");
    $purchase_stmt->bind_param("ii", $user_id, $id);
    $purchase_stmt->execute();
    $purchase_data = $purchase_stmt->get_result()->fetch_assoc();
    $total_bought = $purchase_data['total_bought'] ?? 0;

    // 2. How many reviews has the user already written for this product?
    $written_stmt = $conn->prepare("SELECT COUNT(*) as written_count FROM reviews WHERE user_id = ? AND product_id = ?");
    $written_stmt->bind_param("ii", $user_id, $id);
    $written_stmt->execute();
    $written_data = $written_stmt->get_result()->fetch_assoc();
    $written_count = $written_data['written_count'] ?? 0;

    if ($total_bought > $written_count) {
        $is_eligible_to_review = true;
        $remaining_reviews = $total_bought - $written_count;
    }
}

include __DIR__ . '/header.php';
?>

<div class="dashboard-container">
    <div
        style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 40px; align-items: start;">
        <div class="product-image">
            <img src="<?php echo $product['image_path'] ?: 'assets/images/placeholder-300x300.png'; ?>"
                alt="<?php echo htmlspecialchars($product['name']); ?>"
                style="width: 100%; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
        </div>

        <div class="product-details">
            <nav style="margin-bottom: 20px; font-size: 0.9rem; color: #666;">
                <a href="shop.php" style="color: #666; text-decoration: none;">Shop</a> /
                <span style="color: #333;">
                    <?php echo htmlspecialchars($product['name']); ?>
                </span>
            </nav>

            <h1 style="font-size: 2.5rem; color: #333; margin-bottom: 10px;">
                <?php echo htmlspecialchars($product['name']); ?>
            </h1>

            <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 20px;">
                <div style="color: #f1c40f;">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <i class="<?php echo $i <= $avgRating ? 'fas' : 'far'; ?> fa-star"></i>
                    <?php endfor; ?>
                </div>
                <span style="color: #666; font-size: 0.9rem;">(
                    <?php echo $reviewCount; ?> reviews)
                </span>
                <span
                    style="background: #f8f9fa; padding: 4px 10px; border-radius: 20px; font-size: 0.8rem; color: #ff6b35; font-weight: 700; text-transform: uppercase;">
                    <?php echo $product['type']; ?>
                </span>
            </div>

            <div style="font-size: 2rem; font-weight: 700; color: #333; margin-bottom: 25px;">
                <?php echo CURRENCY_SYMBOL; ?><?php echo number_format($product['price'], 2); ?>
            </div>

            <p style="color: #666; line-height: 1.8; margin-bottom: 30px; font-size: 1.1rem;">
                <?php echo nl2br(htmlspecialchars($product['description'])); ?>
            </p>

            <form action="actions/cart_action.php" method="POST"
                style="display: flex; gap: 15px; align-items: center; margin-bottom: 40px;">
                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                <input type="hidden" name="action" value="add">
                <div
                    style="display: flex; align-items: center; border: 2px solid #e1e5e9; border-radius: 12px; overflow: hidden;">
                    <button type="button" onclick="const q = this.nextElementSibling; if(q.value > 1) q.value--;"
                        style="padding: 10px 15px; border: none; background: white; cursor: pointer;"><i
                            class="fas fa-minus"></i></button>
                    <input type="number" name="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>"
                        style="width: 50px; text-align: center; border: none; font-weight: 700; font-size: 1rem; padding: 10px 0;">
                    <button type="button"
                        onclick="const q = this.previousElementSibling; if(q.value < <?php echo $product['stock']; ?>) q.value++;"
                        style="padding: 10px 15px; border: none; background: white; cursor: pointer;"><i
                            class="fas fa-plus"></i></button>
                </div>
                <button type="submit" class="login-btn" style="flex: 1; margin-bottom: 0;">
                    <i class="fas fa-shopping-basket"></i> Add to Cart
                </button>
            </form>

            <div style="border-top: 1px solid #eee; padding-top: 25px;">
                <div style="display: flex; gap: 20px; color: #666; font-size: 0.9rem;">
                    <span><i class="fas fa-check-circle" style="color: #27ae60;"></i> In Stock:
                        <?php echo $product['stock']; ?>
                    </span>
                    <span><i class="fas fa-shipping-fast"></i> Delivery in 30 mins</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Reviews Section -->
    <div style="margin-top: 80px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px;">
            <h2>Customer Reviews</h2>
            <?php if (isLoggedIn()): ?>
                <?php if ($is_eligible_to_review): ?>
                    <button onclick="document.getElementById('reviewModel').style.display='block'" class="btn-secondary"
                        style="border-color: #ff6b35; color: #ff6b35;">Write a Review (<?php echo $remaining_reviews; ?>
                        left)</button>
                <?php else: ?>
                    <!-- Disabled button with tooltip explaining why -->
                    <button class="btn-secondary" style="border-color: #ccc; color: #999; cursor: not-allowed;" disabled
                        title="You must purchase this product to leave a review, or you have already reached your review limit.">Write
                        a Review</button>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <?php if ($reviews->num_rows > 0): ?>
            <div style="display: grid; gap: 20px;">
                <?php while ($review = $reviews->fetch_assoc()): ?>
                    <div
                        style="background: white; padding: 25px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                            <span style="font-weight: 600;">
                                <?php echo htmlspecialchars($review['username']); ?>
                            </span>
                            <span style="color: #999; font-size: 0.85rem;">
                                <?php echo date('M d, Y', strtotime($review['created_at'])); ?>
                            </span>
                        </div>
                        <div style="color: #f1c40f; margin-bottom: 10px; font-size: 0.8rem;">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="<?php echo $i <= $review['rating'] ? 'fas' : 'far'; ?> fa-star"></i>
                            <?php endfor; ?>
                        </div>
                        <p style="color: #666; line-height: 1.6;">
                            <?php echo nl2br(htmlspecialchars($review['comment'])); ?>
                        </p>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 40px; background: #f8f9fa; border-radius: 15px; color: #666;">
                No reviews yet. Be the first to review this product!
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Simple Review Modal -->
<div id="reviewModel" class="modal">
    <div class="modal-content" style="max-width: 500px; padding: 40px;">
        <h3 style="margin-bottom: 20px;">Write a Review</h3>
        <form action="actions/review_action.php" method="POST">
            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
            <div class="form-group" style="text-align: left;">
                <label>Rating</label>
                <select name="rating" required
                    style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #ddd;">
                    <option value="5">5 - Excellent</option>
                    <option value="4">4 - Very Good</option>
                    <option value="3">3 - Good</option>
                    <option value="2">2 - Fair</option>
                    <option value="1">1 - Poor</option>
                </select>
            </div>
            <div class="form-group" style="text-align: left;">
                <label>Your Comment</label>
                <textarea name="comment" required
                    style="width: 100%; height: 100px; padding: 12px; border-radius: 8px; border: 1px solid #ddd;"></textarea>
            </div>
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" class="login-btn" style="flex: 1; margin: 0;">Post Review</button>
                <button type="button" onclick="document.getElementById('reviewModel').style.display='none'"
                    class="btn-secondary" style="flex: 1; border-color: #ddd;">Cancel</button>
            </div>
        </form>
    </div>
</div>

</main>
</body>

</html>