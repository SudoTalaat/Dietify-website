<?php
require_once __DIR__ . '/../init.php';

if (!isLoggedIn() || !isAdmin()) {
    header("Location: /app/login.php");
    exit();
}

// Handle quantity update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_quantity'])) {
    $id = intval($_POST['product_id']);
    $new_quantity = max(0, intval($_POST['new_quantity'])); // avoid negative quantity
    $update = "UPDATE `products` SET `stock` = ? WHERE `id` = ?";
    $stmt = $conn->prepare($update);
    $stmt->bind_param("ii", $new_quantity, $id);
    $stmt->execute();
    header("Location: Inventory.php");
    exit;
}

// Fetch products
$select = "SELECT id, name, price, image_path, description, type, stock FROM `products`";
$run_select = $conn->query($select);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Inventory Management</title>
    <link rel="stylesheet" href="assets/css/inventory.css">
    <style>
        input[type="number"] {
            width: 60px;
            padding: 4px;
        }

        .update-btn {
            padding: 4px 8px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }

        .update-btn:hover {
            background-color: #45a049;
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
            <li><a href="#" class="active">Inventory Management</a></li>
            <li><a href="order_management.php">Order Management</a></li>
            <li><a href="reviews_management.php">Reviews</a></li>
            <li><a href="/app/logout.php">Logout</a></li>
        </ul>
    </nav>
    <main>
        <h2>Inventory Management</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Image</th>
                    <th>Description</th>
                    <th>Category</th>
                    <th>Quantity</th>
                    <th>Edit Quantity</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($product = $run_select->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $product['id']; ?></td>
                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                        <td><?php echo $product['price']; ?></td>
                        <td><img src="../<?php echo $product['image_path'] ?: 'placeholder.png'; ?>" width="40" height="40">
                        </td>
                        <td><?php echo htmlspecialchars($product['description']); ?></td>
                        <td><?php echo ucfirst($product['type']); ?></td>
                        <td><?php echo $product['stock']; ?></td>
                        <td>
                            <form method="POST">
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                <input type="number" name="new_quantity" value="<?php echo $product['stock']; ?>" min="0">
                                <button type="submit" name="update_quantity" class="update-btn">Update</button>
                            </form>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </main>
</body>

</html>