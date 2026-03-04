<?php
require_once __DIR__ . '/includes/RateLimiter.php';

echo "Testing Redis connectivity...\n";
try {
    $redis = new Predis\Client([
        'scheme' => 'tcp',
        'host' => '127.0.0.1',
        'port' => 6379,
    ]);
    $redis->connect();
    echo "Successfully connected to Redis!\n";

    echo "Testing login failure recording logic...\n";
    $testUser = 'test_user_' . time();
    $count = record_login_failure($testUser);
    echo "Recorded 1st failure for $testUser. Count: $count\n";

    if ($count === 1) {
        echo "Record failure logic works!\n";
    } else {
        echo "Unexpected count: $count\n";
    }

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
