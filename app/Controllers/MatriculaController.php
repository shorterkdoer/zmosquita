<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Models\Matricula;
use App\Core\Session;
use App\Models\ComprobantesPago;
use App\Models\Numeros;
use App\Models\DatosPersonales;
use App\Models\Comision;
use App\Models\Tramites;
use App\Models\User;
use FPDF\FPDF;
use App\Core\Sanitizer;


class MatriculaController extends Controller
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
        
        if ($user['role'] == 'admin' || $id == $user['id']) {
            //puede editar
        }
        else{
            Session::flash('error', 'No tiene permiso para editar estos datos.');
            $this->redirect('/user-dashboard');
        }
        
        // Busca la matrícula asociada al usuario.
        $matricula = Matricula::findByUserId($id);
        if (!$matricula) {
            // Si el registro no existe, crearlo (esto se puede hacer en la activación también)
            Matricula::create(['user_id' => $user['id']]);
            $matricula = Matricula::findByUserId($id);
        }
        $crudstyle = require $_SESSION['directoriobase'] . '/config/cruds/defaults/crudstyle.php';
        $style = $crudstyle['style'] ?? [];
        
        // Matricula::statusmatricula($id) devuelve '' - Baja - Revisión - Verificado - Activa - Solicitada

        if (Matricula::statusmatricula($id) == '') {
            $cfgedit     = require $_SESSION['directoriobase'] . '/config/cruds/matricula/matricula_edit.php';
        }else{
            $cfgedit     = require $_SESSION['directoriobase'] . '/config/cruds/matricula/matricula_review.php';

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
    
    public function matriculacion(Request $request, array $params = []): void
    
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $user = $_SESSION['user'] ?? null;
        if (!$user) {
            Session::flash('error', 'Debe iniciar sesión.');
            $this->redirect('/login');
        }

        $this->redirect('/opcionmatricula');

        //$this->view('matriculas/edit', ['matricula' => $matricula]);
    }

    public function matric_index(): void
    {
        // 1) Obtén al usuario autenticado (según tu sistema)
        $user = $_SESSION['user'] ?? null;
        if (! $user) {
            // si no está logueado, lo mandas al login
            header('Location: /login');
            exit;
        }

        // 2) Comprueba su rol
        if (($user['role'] ?? '') <> 'admin') {
            header('Location: /menumatriculacion');
        }
        exit;
    }

    public function menu_matric(): void
    {
        // Asegurarse de que la sesión está iniciada.
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $user = $_SESSION['user'] ?? null;

        // Renderiza la vista del dashboard pasando los datos del usuario.
        $this->view('dashboard/menumatricula', ['user' => $user]);
    }

    public function edit_rematric(Request $request, array $params = []): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $user = $_SESSION['user']['id'] ?? 0;
        if ($user == 0) {
            Session::flash('error', 'Debe iniciar sesión.');
            $this->redirect('/login');
        }

// [[]]
        if ($user['role'] === 'admin' ) {
            //puede editar
        }
        else{
            Session::flash('error', 'No tiene permiso para editar estos datos.');
            $this->redirect('/user-dashboard');
        }


        //$id = (int)($params[0] ?? 0);
        $id = $user;
        /*
        if ($user['role'] == 'user' && $id != $user['id']) {
            Session::flash('error', 'No tiene permiso para editar estos datos.');
            $this->redirect('/user-dashboard');
        }
        */
        // Busca la matrícula asociada al usuario.
        $matricula = Matricula::findByUserId($id);
        if (!$matricula) {
            // Si el registro no existe, crearlo (esto se puede hacer en la activación también)
            Matricula::create(['user_id' => $id, 'interviniente' => 0]);
            $matricula = Matricula::findByUserId($id);
        }
        $crudstyle = require $_SESSION['directoriobase'] . '/config/cruds/defaults/crudstyle.php';
        $style = $crudstyle['style'] ?? [];

        if (Matricula::statusmatricula($id) === '') {
            $cfgedit     = require $_SESSION['directoriobase'] . '/config/cruds/matricula/matricula_edit_re.php';
        }else{
            $cfgedit     = require $_SESSION['directoriobase'] . '/config/cruds/matricula/matricula_review.php';

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

    public function updaterem(Request $request, array $params = []): void
    {

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $urlanterior =  $_SERVER['HTTP_REFERER'];
        $user = $_SESSION['user'] ?? null;
        if (!$user) {
            Session::flash('error', 'Debe iniciar sesión.');
            $this->redirect('/login');
        }

        $id = (int)($params[0] ?? 0);

        if ( $id !== $user['id']) {
            Session::flash('error', 'No tiene permiso para editar estos datos.');
            $this->redirect('/user-dashboard');
        }

        // Recupera el registro existente.
        $userId = $id;

        
        
        // Procesa los campos de texto.
        $data = $_POST;
        //$data['notaddjj'] = trim($request->input('notaddjj'));
        // Procesa los demás campos de texto si existen...
        
        // Lista de campos de archivo en la tabla "matriculas".
        $fileFields = [
	        'matriculaministerio',
            'notaddjj',
            'dnifrente',
            'dnidorso',
            'titulooriginalfrente',
            'titulooriginaldorso',
            'fotoregistrodegraduados',
            'fotocarnet',
            'antecedentespenales',
            'libredeudaalimentario',
            'constanciaCUIL',
            
        ];
        
        // Asegúrate de que la sesión esté iniciada y obtén el id del usuario.
        /*
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $user = $_SESSION['user'] ?? null;
        if (!$user) {
            Session::flash('error', 'Debe estar logueado para realizar esta acción.');
            $this->redirect('/login');
        }
        $userId = $user['id'];
        */
        // Obtén la carpeta exclusiva del usuario para almacenar archivos.
        $uploadFolder = $this->getUserUploadFolder($userId);
        
        // Para cada campo de archivo, si se envía un archivo, procesa la carga.
        $allowedExtensions = ['pdf', 'png', 'jpg', 'jpeg'];
        foreach ($fileFields as $field) {
            if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
                //eliminar espacios en blanco del nombre del archivo
                $_FILES[$field]['name'] = preg_replace('/[ %()#@$!&+-]/', '_', $_FILES[$field]['name']);

                $originalName = $_FILES[$field]['name'];
                $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
                if (in_array($extension, $allowedExtensions)) {
                    // Genera un nombre único de archivo.
                    //$newFileName = uniqid($field . '_', true) . '.' . $extension;
                    $bytesAleatorios = random_bytes(10);
                    $postname = bin2hex($bytesAleatorios);
                    $xxnombre = pathinfo($originalName, PATHINFO_FILENAME);
                    $xxextension = pathinfo($originalName, PATHINFO_EXTENSION);
                    //$newFileName = $originalName;
                    $newFileName = $xxnombre . '_' . $postname . '.' . $xxextension;
                    $destination = $uploadFolder . $newFileName;
                    // Mueve el archivo subido al destino.
                    if (move_uploaded_file($_FILES[$field]['tmp_name'], $destination)) {
                        // Guarda el nombre del archivo (o una ruta relativa) en el registro.
                        $data[$field] = $newFileName;
                    } else {
                        Session::flash('error', "Error subiendo el archivo para '$field'.");
                        $this->redirect($urlanterior);
                    }
                } else {
                    Session::flash('error', "El archivo para '$field' debe ser de tipo pdf, png, jpg o jpeg.");
                    $this->redirect($urlanterior);
                }
            }
        }
        
        // Actualiza el registro con los nuevos datos.
        Matricula::updatebyUser($userId, $data);
        Session::flash('success', 'Datos actualizados correctamente.');
        $this->redirect('/rematricula');
    }

    public function edit_first(Request $request, array $params = []): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $user = $_SESSION['user'] ?? null;
        if (!$user) {
            Session::flash('error', 'Debe iniciar sesión.');
            $this->redirect('/login');
        }

        //$id = (int)($params[0] ?? 0);
        $id = $user['id'];
        if ( $id !== $user['id']) {
            Session::flash('error', 'No tiene permiso para editar estos datos.');
            $this->redirect('/dashboard');
        }
        
        // Busca la matrícula asociada al usuario.
        $matricula = Matricula::findByUserId($id);
        if (!$matricula) {
            // Si el registro no existe, crearlo (esto se puede hacer en la activación también)
            Matricula::create(['user_id' => $id, 'interviniente' => 0]);
            $matricula = Matricula::findByUserId($user['id']);
        }
        $crudstyle = require $_SESSION['directoriobase'] . '/config/cruds/defaults/crudstyle.php';
        $style = $crudstyle['style'] ?? [];
        if (Matricula::statusmatricula($id) == '') {
            $cfgedit     = require $_SESSION['directoriobase'] . '/config/cruds/matricula/matricula_edit_pri.php';
        }else{
            $cfgedit     = require $_SESSION['directoriobase'] . '/config/cruds/matricula/matricula_review.php';

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

    public function updatefirst(Request $request, array $params = []): void
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
        
        if ( $id != $user['id']) {
            Session::flash('error', 'No tiene permiso para editar estos datos.');
            $this->redirect('/dashboard');
        }

        // Recupera el registro existente.
        $userId = $id;
        $urlanterior = self::getRefererPath();
        
        
        // Procesa los campos de texto.
        $data = $_POST;
        //$data['notaddjj'] = trim($request->input('notaddjj'));
        // Procesa los demás campos de texto si existen...
        
        // Lista de campos de archivo en la tabla "matriculas".
        $fileFields = [
            'notaddjj',
            'dnifrente',
            'dnidorso',
            'titulooriginalfrente',
            'titulooriginaldorso', 
            'fotocarnet',
            'antecedentespenales',
            'libredeudaalimentario',
            'constanciaCUIL',
            
        ];
        
        // Asegúrate de que la sesión esté iniciada y obtén el id del usuario.
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $user = $_SESSION['user'] ?? null;
        if (!$user) {
            Session::flash('error', 'Debe estar logueado para realizar esta acción.');
            $this->redirect('/login');
        }
        $userId = $user['id'];
        // Obtén la carpeta exclusiva del usuario para almacenar archivos.
        $uploadFolder = $this->getUserUploadFolder($userId);
        
        // Para cada campo de archivo, si se envía un archivo, procesa la carga.
        $allowedExtensions = ['pdf', 'png', 'jpg', 'jpeg'];
        foreach ($fileFields as $field) {
            if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
                //eliminar espacios en blanco del nombre del archivo
                $_FILES[$field]['name'] = preg_replace('/[ %()#@$!&+-]/', '_', $_FILES[$field]['name']);

                $originalName = $_FILES[$field]['name'];
                $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
                if (in_array($extension, $allowedExtensions)) {
                    // Genera un nombre único de archivo.
                    //$newFileName = uniqid($field . '_', true) . '.' . $extension;
                    //$newFileName = $originalName;
                    //$destination = $uploadFolder . $newFileName;
                    $bytesAleatorios = random_bytes(10);
                    $postname = bin2hex($bytesAleatorios);
                    $xxnombre = pathinfo($originalName, PATHINFO_FILENAME);
                    $xxextension = pathinfo($originalName, PATHINFO_EXTENSION);
                    //$newFileName = $originalName;
                    $newFileName = $xxnombre . '_' . $postname . '.' . $xxextension;
                    $destination = $uploadFolder . $newFileName;



                    // Mueve el archivo subido al destino.
                    if (move_uploaded_file($_FILES[$field]['tmp_name'], $destination)) {
                        // Guarda el nombre del archivo (o una ruta relativa) en el registro.
                        $data[$field] = $newFileName;
                    } else {
                        Session::flash('error', "Error subiendo el archivo para '$field'.");
                        $this->redirect($urlanterior);
                    }
                } else {
                    Session::flash('error', "El archivo para '$field' debe ser de tipo pdf, png, jpg o jpeg.");
                    $this->redirect($urlanterior);
                }
            }
        }
        
        // Actualiza el registro con los nuevos datos.
        Matricula::updatebyUser($userId, $data);
        Session::flash('success', 'Datos actualizados correctamente.');
        $this->redirect('/primeramatricula');
    }




    public function edit_prov(Request $request, array $params = []): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $user = $_SESSION['user'] ?? null;
        if (!$user) {
            Session::flash('error', 'Debe iniciar sesión.');
            $this->redirect('/login');
        }

        //$id = (int)($params[0] ?? 0);
        $id = $user['id'];
        if ($id !== $user['id']) {
            Session::flash('error', 'No tiene permiso para editar estos datos.');
            $this->redirect('/dashboard');
        }
        
        // Busca la matrícula asociada al usuario.
        $matricula = Matricula::findByUserId($id);
        if (!$matricula) {
            // Si el registro no existe, crearlo (esto se puede hacer en la activación también)
            Matricula::create(['user_id' => $id, 'interviniente' => 0]);
            $matricula = Matricula::findByUserId($user['id']);
        }
        $crudstyle = require $_SESSION['directoriobase'] . '/config/cruds/defaults/crudstyle.php';
        $style = $crudstyle['style'] ?? [];
        if (Matricula::statusmatricula($id) == '') {
            $cfgedit     = require $_SESSION['directoriobase'] . '/config/cruds/matricula/matricula_edit_prov.php';
        }else{
            $cfgedit     = require $_SESSION['directoriobase'] . '/config/cruds/matricula/matricula_review.php';

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

    public function updateprov(Request $request, array $params = []): void
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
        
        if ( $id !== $user['id']) {
            Session::flash('error', 'No tiene permiso para editar estos datos.');
            $this->redirect('/dashboard');
        }

        // Recupera el registro existente.
        $userId = $id;
        $urlanterior= self::getRefererPath();
        
        
        // Procesa los campos de texto.
        $data = $_POST;
        //$data['notaddjj'] = trim($request->input('notaddjj'));
        // Procesa los demás campos de texto si existen...
        
        // Lista de campos de archivo en la tabla "matriculas".
        $fileFields = [
            'matriculaprevia',
            'certificadoetica',
            'notaddjj',
            'dnifrente',
            'dnidorso',
            'titulooriginalfrente',
            'titulooriginaldorso', 
            'fotocarnet',
            'antecedentespenales',
            'libredeudaalimentario',
            'constanciaCUIL',

        ];
        
        // Asegúrate de que la sesión esté iniciada y obtén el id del usuario.
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $user = $_SESSION['user'] ?? null;
        if (!$user) {
            Session::flash('error', 'Debe estar logueado para realizar esta acción.');
            $this->redirect('/login');
        }
        $userId = $user['id'];
        // Obtén la carpeta exclusiva del usuario para almacenar archivos.
        $uploadFolder = $this->getUserUploadFolder($userId);
        
        // Para cada campo de archivo, si se envía un archivo, procesa la carga.
        $allowedExtensions = ['pdf', 'png', 'jpg', 'jpeg'];
        foreach ($fileFields as $field) {
            if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
                //eliminar espacios en blanco del nombre del archivo
                $_FILES[$field]['name'] = preg_replace('/[ %()#@$!&+-]/', '_', $_FILES[$field]['name']);
                $originalName = $_FILES[$field]['name'];
                $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
                if (in_array($extension, $allowedExtensions)) {
                    // Genera un nombre único de archivo.
                    //$newFileName = uniqid($field . '_', true) . '.' . $extension;
                    //$newFileName = $originalName;
                    //$destination = $uploadFolder . $newFileName;
                    $bytesAleatorios = random_bytes(10);
                    $postname = bin2hex($bytesAleatorios);
                    $xxnombre = pathinfo($originalName, PATHINFO_FILENAME);
                    $xxextension = pathinfo($originalName, PATHINFO_EXTENSION);
                    //$newFileName = $originalName;
                    $newFileName = $xxnombre . '_' . $postname . '.' . $xxextension;
                    $destination = $uploadFolder . $newFileName;



                    // Mueve el archivo subido al destino.
                    if (move_uploaded_file($_FILES[$field]['tmp_name'], $destination)) {
                        // Guarda el nombre del archivo (o una ruta relativa) en el registro.
                        $data[$field] = $newFileName;
                    } else {
                        Session::flash('error', "Error subiendo el archivo para '$field'.");
                        $this->redirect($urlanterior);
                    }
                } else {
                    Session::flash('error', "El archivo para '$field' debe ser de tipo pdf, png, jpg o jpeg.");
                    $this->redirect($urlanterior);
                }
            }
        }
        
        // Actualiza el registro con los nuevos datos.
        Matricula::updatebyUser($userId, $data);
        Session::flash('success', 'Datos actualizados correctamente.');
        $this->redirect('/previamatricula');
    }




    public function edit_extranjero(Request $request, array $params = []): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $user = $_SESSION['user'] ?? null;
        if (!$user) {
            Session::flash('error', 'Debe iniciar sesión.');
            $this->redirect('/login');
        }

        //$id = (int)($params[0] ?? 0);
        $id = $user['id'];
        if ($id !== $user['id']) {
            Session::flash('error', 'No tiene permiso para editar estos datos.');
            $this->redirect('/dashboard');
        }
        
        // Busca la matrícula asociada al usuario.
        $matricula = Matricula::findByUserId($id);
        if (!$matricula) {
            // Si el registro no existe, crearlo (esto se puede hacer en la activación también)
            Matricula::create(['user_id' => $id, 'interviniente' => 0]);
            $matricula = Matricula::findByUserId($user['id']);
        }
        $crudstyle = require $_SESSION['directoriobase'] . '/config/cruds/defaults/crudstyle.php';
        $style = $crudstyle['style'] ?? [];
        if (Matricula::statusmatricula($id) == '') {
            $cfgedit     = require $_SESSION['directoriobase'] . '/config/cruds/matricula/matricula_edit_extr.php';
        }else{
            $cfgedit     = require $_SESSION['directoriobase'] . '/config/cruds/matricula/matricula_review.php';

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

    public function updateextranjero(Request $request, array $params = []): void
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
        
        if ($id !== $user['id']) {
            Session::flash('error', 'No tiene permiso para editar estos datos.');
            $this->redirect('/dashboard');
        }

        // Recupera el registro existente.
        $userId = $id;

        $urlanterior = self::getRefererPath();
        
        // Procesa los campos de texto.
        $data = $_POST;
        //$data['notaddjj'] = trim($request->input('notaddjj'));
        // Procesa los demás campos de texto si existen...
        
        // Lista de campos de archivo en la tabla "matriculas".
        $fileFields = [
            'apostillado',
            'notaddjj',
            'dnifrente',
            'dnidorso',
            'titulooriginalfrente',
            'titulooriginaldorso', 
            'fotocarnet',
            'antecedentespenales',
            'libredeudaalimentario',
            'constanciaCUIL',

            
        ];
        
        // Asegúrate de que la sesión esté iniciada y obtén el id del usuario.
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $user = $_SESSION['user'] ?? null;
        if (!$user) {
            Session::flash('error', 'Debe estar logueado para realizar esta acción.');
            $this->redirect('/login');
        }
        $userId = $user['id'];
        // Obtén la carpeta exclusiva del usuario para almacenar archivos.
        $uploadFolder = $this->getUserUploadFolder($userId);
        
        // Para cada campo de archivo, si se envía un archivo, procesa la carga.
        $allowedExtensions = ['pdf', 'png', 'jpg', 'jpeg'];
        foreach ($fileFields as $field) {
            if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
                //eliminar espacios en blanco del nombre del archivo
                $_FILES[$field]['name'] = preg_replace('/[ %()#@$!&+-]/', '_', $_FILES[$field]['name']);
                $originalName = $_FILES[$field]['name'];
                $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
                if (in_array($extension, $allowedExtensions)) {
                    // Genera un nombre único de archivo.
                    //$newFileName = uniqid($field . '_', true) . '.' . $extension;
                    //$newFileName = $originalName;
                    //$destination = $uploadFolder . $newFileName;
                    $bytesAleatorios = random_bytes(10);
                    $postname = bin2hex($bytesAleatorios);
                    $xxnombre = pathinfo($originalName, PATHINFO_FILENAME);
                    $xxextension = pathinfo($originalName, PATHINFO_EXTENSION);
                    //$newFileName = $originalName;
                    $newFileName = $xxnombre . '_' . $postname . '.' . $xxextension;
                    $destination = $uploadFolder . $newFileName;

                    // Mueve el archivo subido al destino.
                    if (move_uploaded_file($_FILES[$field]['tmp_name'], $destination)) {
                        // Guarda el nombre del archivo (o una ruta relativa) en el registro.
                        $data[$field] = $newFileName;
                    } else {
                        Session::flash('error', "Error subiendo el archivo para '$field'.");
                        $this->redirect($urlanterior);
                    }
                } else {
                    Session::flash('error', "El archivo para '$field' debe ser de tipo pdf, png, jpg o jpeg.");
                    $this->redirect($urlanterior);
                }
            }
        }
        
        // Actualiza el registro con los nuevos datos.
        Matricula::updatebyUser($userId, $data);
        Session::flash('success', 'Datos actualizados correctamente.');
        $this->redirect('/titulodeotranacion');
    }








    /**
     * Actualiza el registro de matrícula y procesa la carga de archivos.
     * Se espera que la URL sea algo como: /matriculas/update/{id}
     */
    public function update(Request $request, array $params = []): void
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
        
        if ($id != $user['id']) {
            Session::flash('error', 'No tiene permiso para editar estos datos.');
            $this->redirect('/dashboard');
        }

        // Recupera el registro existente.
        $userId = $id;

        $urlanterior = self::getRefererPath();
        
        // Procesa los campos de texto.
        $data = $_POST;
        //$data['notaddjj'] = trim($request->input('notaddjj'));
        // Procesa los demás campos de texto si existen...
        
        // Lista de campos de archivo en la tabla "matriculas".
        $fileFields = [
            'notaddjj',
            'dnifrente',
            'dnidorso',
            'titulooriginalfrente',
            'titulooriginaldorso',
            'fotoregistrodegraduados',
            'fotocarnet',
            'antecedentespenales',
            'libredeudaalimentario',
            'constanciaCUIL',
            'apostillado',
            'matriculaprevia',
            'certificadoetica'
        ];
        
        // Asegúrate de que la sesión esté iniciada y obtén el id del usuario.
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $user = $_SESSION['user'] ?? null;
        if (!$user) {
            Session::flash('error', 'Debe estar logueado para realizar esta acción.');
            $this->redirect('/login');
        }
        $userId = $user['id'];
        // Obtén la carpeta exclusiva del usuario para almacenar archivos.
        $uploadFolder = $this->getUserUploadFolder($userId);
        
        // Para cada campo de archivo, si se envía un archivo, procesa la carga.
        $allowedExtensions = ['pdf', 'png', 'jpg', 'jpeg'];
        foreach ($fileFields as $field) {
            if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
                //eliminar espacios en blanco del nombre del archivo
                $_FILES[$field]['name'] = preg_replace('/[ %()#@$!&+-]/', '_', $_FILES[$field]['name']);
                $originalName = $_FILES[$field]['name'];
                $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
                if (in_array($extension, $allowedExtensions)) {
                    // Genera un nombre único de archivo.
                    //$newFileName = uniqid($field . '_', true) . '.' . $extension;
                    //$newFileName = $originalName;
                    //$destination = $uploadFolder . $newFileName;
                    $bytesAleatorios = random_bytes(10);
                    $postname = bin2hex($bytesAleatorios);
                    $xxnombre = pathinfo($originalName, PATHINFO_FILENAME);
                    $xxextension = pathinfo($originalName, PATHINFO_EXTENSION);
                    //$newFileName = $originalName;
                    $newFileName = $xxnombre . '_' . $postname . '.' . $xxextension;
                    $destination = $uploadFolder . $newFileName;

                    // Mueve el archivo subido al destino.
                    if (move_uploaded_file($_FILES[$field]['tmp_name'], $destination)) {
                        // Guarda el nombre del archivo (o una ruta relativa) en el registro.
                        $data[$field] = $newFileName;
                    } else {
                        Session::flash('error', "Error subiendo el archivo para '$field'.");
                        $this->redirect($urlanterior);
                    }
                } else {
                    Session::flash('error', "El archivo para '$field' debe ser de tipo pdf, png, jpg o jpeg.");
                    $this->redirect($urlanterior);
                }
            }
        }
        
        // Actualiza el registro con los nuevos datos.
        Matricula::updatebyUser($userId, $data);
        Session::flash('success', 'Datos actualizados correctamente.');
        $this->redirect('/dashboard');
    }
    
    /**
     * Retorna la ruta completa a la carpeta exclusiva del usuario.
     * La carpeta tendrá un nombre obscuro calculado mediante md5.
     * Se almacenarán los archivos en una carpeta fuera del directorio público.
     *
     * @param int $userId
     * @return string Ruta completa.
     */
    



    public function infoaltas(Request $request, array $params = []): void
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
            $this->redirect('/dashboard');
        }
        
        // Busca la matrícula asociada al usuario.
        $matricula = Matricula::findByUserId($id);
        if (!$matricula) {
            // Si el registro no existe, crearlo (esto se puede hacer en la activación también)
            Matricula::create(['user_id' => $user['id']]);
            $matricula = Matricula::findByUserId($user['id']);
        }
        $crudstyle = require $_SESSION['directoriobase'] . '/config/cruds/defaults/crudstyle.php';
        $style = $crudstyle['style'] ?? [];
        $cfgedit     = require $_SESSION['directoriobase'] . '/config/actions/matriculasdealta.php';
        $cfg         = $cfgedit['config']    ?? [];
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





        //$this->view('matriculas/edit', ['matricula' => $matricula]);
    }

    public function mostraradjunto(Request $request, array $params = []):void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Include the file viewer functionality
        require_once $_SESSION['directoriobase'] . '/config/actions/veradjunto.php';
        
        $user = $_SESSION['user'] ?? null;
        if (!$user) {
            Session::flash('error', 'Debe iniciar sesión.');
            $this->redirect('/login');
        }

        $id = $_SESSION["idrec"];
        $myadj = (string)($params[0] ?? '');
        
        if (empty($myadj)) {
            Session::flash('error', 'No se especificó ningún archivo.');
            $this->redirect('/dashboard');
        }

        $rutaaladjunto = $this->getUserFolder($id);
        
        // Call renderFileViewer with correct parameters
        // The function expects (filePath, baseUrl, useThumbs)
        renderFileViewer($myadj, $rutaaladjunto, false);
    }

    public function reportealtas(Request $request, array $params = []): void
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
            $this->redirect('/dashboard');
        }
        // crear la llamada al query para mostrar los datos

    }

// función para otorgar  la matrícula a un usuario
    public function matristatus(Request $request, array $params = []): void
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
        //$id = $user['id'];
        

        if (Comision::espresidente($user['id']) || Comision::esvicepresidente($user['id'])) {
            //son las credenciales necesarias para editar el estado de la matrícula
        }
        else{
            Session::flash('error', 'No tiene permiso para editar estos datos.');
            $this->redirect('/dashboard');
        }        
        // Busca la matrícula asociada al usuario.
        $matricula = Matricula::findByUserId($id);
        if (!$matricula) {
            // Si el registro no existe, crearlo (esto se puede hacer en la activación también)
            Matricula::create(['user_id' => $user['id']]);
            $matricula = Matricula::findByUserId($user['id']);
        }
        if($matricula['matriculaministerio'] <> null && $matricula['matriculaministerio'] <> '') {
                $matricula['matriculaasignada'] = $matricula['matriculaministerio'];
        } else {
            $numeros = Numeros::findByRotulo('Matricula');
            if (!$numeros) {
                Session::flash('error', 'No se encontró el número de matrícula.');
                $this->redirect('/dashboard');
            }
            $siguientemat = $numeros['valor'] + 1;
			$matricula['matriculaasignada'] = $siguientemat; // Agrega el siguiente número de matrícula al array de valores
        }


        $crudstyle = require $_SESSION['directoriobase'] . '/config/cruds/defaults/crudstyle.php';
        $style = $crudstyle['style'] ?? [];
        $cfgedit     = require $_SESSION['directoriobase'] . '/config/cruds/matricula/matriculaestado.php';
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

    public function formcarnet(Request $request): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $user = $_SESSION['user'] ?? null;
        if (!$user) {
            Session::flash('error', 'Debe iniciar sesión.');
            $this->redirect('/login');
        }

        
        $id = $user['id'];
        

        // Busca la matrícula asociada al usuario.
        $matricula = Matricula::findByUserId($id);
        if (!$matricula) {
            // Si el registro no existe, crearlo (esto se puede hacer en la activación también)
            Matricula::create(['user_id' => $user['id']]);
            $matricula = Matricula::findByUserId($user['id']);
        }
        if($matricula['matriculaasignada'] == null){ 
                Session::flash('error', 'No se encontró el número de matrícula.');
                $this->redirect('/dashboard');
        }
        $userfolder = $this->getUserFolder($id);

        $pdfFile = $_SESSION['directoriobase'] . '/' .$userfolder .'credencial_' . $matricula['matriculaasignada'] . '.pdf';
        $pngFile = $_SESSION['directoriobase'] . '/' .$userfolder .'credencial_' . $matricula['matriculaasignada'] . '.png';


        if(!file_exists($pdfFile)){
                Session::flash('error', 'Solicite reemisión del carnet.');
                $this->redirect($_SERVER['HTTP_REFERER']);
        }
        if(!file_exists($pngFile)){
                Session::flash('error', 'Solicite reemisión del carnet.');
                $this->redirect($_SERVER['HTTP_REFERER']);
        }



        $crudstyle = require $_SESSION['directoriobase'] . '/config/cruds/defaults/crudstyle.php';
        $style = $crudstyle['style'] ?? [];
        $cfgedit     = require $_SESSION['directoriobase'] . '/config/cruds/matricula/vistacredencial.php';
        $cfg         = $cfgedit['config']    ?? [];
        //$cfg['url_action'] .= '/' . $id; // <— se agrega el id a la url
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





    public function grabarmatricula(Request $request, array $params = []): void
    {

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $user = $_SESSION['user'] ?? null;
        if (!$user) {
            Session::flash('error', 'Debe iniciar sesion.');
            $this->redirect('/login');
        }
        $id = (int)($params[0] ?? 0);
        if ( !(Comision::espresidente($user['id']) || Comision::esvicepresidente($user['id']))) {
            Session::flash('error', 'No tiene privilegios.');
                   
            $this->redirect('/dashboard');
        }
        // Procesa los campos de texto.
        $data = $_POST;

        //$data['notaddjj'] = trim($request->input('notaddjj'));
        // Procesa los demás campos de texto si existen...

        //Matricula::findbyAsignada($_POST['matriculaasignada']);
        
        if (Matricula::findByAsignada((int)$_POST['matriculaasignada'])) {
            
            Session::flash('error', 'El número de matrícula ya está asignado a otro usuario.');
            $this->redirect('/estadomatricula/' . $id);
        }
        $mfecha = $_POST['aprobado'];
        $fecha_actual = date("Y/m/d");

        if ($mfecha == null || $mfecha == '') {
            
            Session::flash('error', 'Verifique la fecha.');
            $this->redirect('/estadomatricula/' . $id);
        }

        // Calcular la diferencia en días entre las dos fechas
        if (strtotime($mfecha) > strtotime($fecha_actual)) {
            Session::flash('error', 'La fecha de aprobación no puede ser posdatada.');
            $this->redirect('/estadomatricula/' . $id);
        }
        if ((strtotime($mfecha) == strtotime($fecha_actual)) && (strtotime($mfecha) == 0)) {
            Session::flash('error', 'Hay un error en el formato de fechas.');
            $this->redirect('/estadomatricula/' . $id);

        }

        $dias = (strtotime($mfecha)-strtotime($fecha_actual))/86400;
        $dias = abs($dias); 
        $dias = floor($dias);

                // 2. Calcular la diferencia entre las fechas
        $diferencia = $dias;


        $datos = Comision::activa();

        $data['aprobado'] = $mfecha;  // Por defecto, se establece como 'no' hasta que se apruebe.
        // Lista de campos de archivo en la tabla "matriculas".
        $data['funcionario'] = $user['id']; // Asigna el id del funcionario que otorga presi o vice
        $data['comisionotorgante'] = $datos['id']; // Asigna el id de la comisión que otorga la matrícula
        $data['matriculaasignada'] = $_POST['matriculaasignada']; // Número de matrícula asignada
/*
        $fileFields = [
            'matriculaasignada',
            'aprobado',
            'funcionario',
            'comisionotorgante', 
            'carnet'
        ];
*/
        // Asegúrate de que la sesión esté iniciada y obtén el id del usuario.
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if($data['matriculaasignada'] == '') {
            Session::flash('error', 'El número de matrícula no puede estar vacío.');
            $this->redirect('/estadomatricula/' . $id);
        }

        Matricula::updatebyUser($id, $data);

        $ultimamatricula = Numeros::findByRotulo('Matricula');
        if($_POST['matriculaasignada'] > $ultimamatricula) {
            Numeros::updatebyRotulo($_POST['matriculaasignada'], 'Matricula' );
        }
        $mmatricula = $_POST['matriculaasignada'];
        $user = $_SESSION['user']['id']; // ya se sabe que es el presi o el vice
        $nombrerevisor = DatosPersonales::GetNombreById($data['funcionario']);
        $observaciones = 'Intervino: ' . $nombrerevisor . ' Matricula Otorgada ';
        Tramites::CustomQry("Insert into tramites (user_id, fecha, observaciones) values ($id, '". $mfecha ."'" .", '". $observaciones ."')");  ;

        //$numeromatricula = Matricula::getMatriculaIdById($id);

        error_log('Generando credencial para usuario ' . $id);
/*
        // *-*-
        $data2 = [];
        $data2['carnet'] = 'credencial_' . $_POST['matriculaasignada'] . '.png'; // Nombre del archivo de la credencial
        $data2['carnetpdf'] = 'credencial_' . $_POST['matriculaasignada'] . '.pdf'; // Nombre del archivo de la credencial


        //DatosPersonalesController::generarcredenciales($mmatricula);
        
//'credencial_' . $matricula['matriculaasignada'] . '.pdf'

        error_log('Graba los nombres de los archivos de credencial ' . $id);
        
        //Matricula::updatebyUser($id, $data2);

        // generar la credencial en pdf y png
        $observaciones = 'Se generaró la credencial en formato pdf y png ';
        Tramites::CustomQry("Insert into tramites (user_id, fecha, observaciones) values ($id, '". $mfecha ."'" .", '". $observaciones ."')");  ;
*/
        $email = User::GetEmail($id);
        $subject = 'Credencial de matrícula otorgada';
        $body = 'La presente se envía al efecto de hacerle saber que su matrícula ha sido otorgada y que se puede obtener ';
        $body .= ' desde http://www.coprobilp.org.ar/carnet/'. trim((string) $_POST['matriculaasignada']) ;

        AuthController::GeneralEmail($email, $subject, $body);

        //emitir con el fin de guardar el nombre del archivo en la base de datos.

        Session::flash('success', 'Matrícula actualizada correctamente.');

        
        $this->redirect('/dashboard');


    }


        public static function emitircredencial(int $nromatricula)
        {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            //require_once $_SESSION['directoriobase'] . '/lib/fpdf/fpdf.php'; // Ajustar según tu estructura
            //require_once $_SESSION['directoriobase'] . '/libs/phpqrcode/qrlib.php'; // Para generar QR

            $matricula = Matricula::findByAsignada($nromatricula);
            if ($matricula == null) {
                die('Matrícula no encontrada.');
            }
            $locuser = $matricula['user_id'];
            $datos = DatosPersonales::findByUserId( $matricula['user_id']);
            if (!$datos) {
                die('Registro de usuario no encontrados.');
            }

            self::crearcredencial($matricula['matriculaasignada']);
    }
        public static function mostrarcredencial(int $nromatricula)
        {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $matricula = Matricula::findByAsignada($nromatricula);
            if ($matricula == null) {
                die('Matrícula no encontrada.');
            }
            $locuser = $matricula['user_id'];
            $datos = DatosPersonales::findByUserId( $locuser);
            if (!$datos) {
                die('Registro de usuario no encontrados.');
            }

            $uploadFolder = self::getUserUploadFolder($locuser);
            $pdfgen = $uploadFolder . '/credencial_' . $matricula['matriculaasignada'] . '.pdf';
            
            if (file_exists($pdfgen)) {
                header('Content-Type: application/pdf');
                header('Content-Disposition: inline; filename="' . basename($pdfgen) . '"');
                readfile($pdfgen);
                exit;
            } else {
                die('Credencial no encontrada.');
            }
        }
        
        public static function crearcredencial(int $numeromatricula)
        {

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }   

        //require_once $_SESSION['directoriobase'] . '/lib/fpdf/fpdf.php'; // Ajustar según tu estructura
//require_once $_SESSION['directoriobase'] . '/libs/phpqrcode/qrlib.php'; // Para generar QR

        $matricula = Matricula::findByAsignada($numeromatricula);
        if ($matricula <> null) {
            die('Matrícula no encontrada.');
        }
        $locuser = $matricula['user_id'];
        $datos = DatosPersonales::findByUserId( $locuser);
        if (!$datos) {
            die('Registro de usuario no encontrados.');
        }

        $pdf = new FPDF('P', 'mm', 'A5');
        $pdf->AddPage();
        
        // --- CONFIGURACIÓN BÁSICA ---
        $x0 = 36; // margen izquierdo del modelo
        $y0 = 80; // margen superior del modelo

        // --- AGREGAR IMAGEN DE FONDO ---
        $imagen_fondo = $_SESSION['directoriobase'] . '/public/img/credencial_fondo.jpeg';
        $pdf->Image($imagen_fondo, $x0, $y0, 75, 50);

        // --- FOTOCARNET ---

        $uploadFolder = self::getUserUploadFolder($locuser);
        if (!empty($matricula['fotocarnet'])) {
            $fotocarnet =  $uploadFolder . '/'.$matricula['fotocarnet'];
            if (file_exists($fotocarnet)) {
                $pdf->Image($fotocarnet, $x0 + 53, $y0 + 5, 19, 19);
            }
        }

        // --- NOMBRE Y APELLIDO ---
        $pdf->SetFont('Arial', '', 10);
        $pdf->SetXY($x0 + 4, $y0 + 18);
        $pdf->MultiCell(46, 4, $datos['apellido'] . ', ' . $datos['nombres'], 0, 'L');

        // --- MATRÍCULA ASIGNADA ---
        $pdf->SetXY($x0 + 4, $y0 + 28);
        $pdf->Cell(25, 5, $matricula['matriculaasignada'], 0, 0, 'L');

        // --- APROBADO ---
        $pdf->SetXY($x0 + 32, $y0 + 28);
        $pdf->Cell(18, 5, $matricula['aprobado'], 0, 0, 'L');

        // --- QR ---
        $xconfig = require $_SESSION['directoriobase'].'/config/settings.php';

        $qr_text = $xconfig['base_url'] .'/' . 'credencial/' . urlencode($matricula['matriculaasignada']);
        $temp_qr = tempnam(sys_get_temp_dir(), 'qr_') . '.png';
        QRcode::png($qr_text, $temp_qr, QR_ECLEVEL_L, 3);

        $pdf->Image($temp_qr, $x0 + 53, $y0 + 28, 19, 19);
        unlink($temp_qr);

//guardar en la carpeta del usuario
        $savingFolder = self::getUserUploadFolder($locuser);
        $pdfgen = $savingFolder . '/credencial_' . $matricula['matriculaasignada'] . '.pdf';
        $pdf->Output('F', $pdfgen); 
        // Guarda el PDF en el servidor sin abrirlo en el navegador
        //   $pdf->Output('F', 'mi_archivo.pdf'); para guardarlo en el servidor
    }


    public function reviewmatri(Request $request, array $params = []): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $user = $_SESSION['user'] ?? null;
        if (!$user) {
            Session::flash('error', 'Debe iniciar sesión.');
            $this->redirect('/login');
        }

        //$id = (int)($params[0] ?? 0);
        //$id = $user['id'];
        $id = (int)($params[0] ?? 0);
        if ($user['role'] == 'user' && $id != $user['id']) {
            Session::flash('error', 'No tiene permiso para editar estos datos.');
            $this->redirect('/dashboard');
        }
        
        // Busca la matrícula asociada al usuario.
        $matricula = Matricula::findByUserId($id);
        if (!$matricula) {
            // Si el registro no existe, crearlo (esto se puede hacer en la activación también)
            Matricula::create(['user_id' => $user['id']]);
            $matricula = Matricula::findByUserId($user['id']);
        }
        $crudstyle = require $_SESSION['directoriobase'] . '/config/cruds/defaults/crudstyle.php';
        $style = $crudstyle['style'] ?? [];
        $cfgedit     = require $_SESSION['directoriobase'] . '/config/cruds/matricula/matricula_review.php';
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



public function rev2extranjero()
{
        if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $user = $_SESSION['user'] ?? null;
    if (!$user) {
        Session::flash('error', 'Debe iniciar sesión.');
        $this->redirect('/login');
    }

    $id = (int)$_SESSION['user']['id'] ?? 0;
    
    // Busca la matrícula asociada al usuario.
    $matricula = Matricula::findByUserId($id);
    if (!$matricula) {
        // Si el registro no existe, crearlo (esto se puede hacer en la activación también)
        Matricula::create(['user_id' => $user['id']]);
        $matricula = Matricula::findByUserId($user['id']);
    }

    if($matricula['apostillado'] == '') {
        Session::flash('error', 'Falta el apostillado.');
        $this->redirect('/titulodeotranacion');
    }
    if($matricula['notaddjj'] == '') {
        Session::flash('error', 'Falta la nota solicitud.');
        $this->redirect('/titulodeotranacion');
    }
    if($matricula['dnifrente'] == '') {
        Session::flash('error', 'Ingrese el DNI.');
        $this->redirect('/titulodeotranacion');    
    }
    if($matricula['titulooriginalfrente'] == '') {
        Session::flash('error', 'Falta el título original.');
        $this->redirect('/titulodeotranacion');
    }
    if($matricula['fotocarnet'] == '') {
        Session::flash('error', 'Falta la foto carnet.');
        $this->redirect('/titulodeotranacion');    
    }
    if($matricula['antecedentespenales'] == '') {
        Session::flash('error', 'Falta el certificado de antecedentes penales.');
        $this->redirect('/titulodeotranacion');
    }
    if($matricula['libredeudaalimentario'] == '') {
        Session::flash('error', 'Falta el libre deuda alimentario.');
        $this->redirect('/titulodeotranacion');
    }
    if($matricula['constanciaCUIL'] == '') {
        Session::flash('error', 'Falta la constancia de CUIL.');
        $this->redirect('/titulodeotranacion');
    }
    if(ComprobantesPago::informopagos($id) == false) {
        Session::flash('error', 'Debe abonar el arancel de inscripción.');
        $this->redirect('/titulodeotranacion');
    }
    if (DatosPersonales::faltandatos($id))
        {
        Session::flash('error', 'Datos personales incompletos.');
        $this->redirect('/titulodeotranacion');
    }

    
    $this->redirect('/arevision');

} 

public function rev2prim()
{
        if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $user = $_SESSION['user'] ?? null;
    if (!$user) {
        Session::flash('error', 'Debe iniciar sesión.');
        $this->redirect('/login');
    }

    $id = (int)$_SESSION['user']['id'] ?? 0;
    
   
    // Busca la matrícula asociada al usuario.
    $matricula = Matricula::findByUserId($id);
    if (!$matricula) {
        // Si el registro no existe, crearlo (esto se puede hacer en la activación también)
        Matricula::create(['user_id' => $user['id']]);
        $matricula = Matricula::findByUserId($user['id']);
        Session::flash('error', 'Faltan datos.');
        $this->redirect('/primeramatricula');

    }

    if($matricula['notaddjj'] == '') {
        Session::flash('error', 'Falta la nota solicitud.');
        $this->redirect('/primeramatricula');
    }
    if($matricula['dnifrente'] == '') {
        Session::flash('error', 'Ingrese el DNI.');
        $this->redirect('/primeramatricula');    
    }
    if($matricula['titulooriginalfrente'] == '') {
        Session::flash('error', 'Falta el título original.');
        $this->redirect('/primeramatricula');
    }
    if($matricula['fotocarnet'] == '') {
        Session::flash('error', 'Falta la foto carnet.');
        $this->redirect('/primeramatricula');    
    }
    if($matricula['antecedentespenales'] == '') {
        Session::flash('error', 'Falta el certificado de antecedentes penales.');
        $this->redirect('/primeramatricula');
    }
    if($matricula['libredeudaalimentario'] == '') {
        Session::flash('error', 'Falta el libre deuda alimentario.');
        $this->redirect('/primeramatricula');
    }
    if($matricula['constanciaCUIL'] == '') {
        Session::flash('error', 'Falta la constancia de CUIL.');
        $this->redirect('/primeramatricula');
    }
    if(ComprobantesPago::informopagos($id) == false) {
        Session::flash('error', 'Debe abonar el arancel de inscripción.');
        $this->redirect('/primeramatricula');
    }
    if (DatosPersonales::faltandatos($id))
        {
        Session::flash('error', 'Datos personales incompletos.');
        $this->redirect('/primeramatricula');
    }

    $this->redirect('/arevision');
    
}



public function rev2prov()
{
        if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $user = $_SESSION['user'] ?? null;
    if (!$user) {
        Session::flash('error', 'Debe iniciar sesión.');
        $this->redirect('/login');
    }

    $id = (int)$_SESSION['user']['id'] ?? 0;
    
    // Busca la matrícula asociada al usuario.
    $matricula = Matricula::findByUserId($id);
    if (!$matricula) {
        // Si el registro no existe, crearlo (esto se puede hacer en la activación también)
        Matricula::create(['user_id' => $user['id']]);
        $matricula = Matricula::findByUserId($user['id']);
    }

    if($matricula['matriculaprevia'] == '') {
        Session::flash('error', 'Falta el número de matricula de la jurisdicción anterior.');
        $this->redirect('/previamatricula');
    }
    if($matricula['notaddjj'] == '') {
        Session::flash('error', 'Falta la nota solicitud.');
        $this->redirect('/previamatricula');
    }
    if($matricula['dnifrente'] == '') {
        Session::flash('error', 'Ingrese el DNI.');
        $this->redirect('/previamatricula');    
    }
    if($matricula['titulooriginalfrente'] == '') {
        Session::flash('error', 'Falta el título original.');
        $this->redirect('/previamatricula');
    }
    if($matricula['fotocarnet'] == '') {
        Session::flash('error', 'Falta la foto carnet.');
        $this->redirect('/previamatricula');    
    }
    if($matricula['antecedentespenales'] == '') {
        Session::flash('error', 'Falta el certificado de antecedentes penales.');
        $this->redirect('/previamatricula');
    }
    if($matricula['libredeudaalimentario'] == '') {
        Session::flash('error', 'Falta el libre deuda alimentario.');
        $this->redirect('/previamatricula');
    }
    if($matricula['constanciaCUIL'] == '') {
        Session::flash('error', 'Falta la constancia de CUIL.');
        $this->redirect('/previamatricula');
    }
    if($matricula['certificadoetica'] == '') {
        Session::flash('error', 'Falta certificado de ética.');
        $this->redirect('/previamatricula');
    }
    if(ComprobantesPago::informopagos($id) == false) {
        Session::flash('error', 'Debe abonar el arancel de inscripción.');
        $this->redirect('/previamatricula');
    }
    if (DatosPersonales::faltandatos($id))
        {
        Session::flash('error', 'Datos personales incompletos.');
        $this->redirect('/previamatricula');
    }

    
    $this->redirect('/arevision');

}

public function rev2rematric()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $user = $_SESSION['user'] ?? null;
    if (!$user) {
        Session::flash('error', 'Debe iniciar sesión.');
        $this->redirect('/login');
    }

    $id = (int)$_SESSION['user']['id'] ?? 0;
    
    // Busca la matrícula asociada al usuario.
    $matricula = Matricula::findByUserId($id);
    if (!$matricula) {
        // Si el registro no existe, crearlo (esto se puede hacer en la activación también)
        Matricula::create(['user_id' => $user['id']]);
        $matricula = Matricula::findByUserId($user['id']);
    }

    if($matricula['matriculaministerio'] == '') {
        Session::flash('error', 'Falta el número de matricula del ministerio.');
        $this->redirect('/rematricula');
    }
    if($matricula['notaddjj'] == '') {
        Session::flash('error', 'Falta la nota solicitud.');
        $this->redirect('/rematricula');
    }
    if($matricula['dnifrente'] == '') {
        Session::flash('error', 'Ingrese el DNI.');
        $this->redirect('/rematricula');    
    }
    if($matricula['titulooriginalfrente'] == '') {
        Session::flash('error', 'Falta el título original.');
        $this->redirect('/rematricula');
    }
    if($matricula['fotocarnet'] == '') {
        Session::flash('error', 'Falta la foto carnet.');
        $this->redirect('/rematricula');    
    }
    if($matricula['antecedentespenales'] == '') {
        Session::flash('error', 'Falta el certificado de antecedentes penales.');
        $this->redirect('/rematricula');
    }
    if($matricula['libredeudaalimentario'] == '') {
        Session::flash('error', 'Falta el libre deuda alimentario.');
        $this->redirect('/rematricula');
    }
    if($matricula['constanciaCUIL'] == '') {
        Session::flash('error', 'Falta la constancia de CUIL.');
        $this->redirect('/rematricula');
    }
    if(ComprobantesPago::informopagos($id) == false) {
        Session::flash('error', 'Debe abonar el arancel de inscripción.');
        $this->redirect('/rematricula');
    }
    if (DatosPersonales::faltandatos($id))
        {
        Session::flash('error', 'Datos personales incompletos.');
        $this->redirect('/rematricula');
    }

    $this->redirect('/arevision');

}


    public function gestionbajas(Request $request): void
    {
        $user = Session::get('user');
        if (!$user || !isset($user['id'])) {
            Session::flash('error', 'Debe iniciar sesión para acceder al panel de control');
            $this->redirect('/login');
            return;
        }

        $userId = $user['id'];

        // 2) Cargar configuración de landing
        $cfgdash     = require $_SESSION['directoriobase'] . '/views/dashboard/menubajas.php';

        $landingCfg  = $cfgdash['landing']    ?? [];
        $cfgHeader   = $landingCfg['header']  ?? [];
        $buttons     = $landingCfg['botones'] ?? [];

        $cfgstyle   = require $_SESSION['directoriobase'] . '/config/cruds/defaults/landingstyle.php';
        $landinCSS  = $cfgstyle['styles']    ?? [];
        // 3) Renderizar con Plates
        $this->view('dashboard/mylandingpage', [
            'cfgHeader'  => $cfgHeader,
            'estilos'    => $landinCSS,
            'buttons'    => $buttons,
            'userId'     => $userId,
        ]);

    }

public function dardebaja(Request $request, array $params = []): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $user = $_SESSION['user'] ?? null;
        if (!$user) {
            Session::flash('error', 'Debe iniciar sesión.');
            $this->redirect('/login');
        }
        if ($user['role'] <> 'admin' ) {
            Session::flash('error', 'No tiene permiso para editar estos datos.');
            $this->redirect('/user-dashboard');
        }
        //verificar que sea el presidente o el vicepresidente quien otorga la baja
        if ( !(Comision::espresidente($user['id']) || Comision::esvicepresidente($user['id']))) {
            Session::flash('error', 'No tiene privilegios.');
            $this->redirect('/dashboard');
        }

        $crudstyle = require $_SESSION['directoriobase'] . '/config/cruds/defaults/crudstyle.php';
        $style = $crudstyle['style'] ?? [];

        $cfgedit     = require $_SESSION['directoriobase'] . '/config/cruds/tramites/bajas/nuevabaja.php';
        $cfg         = $cfgedit['config']    ?? [];
        $id_field = $cfgedit['config']['field_id'];

        $campos      = $cfgedit['campos']    ?? [];
        $actividades = $cfgedit['actividades'] ?? [];
        $comandos    = $cfgedit['comandos']  ?? [];
        $buttons     = $cfgedit['buttons']   ?? [];
        $tables      = $cfgedit['QrySpec']['tables'] ?? [];
        $joinconditions = $cfgedit['QrySpec']['joincond'] ?? '';
        $filter      = $cfgedit['QrySpec']['filter'] ?? '';
        $order       = $cfgedit['QrySpec']['order'] ?? [];
        
        require_once $_SESSION['directoriobase'] . '/app/Core/Helpers/string4query.php';
        
        // Initialize datos array
        $datos = [];
        
        // Generate dropdown lists if needed
        $matriculadosList = [];
        $matriculadosList = DatosPersonales::HtmlDropDown($campos['matriculado']['options']);

        $campos['matriculado']['listavalores'] = $matriculadosList;
        // Fix function call - add missing id_field parameter
        $this->pendingquery = str4qry($tables, $campos, $actividades, $filter, $joinconditions, $order, $id_field);

        $zcolumns = Self::mkColumns($campos, $actividades);
        $zcolumns = trim(stripslashes($zcolumns), '"');

        $this->view('cruds/index', [
            'cfg'      => $cfg,
            'fields'   => $campos,
            'style'    => $style,
            'values'   => $datos,
            'actions'  => $actividades,
            'comandos' => $comandos,
            'buttons'  => $buttons,
            'divname' => $cfg['divname'],            
            'id'      => 'id',
            'link_id' => 'user_id',
            'scriptjs_data' => $this->pendingquery,
            'scriptjs_columns' => $this->pendingcolumns,
            'zcolumns' => $zcolumns,
            'url_data' => $_SESSION['base_url']. $cfg['url_data'],
            'matriculadosList' => $matriculadosList,
        ]);
    }

    public function bajarmatricula(Request $request, array $params = []): void
    {

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $user = $_SESSION['user'] ?? null;
        if (!$user) {
            Session::flash('error', 'Debe iniciar sesion.');
            $this->redirect('/login');
        }
        //$id = (int)($params[0] ?? 0); // pasa el número de matrícula como parámetro en la URL
        if ( !(Comision::espresidente($user['id']) || Comision::esvicepresidente($user['id']))) {
            Session::flash('error', 'No tiene privilegios.');
                   
            $this->redirect('/dashboard');
        }
        // Procesa los campos de texto.
        //$data = $_POST;
        //$id = $_POST['matriculado']; // Asegúrate de que el campo 'matriculado' esté presente en el formulario y contenga el ID correcto
        $mfecha = $_POST['fecha'];
        $fecha_actual = date("Y/m/d");
        $xmat = $_POST['matriculado'];
        if ($mfecha == null || $mfecha == '') {
            
            Session::flash('error', 'Verifique la fecha.');
            $this->redirect('/iniciarbaja' );
        }
/* ver control de fechas, preguntar fecha admisible
        // Calcular la diferencia en días entre las dos fechas
        if (strtotime($mfecha) > strtotime($fecha_actual)) {
            Session::flash('error', 'La fecha de aprobación no puede ser posdatada.');
            $this->redirect('/estadomatricula/' . $id);
        }

        if ((strtotime($mfecha) == strtotime($fecha_actual)) && (strtotime($mfecha) == 0)) {
            Session::flash('error', 'Hay un error en el formato de fechas.');
            $this->redirect('/estadomatricula' );

        }

        $dias = (strtotime($mfecha)-strtotime($fecha_actual))/86400;
        $dias = abs($dias); 
        $dias = floor($dias);

                // 2. Calcular la diferencia entre las fechas
        $diferencia = $dias;
*/
        $datos = Comision::activa();

        // Lista de campos de archivo en la tabla "matriculas".
        $fechaBarras = str_replace('-', '/', $mfecha);
        $data['baja'] = $fechaBarras;  // Por defecto, se establece como 'no' hasta que se apruebe.

        $data['funcionario'] = $user['id']; // Asigna el id del funcionario que otorga presi o vice
        $data['comisionotorgante'] = $datos['id']; // Asigna el id de la comisión que otorga la matrícula

        // Asegúrate de que la sesión esté iniciada y obtén el id del usuario.
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        //$registrobajauser = Matricula::findByUserId($xmat);
        //$bajauser = $registrobajauser['user_id'];
        $bajauser = (int)$xmat;
        Matricula::updatebyUser($bajauser, $data);

        //eliminar el carnet
        

        $mmatricula = $xmat;
        $motivo = $_POST['motivo'];
        
        $user = $_SESSION['user']['id']; // ya se sabe que es el presi o el vice
        $nombrerevisor = DatosPersonales::GetNombreById($data['funcionario']);
        $observaciones = '** Dado de baja ** ';
	    $observaciones .= 'Motivo: ' . $motivo;
	    $observaciones .= 'Intervino: ' . $nombrerevisor . " - " ;
//agregar el motivo ingresado
        Tramites::CustomQry("Insert into tramites (user_id, fecha, observaciones) values ($bajauser, '". $mfecha ."'" .", '". $observaciones ."')");  ;

        error_log('Baja para la matrícula ' . $bajauser);

        Session::flash('success', 'Matrícula dada de baja.');

        
        $this->redirect('/menubajas');


    }
    

}
