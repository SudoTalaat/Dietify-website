<?php
require_once __DIR__ . '/vendor/autoload.php';

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "login_test";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Enforce UTC for both PHP and MySQL to prevent timezone sync issues
// note TOTP use unixtimestamp so it doesn't matter but it affect 2fa email 
date_default_timezone_set('UTC');
$conn->query("SET time_zone = '+00:00'");
?>