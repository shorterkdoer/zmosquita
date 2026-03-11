<?php
namespace App\Controllers;

use App\Core\Controller;
use Foundation\Core\Request;

class HomeController extends Controller
{
    public function index()
    {
        $this->view('welcome', [
            'appName' => 'Sistema de Matriculación - CoProBiLP'
        ]);
    }

    public function welcome()
    {
        $this->view('welcome', [
            'appName' => 'Sistema de Matriculación - CoProBiLP'
        ]);
    }
}
