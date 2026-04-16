<?php

declare(strict_types=1);

namespace {{ namespace }};

use {{ model_namespace }}\{{ model_class }};
use {{ validator_namespace }}\{{ validator_class }};
use ZMosquita\Core\Http\Controllers\BaseController;

final class {{ controller_class }} extends BaseController
{
    public function index(): void
    {
        $this->authorize('{{ index_permission }}');

        $model = new {{ model_class }}();
        $rows = $model->all();

        $view = __DIR__ . '/../Views/{{ view_folder }}/index.php';
        $this->render($view, compact('rows'));
    }

    public function create(): void
    {
        $this->authorize('{{ create_permission }}');

        $item = [];
        $errors = [];
        {{ create_lookups }}
        $action = '{{ route_base }}';
        $view = __DIR__ . '/../Views/{{ view_folder }}/form.php';
        $this->render($view, compact('item', 'errors', 'action'));
    }

    public function store(): void
    {
        $this->authorize('{{ create_permission }}');

        $validator = new {{ validator_class }}();
        $validation = $validator->validate($_POST);

        if (!$validation['valid']) {
            $item = $_POST;
            $errors = $validation['errors'];
            {{ create_lookups }}
            $action = '{{ route_base }}';
            $view = __DIR__ . '/../Views/{{ view_folder }}/form.php';
            $this->render($view, compact('item', 'errors', 'action'));
            return;
        }

        $model = new {{ model_class }}();
        $model->create($validation['cleaned']);

        $this->redirect('{{ route_base }}');
    }

    public function edit(int $id): void
    {
        $this->authorize('{{ edit_permission }}');

        $model = new {{ model_class }}();
        $item = $model->find($id);

        if (!$item) {
            $this->abort(404, 'Registro no encontrado');
        }

        $errors = [];
        {{ edit_lookups }}
        $action = '{{ route_base }}/' . $id;
        $view = __DIR__ . '/../Views/{{ view_folder }}/form.php';
        $this->render($view, compact('item', 'errors', 'action'));
    }

    public function update(int $id): void
    {
        $this->authorize('{{ edit_permission }}');

        $validator = new {{ validator_class }}();
        $validation = $validator->validate($_POST);

        if (!$validation['valid']) {
            $item = array_merge($_POST, ['{{ primary_key }}' => $id]);
            $errors = $validation['errors'];
            {{ edit_lookups }}
            $action = '{{ route_base }}/' . $id;
            $view = __DIR__ . '/../Views/{{ view_folder }}/form.php';
            $this->render($view, compact('item', 'errors', 'action'));
            return;
        }

        $model = new {{ model_class }}();
        $model->update($id, $validation['cleaned']);

        $this->redirect('{{ route_base }}');
    }

    public function delete(int $id): void
    {
        $this->authorize('{{ delete_permission }}');

        $model = new {{ model_class }}();
        $model->delete($id);

        $this->redirect('{{ route_base }}');
    }

{{ lookup_methods }}
}