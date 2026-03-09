<?php
// Enable error reporting for development
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
//use ParagonIE\AntiCSRF\AntiCSRF;

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
//$csrf = new AntiCSRF();

// Set base directory

$_SESSION['directoriobase'] = __DIR__;
$_SESSION['base_pathviews'] = $_SESSION['directoriobase'] . '/views';
$_SESSION['base_url'] = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];


// Autoload dependencies
require_once 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable($_SESSION['directoriobase'] . "/");
$dotenv->load();
// Load configuration and routes
use App\Core\Router;
$config = require 'config/settings.php';

$_SESSION['Title'] = $config['title'];
$_SESSION['Subtitle'] = $config['subtitle'];

require_once 'config/routes.php';
//$templates = new League\Plates\Engine($_SESSION['base_pathviews']);
//$templates->addData(['csrf' => $csrf]); // 👈 clave


// Dispatch the request
Router::dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
