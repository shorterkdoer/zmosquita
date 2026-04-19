<?php

namespace App\Controllers;

use App\Core\Controller;
use Foundation\Core\Request;
use Foundation\Core\Session;
use App\Models\Departamento;
use App\Middlewares\AdminMiddleware;

class DepartamentoController extends Controller
{
    // Muestra la lista de departamentos
    public function index(Request $request): void
    {
        $cfgindex = require $_SESSION['directoriobase'] . '/config/cruds/departamentos/departamentos_index.php';
        $cfg = $cfgindex['config'] ?? [];
        $actividades = $cfgindex['actividades'] ?? [];
        $campos = $cfgindex['campos'] ?? [];
        $comandos = $cfgindex['comandos'] ?? [];
        $buttons = $cfgindex['buttons'] ?? [];

        $todos = Departamento::all();
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
        $cfgcreate = require $_SESSION['directoriobase'] . '/config/cruds/departamentos/departamentos_create.php';
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

        $cargo_id = trim($request->input('cargo_id'));
        $data['cargo_id'] = $cargo_id;

        $descripcion = trim($request->input('descripcion'));
        if (empty($descripcion)) {
            Session::flash('error', 'El campo descripcion es obligatorio.');
            $this->redirect('/departamentos/create');
            return;
        }

        $data['descripcion'] = $descripcion;

        $activo = trim($request->input('activo'));
        if (empty($activo)) {
            Session::flash('error', 'El campo activo es obligatorio.');
            $this->redirect('/departamentos/create');
            return;
        }

        $data['activo'] = $activo;

        $created_at = trim($request->input('created_at'));
        if (empty($created_at)) {
            Session::flash('error', 'El campo created_at es obligatorio.');
            $this->redirect('/departamentos/create');
            return;
        }

        $data['created_at'] = $created_at;

        Departamento::create($data);
        Session::flash('success', 'Registro creado correctamente.');
        $this->redirect('/departamentos');
    }

    public function edit(Request $request, array $params): void
    {
        $id = $params[0] ?? null;
        if (!$id) {
            Session::flash('error', 'ID no especificado.');
            $this->redirect('/departamentos');
            return;
        }

        $data = Departamento::find($id);
        if (!$data) {
            Session::flash('error', 'Registro no encontrado.');
            $this->redirect('/departamentos');
            return;
        }

        $cfgedit = require $_SESSION['directoriobase'] . '/config/cruds/departamentos/departamentos_edit.php';
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
            $this->redirect('/departamentos');
            return;
        }

        $data = [];

        $nombre = trim($request->input('nombre'));

        $data['nombre'] = $nombre;

        $cargo_id = trim($request->input('cargo_id'));

        $data['cargo_id'] = $cargo_id;

        $descripcion = trim($request->input('descripcion'));

        if (empty($descripcion)) {
            Session::flash('error', 'El campo descripcion es obligatorio.');
            $this->redirect('/departamentos/edit/' . $id);
            return;
        }

        $data['descripcion'] = $descripcion;

        $activo = trim($request->input('activo'));

        if (empty($activo)) {
            Session::flash('error', 'El campo activo es obligatorio.');
            $this->redirect('/departamentos/edit/' . $id);
            return;
        }

        $data['activo'] = $activo;

        $created_at = trim($request->input('created_at'));

        if (empty($created_at)) {
            Session::flash('error', 'El campo created_at es obligatorio.');
            $this->redirect('/departamentos/edit/' . $id);
            return;
        }

        $data['created_at'] = $created_at;

        if (!Departamento::find($id)) {
            Session::flash('error', 'Registro no encontrado.');
            $this->redirect('/departamentos');
            return;
        }

        Departamento::update($id, $data);
        Session::flash('success', 'Registro actualizado correctamente.');
        $this->redirect('/departamentos');
    }

    public function vista(Request $request, array $params): void
    {
        $id = $params[0] ?? null;
        if (!$id) {
            Session::flash('error', 'ID no especificado.');
            $this->redirect('/departamentos');
            return;
        }

        $data = Departamento::find($id);
        if (!$data) {
            Session::flash('error', 'Registro no encontrado.');
            $this->redirect('/departamentos');
            return;
        }

        $cfgdelete = require $_SESSION['directoriobase'] . '/config/cruds/departamentos/departamentos_delete.php';
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
            $this->redirect('/departamentos');
            return;
        }

        $registro = Departamento::find($id);
        if (!$registro) {
            Session::flash('error', 'Registro no encontrado.');
            $this->redirect('/departamentos');
            return;
        }

        Departamento::delete($id);
        Session::flash('success', 'Registro eliminado correctamente.');
        $this->redirect('/departamentos');
    }

}
