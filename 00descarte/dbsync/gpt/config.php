<?php
return [
    'dev' => [
        'dsn' => 'mysql:host=localhost;dbname=devdb;charset=utf8mb4',
        'user' => 'devuser',
        'pass' => 'devpass',
    ],
    'prod' => [
        'dsn' => 'mysql:host=localhost;dbname=proddb;charset=utf8mb4',
        'user' => 'produser',
        'pass' => 'prodpass',
    ]
];
