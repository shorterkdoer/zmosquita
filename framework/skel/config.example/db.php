<?php
/**
 * Database Configuration
 */

return [
    'dsn' => 'mysql:host=' . ($_ENV['DB_HOST'] ?? 'localhost') . ';' .
            'dbname=' . ($_ENV['DB_NAME'] ?? 'myapp') . ';' .
            'charset=utf8mb4',
    'username' => $_ENV['DB_USER'] ?? 'root',
    'password' => $_ENV['DB_PASS'] ?? '',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ]
];
