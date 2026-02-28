<?php
require_once __DIR__ . '/../init.php';
require_once __DIR__ . '/../vendor/autoload.php';

// Stripe Webhook Endpoint

\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

// Replace this with your actual webhook secret from Stripe dashboard or CLI
$endpoint_secret = $_ENV['STRIPE_WEBHOOK_SECRET'] ?? '';

$payload = @file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';
$event = null;

try {
    if ($endpoint_secret) {
        $event = \Stripe\Webhook::constructEvent(
            $payload,
            $sig_header,
            $endpoint_secret
        );
    } else {
        // Fallback for testing without a secret (NOT RECOMMENDED FOR PRODUCTION)
        $event = \Stripe\Event::constructFrom(
            json_decode($payload, true)
        );
    }
} catch (\UnexpectedValueException $e) {
    // Invalid payload
    http_response_code(400);
    exit();
} catch (\Stripe\Exception\SignatureVerificationException $e) {
    // Invalid signature
    http_response_code(400);
    exit();
}

// Handle the event
if ($event->type == 'checkout.session.completed') {
    $session = $event->data->object;
    $orderId = $session->metadata->order_id ?? null;
    $transactionId = $session->id;

    if ($orderId) {
        // Start transaction to ensure atomicity
        $conn->begin_transaction();
        try {
            // 1. Check if order is already processed (Idempotency)
            $checkStmt = $conn->prepare("SELECT status, user_id FROM orders WHERE id = ?");
            $checkStmt->bind_param("i", $orderId);
            $checkStmt->execute();
            $order = $checkStmt->get_result()->fetch_assoc();

            if ($order && $order['status'] === 'pending') {
                $userId = $order['user_id'];

                // 2. Update Order to 'paid'
                $orderStmt = $conn->prepare("UPDATE orders SET status = 'paid' WHERE id = ?");
                $orderStmt->bind_param("i", $orderId);
                $orderStmt->execute();

                // 3. Create Payment record (completed)
                // Use REPLACE or IGNORE if we want to be extra safe against duplicates
                $paymentStmt = $conn->prepare("INSERT IGNORE INTO payments (order_id, method, status, transaction_id, paid_at) VALUES (?, 'stripe', 'completed', ?, NOW())");
                $paymentStmt->bind_param("is", $orderId, $transactionId);
                $paymentStmt->execute();

                // 4. Clear Cart (based on user_id from order)
                $conn->query("DELETE FROM cart_items WHERE cart_id = (SELECT id FROM carts WHERE user_id = $userId)");

                // 5. Update Stock
                $itemsResult = $conn->query("SELECT product_id, quantity FROM order_items WHERE order_id = $orderId");
                while ($item = $itemsResult->fetch_assoc()) {
                    $pId = $item['product_id'];
                    $qty = $item['quantity'];
                    $conn->query("UPDATE products SET stock = stock - $qty WHERE id = $pId");
                }
            }
            $conn->commit();
        } catch (Exception $e) {
            $conn->rollback();
            // Logging would go here
            http_response_code(500);
            exit();
        }
    }
}

http_response_code(200);
?>