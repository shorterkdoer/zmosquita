<?php
namespace App\Middlewares;

use Foundation\Core\Response;
use Foundation\Core\Request;
use Foundation\Core\Session;
use Foundation\Middleware\BaseMiddleware;
use App\Models\User;

class AuthMiddleware extends BaseMiddleware
{
    /**
     * Handle the middleware check
     */
    public function handle(): void
    {
        // Ejemplo sencillo: Si no hay user_id en la sesión, redirigimos al login
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user']['id'])) {
            Response::redirect('/login');
        }
        /*
        if (($_SESSION['user']['role']) == 'admin') {
            Response::redirect('/admin-dashboard');
        }
        if (($_SESSION['user']['role']) == 'user') {
            Response::redirect('/user-dashboard');
        }
            */
    }

    public function login(Request $request): void
    {
        // Extraer credenciales del formulario
        $email = trim($request->input('email'));
        $password = trim($request->input('password'));

        // Usar el método estático findByEmail ya que está definido como static

        $user = User::findByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            Session::flash('error', 'Credenciales inválidas.');
            Response::redirect('/login');
        }

        // Iniciar sesión y almacenar los datos del usuario
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Guardar en sesión un array con los datos del usuario
        $_SESSION['user'] = [
            'id'    => $user['id'],
            'email' => $user['email'],
            'role'  => $user['role']
        ];

        // Redireccionar según el rol del usuario
        if ($user['role'] === 'admin') {
            Response::redirect('/admin-dashboard');
        } else {
            Response::redirect('/user-dashboard');
        }
    }

    /**
     * Muestra el formulario de login
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
