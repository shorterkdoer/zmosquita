<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;

class DashboardController extends Controller
{
    /**
     * Dashboard para usuarios regulares.
     */


     public function themepreview(): void
     {
         // 1) Obtén al usuario autenticado (según tu sistema)
             // si no está logueado, lo mandas al login
             header('Location: /views/editorvisual/preview.php');
             exit;
         }
 

     public function index(): void
     {
         // 1) Obtén al usuario autenticado (según tu sistema)
         $user = $_SESSION['user'] ?? null;
         if (! $user) {
             // si no está logueado, lo mandas al login
             header('Location: /login');
             exit;
         }
 
         // 2) Comprueba su rol
         if (($user['role'] ?? '') === 'admin') {
             header('Location: /admin-dashboard');
         } else {
             header('Location: /user-dashboard');
         }
         exit;
     }

     public function userDashboard(): void
     {
         // Asegurarse de que la sesión está iniciada.
         if (session_status() === PHP_SESSION_NONE) {
             session_start();
         }
         $user = $_SESSION['user'] ?? null;
 
         // Renderiza la vista del dashboard pasando los datos del usuario.
         $this->view('dashboard/user', ['user' => $user]);
     }
 
     /**
      * Dashboard para administradores.
      */
     public function adminDashboard(): void
     {
         if (session_status() === PHP_SESSION_NONE) {
             session_start();
         }
         $user = $_SESSION['user'] ?? null;
 
         // Aquí se puede verificar que el usuario tiene rol admin, pero el middleware ya se encarga de ello.
         $this->view('dashboard/admin', ['user' => $user]);
     }




}
