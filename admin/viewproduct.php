<?php
require_once __DIR__ . '/../init.php';

if (!isLoggedIn() || !isAdmin()) {
    header("Location: /app/login.php");
    exit();
}

// Handle delete action
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM `products` WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: viewproduct.php");
    exit;
}

$select = "SELECT id, name, price, image_path, description, type, stock FROM `products`";
$run_select = $conn->query($select);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Products</title>
    <link rel="stylesheet" href="assets/css/view_products.css">
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
            <li><a href="#" class="active">View Products</a></li>
            <li><a href="Inventory.php">Inventory Management</a></li>
            <li><a href="order_management.php">Order Management</a></li>
            <li><a href="reviews_management.php">Reviews</a></li>
            <li><a href="../actions/logout.php">Logout</a></li>
        </ul>
    </nav>
    <main>
        <h2>Product List</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Image</th>
                    <th>Description</th>
                    <th>Type</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($value = $run_select->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $value['id']; ?></td>
                        <td><?php echo htmlspecialchars($value['name']); ?></td>
                        <td><?php echo CURRENCY_SYMBOL; ?><?php echo number_format($value['price'], 2); ?></td>
                        <td><?php echo $value['stock']; ?></td>
                        <td><img style="width:40px; height:40px; object-fit: cover;"
                                src="../<?php echo $value['image_path'] ?: 'assets/images/placeholder-300x300.png'; ?>"
                                alt="Product Image"></td>
                        <td><?php echo htmlspecialchars(substr($value['description'], 0, 50)) . '...'; ?></td>
                        <td><?php echo ucfirst($value['type']); ?></td>
                        <td>
                            <a href="addproduct.php?edit=<?php echo $value['id']; ?>" class="edit-link">Edit</a> |
                            <a href="?delete=<?php echo $value['id']; ?>" style="color: #dc3545;"
                                onclick="return confirm('Are you sure you want to delete this product?');">
                                Delete
                            </a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </main>
</body>

</html>