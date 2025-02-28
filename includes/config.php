<?php
// Load environment variables from config.env file
function loadEnv($path) {
    if (!file_exists($path)) {
        die("Configuration file {$path} not found");
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        // Process key-value pairs
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Remove quotes from values if present
            if (strpos($value, '"') === 0 || strpos($value, "'") === 0) {
                $value = substr($value, 1, -1);
            }

            // Set as environment variable and as constant
            putenv("{$key}={$value}");
            if (!defined($key)) define($key, $value);
        }
    }
}

// Load environment variables
$env_path = __DIR__ . '/../config.env';
loadEnv($env_path);

// Configuration settings (now using environment variables)
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'foodCost_db');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('SITE_NAME', getenv('SITE_NAME') ?: 'FoodCost Manager');
define('SITE_URL', getenv('SITE_URL') ?: 'http://localhost/foodCost');

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Error reporting for development (turn off in production)
if (getenv('ENVIRONMENT') === 'production') {
    error_reporting(0);
    ini_set('display_errors', 0);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Time zone
date_default_timezone_set('UTC');

// Autoload classes
spl_autoload_register(function($class) {
    $class_file = __DIR__ . '/../classes/' . $class . '.php';
    if (file_exists($class_file)) {
        require_once $class_file;
    }
}); 