<?php 
use PHPMailer\PHPMailer\PHPMailer;


return [


'title' => 'CoProBiLP',
'subtitle' => 'Sistema de Matriculaciones',
'debug' => false,

'timezone' => 'UTC',


'locale' => 'es',
'charset' => 'UTF-8',
'base_url' => 'https://coprobilp.org.ar',
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
'MAIL_HOST' => 'mail.coprobilp.org.ar',
'MAIL_PORT' => 465,
'MAIL_USERNAME' => 'no_responder@coprobilp.org.ar',
'MAIL_PASSWORD' => 'Ym2g632*za',
'MAIL_ENCRYPTION' => PHPMailer::ENCRYPTION_SMTPS, //'tls', //PHPMailer::ENCRYPTION_STARTTLS
'MAIL_FROM_ADDRESS' => "no_responder@coprobilp.org.ar",
'MAIL_FROM_NAME' => "Notificaciones CoProBiLP",
'MAIL_NOREPLAY_LABEL' => "Sistema de matriculaciones CoProBiLP",
'MAIL_SUBJ_PREFIX' => "Matriculaciones",

'DB_HOST' => 'localhost',
'DB_NAME' => 'copro6testing',
'DB_USER' => 'copro6testing',
'DB_PASSWORD' => 'copro6testing',
'DB_CHARSET' => 'utf8mb4',
'DB_COLLATE' => 'utf8mb4_general_ci',
'DB_PORT' => 3306,


];
