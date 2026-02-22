<?php

require_once __DIR__ . '/vendor/autoload.php';

function check_rate_limit($ip, $limit = 5, $window = 60)
{
    try {
        $redis = new Predis\Client([
            'scheme' => 'tcp',
            'host' => '127.0.0.1',
            'port' => 6379,
        ]);

        $key = "login_attempt:$ip";
        $current = $redis->get($key);

        if ($current !== null && $current >= $limit) {
            return false;
        }

        if ($current === null) {
            $redis->setex($key, $window, 1);
        } else {
            $redis->incr($key);
        }

        return true;
    } catch (Exception $e) {
        // Fallback to allow login if Redis is down
        return true;
    }
}
