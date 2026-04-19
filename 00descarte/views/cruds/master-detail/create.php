<?php
/**
 * Master-Detail Create View
 * Form to create a new master record
 */
$this->layout('layouts/app', ['title' => $title ?? 'New Record'])
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-plus-circle me-2"></i>
                        <?= htmlspecialchars($title) ?>
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= "/{$master['route']}/store" ?>">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                        <div class="row g-3">
                            <?php foreach ($master['fields'] as $field): ?>
                                <?php if (!$field['hidden'] && !$field['readonly']): ?>
                                    <div class="col-md-<?= $field['width'] ?? '12' ?>">
                                        <div class="form-group">
                                            <label for="<?= $field['name'] ?>" class="form-label">
                                                <?= htmlspecialchars($field['label']) ?>
                                                <?php if ($field['required']): ?>
                                                    <span class="text-danger">*</span>
                                                <?php endif; ?>
                                            </label>

                                            <?php if ($field['type'] === 'text'): ?>
                                                <input type="text"
                                                       id="<?= $field['name'] ?>"
                                                       name="<?= $field['name'] ?>"
                                                       class="form-control"
                                                       placeholder="<?= htmlspecialchars($field['placeholder'] ?? '') ?>"
                                                       <?= $field['required'] ? 'required' : '' ?>>
                                            <?php elseif ($field['type'] === 'textarea'): ?>
                                                <textarea id="<?= $field['name'] ?>"
                                                          name="<?= $field['name'] ?>"
                                                          class="form-control"
                                                          rows="4"
                                                          placeholder="<?= htmlspecialchars($field['placeholder'] ?? '') ?>"
                                                          <?= $field['required'] ? 'required' : '' ?>></textarea>
                                            <?php elseif ($field['type'] === 'number'): ?>
                                                <input type="number"
                                                       id="<?= $field['name'] ?>"
                                                       name="<?= $field['name'] ?>"
                                                       class="form-control"
                                                       placeholder="<?= htmlspecialchars($field['placeholder'] ?? '') ?>"
                                                       step="0.01"
                                                       <?= $field['required'] ? 'required' : '' ?>>
                                            <?php elseif ($field['type'] === 'date'): ?>
                                                <input type="date"
                                                       id="<?= $field['name'] ?>"
                                                       name="<?= $field['name'] ?>"
                                                       class="form-control"
                                                       <?= $field['required'] ? 'required' : '' ?>>
                                            <?php elseif ($field['type'] === 'select'): ?>
                                                <select id="<?= $field['name'] ?>"
                                                        name="<?= $field['name'] ?>"
                                                        class="form-select"
                                                        <?= $field['required'] ? 'required' : '' ?>>
                                                    <option value="">Select...</option>
                                                    <?php if (isset($field['options'])): ?>
                                                        <?php foreach ($field['options'] as $option): ?>
                                                            <option value="<?= $option['id'] ?>">
                                                                <?= htmlspecialchars($option['label']) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </select>
                                            <?php endif; ?>

                                            <?php if (isset($field['help'])): ?>
                                                <small class="form-text text-muted">
                                                    <?= htmlspecialchars($field['help']) ?>
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="<?= "/{$master['route']}" ?>" class="btn btn-secondary">
                                <i class="bi bi-arrow-left me-1"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-gradient">
                                <i class="bi bi-save me-1"></i>Save & Add Details
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
