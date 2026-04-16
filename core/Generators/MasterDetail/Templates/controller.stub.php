<?php

declare(strict_types=1);

namespace {{ namespace }};

use {{ model_namespace }}\{{ model_class }};
use {{ validator_namespace }}\{{ validator_class }};
use ZMosquita\Core\Http\Controllers\BaseController;

final class {{ controller_class }} extends BaseController
{
    public function index(int $masterId): void
    {
        $this->authorize('{{ index_permission }}');

        $model = new {{ model_class }}();
        $rows = $model->allByMaster($masterId, '{{ foreign_key }}');

        $view = __DIR__ . '/../Views/{{ view_folder }}/index.php';
        $this->render($view, compact('rows', 'masterId'));
    }

    public function create(int $masterId): void
    {
        $this->authorize('{{ create_permission }}');

        $item = ['{{ foreign_key }}' => $masterId];
        $errors = [];
        {{ create_lookups }}

        $action = '{{ master_route_base }}/' . $masterId . '/{{ detail_route_segment }}';
        $view = __DIR__ . '/../Views/{{ view_folder }}/form.php';
        $this->render($view, compact('item', 'errors', 'action', 'masterId'));
    }

    public function store(int $masterId): void
    {
        $this->authorize('{{ create_permission }}');

        $validator = new {{ validator_class }}();
        $data = $_POST;
        $data['{{ foreign_key }}'] = $masterId;

        $validation = $validator->validate($data);

        if (!$validation['valid']) {
            $item = $data;
            $errors = $validation['errors'];
            {{ create_lookups }}

            $action = '{{ master_route_base }}/' . $masterId . '/{{ detail_route_segment }}';
            $view = __DIR__ . '/../Views/{{ view_folder }}/form.php';
            $this->render($view, compact('item', 'errors', 'action', 'masterId'));
            return;
        }

        $model = new {{ model_class }}();
        $model->create($validation['cleaned']);

        $this->redirect('{{ master_route_base }}/' . $masterId . '/{{ detail_route_segment }}');
    }

    public function edit(int $masterId, int $id): void
    {
        $this->authorize('{{ edit_permission }}');

        $model = new {{ model_class }}();
        $item = $model->findByMaster($id, $masterId, '{{ foreign_key }}');

        if (!$item) {
            $this->abort(404, 'Registro no encontrado');
        }

        $errors = [];
        {{ edit_lookups }}

        $action = '{{ master_route_base }}/' . $masterId . '/{{ detail_route_segment }}/' . $id;
        $view = __DIR__ . '/../Views/{{ view_folder }}/form.php';
        $this->render($view, compact('item', 'errors', 'action', 'masterId'));
    }

    public function update(int $masterId, int $id): void
    {
        $this->authorize('{{ edit_permission }}');

        $validator = new {{ validator_class }}();
        $data = $_POST;
        $data['{{ foreign_key }}'] = $masterId;

        $validation = $validator->validate($data);

        if (!$validation['valid']) {
            $item = array_merge($data, ['{{ detail_primary_key }}' => $id]);
            $errors = $validation['errors'];
            {{ edit_lookups }}

            $action = '{{ master_route_base }}/' . $masterId . '/{{ detail_route_segment }}/' . $id;
            $view = __DIR__ . '/../Views/{{ view_folder }}/form.php';
            $this->render($view, compact('item', 'errors', 'action', 'masterId'));
            return;
        }

        $model = new {{ model_class }}();
        $model->updateByMaster($id, $masterId, '{{ foreign_key }}', $validation['cleaned']);

        $this->redirect('{{ master_route_base }}/' . $masterId . '/{{ detail_route_segment }}');
    }

    public function delete(int $masterId, int $id): void
    {
        $this->authorize('{{ delete_permission }}');

        $model = new {{ model_class }}();
        $model->deleteByMaster($id, $masterId, '{{ foreign_key }}');

        $this->redirect('{{ master_route_base }}/' . $masterId . '/{{ detail_route_segment }}');
    }

{{ lookup_methods }}
}