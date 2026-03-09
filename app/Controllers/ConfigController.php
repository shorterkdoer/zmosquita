<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Models\Config;

class ConfigController extends Controller
{

    public function editvalores(): void
    {
        // Asegurarse de que la sesión está iniciada.
/*        $this->view('config/valores', [
            'appName' => 'Sistema de Matriculación - CoProBiLP'
        ]);
*/
        }
    public function consultar(Request $request, array $params): void
    {

        //leer los datos de la configuración
        $data = Config::findById(1);
        $crudstyle = require $_SESSION['directoriobase'] . '/config/cruds/defaults/crudstyle.php';
        $style = $crudstyle['style'] ?? [];
        $cfgcreate = require $_SESSION['directoriobase'] . '/config/cruds/config/config_view.php';
        $cfg       = $cfgcreate['config']      ?? [];
        
        $cfg['url_action'] .= '/' . $id; // <— se agrega el id a la url

        $campos    = $cfgcreate['campos']      ?? [];
        $actividades = $cfgcreate['actividades'] ?? [];
        $comandos    = $cfgcreate['comandos']  ?? [];
        $buttons     = $cfgcreate['buttons']   ?? [];
    
        $this->view('cruds/index', [
            'cfg'      => $cfg,
            'style'    => $style,
            'fields'   => $campos,
            'values'   => $data,
            'actions'  => $actividades,
            'comandos' => $comandos,
            'buttons'  => $buttons,
            'id'       => $id,  // Add the ID here
            'user_id' => $_SESSION['user']['id'], // Add user ID for file upload
        ]);
    }


    public function soportesistema(Request $request, array $params): void
    {

        //leer los datos de la configuración
        
        $crudstyle = require $_SESSION['directoriobase'] . '/config/cruds/defaults/crudstyle.php';
        $style = $crudstyle['style'] ?? [];
        $cfgcreate = require $_SESSION['directoriobase'] . '/views/dashboard/info.php';
        $cfg       = $cfgcreate['config']      ?? [];
        
        //$cfg['url_action'] .= '/' . $id; // <— se agrega el id a la url

        $campos    = $cfgcreate['campos']      ?? [];
        $actividades = $cfgcreate['actividades'] ?? [];
        $comandos    = $cfgcreate['comandos']  ?? [];
        $buttons     = $cfgcreate['buttons']   ?? [];
        $data =[
            'mensaje' => 'Para reportar inconvenientes con el sistema, por favor envíenos un correo .',
            'mensaje2' => 'No olvide informar el mail con el que se registró en el sistema si no correspondiera con el está usando.', 
            'mensaje3' => 'Si nos envía una captura de pantalla del error, será más fácil para nosotros ayudarle.',
            'mensaje4' => 'soportesistema@coprobilp.org.ar',
            'mensaje5' => 'Gracias por su colaboración.',

        ];
        $this->view('cruds/index', [
            'cfg'      => $cfg,
            'style'    => $style,
            'fields'   => $campos,
            'values'   => $data,
            'actions'  => $actividades,
            'comandos' => $comandos,
            'buttons'  => $buttons,
            'id'       => $id,  // Add the ID here
            'user_id' => $_SESSION['user']['id'], // Add user ID for file upload
        ]);
    }


}
