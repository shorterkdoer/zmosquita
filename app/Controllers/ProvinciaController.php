<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Models\Provincia;
use App\Core\Session;
use App\Core\MasterCrud;

use App\Core\AuthMiddleware;

class ProvinciaController extends Controller
{
    // Muestra la lista de provincias
    public function index(Request $request): void

    {
        $cfgindex    = require $_SESSION['directoriobase'] . '/config/cruds/provincias/provincias_index.php';
        $cfg = $cfgindex['config'] ?? [];
        $actividades = $cfgindex['actividades'] ?? [];
        $campos = $cfgindex['campos'] ?? [];
        $comandos = $cfgindex['comandos'] ?? [];
        $buttons = $cfgindex['buttons'] ?? [];

        $todos = Provincia::all();
        $this->view('cruds/index', [
            'cfg'      => $cfg,
            'fields'   => $campos,        // <— aquí
            'values'   => $todos,         // array de filas
            'actions'  => $actividades,
            'comandos' => $comandos,
            'buttons'  => $buttons,
        ]);
        /*
        $this->view('cruds/index', [
            'cfg' => $cfg,
            'actions' => $actividades,
            'fields' => $campos,
            'comandos' => $comandos,
            'buttons' => $buttons,
            'values' => $todos,
        ]);
        */
    }
    public function edit(Request $request, array $params): void
    {
        
        $id = $params[0] ?? null; // Get the ID from the URL parameters
        if (!$id) {
            Session::flash('error', 'ID de Provincia no especificado.');
            $this->redirect('/provincias');
            return;
        }
        
//        $id = $request->input('id');

        $data = Provincia::find($id);
        if (!$data) {
            Session::flash('error', 'Provincia no encontrada.');
            $this->redirect('/provincias');
            return;
        }

        $isEdit = true;

        $cfgedit     = require $_SESSION['directoriobase'] . '/config/cruds/provincias/provincias_edit.php';
        $cfg         = $cfgedit['config']    ?? [];
        $cfg['url_action'] .= '/' . $id; // <— se agrega el id a la url
        $campos      = $cfgedit['campos']    ?? [];
        $actividades = $cfgedit['actividades'] ?? [];
        $comandos    = $cfgedit['comandos']  ?? [];
        $buttons     = $cfgedit['buttons']   ?? [];
    
        $this->view('cruds/index', [
            'cfg'      => $cfg,
            'fields'   => $campos,     // <— coherente con index/create
            'values'   => $data,       // array simple con claves=>valores
            'actions'  => $actividades,
            'comandos' => $comandos,
            'buttons'  => $buttons,
            'id'      => 'id',
        ]);

    }



    // Muestra el formulario para crear una nueva ciudad
    public function create(Request $request): void
    {
        $cfgcreate = require $_SESSION['directoriobase'] . '/config/cruds/provincias/provincias_create.php';
        $cfg       = $cfgcreate['config']      ?? [];
        $campos    = $cfgcreate['campos']      ?? [];
        $actividades = $cfgcreate['actividades'] ?? [];
        $comandos    = $cfgcreate['comandos']  ?? [];
        $buttons     = $cfgcreate['buttons']   ?? [];
    
        $this->view('cruds/index', [
            'cfg'      => $cfg,
            'fields'   => $campos,        // <— ¡ya no 'campos'!
            'values'   => [],             // vacío para nuevo
            'actions'  => $actividades,             // o los que correspondan
            'comandos' => $comandos,
            'buttons'  => $buttons,
        ]);
    }


    // Procesa el formulario y guarda la nueva provincia
    public function store(Request $request): void
    {
        $nombre = trim($request->input('nombre'));

        if (empty($nombre)) {
            Session::flash('error', 'El nombre de la provincia es obligatorio.');
            $this->redirect('/provincias/create');
        }

        Provincia::create(['nombre' => $nombre]);
        Session::flash('success', 'Provincia creada correctamente.');
        $this->redirect('/provincias');
    }


    // Procesa el formulario de edición y actualiza la provincia
    public function update(Request $request, array $params): void
    {
        $id = $params[0] ?? null;
        if (!$id) {
            Session::flash('error', 'ID de provincia no especificado.');
            $this->redirect('/provincias');
            return;
        }
/*
        if (!$csrf->validateRequest()) {
            // Token inválido: abortar
            Session::flash('error', 'Recargue el formulario.');
            $this->redirect('/provincias/edit/' . $id);
        }
*/
        $nombre = trim($request->input('nombre'));
        if (empty($nombre)) {
            Session::flash('error', 'El nombre de la provincia es obligatorio.');
            $this->redirect('/provincias/edit/' . $id);
            return;
        }

        if (!Provincia::find($id)) {
            Session::flash('error', 'Provincia no encontrada.');
            $this->redirect('/provincias');
            return;
        }

        Provincia::update($id, ['nombre' => $nombre]);
        Session::flash('success', 'Provincia actualizada correctamente.');
        $this->redirect('/provincias');
    }

    // Elimina una provincia
    public function delete(Request $request, array $params): void
    {
        $id = $params[0] ?? null;
        if (!$id) {
            Session::flash('error', 'ID de provincia no especificado.');
            $this->redirect('/provincias');
            return;
        }

        $provincia = Provincia::find($id);
        if (!$provincia) {
            Session::flash('error', 'Provincia no encontrada.');
            $this->redirect('/provincias');
            return;
        }

        Provincia::delete($id);
        Session::flash('success', 'Provincia eliminada correctamente.');
        $this->redirect('/provincias');
    }
    public function vista(Request $request, array $params): void
    {
        
        $id = $params[0] ?? null; // Get the ID from the URL parameters
        if (!$id) {
            Session::flash('error', 'ID de provincia no especificado.');
            $this->redirect('/provincias');
            return;
        }
        
//        $id = $request->input('id');

        $data = Provincia::find($id);
        if (!$data) {
            Session::flash('error', 'Provincia no encontrada.');
            $this->redirect('/provincias');
            return;
        }

        $isEdit = true;

        $cfgedit     = require $_SESSION['directoriobase'] . '/config/cruds/provincias/provincias_borrar.php';
        $cfg         = $cfgedit['config']    ?? [];
        $cfg['url_action'] .= '/' . $id; // <— se agrega el id a la url
        $campos      = $cfgedit['campos']    ?? [];
        $actividades = $cfgedit['actividades'] ?? [];
        $comandos    = $cfgedit['comandos']  ?? [];
        $buttons     = $cfgedit['buttons']   ?? [];
    
        $this->view('cruds/index', [
            'cfg'      => $cfg,
            'fields'   => $campos,     // <— coherente con index/create
            'values'   => $data,       // array simple con claves=>valores
            'actions'  => $actividades,
            'comandos' => $comandos,
            'buttons'  => $buttons,
            'id'      => 'id',
        ]);

    }

}
