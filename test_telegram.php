<?php
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

$conn = new mysqli('127.0.0.1', 'root', '', 'healthyfood');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

require_once __DIR__ . '/includes/telegram.php';

if (notifyTelegramDispatcher(22, $conn)) {
    echo "SUCCESS: Message sent to your Telegram!\n";
} else {
    echo "FAILED: Please check if you put the correct token and chat ID in .env\n";
}
?>
