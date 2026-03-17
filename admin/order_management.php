<?php
require_once __DIR__ . '/../init.php';

if (!isLoggedIn() || !isAdmin()) {
    header("Location: /app/login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Order Management - Healthy Food</title>
    <link rel="stylesheet" href="assets/css/ordermanagement.css">
</head>

<body>
    <nav>
        <ul>
            <li><a href="../shop.php"
                    style="background-color: #27ae60; color: white; margin-bottom: 20px; font-weight: bold;">← Back to
                    Shop</a></li>
            <li><a href="Dashboard.php">Dashboard</a></li>
            <li><a href="useradmin.php">Users</a></li>
            <li><a href="products.php">Products</a></li>
            <li><a href="Inventory.php">Inventory Management</a></li>
            <li><a href="#" class="active">Order Management</a></li>
            <li><a href="reviews_management.php">Reviews</a></li>
            <li><a href="/app/logout.php">Logout</a></li>
        </ul>
    </nav>
    <h1>🛒 Order Management</h1>

    <?php
    $sql = "SELECT 
                u.username,
                u.email,
                o.location_description,
                o.total_amount,
                o.status,
                o.created_at
            FROM orders o
            JOIN users u ON o.user_id = u.id
            ORDER BY o.created_at DESC";

    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<div class='order' style='border: 1px solid #ddd; padding: 15px; margin-bottom: 10px; border-radius: 8px;'>";
            echo "<h3>Order #" . ($row['username']) . "</h3>";
            echo "<p><strong>Customer:</strong> " . htmlspecialchars($row['username']) . " (" . htmlspecialchars($row['email']) . ")</p>";
            echo "<p><strong>Shipping Address:</strong> " . htmlspecialchars($row['location_description']) . "</p>";
            echo "<p><strong>Total:</strong> " . number_format($row['total_amount'], 2) . " $</p>";
            echo "<p><strong>Status:</strong> <span style='color: " . ($row['status'] === 'paid' ? '#27ae60' : '#e67e22') . "'>" . ucfirst($row['status']) . "</span></p>";
            echo "<p><strong>Date:</strong> {$row['created_at']}</p>";
            echo "</div>";
        }
    } else {
        echo "<p>No orders found.</p>";
    }
    ?>
</body>

</html>