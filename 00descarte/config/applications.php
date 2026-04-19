<?php

/**
 * Applications Configuration
 *
 * Register your applications here. Each application can have its own
 * subdomain, controllers, models, views, and configuration.
 *
 * Example:
 *
 * return [
 *     'blog' => [
 *         'subdomain' => 'blog',
 *         'name' => 'Blog Application',
 *         'description' => 'Company blog',
 *     ],
 *     'api' => [
 *         'subdomain' => 'api',
 *         'name' => 'API Application',
 *         'description' => 'REST API',
 *     ],
 * ];
 *
 * Available options for each application:
 * - subdomain: The subdomain that triggers this application (required)
 * - name: Human-readable name of the application
 * - description: Description of the application
 * - config: Array of application-specific config overrides (optional)
 *
 * Application structure:
 * applications/{app_name}/
 * ├── Controllers/     # Application controllers
 * ├── Models/          # Application models
 * ├── Services/        # Application services
 * ├── Middlewares/     # Application middlewares
 * ├── views/           # Application views (optional)
 * └── config/          # Application-specific config (optional)
 *     └── routes.php   # Application-specific routes (optional)
 */

return [
    // Example application (commented out - uncomment to use)
    /*
    'example' => [
        'subdomain' => 'example',
        'name' => 'Example Application',
        'description' => 'An example application to demonstrate multi-app support',
        'config' => [
            'title' => 'Example App',
            'subtitle' => 'Multi-Application Framework Example',
        ],
    ],
    */

    // Add your applications here
    // 'blog' => [
    //     'subdomain' => 'blog',
    //     'name' => 'Blog Application',
    //     'description' => 'Company blog',
    // ],
];
