<?php


namespace App\Core\Helpers;
use Foundation\Core\Session;
use PDO;

class DB
{
    /**
     * Obtiene la conexión PDO.
     */
    public static function getDB(): PDO
    {
        $config = include $_SESSION['directoriobase'] . '/config/db.php';
        return new PDO(
            $config['dsn'], 
            $config['user'], 
            $config['password'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ]
        );
    }
}
?>