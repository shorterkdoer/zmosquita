<?php
/**
 * Master-Detail Index View
 * Displays list of master records with detail counts
 */
$this->layout('layouts/app', ['title' => $title ?? 'Master-Detail List'])
?>

<div class="container-fluid mt-4">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-list-ul me-2"></i>
                <?= htmlspecialchars($title) ?>
            </h5>
            <a href="<?= "/{$master['route']}/create" ?>" class="btn btn-light btn-sm">
                <i class="bi bi-plus-circle me-1"></i>New
            </a>
        </div>
        <div class="card-body">
            <table id="masterTable" class="table table-striped table-bordered table-hover" style="width:100%">
                <thead class="table-light">
                    <tr>
                        <?php foreach ($master['fields'] as $field): ?>
                            <?php if (!$field['hidden']): ?>
                                <th class="text-center"><?= htmlspecialchars($field['label']) ?></th>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        <th class="text-center">Details</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#masterTable').DataTable({
        ajax: {
            url: '<?= "/api/{$master['route']}/data" ?>',
            dataSrc: 'data'
        },
        processing: true,
        serverSide: true,
        responsive: true,
        order: [[0, 'asc']],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
        },
        columns: [
            <?php
            $columns = [];
            foreach ($master['fields'] as $field) {
                if (!$field['hidden']) {
                    $columns[] = "{ data: '{$field['name']}', title: '{$field['label']}' }";
                }
            }
            $columns[] = "{ data: 'detail_count', title: 'Details', className: 'text-center', orderable: false }";
            $columns[] = "{ data: 'acciones', title: 'Actions', className: 'text-center', orderable: false }";
            echo implode(",\n            ", $columns);
            ?>
        ],
        columnDefs: [
            {
                targets: -2, // Details column
                render: function(data, type, row) {
                    if (data === '0') {
                        return '<span class="badge bg-secondary">0</span>';
                    }
                    return '<a href="<?= "/{$master['route']}/edit" ?>/' + row.<?= $master['primaryKey'] ?> +
                           '" class="badge bg-primary">' + data + ' items</a>';
                }
            }
        ]
    });
});

function confirmDelete(id) {
    if (confirm('Are you sure you want to delete this <?= htmlspecialchars($master['singular']) ?>?')) {
        $.post('<?= "/{$master['route']}/delete" ?>/' + id, {
            csrf_token: '<?= csrf_token() ?>'
        }, function(response) {
            window.location.reload();
        });
    }
}
</script>
