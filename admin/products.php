<?php
require_once __DIR__ . '/../init.php';

if (!isLoggedIn() || !isAdmin()) {
    header("Location: /app/login.php");
    exit();
}

$message = '';
$error = '';

// Handle delete action
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    try {
        $conn->begin_transaction();

        // 1. Delete dependent entries first (Cascade Simulation)
        $conn->query("DELETE FROM `cart_items` WHERE `product_id` = $id");
        $conn->query("DELETE FROM `order_items` WHERE `product_id` = $id");
        $conn->query("DELETE FROM `reviews` WHERE `product_id` = $id");

        // 2. Delete the actual product
        $stmt = $conn->prepare("DELETE FROM `products` WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            $conn->commit();
            header("Location: products.php?msg=deleted");
            exit;
        } else {
            $conn->rollback();
            $error = "Error deleting product: " . $conn->error;
        }
    } catch (Exception $e) {
        $conn->rollback();
        $error = "Database Error: " . $e->getMessage();
    }
}

// Handle "Edit" data loading
$edit_id = isset($_GET['edit']) ? intval($_GET['edit']) : null;
$name = '';
$price = '';
$description = '';
$type = 'food';
$image = '';
$stock = 0;

if ($edit_id) {
    $stmt = $conn->prepare("SELECT * FROM `products` WHERE `id` = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $product = $result->fetch_assoc();
        $name = $product['name'];
        $price = $product['price'];
        $description = $product['description'];
        $image = $product['image_path'];
        $type = $product['type'];
        $stock = $product['stock'];
    }
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add']) || isset($_POST['update'])) {
        $name = $_POST['name'];
        $price = $_POST['price'];
        $description = $_POST['description'] ?? '';
        $type = !empty($_POST['type']) ? $_POST['type'] : 'food';
        $stock = intval($_POST['stock'] ?? 0);

        $imagePath = $image; // Use existing if updating and no new image
        if (!empty($_FILES['image']['name'])) {
            $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
            $magicNumbers = [
                "\xFF\xD8\xFF" => 'jpg',
                "\x89\x50\x4E\x47" => 'png',
                "RIFF" => 'webp'
            ];

            $fileTitle = $_FILES['image']['name'];
            $fileExt = strtolower(pathinfo($fileTitle, PATHINFO_EXTENSION));

            if (!in_array($fileExt, $allowedExts)) {
                $error = "Invalid file extension. Only JPG, PNG, and WebP are allowed.";
            } else {
                $handle = fopen($_FILES['image']['tmp_name'], 'rb');
                $fileHeader = fread($handle, 4);
                fclose($handle);

                $isValidMagic = false;
                foreach ($magicNumbers as $magic => $mtype) {
                    if (str_starts_with($fileHeader, $magic)) {
                        $isValidMagic = true;
                        break;
                    }
                }

                if ($isValidMagic) {
                    $imageName = time() . '_' . bin2hex(random_bytes(8)) . '.' . $fileExt;
                    $target = "../assets/images/" . $imageName;
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                        $imagePath = "assets/images/" . $imageName;
                    } else {
                        $error = "Failed to upload image.";
                    }
                } else {
                    $error = "File header does not match a valid image type.";
                }
            }
        }

        if (empty($error)) {
            if (isset($_POST['add'])) {
                $stmt = $conn->prepare("INSERT INTO `products` (`name`, `price`, `description`, `image_path`, `stock`, `type`) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sdssis", $name, $price, $description, $imagePath, $stock, $type);
                if ($stmt->execute()) {
                    header("Location: products.php?msg=added");
                    exit;
                } else {
                    $error = "Error adding product: " . $conn->error;
                }
            } else {
                $stmt = $conn->prepare("UPDATE `products` SET `name` = ?, `price` = ?, `description` = ?, `image_path` = ?, `stock` = ?, `type` = ? WHERE `id` = ?");
                $stmt->bind_param("sdssisi", $name, $price, $description, $imagePath, $stock, $type, $edit_id);
                if ($stmt->execute()) {
                    header("Location: products.php?msg=updated");
                    exit;
                } else {
                    $error = "Error updating product: " . $conn->error;
                }
            }
        }
    }
}

// Fetch products for list view
$run_select = $conn->query("SELECT id, name, price, image_path, description, type, stock FROM `products` ORDER BY id DESC");

$view = isset($_GET['form']) || isset($_GET['edit']) ? 'form' : 'list';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products</title>
    <link rel="stylesheet" href="assets/css/products.css">
</head>

<body>
    <nav>
        <ul>
            <li><a href="../shop.php"
                    style="background-color: #27ae60; color: white; margin-bottom: 20px; font-weight: bold;">← Back to
                    Shop</a></li>
            <li><a href="Dashboard.php">Dashboard</a></li>
            <li><a href="useradmin.php">Users</a></li>
            <li><a href="products.php" class="active">Products</a></li>
            <li><a href="Inventory.php">Inventory Management</a></li>
            <li><a href="order_management.php">Order Management</a></li>
            <li><a href="reviews_management.php">Reviews</a></li>
            <li><a href="/app/logout.php">Logout</a></li>
        </ul>
    </nav>
    <main>
        <?php
        if (isset($_GET['msg'])) {
            $m = $_GET['msg'];
            if ($m == 'added')
                echo '<div class="alert alert-success">Product added successfully!</div>';
            if ($m == 'updated')
                echo '<div class="alert alert-success">Product updated successfully!</div>';
            if ($m == 'deleted')
                echo '<div class="alert alert-success">Product deleted successfully!</div>';
        }
        if ($error)
            echo '<div class="alert alert-error">' . $error . '</div>';
        ?>

        <div class="tabs">
            <a href="products.php" class="tab <?php echo $view === 'list' ? 'active' : ''; ?>">Product List</a>
            <a href="products.php?form" class="tab <?php echo $view === 'form' && !$edit_id ? 'active' : ''; ?>">Add
                Product</a>
            <?php if ($edit_id): ?>
                <a href="#" class="tab active">Edit Product</a>
            <?php endif; ?>
        </div>

        <?php if ($view === 'list'): ?>
            <h2>Product List</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Image</th>
                        <th>Type</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($value = $run_select->fetch_assoc()) { ?>
                        <tr>
                            <td>
                                <?php echo $value['id']; ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($value['name']); ?>
                            </td>
                            <td>
                                <?php echo CURRENCY_SYMBOL; ?>
                                <?php echo number_format($value['price'], 2); ?>
                            </td>
                            <td>
                                <?php echo $value['stock']; ?>
                            </td>
                            <td><img style="width:40px; height:40px; object-fit: cover;"
                                    src="<?php $imgUrl = getImageUrl($value['image_path']);
                                    echo (str_starts_with($imgUrl, 'http') ? $imgUrl : '../' . ($imgUrl ?: 'assets/images/placeholder-300x300.png')); ?>" alt="Product">
                            </td>
                            <td>
                                <?php echo ucfirst($value['type']); ?>
                            </td>
                            <td>
                                <a href="products.php?edit=<?php echo $value['id']; ?>" class="edit-link">Edit</a> |
                                <a href="?delete=<?php echo $value['id']; ?>" style="color: #dc3545;"
                                    onclick="return confirm('Delete this product?');">Delete</a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php else: ?>
            <form method="POST" enctype="multipart/form-data">
                <h2>
                    <?php echo $edit_id ? 'Edit' : 'Add New'; ?> Product
                </h2>

                <label for="name">Product Name</label>
                <input value="<?php echo htmlspecialchars($name); ?>" id="name" type="text" name="name" required><br><br>

                <label for="price">Product Price ($)</label>
                <input value="<?php echo $price; ?>" id="price" type="number" step="0.01" name="price" required><br><br>

                <label for="stock">Stock Quantity</label>
                <input value="<?php echo $stock; ?>" id="stock" type="number" name="stock" required><br><br>

                <label for="description">Product Description</label>
                <textarea id="description" name="description"
                    rows="4"><?php echo htmlspecialchars($description); ?></textarea><br><br>

                <label for="type">Product Type</label>
                <select id="type" name="type">
                    <option value="food" <?php echo $type === 'food' ? 'selected' : ''; ?>>Food</option>
                    <option value="drink" <?php echo $type === 'drink' ? 'selected' : ''; ?>>Drink</option>
                </select><br><br>

                <label for="image">Product Image</label>
                <?php if (!empty($image)): ?>
                    <div style="margin-bottom: 10px;">
                        <img src="<?php $imgUrl = getImageUrl($image);
                        echo (str_starts_with($imgUrl, 'http') ? $imgUrl : '../' . ($imgUrl ?: 'assets/images/placeholder-300x300.png')); ?>"
                            alt="Current Image" width="100">
                    </div>
                <?php endif; ?>
                <input id="image" type="file" name="image"><br><br>

                <div class="f-btn">
                    <?php if ($edit_id): ?>
                        <button type="submit" name="update">Update Product</button>
                        <a href="products.php" style="padding: 10px; color: #666;">Cancel</a>
                    <?php else: ?>
                        <button type="submit" name="add">Add Product</button>
                    <?php endif; ?>
                </div>
            </form>
        <?php endif; ?>
    </main>
</body>

</html>