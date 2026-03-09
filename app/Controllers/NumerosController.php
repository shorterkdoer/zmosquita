<?php
namespace App\Controllers;



use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;

use App\Core\Session;
use App\Models\Numeros;

// Ensure the User model exists in the App\Models namespace
use App\Core\Controller;
use App\Support\Sanitizer;

class NumerosController extends Controller
{

    public function editnumerosmat(Request $request, array $params): void
    {
         $id = $params[0] ?? null; // Get the ID from the URL parameters

        $datos = Numeros::findByRotulo('Matricula');
        if (!$datos) {
            Session::flash('error', 'Registro no encontrado.');
            $this->redirect('/dashboard');
            return;
        }
        
        $crudstyle = require $_SESSION['directoriobase'] . '/config/cruds/defaults/crudstyle.php';
        $style = $crudstyle['style'] ?? [];
        $cfgcreate = require $_SESSION['directoriobase'] . '/config/cruds/numeros/numeros_ver.php';
        $cfg       = $cfgcreate['config']      ?? [];
        
        //$cfg['url_action'] .= '/' . $id; // <— se agrega el id a la url

        $campos    = $cfgcreate['campos']      ?? [];
        $actividades = $cfgcreate['actividades'] ?? [];
        $comandos    = $cfgcreate['comandos']  ?? [];
        $buttons     = $cfgcreate['buttons']   ?? [];
    
        $this->view('cruds/index', [
            'cfg'      => $cfg,
            'style'    => $style,
            'fields'   => $campos,
            'values'   => $datos,
            'actions'  => $actividades,
            'comandos' => $comandos,
            'buttons'  => $buttons,
            'id'       => $id , // Add the ID here
            'user_id' => $_SESSION['user']['id'], // Add user ID for file upload
        ]);

    }

        public function update(Request $request, array $params): void
    {
        $id = $params[0] ?? null;
        if (!$id) {
            Session::flash('error', 'ID de comprobante no especificado.');
            $this->redirect('/miscomprobantes');
            return;
        }


        if (!Numeros::find($id)) {
            Session::flash('error', 'Registro no encontrado.');
            $this->redirect('/miscomprobantes');
            return;
        }

        Numeros::update($id, ['rotulo' => Sanitizer::text($_POST['rotulo'])]);
        Session::flash('success', 'Numerador actualizado.');
        $this->redirect('/miscomprobantes');
    }


}