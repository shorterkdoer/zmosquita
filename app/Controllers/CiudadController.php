<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Models\Ciudad;
use App\Core\Session;

class CiudadController extends Controller
{
    // Muestra la lista de ciudades
    public function index(Request $request): void
    {
        $cfgindex    = require $_SESSION['directoriobase'] . '/config/cruds/ciudad/ciudades_index.php';
        $cfg = $cfgindex['config'] ?? [];
        $actividades = $cfgindex['actividades'] ?? [];
        $campos = $cfgindex['campos'] ?? [];
        $comandos = $cfgindex['comandos'] ?? [];
        $buttons = $cfgindex['buttons'] ?? [];

        $todos = Ciudad::all();
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
    // Muestra el formulario para editar una ciudad existente
    public function edit(Request $request, array $params): void
    {
        
        $id = $params[0] ?? null; // Get the ID from the URL parameters
        if (!$id) {
            Session::flash('error', 'ID de ciudad no especificado.');
            $this->redirect('/ciudades');
            return;
        }
        
//        $id = $request->input('id');

        $data = Ciudad::find($id);
        if (!$data) {
            Session::flash('error', 'Ciudad no encontrada.');
            $this->redirect('/ciudades');
            return;
        }

        $isEdit = true;

        $cfgedit     = require $_SESSION['directoriobase'] . '/config/cruds/ciudad/ciudades_edit.php';
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
        $cfgcreate = require $_SESSION['directoriobase'] . '/config/cruds/ciudad/ciudades_create.php';
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


    // Procesa el formulario y guarda la nueva ciudad
    public function store(Request $request): void
    {
        $nombre = trim($request->input('nombre'));

        if (empty($nombre)) {
            Session::flash('error', 'El nombre de la ciudad es obligatorio.');
            $this->redirect('/ciudades/create');
        }

        Ciudad::create(['nombre' => $nombre]);
        Session::flash('success', 'Ciudad creada correctamente.');
        $this->redirect('/ciudades');
    }



    // Procesa el formulario de edición y actualiza la ciudad
    public function update(Request $request, array $params): void
    {
        $id = $params[0] ?? null;
        if (!$id) {
            Session::flash('error', 'ID de ciudad no especificado.');
            $this->redirect('/ciudades');
            return;
        }

        $nombre = trim($request->input('nombre'));
        if (empty($nombre)) {
            Session::flash('error', 'El nombre de la ciudad es obligatorio.');
            $this->redirect('/ciudades/edit/' . $id);
            return;
        }

        if (!Ciudad::find($id)) {
            Session::flash('error', 'Ciudad no encontrada.');
            $this->redirect('/ciudades');
            return;
        }

        Ciudad::update($id, ['nombre' => $nombre]);
        Session::flash('success', 'Ciudad actualizada correctamente.');
        $this->redirect('/ciudades');
    }

    // Elimina una ciudad
    public function delete(Request $request, array $params): void
    {
        $id = $params[0] ?? null;
        if (!$id) {
            Session::flash('error', 'ID de ciudad no especificado.');
            $this->redirect('/ciudades');
            return;
        }

        $ciudad = Ciudad::find($id);
        if (!$ciudad) {
            Session::flash('error', 'Ciudad no encontrada.');
            $this->redirect('/ciudades');
            return;
        }

        Ciudad::delete($id);
        Session::flash('success', 'Ciudad eliminada correctamente.');
        $this->redirect('/ciudades');
    }

    public function vista(Request $request, array $params): void
    {
        
        $id = $params[0] ?? null; // Get the ID from the URL parameters
        if (!$id) {
            Session::flash('error', 'ID de ciudad no especificado.');
            $this->redirect('/ciudades');
            return;
        }
        
//        $id = $request->input('id');

        $data = Ciudad::find($id);
        if (!$data) {
            Session::flash('error', 'Ciudad no encontrada.');
            $this->redirect('/ciudades');
            return;
        }

        $isEdit = true;

        $cfgedit     = require $_SESSION['directoriobase'] . '/config/cruds/ciudad/ciudades_borrar.php';
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
