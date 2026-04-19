<?php

$config = require 'settings.php';

return [
    'dsn' => 'mysql:host='. $config['DB_HOST'] .';dbname='. $config['DB_NAME'],
    'user' => $config['DB_USER'],
    'password' => $config['DB_PASSWORD'],
    'charset' => 'utf8mb4',
];
