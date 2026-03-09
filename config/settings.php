<?php 
use PHPMailer\PHPMailer\PHPMailer;


return [


'title' => 'ZMosquita',
'subtitle' => 'Zmosquita',
'debug' => true,

'timezone' => 'UTC',


'locale' => 'es',
'charset' => 'UTF-8',
'base_url' => 'http://zmosquita.local',
'directoriobase' => '',
'base_path' => '/app',
'controllers_path' => '/app/Controllers',
'views_path' => '/views',
'cache_path' => '/app/Cache',
'logs_path' => '/app/Logs',
'assets_path' => '/app/Assets',
'public_path' => '/public',
'root_path' => '',
'basellave' => 'M4nd4ng4$_$Buc4m4r4ng4',

'MAIL_MAILER' => 'smtp',
'MAIL_HOST' => 'medanodigital.net',
'MAIL_PORT' => 465,
'MAIL_USERNAME' => 'no_responder@medanodigital.net',
'MAIL_PASSWORD' => '.....................',
'MAIL_ENCRYPTION' => PHPMailer::ENCRYPTION_SMTPS, //'tls', //PHPMailer::ENCRYPTION_STARTTLS
'MAIL_FROM_ADDRESS' => "no_responder@medanodigital.net",
'MAIL_FROM_NAME' => "Notificaciones",
'MAIL_NOREPLAY_LABEL' => "ZMosquita",
'MAIL_SUBJ_PREFIX' => "Zmosquita",

'DB_HOST' => 'localhost',
'DB_NAME' => 'copro6',
'DB_USER' => 'copro6',
'DB_PASSWORD' => 'copro6',
'DB_CHARSET' => 'utf8mb4',
'DB_COLLATE' => 'utf8mb4_general_ci',
'DB_PORT' => 3306,

// Database configuration for PDO
'db' => [
    'dsn' => 'mysql:host=localhost;dbname=copro6;charset=utf8mb4',
    'username' => 'copro6',
    'password' => 'copro6',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ],
],

];
