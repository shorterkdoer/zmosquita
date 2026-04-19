<?php
namespace App\Middlewares;

use App\Enums\UserRole;
use Foundation\Core\Response;
use Foundation\Core\Request;
use Foundation\Core\Session;
use Foundation\Middleware\BaseMiddleware;
use App\Models\User;

/**
 * AuthMiddleware
 *
 * Authentication middleware that checks if a user is logged in.
 * Allows any authenticated role (user, admin, superuser) to pass.
 */
class AuthMiddleware extends BaseMiddleware
{
    /**
     * Handle the middleware check
     *
     * Verifies that a user is authenticated. Any valid authenticated user
     * (user, admin, or superuser) can pass through this middleware.
     */
    public function handle(): void
    {
        // Ensure session is started
        Session::start();

        // Check if user is authenticated
        if (!Session::isAuthenticated()) {
            Response::redirect('/login');
        }
    }

    /**
     * Handle login request
     *
     * Authenticates user credentials and creates session.
     * Redirects to dashboard based on user role.
     */
    public function login(Request $request): void
    {
        // Extract credentials from form
        $email = trim($request->input('email'));
        $password = trim($request->input('password'));

        // Find user by email
        $user = User::findByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            Session::flash('error', 'Credenciales inválidas.');
            Response::redirect('/login');
        }

        // Validate role
        $userRole = $user['role'] ?? 'guest';
        if (!UserRole::isValid($userRole)) {
            Session::flash('error', 'Rol de usuario inválido.');
            Response::redirect('/login');
        }

        // Ensure session is started
        Session::start();

        // Regenerate session for security
        Session::regenerate();

        // Store user data in session
        $_SESSION['user'] = [
            'id' => $user['id'],
            'email' => $user['email'],
            'role' => $userRole
        ];

        // Redirect to unified dashboard (role-based rendering happens there)
        Response::redirect('/dashboard');
    }

    /**
     * Display login form
     *
     * Shows the login page for unauthenticated users.
     */
    public function loginForm(Request $request): void
    {
        // Using app's view rendering, not Foundation's
        $template = $_SESSION['directoriobase'] . '/views/auth/login.php';
        if (file_exists($template)) {
            extract(['error' => Session::flash('error')]);
            require $template;
        } else {
            echo 'Error: Template not found';
        }
        exit;
    }
}
