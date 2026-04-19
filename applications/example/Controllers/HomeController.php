<?php

namespace Applications\Example\Controllers;

use Foundation\Crud\Controller;
use Foundation\Core\Request;

/**
 * Home Controller for Example Application
 *
 * This controller demonstrates how to create controllers
 * for additional applications in the framework.
 */
class HomeController extends Controller
{
    /**
     * Display the home page
     *
     * @return void
     */
    public function index(): void
    {
        $this->view('example/home', [
            'title' => 'Example Application',
            'message' => 'Welcome to the example application!'
        ]);
    }

    /**
     * Example route with parameter
     *
     * @param Request $request
     * @param array $params
     * @return void
     */
    public function test(Request $request, array $params): void
    {
        $id = $params[0] ?? 'unknown';

        $this->view('example/test', [
            'title' => 'Test Page',
            'id' => $id,
            'namespace' => __NAMESPACE__,
            'app' => $_SESSION['current_app'] ?? 'default',
        ]);
    }
}
