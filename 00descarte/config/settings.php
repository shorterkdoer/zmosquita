<?php
use PHPMailer\PHPMailer\PHPMailer;

// Load environment variables
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->safeLoad();
}

return [

    'title' => $_ENV['APP_NAME'] ?? 'ZMosquita',
    'subtitle' => $_ENV['APP_NAME'] ?? 'ZMosquita',
    'debug' => filter_var($_ENV['APP_DEBUG'] ?? 'false', FILTER_VALIDATE_BOOLEAN),

    'timezone' => 'UTC',

    'locale' => 'es',
    'charset' => 'UTF-8',
    'base_url' => $_ENV['APP_URL'] ?? 'http://localhost',
    'directoriobase' => '',
    'base_path' => '/app',
    'controllers_path' => '/app/Controllers',
    'views_path' => '/views',
    'cache_path' => '/app/Cache',
    'logs_path' => '/app/Logs',
    'assets_path' => '/app/Assets',
    'public_path' => '/public',
    'root_path' => '',

    // Application secret key - IMPORTANT: Generate a strong random key for production
    'basellave' => $_ENV['APP_KEY'] ?? 'change-this-key-in-production',

    'MAIL_MAILER' => $_ENV['MAIL_MAILER'] ?? 'smtp',
    'MAIL_HOST' => $_ENV['MAIL_HOST'] ?? 'localhost',
    'MAIL_PORT' => (int)($_ENV['MAIL_PORT'] ?? 587),
    'MAIL_USERNAME' => $_ENV['MAIL_USERNAME'] ?? '',
    'MAIL_PASSWORD' => $_ENV['MAIL_PASSWORD'] ?? '',
    'MAIL_ENCRYPTION' => $_ENV['MAIL_ENCRYPTION'] === 'tls' ? PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_SMTPS,
    'MAIL_FROM_ADDRESS' => $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@example.com',
    'MAIL_FROM_NAME' => $_ENV['MAIL_FROM_NAME'] ?? 'ZMosquita',
    'MAIL_NOREPLAY_LABEL' => $_ENV['MAIL_FROM_NAME'] ?? 'ZMosquita',
    'MAIL_SUBJ_PREFIX' => $_ENV['APP_NAME'] ?? 'ZMosquita',

    'DB_HOST' => $_ENV['DB_HOST'] ?? 'localhost',
    'DB_NAME' => $_ENV['DB_NAME'] ?? '',
    'DB_USER' => $_ENV['DB_USER'] ?? '',
    'DB_PASSWORD' => $_ENV['DB_PASSWORD'] ?? '',
    'DB_CHARSET' => $_ENV['DB_CHARSET'] ?? 'utf8mb4',
    'DB_COLLATE' => $_ENV['DB_COLLATE'] ?? 'utf8mb4_general_ci',
    'DB_PORT' => (int)($_ENV['DB_PORT'] ?? 3306),

    // Database configuration for PDO
    'db' => [
        'dsn' => 'mysql:host=' . ($_ENV['DB_HOST'] ?? 'localhost') . ';dbname=' . ($_ENV['DB_NAME'] ?? '') . ';charset=utf8mb4',
        'username' => $_ENV['DB_USER'] ?? '',
        'password' => $_ENV['DB_PASSWORD'] ?? '',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ],
    ],
];
