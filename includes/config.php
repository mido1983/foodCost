<?php
// Load environment variables from config.env file
function loadEnv($path) {
    if (!file_exists($path)) {
        return false;
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

            // Set as environment variable
            putenv("{$key}={$value}");
        }
    }
    
    return true;
}

// Load environment variables
$env_path = __DIR__ . '/../config.env';
loadEnv($env_path);

// Configuration settings (now using environment variables)
if (!defined('DB_HOST')) define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
if (!defined('DB_NAME')) define('DB_NAME', getenv('DB_NAME') ?: 'foodCost_db');
if (!defined('DB_USER')) define('DB_USER', getenv('DB_USER') ?: 'root');
if (!defined('DB_PASS')) define('DB_PASS', getenv('DB_PASS') ?: '');
if (!defined('SITE_NAME')) define('SITE_NAME', getenv('SITE_NAME') ?: 'FoodCost Manager');
if (!defined('SITE_URL')) define('SITE_URL', getenv('SITE_URL') ?: 'http://localhost/foodcost');
if (!defined('SITE_EMAIL')) define('SITE_EMAIL', getenv('SITE_EMAIL') ?: 'admin@foodcostmanager.com');

// Добавьте константы для путей к медиа-файлам
if (!defined('UPLOADS_PATH')) define('UPLOADS_PATH', __DIR__ . '/../uploads');
if (!defined('UPLOADS_URL')) define('UPLOADS_URL', SITE_URL . '/uploads');

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
date_default_timezone_set('Europe/Kiev');

// Autoload classes
spl_autoload_register(function($className) {
    $file = __DIR__ . '/../classes/' . $className . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Добавьте эту строку после всех остальных подключений файлов
require_once __DIR__ . '/admin_functions.php'; 