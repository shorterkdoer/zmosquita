<?php
/**
 * Master-Detail Edit View
 * Form to edit master record with inline detail records management
 */
$this->layout('layouts/app', ['title' => $title ?? 'Edit Record'])
?>

<style>
.detail-row { transition: background-color 0.2s; }
.detail-row:hover { background-color: #f8f9fa; }
.detail-actions { min-width: 100px; }
</style>

<div class="container-fluid mt-4">
    <div class="row">
        <!-- Master Form -->
        <div class="col-lg-12">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-pencil-square me-2"></i>
                        <?= htmlspecialchars($title) ?> #<?= $id ?>
                    </h5>
                    <a href="<?= "/{$master['route']}" ?>" class="btn btn-light btn-sm">
                        <i class="bi bi-arrow-left me-1"></i>Back to List
                    </a>
                </div>
                <div class="card-body">
                    <form id="masterForm" method="POST" action="<?= "/{$master['route']}/update/$id" ?>">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                        <div class="row g-3">
                            <?php foreach ($master['fields'] as $field): ?>
                                <?php if (!$field['hidden']): ?>
                                    <div class="col-md-<?= $field['width'] ?? '12' ?>">
                                        <div class="form-group">
                                            <label for="<?= $field['name'] ?>" class="form-label">
                                                <?= htmlspecialchars($field['label']) ?>
                                                <?php if ($field['required']): ?>
                                                    <span class="text-danger">*</span>
                                                <?php endif; ?>
                                            </label>

                                            <?php
                                            $value = $masterRecord[$field['name']] ?? '';
                                            $readonly = $field['readonly'] ?? false;
                                            ?>

                                            <?php if ($field['type'] === 'text'): ?>
                                                <input type="text"
                                                       id="<?= $field['name'] ?>"
                                                       name="<?= $field['name'] ?>"
                                                       value="<?= htmlspecialchars($value) ?>"
                                                       class="form-control"
                                                       placeholder="<?= htmlspecialchars($field['placeholder'] ?? '') ?>"
                                                       <?= $readonly ? 'readonly' : '' ?>
                                                       <?= $field['required'] && !$readonly ? 'required' : '' ?>>
                                            <?php elseif ($field['type'] === 'textarea'): ?>
                                                <textarea id="<?= $field['name'] ?>"
                                                          name="<?= $field['name'] ?>"
                                                          class="form-control"
                                                          rows="3"
                                                          <?= $readonly ? 'readonly' : '' ?>
                                                          <?= $field['required'] && !$readonly ? 'required' : '' ?>><?= htmlspecialchars($value) ?></textarea>
                                            <?php elseif ($field['type'] === 'number'): ?>
                                                <input type="number"
                                                       id="<?= $field['name'] ?>"
                                                       name="<?= $field['name'] ?>"
                                                       value="<?= htmlspecialchars($value) ?>"
                                                       class="form-control"
                                                       step="0.01"
                                                       <?= $readonly ? 'readonly' : '' ?>
                                                       <?= $field['required'] && !$readonly ? 'required' : '' ?>>
                                            <?php elseif ($field['type'] === 'date'): ?>
                                                <input type="date"
                                                       id="<?= $field['name'] ?>"
                                                       name="<?= $field['name'] ?>"
                                                       value="<?= htmlspecialchars($value) ?>"
                                                       class="form-control"
                                                       <?= $readonly ? 'readonly' : '' ?>
                                                       <?= $field['required'] && !$readonly ? 'required' : '' ?>>
                                            <?php elseif ($field['type'] === 'select'): ?>
                                                <select id="<?= $field['name'] ?>"
                                                        name="<?= $field['name'] ?>"
                                                        class="form-select"
                                                        <?= $readonly ? 'disabled' : '' ?>
                                                        <?= $field['required'] && !$readonly ? 'required' : '' ?>>
                                                    <option value="">Select...</option>
                                                    <?php if (isset($field['options'])): ?>
                                                        <?php foreach ($field['options'] as $option): ?>
                                                            <option value="<?= $option['id'] ?>" <?= $value == $option['id'] ? 'selected' : '' ?>>
                                                                <?= htmlspecialchars($option['label']) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </select>
                                                <?php if ($readonly): ?>
                                                    <input type="hidden" name="<?= $field['name'] ?>" value="<?= htmlspecialchars($value) ?>">
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Detail Records -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-list-nested me-2"></i>
                        <?= htmlspecialchars($detail['title']) ?>
                    </h5>
                    <button type="button" class="btn btn-light btn-sm" onclick="addDetailRow()">
                        <i class="bi bi-plus-circle me-1"></i>Add Item
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered mb-0" id="detailTable">
                            <thead class="table-light">
                                <tr>
                                    <th width="50">#</th>
                                    <?php foreach ($detail['fields'] as $field): ?>
                                        <?php if (!$field['hidden']): ?>
                                            <th><?= htmlspecialchars($field['label']) ?></th>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                    <th width="120">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="detailBody">
                                <?php foreach ($detailRecords as $index => $record): ?>
                                    <tr class="detail-row" data-row-id="<?= $record[$detail['primaryKey']] ?>">
                                        <td class="text-center"><?= $index + 1 ?></td>
                                        <?php foreach ($detail['fields'] as $field): ?>
                                            <?php if (!$field['hidden']): ?>
                                                <td>
                                                    <?php
                                                    $fieldName = "details[{$index}][{$field['name']}]";
                                                    $value = $record[$field['name']] ?? '';
                                                    ?>
                                                    <input type="hidden"
                                                           name="details[<?= $index ?>][<?= $detail['primaryKey'] ?>]"
                                                           value="<?= $record[$detail['primaryKey']] ?>">

                                                    <?php if ($field['type'] === 'text'): ?>
                                                        <input type="text"
                                                               name="<?= $fieldName ?>"
                                                               value="<?= htmlspecialchars($value) ?>"
                                                               class="form-control form-control-sm"
                                                               placeholder="<?= htmlspecialchars($field['placeholder'] ?? '') ?>">
                                                    <?php elseif ($field['type'] === 'number'): ?>
                                                        <input type="number"
                                                               name="<?= $fieldName ?>"
                                                               value="<?= htmlspecialchars($value) ?>"
                                                               class="form-control form-control-sm detail-qty"
                                                               step="0.01"
                                                               onchange="calculateRow(this)">
                                                    <?php elseif ($field['type'] === 'select'): ?>
                                                        <select name="<?= $fieldName ?>" class="form-select form-select-sm">
                                                            <option value="">Select...</option>
                                                            <?php if (isset($field['options'])): ?>
                                                                <?php foreach ($field['options'] as $option): ?>
                                                                    <option value="<?= $option['id'] ?>" <?= $value == $option['id'] ? 'selected' : '' ?>>
                                                                        <?= htmlspecialchars($option['label']) ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            <?php endif; ?>
                                                        </select>
                                                    <?php endif; ?>
                                                </td>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                        <td class="detail-actions">
                                            <button type="button" class="btn btn-sm btn-danger" onclick="removeDetailRow(this)">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="d-flex justify-content-between">
                        <div>
                            <strong>Total Items:</strong> <span id="totalItems"><?= count($detailRecords) ?></span>
                        </div>
                        <div>
                            <button type="button" class="btn btn-secondary" onclick="window.history.back()">
                                <i class="bi bi-x-circle me-1"></i>Cancel
                            </button>
                            <button type="submit" form="masterForm" class="btn btn-gradient">
                                <i class="bi bi-check-circle me-1"></i>Save Changes
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->fetch('cruds/master-detail/templates') ?>

<script>
let detailRowIndex = <?= count($detailRecords) ?>;

function addDetailRow() {
    const tbody = document.getElementById('detailBody');
    const newRow = document.createElement('tr');
    newRow.className = 'detail-row';
    newRow.setAttribute('data-row-id', '');

    let rowHtml = '<td class="text-center">' + (detailRowIndex + 1) + '</td>';

    <?php
    $indexPlaceholder = '__INDEX__';
    foreach ($detail['fields'] as $field): ?>
        <?php if (!$field['hidden']): ?>
            <?php if ($field['type'] === 'text'): ?>
                rowHtml += `
                    <td>
                        <input type="hidden" name="details[<?= $indexPlaceholder ?>][<?= $detail['primaryKey'] ?>]" value="">
                        <input type="text" name="details[<?= $indexPlaceholder ?>][<?= $field['name'] ?>]"
                               class="form-control form-control-sm"
                               placeholder="<?= htmlspecialchars($field['placeholder'] ?? $field['label']) ?>">
                    </td>`;
            <?php elseif ($field['type'] === 'number'): ?>
                rowHtml += `
                    <td>
                        <input type="hidden" name="details[<?= $indexPlaceholder ?>][<?= $detail['primaryKey'] ?>]" value="">
                        <input type="number" name="details[<?= $indexPlaceholder ?>][<?= $field['name'] ?>]"
                               class="form-control form-control-sm detail-qty"
                               step="0.01" value="0" onchange="calculateRow(this)">
                    </td>`;
            <?php elseif ($field['type'] === 'select'): ?>
                rowHtml += `
                    <td>
                        <input type="hidden" name="details[<?= $indexPlaceholder ?>][<?= $detail['primaryKey'] ?>]" value="">
                        <select name="details[<?= $indexPlaceholder ?>][<?= $field['name'] ?>]" class="form-select form-select-sm">
                            <option value="">Select...</option>
                            <?php if (isset($field['options'])): ?>
                                <?php foreach ($field['options'] as $option): ?>
                                    <option value="<?= $option['id'] ?>"><?= htmlspecialchars($option['label']) ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </td>`;
            <?php endif; ?>
        <?php endif; ?>
    <?php endforeach; ?>

    rowHtml += `
        <td class="detail-actions">
            <button type="button" class="btn btn-sm btn-danger" onclick="removeDetailRow(this)">
                <i class="bi bi-trash"></i>
            </button>
        </td>`;

    newRow.innerHTML = rowHtml.replace(/<?= $indexPlaceholder ?>/g, detailRowIndex);
    tbody.appendChild(newRow);

    detailRowIndex++;
    updateTotalItems();
}

function removeDetailRow(button) {
    if (confirm('Remove this item?')) {
        const row = button.closest('tr');
        row.remove();
        updateTotalItems();
        renumberRows();
    }
}

function renumberRows() {
    const rows = document.querySelectorAll('#detailBody tr');
    rows.forEach((row, index) => {
        row.querySelector('td:first-child').textContent = index + 1;
    });
}

function updateTotalItems() {
    const count = document.querySelectorAll('#detailBody tr').length;
    document.getElementById('totalItems').textContent = count;
}

// Form validation
document.getElementById('masterForm').addEventListener('submit', function(e) {
    const detailRows = document.querySelectorAll('#detailBody tr');
    if (detailRows.length === 0) {
        e.preventDefault();
        alert('Please add at least one item.');
        return false;
    }
});
</script>
