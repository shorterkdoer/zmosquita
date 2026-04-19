<?php

namespace Foundation\Core;

/**
 * Application Manager
 *
 * Manages multiple applications in the framework, detecting which application
 * should be used based on the current subdomain.
 */
class AppManager
{
    private static ?array $applications = null;
    private static ?string $currentApp = null;
    private static ?array $currentAppConfig = null;

    /**
     * Load applications configuration
     *
     * @return array
     */
    private static function loadApplications(): array
    {
        if (self::$applications === null) {
            $configPath = $_SESSION['directoriobase'] ?? __DIR__ . '/../../../../';
            $configFile = $configPath . '/config/applications.php';

            if (file_exists($configFile)) {
                self::$applications = require $configFile;
            } else {
                self::$applications = [];
            }
        }

        return self::$applications;
    }

    /**
     * Detect the current application based on subdomain
     *
     * @return string Application name (null for default app)
     */
    public static function detectApplication(): ?string
    {
        if (self::$currentApp !== null) {
            return self::$currentApp;
        }

        $applications = self::loadApplications();
        $host = $_SERVER['HTTP_HOST'] ?? '';

        // Extract subdomain
        $parts = explode('.', $host);

        // If we have at least 3 parts (subdomain.domain.tld)
        if (count($parts) >= 3) {
            $subdomain = $parts[0];

            // Check if this subdomain matches any registered application
            foreach ($applications as $appName => $appConfig) {
                if (isset($appConfig['subdomain']) && $appConfig['subdomain'] === $subdomain) {
                    self::$currentApp = $appName;
                    self::$currentAppConfig = $appConfig;
                    return $appName;
                }
            }
        }

        // No specific application found, use default
        self::$currentApp = null;
        self::$currentAppConfig = null;
        return null;
    }

    /**
     * Get the current application name
     *
     * @return string|null
     */
    public static function getCurrentApp(): ?string
    {
        return self::$currentApp;
    }

    /**
     * Get the current application configuration
     *
     * @return array|null
     */
    public static function getCurrentAppConfig(): ?array
    {
        return self::$currentAppConfig;
    }

    /**
     * Get the base path for the current application
     *
     * @return string
     */
    public static function getAppBasePath(): string
    {
        $currentApp = self::getCurrentApp();

        if ($currentApp === null) {
            // Default application
            return $_SESSION['directoriobase'] . '/app';
        }

        return $_SESSION['directoriobase'] . "/applications/$currentApp";
    }

    /**
     * Get the namespace for the current application
     *
     * @return string
     */
    public static function getAppNamespace(): string
    {
        $currentApp = self::getCurrentApp();

        if ($currentApp === null) {
            // Default application
            return 'App';
        }

        return "Applications\\$currentApp";
    }

    /**
     * Get the controllers path for the current application
     *
     * @return string
     */
    public static function getControllersPath(): string
    {
        return self::getAppBasePath() . '/Controllers';
    }

    /**
     * Get the models path for the current application
     *
     * @return string
     */
    public static function getModelsPath(): string
    {
        return self::getAppBasePath() . '/Models';
    }

    /**
     * Get the services path for the current application
     *
     * @return string
     */
    public static function getServicesPath(): string
    {
        return self::getAppBasePath() . '/Services';
    }

    /**
     * Get the views path for the current application
     *
     * @return string
     */
    public static function getViewsPath(): string
    {
        $currentApp = self::getCurrentApp();

        if ($currentApp === null) {
            // Default application
            return $_SESSION['directoriobase'] . '/views';
        }

        return $_SESSION['directoriobase'] . "/applications/$currentApp/views";
    }

    /**
     * Get the config path for the current application
     *
     * @return string
     */
    public static function getConfigPath(): string
    {
        $currentApp = self::getCurrentApp();

        if ($currentApp === null) {
            // Default application uses global config
            return $_SESSION['directoriobase'] . '/config';
        }

        $appConfigPath = $_SESSION['directoriobase'] . "/applications/$currentApp/config";

        // If app has its own config directory, use it
        if (is_dir($appConfigPath)) {
            return $appConfigPath;
        }

        // Otherwise use global config
        return $_SESSION['directoriobase'] . '/config';
    }

    /**
     * Get the routes file for the current application
     *
     * @return string
     */
    public static function getRoutesFile(): string
    {
        $configPath = self::getConfigPath();

        // Check if app has its own routes file
        $appRoutes = $configPath . '/routes.php';

        if (file_exists($appRoutes)) {
            return $appRoutes;
        }

        // Default to global routes
        return $_SESSION['directoriobase'] . '/config/routes.php';
    }

    /**
     * Check if an application exists
     *
     * @param string $appName
     * @return bool
     */
    public static function appExists(string $appName): bool
    {
        $applications = self::loadApplications();
        return isset($applications[$appName]);
    }

    /**
     * Get all registered applications
     *
     * @return array
     */
    public static function getApplications(): array
    {
        return self::loadApplications();
    }
}
