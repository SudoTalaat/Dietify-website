<?php
require_once __DIR__ . '/../init.php';

if (!isLoggedIn()) {
    // Redirect to root login.php (must go up one level from 'actions/' folder)
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'];
    $productId = $_POST['product_id'];
    $rating = $_POST['rating'];
    $comment = $_POST['comment'];

    $stmt = $conn->prepare("INSERT INTO reviews (user_id, product_id, rating, comment) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiis", $userId, $productId, $rating, $comment);
    $stmt->execute();

    header("Location: /app/product.php?id=$productId");
}
?>