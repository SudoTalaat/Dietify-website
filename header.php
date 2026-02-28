<?php require_once __DIR__ . '/init.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php echo SITE_NAME; ?>
    </title>
    <link rel="stylesheet" href="/app/assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <nav class="navbar">
        <div class="nav-container">
            <a href="/app/index1.php" class="logo">
                <?php echo SITE_NAME; ?>
            </a>
            <div class="nav-links">
                <a href="/app/index1.php">Home</a>
                <a href="/app/shop.php">Shop</a>
                <?php if (isLoggedIn()): ?>
                    <a href="/app/cart.php" class="cart-link">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="cart-count">
                            <?php echo getCartCount($conn); ?>
                        </span>
                    </a>
                    <a href="/app/profile.php">Profile</a>
                    <?php if (isAdmin()): ?>
                        <a href="/app/admin/Dashboard.php" class="btn-admin">Admin</a>
                    <?php endif; ?>
                    <a href="/app/logout.php">Logout</a>
                <?php else: ?>
                    <a href="/app/login.php" class="btn-login">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    <main class="main-content">