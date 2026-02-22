<?php

function check_rate_limit($ip, $limit = 5, $window = 60)
{
    try {
        $redis = new Redis();
        $redis->connect('127.0.0.1', 6379);

        $key = "login_attempt:$ip";
        $current = $redis->get($key);

        if ($current !== false && $current >= $limit) {
            return false;
        }

        if ($current === false) {
            $redis->set($key, 1, $window);
        } else {
            $redis->incr($key);
        }

        return true;
    } catch (Exception $e) {
        // Fallback to allow login if Redis is down (or handle differently based on policy)
        return true;
    }
}
