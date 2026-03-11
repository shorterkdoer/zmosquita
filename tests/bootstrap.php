<?php

/**
 * PHPUnit Bootstrap File
 * Sets up the environment for running tests
 */

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Define that we're in testing mode
define('PHPUNIT_TESTING', true);

// Set base directory
$_SESSION['directoriobase'] = dirname(__DIR__);
$_SESSION['base_pathviews'] = $_SESSION['directoriobase'] . '/views';
$_SESSION['base_url'] = 'http://localhost';

// Start session for tests that need it
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Load test configuration
$config = require __DIR__ . '/../config/settings.php';

// Set up test environment constants
define('TEST_ROOT', __DIR__);
define('APP_ROOT', dirname(__DIR__));

// Load any test-specific helpers
require_once __DIR__ . '/helpers.php';

// Clear any existing session data for clean state
$_SESSION = [];

echo "PHPUnit Bootstrap loaded.\n";
echo "Test environment initialized.\n";
