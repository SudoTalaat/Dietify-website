<?php
require_once __DIR__ . '/../init.php';

if (!isLoggedIn() || !isAdmin()) {
    header("Location: /app/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $role = $_POST['role'] == '1' ? 'admin' : 'customer';

    $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
    $stmt->bind_param("si", $role, $id);
    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error";
    }
    exit;
}

$query = "SELECT id, username, email, role FROM users";
$result = $conn->query($query);
$users = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>User Management</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/useradmin.css">
</head>

<body>
    <nav>
        <ul>
            <li><a href="../shop.php"
                    style="background-color: #27ae60; color: white; margin-bottom: 20px; font-weight: bold;">← Back to
                    Shop</a></li>
            <li><a href="Dashboard.php">Dashboard</a></li>
            <li><a href="#" class="active">Users</a></li>
            <li><a href="addproduct.php">Add Products</a></li>
            <li><a href="viewproduct.php">View Products</a></li>
            <li><a href="Inventory.php">Inventory Management</a></li>
            <li><a href="order_management.php">Order Management</a></li>
            <li><a href="reviews_management.php">Reviews</a></li>
            <li><a href="../actions/logout.php">Logout</a></li>
        </ul>
    </nav>

    <div class="main-content">
        <h1 class="page-title">USERS</h1>
        <div class="dashboard-cards">
            <div class="card">
                <div class="card-header">
                    <div class="card-icon users">
                        <i class="fas fa-users"></i>
                    </div>
                    <div>
                        <p class="card-title">TOTAL USERS</p>
                        <p class="card-value"><?php echo count($users); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="tables-section">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($users as $user) {
                            echo "<tr>";
                            echo "<td>" . $user['id'] . "</td>";
                            echo "<td>" . htmlspecialchars($user['username']) . "</td>";
                            echo "<td>" . htmlspecialchars($user['email']) . "</td>";
                            echo "<td>";
                            echo "<select onchange='updateRole(" . $user['id'] . ", this.value)'>";
                            if ($user['role'] == 'admin') {
                                echo "<option value='1' selected>Admin</option>";
                                echo "<option value='0'>User</option>";
                            } else {
                                echo "<option value='1'>Admin</option>";
                                echo "<option value='0' selected>User</option>";
                            }
                            echo "</select>";
                            echo "</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
<script>
    function updateRole(id, role) {
        fetch('', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'id=' + id + '&role=' + role
        })
            .then(response => response.text())
            .then(result => {
                if (result.trim() == 'success') {
                    alert('Role updated successfully');
                } else {
                    alert('Error updating role');
                }
            });
    }
</script>

</html>