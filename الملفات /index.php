<?php

if (!function_exists('checkRateLimit')) {
    function checkRateLimit() {
        $cache_dir = sys_get_temp_dir() . '/rate_limit/';
        
        if (!file_exists($cache_dir)) {
            mkdir($cache_dir, 0755, true);
        }
        
        $global_block_file = $cache_dir . 'GLOBAL_BLOCK';
        $current_time = time();
        $block_duration = 6000;
        $rate_limit = 5;
        $rate_limit_period = 30;
        
        cleanOldCacheFiles($cache_dir, $block_duration);
        
        if (file_exists($global_block_file)) {
            $block_time = filemtime($global_block_file);
            if ($current_time - $block_time < $block_duration) {
                header('Location: n.html');
                exit;
            } else {
                unlink($global_block_file);
            }
        }
        
        $ip_addresses = [];
        $ip_keys = [
            'REMOTE_ADDR',
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED'
        ];
        
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) && !empty($_SERVER[$key])) {
                $values = explode(',', $_SERVER[$key]);
                foreach ($values as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP)) {
                        $ip_addresses[] = $ip;
                    }
                }
            }
        }
        
        $ip_addresses = array_unique($ip_addresses);
        if (empty($ip_addresses)) {
            $ip_addresses = ['unknown'];
        }
        
        foreach ($ip_addresses as $ip) {
            $cache_file = $cache_dir . md5($ip);
            
            if (file_exists($cache_file)) {
                $data = json_decode(file_get_contents($cache_file), true);
                
                if ($current_time - $data['first_request'] <= $rate_limit_period) {
                    $data['count']++;
                    
                    if ($data['count'] >= $rate_limit) {
                        file_put_contents($global_block_file, '1');
                        header('Location: n.html');
                        exit;
                    }
                } else {
                    $data = [
                        'first_request' => $current_time,
                        'count' => 1
                    ];
                }
            } else {
                $data = [
                    'first_request' => $current_time,
                    'count' => 1
                ];
            }
            
            file_put_contents($cache_file, json_encode($data));
        }
    }
}

if (!function_exists('cleanOldCacheFiles')) {
    function cleanOldCacheFiles($cache_dir, $max_age) {
        $files = glob($cache_dir . '*');
        $current_time = time();
        
        foreach ($files as $file) {
            if (filemtime($file) < ($current_time - $max_age)) {
                unlink($file);
            }
        }
    }
}

checkRateLimit();

define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);
chdir(__DIR__);

$pathsConfig = FCPATH . 'app/Config/Paths.php';
require realpath($pathsConfig) ?: $pathsConfig;

$paths = new Config\Paths();
$bootstrap = rtrim($paths->systemDirectory, '\\/ ') . DIRECTORY_SEPARATOR . 'bootstrap.php';
$app = require realpath($bootstrap) ?: $bootstrap;

$app->run();