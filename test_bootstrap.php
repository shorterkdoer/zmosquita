<?php

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "Iniciando prueba...\n";
echo "Incluyendo autoload...\n";

require_once __DIR__ . '/vendor/autoload.php';

echo "Autoload incluido correctamente.\n";

echo "Verificando clases...\n";
echo "Container: " . (class_exists('ZMosquita\Core\Support\Container') ? 'EXISTS' : 'NOT FOUND') . "\n";
echo "Connection: " . (class_exists('ZMosquita\Core\Database\Connection') ? 'EXISTS' : 'NOT FOUND') . "\n";
echo "TenantConnectionResolver: " . (class_exists('ZMosquita\Core\Database\TenantConnectionResolver') ? 'EXISTS' : 'NOT FOUND') . "\n";

echo "Incluyendo bootstrap_core.php...\n";
require_once __DIR__ . '/bootstrap_core.php';

echo "Bootstrap completado.\n";
