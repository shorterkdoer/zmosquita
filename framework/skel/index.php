<?php
/**
 * Application Entry Point
 */

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define base directory
define('BASE_DIR', dirname(__DIR__));
$_SESSION['directoriobase'] = BASE_DIR;
$_SESSION['base_url'] = ''; // Configure your base URL here

// Load autoloader
require_once BASE_DIR . '/vendor/autoload.php';

// Load configuration
require_once BASE_DIR . '/config/routes.php';

// Dispatch request
$uri = $_SERVER['REQUEST_URI'] ?? '/';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

try {
    \Foundation\Core\Router::dispatch($uri, $method);
} catch (\Exception $e) {
    http_response_code(500);
    echo "Error: " . $e->getMessage();
}
