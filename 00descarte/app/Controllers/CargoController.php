<?php

namespace App\Controllers;

use App\Core\Controller;
use Foundation\Core\Request;
use Foundation\Core\Session;
use App\Models\Cargo;
use App\Middlewares\AdminMiddleware;

class CargoController extends Controller
{
    // Muestra la lista de cargos
    public function index(Request $request): void
    {
        $cfgindex = require $_SESSION['directoriobase'] . '/config/cruds/cargos/cargos_index.php';
        $cfg = $cfgindex['config'] ?? [];
        $actividades = $cfgindex['actividades'] ?? [];
        $campos = $cfgindex['campos'] ?? [];
        $comandos = $cfgindex['comandos'] ?? [];
        $buttons = $cfgindex['buttons'] ?? [];

        $todos = Cargo::all();
        $this->view('cruds/index', [
            'cfg' => $cfg,
            'fields' => $campos,
            'values' => $todos,
            'actions' => $actividades,
            'comandos' => $comandos,
            'buttons' => $buttons,
        ]);
    }

    // Muestra el formulario para crear un nuevo registro
    public function create(Request $request): void
    {
        $cfgcreate = require $_SESSION['directoriobase'] . '/config/cruds/cargos/cargos_create.php';
        $cfg = $cfgcreate['config'] ?? [];
        $campos = $cfgcreate['campos'] ?? [];
        $actividades = $cfgcreate['actividades'] ?? [];
        $comandos = $cfgcreate['comandos'] ?? [];
        $buttons = $cfgcreate['buttons'] ?? [];

        $this->view('cruds/index', [
            'cfg' => $cfg,
            'fields' => $campos,
            'values' => [],
            'actions' => $actividades,
            'comandos' => $comandos,
            'buttons' => $buttons,
        ]);
    }

    // Procesa el formulario y guarda el nuevo registro
    public function store(Request $request): void
    {
        $data = [];

        $nombre = trim($request->input('nombre'));
        $data['nombre'] = $nombre;

        $jerarquia = trim($request->input('jerarquia'));
        if (empty($jerarquia)) {
            Session::flash('error', 'El campo jerarquia es obligatorio.');
            $this->redirect('/cargos/create');
            return;
        }

        $data['jerarquia'] = $jerarquia;

        Cargo::create($data);
        Session::flash('success', 'Registro creado correctamente.');
        $this->redirect('/cargos');
    }

    public function edit(Request $request, array $params): void
    {
        $id = $params[0] ?? null;
        if (!$id) {
            Session::flash('error', 'ID no especificado.');
            $this->redirect('/cargos');
            return;
        }

        $data = Cargo::find($id);
        if (!$data) {
            Session::flash('error', 'Registro no encontrado.');
            $this->redirect('/cargos');
            return;
        }

        $cfgedit = require $_SESSION['directoriobase'] . '/config/cruds/cargos/cargos_edit.php';
        $cfg = $cfgedit['config'] ?? [];
        $cfg['url_action'] .= '/' . $id;
        $campos = $cfgedit['campos'] ?? [];
        $actividades = $cfgedit['actividades'] ?? [];
        $comandos = $cfgedit['comandos'] ?? [];
        $buttons = $cfgedit['buttons'] ?? [];

        $this->view('cruds/index', [
            'cfg' => $cfg,
            'fields' => $campos,
            'values' => $data,
            'actions' => $actividades,
            'comandos' => $comandos,
            'buttons' => $buttons,
        ]);
    }

    public function update(Request $request, array $params): void
    {
        $id = $params[0] ?? null;
        if (!$id) {
            Session::flash('error', 'ID no especificado.');
            $this->redirect('/cargos');
            return;
        }

        $data = [];

        $nombre = trim($request->input('nombre'));

        $data['nombre'] = $nombre;

        $jerarquia = trim($request->input('jerarquia'));

        if (empty($jerarquia)) {
            Session::flash('error', 'El campo jerarquia es obligatorio.');
            $this->redirect('/cargos/edit/' . $id);
            return;
        }

        $data['jerarquia'] = $jerarquia;

        if (!Cargo::find($id)) {
            Session::flash('error', 'Registro no encontrado.');
            $this->redirect('/cargos');
            return;
        }

        Cargo::update($id, $data);
        Session::flash('success', 'Registro actualizado correctamente.');
        $this->redirect('/cargos');
    }

    public function vista(Request $request, array $params): void
    {
        $id = $params[0] ?? null;
        if (!$id) {
            Session::flash('error', 'ID no especificado.');
            $this->redirect('/cargos');
            return;
        }

        $data = Cargo::find($id);
        if (!$data) {
            Session::flash('error', 'Registro no encontrado.');
            $this->redirect('/cargos');
            return;
        }

        $cfgdelete = require $_SESSION['directoriobase'] . '/config/cruds/cargos/cargos_delete.php';
        $cfg = $cfgdelete['config'] ?? [];
        $cfg['url_action'] .= '/' . $id;
        $campos = $cfgdelete['campos'] ?? [];
        $actividades = $cfgdelete['actividades'] ?? [];
        $comandos = $cfgdelete['comandos'] ?? [];
        $buttons = $cfgdelete['buttons'] ?? [];

        $this->view('cruds/index', [
            'cfg' => $cfg,
            'fields' => $campos,
            'values' => $data,
            'actions' => $actividades,
            'comandos' => $comandos,
            'buttons' => $buttons,
        ]);
    }

    public function delete(Request $request, array $params): void
    {
        $id = $params[0] ?? null;
        if (!$id) {
            Session::flash('error', 'ID no especificado.');
            $this->redirect('/cargos');
            return;
        }

        $registro = Cargo::find($id);
        if (!$registro) {
            Session::flash('error', 'Registro no encontrado.');
            $this->redirect('/cargos');
            return;
        }

        Cargo::delete($id);
        Session::flash('success', 'Registro eliminado correctamente.');
        $this->redirect('/cargos');
    }

}
