<?php
// Enable error reporting for development
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Autoload dependencies FIRST
require_once 'vendor/autoload.php';

use Foundation\Core\CSRF;
use Foundation\Core\AppManager;

// Start session with secure configuration
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'cookie_samesite' => 'Strict',
        'use_strict_mode' => true,
    ]);
}

// Set base directory
$_SESSION['directoriobase'] = __DIR__;

// Detect the current application based on subdomain
$currentApp = AppManager::detectApplication();

// Set application-specific paths
$_SESSION['base_pathviews'] = AppManager::getViewsPath();
$_SESSION['base_url'] = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];
$_SESSION['current_app'] = $currentApp;
$_SESSION['current_app_namespace'] = AppManager::getAppNamespace();
$_SESSION['current_app_base_path'] = AppManager::getAppBasePath();

$dotenv = Dotenv\Dotenv::createImmutable($_SESSION['directoriobase'] . "/");
$dotenv->load();

// Load configuration and routes
use Foundation\Core\Router;
$config = require 'config/settings.php';

// Allow app-specific config to override global config
$appConfig = AppManager::getCurrentAppConfig();
if ($appConfig && isset($appConfig['config'])) {
    $config = array_merge($config, $appConfig['config']);
}

$_SESSION['Title'] = $config['title'];
$_SESSION['Subtitle'] = $config['subtitle'];

// Load routes for the current application
require_once AppManager::getRoutesFile();

//$templates = new League\Plates\Engine($_SESSION['base_pathviews']);
//$templates->addData(['csrf' => $csrf]); // 👈 clave


// Dispatch the request
Router::dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
