<?php

namespace App\Core;

use Foundation\Database\Model as FrameworkModel;

/**
 * Application Model base class
 *
 * Extends the framework Model class.
 * Add application-specific methods here if needed.
 */
abstract class Model extends FrameworkModel
{
    // The framework provides all CRUD functionality
    // Add any application-specific methods below

    protected string $pendingquery = '';
    protected string $pendingcolumns = '';

    /**
     * Get database connection using application config.
     *
     * Override to use application-specific config path.
     *
     * @return \PDO
     */
    protected static function getDB(): \PDO
    {
        $config = require $_SESSION['directoriobase'] . '/config/db.php';
        return new \PDO(
            $config['dsn'],
            $config['user'],
            $config['password'],
            [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ]
        );
    }
}
