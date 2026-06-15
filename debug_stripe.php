<?php
require_once __DIR__ . '/vendor/autoload.php';
// Load .env manually to avoid DB connection
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        $_ENV[trim($name)] = trim($value);
    }
}
define('STRIPE_SECRET_KEY', $_ENV['STRIPE_SECRET_KEY']);
require_once __DIR__ . '/vendor/autoload.php';

\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);
$sessionId = 'cs_test_a1rnYpijlzya42CZU3a0qnej4BnuIIgax4T0CybKwOhkwJxZL8RLDlf5K1';
try {
    $session = \Stripe\Checkout\Session::retrieve($sessionId);
    echo "Session ID: " . $session->id . "\n";
    echo "Payment Intent: " . $session->payment_intent . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
