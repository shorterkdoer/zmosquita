<?php

namespace App\Controllers;

use App\Core\Controller;
use Foundation\Core\Request;
use App\Models\Comision; 
use Foundation\Core\Session;
use App\Models\Numeros;
use App\Models\DatosPersonales;


class ComisionController extends Controller
{
    public function edit(Request $request, array $params = []): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $user = $_SESSION['user'] ?? null;
        if (!$user) {
            Session::flash('error', 'Debe iniciar sesión.');
            $this->redirect('/login');
        }

        $id = (int)($params[0] ?? 0);
        
        if ($user['role'] == 'user' && $id != $user['id']) {
            Session::flash('error', 'No tiene permiso para editar estos datos.');
            $this->redirect('/user-dashboard');
        }
        
        // Busca la matrícula asociada al usuario.
        $comision = Comision::findById($id);
        if (!$comision) {
            Session::flash('error', 'No existe.');
            $this->redirect('/user-dashboard');
        }
        $crudstyle = require $_SESSION['directoriobase'] . '/config/cruds/defaults/crudstyle.php';
        $style = $crudstyle['style'] ?? [];
        if($matricula['freezedata'] == null) {
            $cfgedit     = require $_SESSION['directoriobase'] . '/config/cruds/comision/comision_edit.php';
        }else{
            $cfgedit     = require $_SESSION['directoriobase'] . '/config/cruds/comision/comision_review.php';

        }
        $cfg         = $cfgedit['config']    ?? [];
        $cfg['url_action'] .= '/' . $id; // <— se agrega el id a la url
        $campos      = $cfgedit['campos']    ?? [];
        $actividades = $cfgedit['actividades'] ?? [];
        $comandos    = $cfgedit['comandos']  ?? [];
        $buttons     = $cfgedit['buttons']   ?? [];

        $this->view('cruds/index', [
            'cfg'      => $cfg,
            'style'    => $style,
            'fields'   => $campos,     // <— coherente con index/create
            'values'   => $matricula,       // array simple con claves=>valores
            'actions'  => $actividades,
            'comandos' => $comandos,
            'buttons'  => $buttons,
            'id'      => $id,
            'user_id' => $id,
        ]);

    }
    public function store(Request $request, array $params = []): void
    {

    }

    public function update(Request $request, array $params = []): void
    {
        // Implementación del método update
        // Aquí se procesaría la actualización de la comisión
    }
    
}
