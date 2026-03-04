<?php
require_once __DIR__ . '/../init.php';

if (!isLoggedIn() || !isAdmin()) {
    header("Location: /app/login.php");
    exit();
}

// Handle delete action
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM `reviews` WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        header("Location: reviews_management.php?msg=deleted");
    } else {
        header("Location: reviews_management.php?msg=error");
    }
    exit;
}

// Fetch all reviews with user and product details
$query = "SELECT r.id, r.rating, r.comment, r.created_at, u.username, p.name as product_name 
          FROM reviews r 
          JOIN users u ON r.user_id = u.id 
          JOIN products p ON r.product_id = p.id 
          ORDER BY r.created_at DESC";
$result = $conn->query($query);
$reviews = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Review Management - Healthy Food</title>
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <style>
        .review-card {
            background: white;
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .review-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }

        .rating {
            color: #f1c40f;
            font-weight: bold;
        }

        .delete-btn {
            color: #e74c3c;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .delete-btn:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <nav>
        <ul>
            <li><a href="../shop.php"
                    style="background-color: #27ae60; color: white; margin-bottom: 20px; font-weight: bold;">← Back to
                    Shop</a></li>
            <li><a href="Dashboard.php">Dashboard</a></li>
            <li><a href="useradmin.php">Users</a></li>
            <li><a href="addproduct.php">Add Products</a></li>
            <li><a href="viewproduct.php">View Products</a></li>
            <li><a href="Inventory.php">Inventory Management</a></li>
            <li><a href="order_management.php">Order Management</a></li>
            <li><a href="reviews_management.php" class="active">Reviews</a></li>
            <li><a href="/app/logout.php">Logout</a></li>
        </ul>
    </nav>
    <main style="padding: 20px; margin-left: 270px; width: calc(100% - 270px);">
        <h1>💬 Product Reviews</h1>

        <?php if (isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
            <p style="color: #27ae60; background: #d4edda; padding: 10px; border-radius: 5px;">Review deleted successfully.
            </p>
        <?php endif; ?>

        <?php if (count($reviews) > 0): ?>
            <?php foreach ($reviews as $review): ?>
                <div class="review-card">
                    <div class="review-header">
                        <span>
                            <strong>
                                <?php echo htmlspecialchars($review['username']); ?>
                            </strong>
                            on <em>
                                <?php echo htmlspecialchars($review['product_name']); ?>
                            </em>
                        </span>
                        <span class="rating">
                            <?php echo str_repeat('★', $review['rating']) . str_repeat('☆', 5 - $review['rating']); ?>
                        </span>
                    </div>
                    <p style="margin: 10px 0;">
                        <?php echo htmlspecialchars($review['comment']); ?>
                    </p>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <small style="color: #888;">
                            <?php echo $review['created_at']; ?>
                        </small>
                        <a href="?delete=<?php echo $review['id']; ?>" class="delete-btn"
                            onclick="return confirm('Are you sure you want to delete this review?');">Delete</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No reviews found.</p>
        <?php endif; ?>
    </main>
</body>

</html>