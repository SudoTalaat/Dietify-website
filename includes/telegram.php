<?php

/**
 * Sends a formatted order message to a configured Telegram Bot.
 *
 * @param int $orderId The ID of the order
 * @param mysqli $conn The database connection
 * @return bool True if successful, false otherwise
 */
function notifyTelegramDispatcher(int $orderId, mysqli $conn): bool
{
    $botToken = $_ENV['TELEGRAM_BOT_TOKEN'] ?? '';
    $chatId = $_ENV['TELEGRAM_CHAT_ID'] ?? '';

    // If credentials are missing, silently fail (or could throw an exception)
    if (empty($botToken) || empty($chatId)) {
        error_log("Telegram credentials missing in .env for order dispatch.");
        return false;
    }

    // Fetch order details
    $orderStmt = $conn->prepare("
        SELECT o.*, u.username, u.email 
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        WHERE o.id = ?
    ");
    $orderStmt->bind_param("i", $orderId);
    $orderStmt->execute();
    $orderResult = $orderStmt->get_result();
    $order = $orderResult->fetch_assoc();
    $orderStmt->close();

    if (!$order) {
        return false;
    }

    // Fetch order items
    $itemsStmt = $conn->prepare("SELECT product_name, quantity, price FROM order_items WHERE order_id = ?");
    $itemsStmt->bind_param("i", $orderId);
    $itemsStmt->execute();
    $itemsResult = $itemsStmt->get_result();
    
    $itemsList = "";
    while ($item = $itemsResult->fetch_assoc()) {
        $itemsList .= "• {$item['quantity']}x {$item['product_name']} (" . number_format($item['price'], 2) . ")\n";
    }
    $itemsStmt->close();

    // Format the message
    $message = "🚨 *NEW ORDER RECEIVED* 🚨\n\n";
    $message .= "*Order ID:* #" . $order['id'] . "\n";
    $message .= "*Customer:* " . htmlspecialchars($order['username']) . "\n";
    $message .= "*Phone:* " . htmlspecialchars($order['phone']) . "\n";
    $message .= "*Delivery Address:* " . htmlspecialchars($order['location_description']) . "\n\n";
    
    $message .= "*Items:*\n" . $itemsList . "\n";
    
    $message .= "*Total Amount:* " . CURRENCY_SYMBOL . number_format($order['total_amount'], 2) . "\n";
    $message .= "*Payment Status:* " . ucfirst($order['status']) . " (Stripe)\n";

    // Send via Telegram API
    $url = "https://api.telegram.org/bot{$botToken}/sendMessage";
    
    $postData = [
        'chat_id' => $chatId,
        'text' => $message,
        'parse_mode' => 'Markdown'
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    
    // In local dev, you might want to disable SSL verification if it causes issues, 
    // but in production it should be true.
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        error_log("Failed to send Telegram message for order $orderId. Response: " . print_r($response, true));
        return false;
    }

    return true;
}

/**
 * Sends a cancellation notice to the configured Telegram Bot.
 *
 * @param int $orderId The ID of the cancelled order
 * @param mysqli $conn The database connection
 * @return bool True if successful, false otherwise
 */
function notifyTelegramCancellation(int $orderId, mysqli $conn): bool
{
    $botToken = $_ENV['TELEGRAM_BOT_TOKEN'] ?? '';
    $chatId = $_ENV['TELEGRAM_CHAT_ID'] ?? '';

    if (empty($botToken) || empty($chatId)) {
        return false;
    }

    $message = "🚫 *ORDER CANCELLED* 🚫\n\n";
    $message .= "*Order ID:* #" . $orderId . "\n";
    $message .= "The customer has cancelled this order.\n";
    $message .= "Please DO NOT prepare or deliver it.";

    $url = "https://api.telegram.org/bot{$botToken}/sendMessage";
    $postData = [
        'chat_id' => $chatId,
        'text' => $message,
        'parse_mode' => 'Markdown'
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return $httpCode === 200;
}
