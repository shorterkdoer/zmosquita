<?php

use Foundation\Core\Router;

/**
 * Routes for Example Application
 *
 * These routes are only loaded when accessing the example application
 * via the configured subdomain.
 */

// Home page
Router::get('/', ['HomeController', 'index']);

// Example route with parameter
Router::get('/test/{id}', ['HomeController', 'test']);
