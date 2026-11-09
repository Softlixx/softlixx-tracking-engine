<?php
/**
 * Softlixx Email Tracking Configuration
 * Rename this file to `config.php` and place it in the root or parent directory.
 */
return [
    'host'     => '127.0.0.1',
    'port'     => 3306,
    'dbname'   => 'u123456789_tracking',
    'username' => 'u123456789_tracker',
    'password' => 'YOUR_SECURE_DB_PASSWORD',
    'charset'  => 'utf8mb4',
    
    // Application settings
    'app_name' => 'Softlixx Email Intelligence',
    'base_url' => 'https://tracking.softlixx.com',
    'secret_key' => bin2hex(random_bytes(32)),
];
