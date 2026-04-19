<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Helpers\DashboardMenuHelper;
use App\Enums\UserRole;
use Foundation\Core\Session;
use Foundation\Core\Response;

/**
 * DashboardController
 *
 * Unified dashboard controller that dynamically displays content
 * based on the authenticated user's role.
 */
class DashboardController extends Controller
{
    /**
     * Main dashboard entry point
     * Redirects to appropriate dashboard or displays unified view
     */
    public function index(): void
    {
        $user = Session::user();

        if (!$user) {
            // No user session, redirect to login
            Response::redirect('/login');
            return;
        }

        // User is authenticated, display their dashboard
        $this->displayDashboard($user);
    }

    /**
     * Display the unified dashboard view
     *
     * @param array $user The user session data
     */
    protected function displayDashboard(array $user): void
    {
        $role = $user['role'] ?? 'guest';
        $userId = $user['id'] ?? null;

        // Validate role
        if (!UserRole::isValid($role)) {
            $role = UserRole::USER->value;
        }

        // Get menu items for this role
        $menuItems = DashboardMenuHelper::getMenuForRole($role);

        // Get role enum for additional info
        $roleEnum = UserRole::fromString($role);

        // Set title and subtitle for the layout
        Session::set('Title', DashboardMenuHelper::getTitleForRole($roleEnum));
        Session::set('Subtitle', $roleEnum->getLabel());

        // Render the unified dashboard view
        $this->view('dashboard/index', [
            'user' => $user,
            'menuItems' => $menuItems,
            'role' => $roleEnum,
            'userId' => $userId,
        ]);
    }

    /**
     * Admin dashboard (legacy route - redirects to main dashboard)
     * Maintained for backward compatibility
     */
    public function adminDashboard(): void
    {
        $user = Session::user();

        if (!$user) {
            Response::redirect('/login');
            return;
        }

        // Check if user has admin privileges
        $role = UserRole::fromString($user['role'] ?? 'guest');
        if (!$role->isAdmin()) {
            Response::redirect('/dashboard');
            return;
        }

        $this->displayDashboard($user);
    }

    /**
     * User dashboard (legacy route - redirects to main dashboard)
     * Maintained for backward compatibility
     */
    public function userDashboard(): void
    {
        $user = Session::user();

        if (!$user) {
            Response::redirect('/login');
            return;
        }

        $this->displayDashboard($user);
    }

    /**
     * Theme preview route
     * Redirects to visual editor preview
     */
    public function themepreview(): void
    {
        header('Location: /views/editorvisual/preview.php');
        exit;
    }
}
