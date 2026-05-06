<?php
require_once 'config.php';
$tables = ['orders', 'payments', 'users', 'order_items'];
foreach ($tables as $table) {
    echo "--- $table ---\n";
    $result = $conn->query("DESCRIBE $table");
    while ($row = $result->fetch_assoc()) {
        echo "{$row['Field']} ({$row['Type']})\n";
    }
}
