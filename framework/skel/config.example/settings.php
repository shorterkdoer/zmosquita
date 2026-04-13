<?php
/**
 * Application Settings
 */

return [
    'name' => $_ENV['APP_NAME'] ?? 'My App',
    'env' => $_ENV['APP_ENV'] ?? 'production',
    'debug' => filter_var($_ENV['APP_DEBUG'] ?? 'false', FILTER_VALIDATE_BOOLEAN),
    'url' => $_ENV['APP_URL'] ?? 'http://localhost',
    'basellave' => $_ENV['BASE_KEY'] ?? 'change-this-key',

    'db' => require __DIR__ . '/db.php',
];
