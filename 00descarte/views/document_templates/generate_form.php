
<h2>Generar documento: <?= $this->e($template->name) ?></h2>
<p><?= $this->e($template->description) ?></p>

<form method="post" action="/document_templates/generate">
  <input type="hidden" name="template_id" value="<?= $template->id ?>">

  <div class="mb-3">
    <label class="form-label">ID del registro</label>
    <input type="number" name="record_id" class="form-control" required>
  </div>

  <button type="submit" class="btn btn-success">Generar PDF</button>
</form>
