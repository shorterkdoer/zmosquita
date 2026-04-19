<?php

namespace App\Core;

use Foundation\Crud\Controller as FrameworkController;
use League\Plates\Engine;
use PDO;
use Foundation\Core\Request;
use Foundation\Core\CSRF;
use App\Core\Helpers\Sanitizer;

/**
 * Application Controller base class
 *
 * Extends the framework Controller class.
 * Add application-specific methods here if needed.
 */
class Controller extends FrameworkController
{
    // The framework provides all CRUD functionality
    // Add any application-specific methods below

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get a PDO database connection instance.
     *
     * Override to use application-specific settings.
     *
     * @return PDO
     */
    protected static function getDB(): PDO
    {
        $config = require $_SESSION['directoriobase'] . '/config/settings.php';
        $dsn = $config['db']['dsn'];
        $username = $config['db']['username'];
        $password = $config['db']['password'];
        $options = $config['db']['options'] ?? [];

        return new PDO($dsn, $username, $password, $options);
    }

    /**
     * Execute a custom SQL query.
     *
     * @param string $customquery SQL query
     * @return array Results as associative arrays
     */
    public static function CustomQry(string $customquery): array
    {
        $stmt = self::getDB()->query($customquery);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get the last database error message.
     *
     * @return string Error message
     */
    public static function CustomError(): string
    {
        $localerror = self::getDB()->errorInfo();
        return $localerror[2] ?? 'Unknown database error';
    }

    /**
     * Get field name from dotted notation.
     *
     * @param string $zstring Dotted notation (e.g., 'table.field')
     * @return string The field name
     */
    public function getCampo(string $zstring): string
    {
        $pos = strpos($zstring, '.');
        if ($pos === false) {
            return $zstring;
        }
        return substr($zstring, $pos + 1);
    }

    /**
     * Generate JavaScript columns string.
     *
     * @param array $campos Fields configuration
     * @return string JavaScript columns definition
     */
    protected function jscolumns(array $campos): string
    {
        $jscolumns = "columns: [";
        foreach ($campos as $campo) {
            $jscolumns .= "{ data: '" . trim($campo['nombre']) . "', title: '" . trim($campo['label']) . "' },";
        }
        $jscolumns = rtrim($jscolumns, ',') . "],";

        return $jscolumns;
    }
}
