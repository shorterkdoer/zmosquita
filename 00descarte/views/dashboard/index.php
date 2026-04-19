<?php
/**
 * Unified Dashboard View
 *
 * This view dynamically adapts based on the user's role, displaying
 * appropriate menu items and actions.
 *
 * Available variables:
 * - $user: Array with user data (id, email, role)
 * - $menuItems: Array with menu configuration for the user's role
 * - $role: UserRole enum instance
 * - $userId: The user's ID (null for guests)
 */

use App\Core\Helpers\DashboardMenuHelper;

$this->layout('layout', ['title' => 'Dashboard']);
?>

<!-- Dashboard Header -->
<?= DashboardMenuHelper::renderHeader($menuItems) ?>

<!-- Welcome message for authenticated users -->
<?php if ($userId): ?>
<div class="alert alert-info alert-dismissible fade show" role="alert">
    <strong>¡Bienvenido, <?= $this->e($user['email']) ?>!</strong>
    Tu rol actual es: <span class="badge bg-primary"><?= $this->e($role->getLabel()) ?></span>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<!-- Role-specific information -->
<?php if ($role->isSuperuser()): ?>
<div class="alert alert-warning" role="alert">
    <i class="bi bi-exclamation-triangle-fill"></i>
    <strong>Modo Superusuario:</strong> Tienes acceso completo al sistema, incluyendo gestión de administradores y configuración del sistema.
</div>
<?php elseif ($role->isAdmin()): ?>
<div class="alert alert-primary" role="alert">
    <i class="bi bi-shield-fill-check"></i>
    <strong>Panel Administrativo:</strong> Puedes gestionar usuarios, matrículas, cobranzas y realizar tareas administrativas.
</div>
<?php elseif ($role->isUser()): ?>
<div class="alert alert-success" role="alert">
    <i class="bi bi-person-check-fill"></i>
    <strong>Panel de Usuario:</strong> Gestiona tu matriculación, datos personales y pagos desde aquí.
</div>
<?php endif; ?>

<!-- Quick Stats (for admin roles) -->
<?php if ($role->isAdmin()): ?>
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title">Matriculados Activos</h5>
                <p class="card-text display-6 text-primary">
                    <i class="bi bi-people-fill"></i>
                </p>
                <a href="/activos" class="btn btn-sm btn-outline-primary">Ver Detalles</a>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title">Inscripciones</h5>
                <p class="card-text display-6 text-success">
                    <i class="bi bi-file-earmark-text-fill"></i>
                </p>
                <a href="/controlinscripciones" class="btn btn-sm btn-outline-success">Gestionar</a>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title">Cobranzas</h5>
                <p class="card-text display-6 text-warning">
                    <i class="bi bi-currency-dollar"></i>
                </p>
                <a href="/controlcobros" class="btn btn-sm btn-outline-warning">Ver</a>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title">Bajas</h5>
                <p class="card-text display-6 text-danger">
                    <i class="bi bi-person-dash-fill"></i>
                </p>
                <a href="/menubajas" class="btn btn-sm btn-outline-danger">Administrar</a>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- User-specific quick actions -->
<?php if ($role->isUser()): ?>
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title">Estado de Matrícula</h5>
                <p class="card-text">Consulta y gestiona tu matrícula profesional</p>
                <a href="/matriculas/edit/<?= $this->e($userId) ?>" class="btn btn-primary">
                    <i class="bi bi-card-list"></i> Ver Mi Matrícula
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title">Datos Personales</h5>
                <p class="card-text">Mantén tu información actualizada</p>
                <a href="/datospersonales/edit/<?= $this->e($userId) ?>" class="btn btn-primary">
                    <i class="bi bi-card-checklist"></i> Actualizar Datos
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title">Mis Pagos</h5>
                <p class="card-text">Historial de comprobantes de pago</p>
                <a href="/miscomprobantes/" class="btn btn-primary">
                    <i class="bi bi-cash-stack"></i> Ver Pagos
                </a>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Main Menu Grid -->
<?= DashboardMenuHelper::renderMenu($menuItems, $userId) ?>

<!-- Help/Support section -->
<?php if (!$role->isGuest()): ?>
<div class="mt-5 p-4 bg-light rounded">
    <h5><i class="bi bi-question-circle"></i> ¿Necesitas ayuda?</h5>
    <p>Si tienes algún problema o pregunta sobre el sistema, puedes:</p>
    <ul>
        <li>Consultar la sección de <a href="/requisitos">Requisitos de Matriculación</a></li>
        <li>Visitar la sección <a href="/institucional">Institucional</a> para más información</li>
        <li>Reportar un problema desde el botón <strong>Reporte de Inconvenientes</strong> en tu menú</li>
    </ul>
</div>
<?php else: ?>
<!-- Guest welcome section -->
<div class="mt-5 p-4 bg-light rounded text-center">
    <h4><i class="bi bi-info-circle"></i> Sistema de Matriculación</h4>
    <p>Bienvenido al sistema de matriculación del Colegio de Bioquímicos.</p>
    <p>Para acceder a todas las funcionalidades, por favor <a href="/login">inicia sesión</a> o <a href="/register">crea una cuenta</a>.</p>
</div>
<?php endif; ?>

<style>
/* Custom dashboard styles */
.dashboard-card {
    transition: transform 0.2s, box-shadow 0.2s;
}

.dashboard-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
}

.btn-outline-primary:hover,
.btn-outline-success:hover {
    transform: scale(1.05);
    transition: transform 0.2s;
}

/* Animation for dashboard cards */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.col {
    animation: fadeInUp 0.5s ease-out;
}

/* Stagger animation for cards */
.col:nth-child(1) { animation-delay: 0.05s; }
.col:nth-child(2) { animation-delay: 0.1s; }
.col:nth-child(3) { animation-delay: 0.15s; }
.col:nth-child(4) { animation-delay: 0.2s; }
.col:nth-child(5) { animation-delay: 0.25s; }
.col:nth-child(6) { animation-delay: 0.3s; }
.col:nth-child(7) { animation-delay: 0.35s; }
.col:nth-child(8) { animation-delay: 0.4s; }
</style>
