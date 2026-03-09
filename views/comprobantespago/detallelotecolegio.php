<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$preview = $_SESSION['lote_colegio_preview'] ?? null;
if (!$preview) {
    ?>
    <div class="alert alert-warning">No hay lote en previsualización.</div>
    <a href="/comprobantespago/lote-colegio" class="btn btn-secondary">Volver al formulario</a>
    <?php
    return;
}
$rows  = $preview['rows'] ?? [];
$fecha = $preview['fecha'] ?? '';
$monto = $preview['monto'] ?? 0;
?>

<div class="container my-4">
    <h3>Lote de cobranzas – Previsualización</h3>
    <p><strong>Fecha de imputación:</strong> <?= htmlspecialchars($fecha) ?></p>
    <p><strong>Importe por profesional:</strong> $ <?= number_format($monto, 2, ',', '.') ?></p>
    <p><strong>Cantidad de registros:</strong> <?= count($rows) ?></p>

    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>Matrícula</th>
                <th>Nombre</th>
                <th>DNI</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
            <tr>
                <td><?= htmlspecialchars($r['matricula']) ?></td>
                <td><?= htmlspecialchars($r['nombre']) ?></td>
                <td><?= htmlspecialchars($r['dni']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <form method="post" action="/comprobantespago/lote-colegio/confirm" class="d-inline">
        <button type="submit" class="btn btn-success">
            <i class="bi bi-check-circle"></i> Confirmar lote
        </button>
    </form>

    <form method="post" action="/comprobantespago/lote-colegio/cancel" class="d-inline ms-2">
        <button type="submit" class="btn btn-outline-danger">
            <i class="bi bi-x-circle"></i> Cancelar
        </button>
    </form>

    <a href="/comprobantespago/lote-colegio" class="btn btn-link ms-2">Volver al formulario</a>
</div>
