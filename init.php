<?php
require_once __DIR__ . '/vendor/autoload.php';

// Load environment variables
// i just copy thisfrom dotenv docs as it is 
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// Database connection
require_once __DIR__ . '/includes/db_connect.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Global constants
define('SITE_NAME', $_ENV['SMTP_NAME'] ?? 'Healthy Food App');
define('STRIPE_PUBLISHABLE_KEY', $_ENV['STRIPE_PUBLISHABLE_KEY'] ?? '');
define('STRIPE_SECRET_KEY', $_ENV['STRIPE_SECRET_KEY'] ?? '');
// CURRENCY_CODE: Standard 3-letter ISO code required by payment gateways like Stripe (e.g., 'EGP')
// CURRENCY_SYMBOL: The visual symbol displayed to users on the frontend (e.g., 'EGP ', 'LE ', or 'ج.م ')
define('CURRENCY_CODE', 'EGP');
define('CURRENCY_SYMBOL', 'EGP ');

// Base URL for Stripe and absolute redirects (from .env)
//
define('APP_URL', $_ENV['APP_URL'] ?? 'http://localhost/app/');

// Helper to check if user is admin
function isAdmin()
{
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Helper to check if user is logged in
function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

// Helper to get cart item count
function getCartCount($conn)
{
    if (!isLoggedIn())
        return 0;

    $userId = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT SUM(ci.quantity) as total FROM cart_items ci JOIN carts c ON ci.cart_id = c.id WHERE c.user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result['total'] ?? 0;
}
?>