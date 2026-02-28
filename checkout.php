<?php
require_once __DIR__ . '/init.php';
require_once __DIR__ . '/vendor/autoload.php';

//to check if user is login and did i put the api key or not
if (!isLoggedIn() || STRIPE_SECRET_KEY === '') {
    header('Location: /app/login.php');
    exit();
}

\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

$userId = $_SESSION['user_id'];
$cartQuery = "SELECT ci.*, p.name, p.price, p.image_path 
              FROM cart_items ci 
              JOIN carts c ON ci.cart_id = c.id 
              JOIN products p ON ci.product_id = p.id 
              WHERE c.user_id = $userId";
$result = $conn->query($cartQuery);

$line_items = [];
$totalAmount = 0;
$items = [];

while ($row = $result->fetch_assoc()) {
    $totalAmount += $row['price'] * $row['quantity'];
    $items[] = $row;
    $line_items[] = [
        'price_data' => [
            'currency' => 'usd',
            'product_data' => [
                'name' => $row['name'],
                'images' => [$row['image_path'] ? (str_starts_with($row['image_path'], 'http') ? $row['image_path'] : APP_URL . $row['image_path']) : 'https://via.placeholder.com/300'],
            ],
            'unit_amount' => $row['price'] * 100, // Amount in cents
        ],
        'quantity' => $row['quantity'],
    ];
}

if (empty($line_items)) {
    header('Location: /app/cart.php');
    exit();
}

// 1. Create Pending Order before redirecting to Stripe
$addrResult = $conn->query("SELECT * FROM user_addresses WHERE user_id = $userId AND is_default = 1");
$address = $addrResult->fetch_assoc();
//FOR ADDRESS NOW IF USER DON'T HAVE SOMETHING IN DATABASE IT WILL RETURN DEFULATE VALUE 
$location = $address['location_description'] ?? 'No Address Provided';
$phone = $address['phone'] ?? '0000000000';

$orderStmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, status, location_description, phone) VALUES (?, ?, 'pending', ?, ?)");
$orderStmt->bind_param("idss", $userId, $totalAmount, $location, $phone);
$orderStmt->execute();
$orderId = $conn->insert_id;

// 2. Insert Order Items
foreach ($items as $item) {
    $itemStmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, product_name, price, quantity) VALUES (?, ?, ?, ?, ?)");
    $itemStmt->bind_param("iisdi", $orderId, $item['product_id'], $item['name'], $item['price'], $item['quantity']);
    $itemStmt->execute();
}

// 3. Create Stripe Session with order_id in metadata
$checkout_session = \Stripe\Checkout\Session::create([
    'payment_method_types' => ['card'],
    'line_items' => $line_items,
    'mode' => 'payment',
    'metadata' => [
        'order_id' => $orderId
    ],
    // for now the checkout can be success or canceld depending on  
    'success_url' => APP_URL . 'payment_success.php?session_id={CHECKOUT_SESSION_ID}',
    'cancel_url' => APP_URL . 'payment_success.php?session_id={CHECKOUT_SESSION_ID}&cancelled=1',
]);

// 303 See Other code is to make browser forget the post ensures that the transition from  server to Stripe's server is clean
//avoid data resending from browser 
header("HTTP/1.1 303 See Other");
header("Location: " . $checkout_session->url);
?>