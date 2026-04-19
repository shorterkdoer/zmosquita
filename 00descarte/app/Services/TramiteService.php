<?php
namespace App\Services;

use App\Repositories\TramiteRepository;
use App\Repositories\UserRepository;
use App\Repositories\DatosPersonalesRepository;

/**
 * TramiteService - Handles tramites (paperwork) business logic
 *
 * Refactored to use Repository Pattern for data access
 */
class TramiteService
{
    protected EmailService $emails;
    protected TramiteRepository $tramiteRepo;
    protected UserRepository $userRepo;
    protected DatosPersonalesRepository $datosPersonalesRepo;

    public function __construct(
        EmailService $emails,
        ?TramiteRepository $tramiteRepo = null,
        ?UserRepository $userRepo = null,
        ?DatosPersonalesRepository $datosPersonalesRepo = null
    ) {
        $this->emails = $emails;
        $this->tramiteRepo = $tramiteRepo ?? new TramiteRepository();
        $this->userRepo = $userRepo ?? new UserRepository();
        $this->datosPersonalesRepo = $datosPersonalesRepo ?? new DatosPersonalesRepository();
    }

    /**
     * Create a revision tramite for a user
     *
     * @param int $userId User ID
     * @return array ['success' => bool, 'error' => string|null, 'tramite_id' => int|null]
     */
    public function crearRevision(int $userId): array
    {
        $data = [
            'user_id' => $userId,
            'tipo' => 'revision',
            'fecha' => date('Y-m-d H:i:s')
        ];

        $tramiteId = $this->tramiteRepo->create($data);

        if (!$tramiteId) {
            return [
                'success' => false,
                'error' => 'Error creando trámite de revisión.',
                'tramite_id' => null
            ];
        }

        return [
            'success' => true,
            'error' => null,
            'tramite_id' => $tramiteId
        ];
    }

    /**
     * Assign reviewer to a tramite
     *
     * @param int $tramiteId Tramite ID
     * @param int $reviewerId Reviewer user ID
     * @return array ['success' => bool, 'error' => string|null]
     */
    public function asignarRevisor(int $tramiteId, int $reviewerId): array
    {
        if (!$this->tramiteRepo->assignRevisor($tramiteId, $reviewerId)) {
            return [
                'success' => false,
                'error' => 'Error asignando revisor.'
            ];
        }

        return [
            'success' => true,
            'error' => null
        ];
    }

    /**
     * Mark tramite as completed by reviewer
     *
     * @param int $tramiteId Tramite ID
     * @param string $observaciones Optional observations
     * @return array ['success' => bool, 'error' => string|null]
     */
    public function completarRevision(int $tramiteId, string $observaciones = ''): array
    {
        if (!$this->tramiteRepo->markCompletado($tramiteId, $observaciones)) {
            return [
                'success' => false,
                'error' => 'Error completando revisión.'
            ];
        }

        return [
            'success' => true,
            'error' => null
        ];
    }

    /**
     * Assign verifier for physical verification
     *
     * @param int $tramiteId Tramite ID
     * @param int $verifierId Verifier user ID
     * @return array ['success' => bool, 'error' => string|null]
     */
    public function asignarVerificador(int $tramiteId, int $verifierId): array
    {
        if (!$this->tramiteRepo->assignVerificador($tramiteId, $verifierId)) {
            return [
                'success' => false,
                'error' => 'Error asignando verificador.'
            ];
        }

        return [
            'success' => true,
            'error' => null
        ];
    }

    /**
     * Get pending tramites for review
     *
     * @return array Tramites pending review
     */
    public function getPendingRevision(): array
    {
        return $this->tramiteRepo->getPendingRevision();
    }

    /**
     * Get tramites assigned to a specific reviewer
     *
     * @param int $reviewerId Reviewer user ID
     * @return array Assigned tramites
     */
    public function getByRevisor(int $reviewerId): array
    {
        return $this->tramiteRepo->getByRevisor($reviewerId);
    }

    /**
     * Get tramite by user ID
     *
     * @param int $userId User ID
     * @return array|null Tramite data or null
     */
    public function findByUserId(int $userId): ?array
    {
        return $this->tramiteRepo->findByUserId($userId);
    }

    /**
     * Notify user about revision assignment
     *
     * @param string $email User email
     * @param string $nombre User name
     * @param string $fecha Assigned date/time
     * @return bool Success status
     */
    public function notifyRevisionAssignment(string $email, string $nombre, string $fecha): bool
    {
        $body = "
            <p>Estimado/a {$nombre},</p>
            <p>Su documentación ha sido asignada para revisión.</p>
            <p>Fecha de asignación: {$fecha}</p>
            <p>En breve será contactado para la presentación física.</p>
        ";

        return $this->emails->send($email, 'Revisión asignada', $body);
    }

    /**
     * Send verification appointment notification
     *
     * @param string $email User email
     * @param string $nombre User name
     * @param string $fecha Appointment date/time
     * @return bool Success status
     */
    public function notifyVerificationAppointment(string $email, string $nombre, string $fecha): bool
    {
        $body = "
            <p>Estimado/a {$nombre},</p>
            <p>Su turno para verificación física ha sido programado.</p>
            <p><strong>Fecha y hora:</strong> {$fecha}</p>
            <p>Por favor presentarse con documentación original.</p>
        ";

        return $this->emails->send($email, 'Turno de verificación física', $body);
    }

    /**
     * Get tramite status for a user
     *
     * @param int $userId User ID
     * @return array Status information
     */
    public function getStatus(int $userId): array
    {
        return $this->tramiteRepo->getStatus($userId);
    }

    /**
     * Register a tramite with custom observations
     *
     * @param int $userId User ID
     * @param string $observaciones Observations/description
     * @param string $fecha Optional date (defaults to today)
     * @return array ['success' => bool, 'error' => string|null]
     */
    public function registrarTramite(int $userId, string $observaciones, string $fecha = ''): array
    {
        if ($fecha === '') {
            $fecha = date('Y/m/d');
        }

        $data = [
            'user_id' => $userId,
            'fecha' => $fecha,
            'observaciones' => $observaciones
        ];

        if (!$this->tramiteRepo->create($data)) {
            return [
                'success' => false,
                'error' => 'Error registrando trámite.'
            ];
        }

        return [
            'success' => true,
            'error' => null
        ];
    }

    /**
     * Reject revision and notify user
     *
     * @param int $userId User ID
     * @param int $revisorId Revisor user ID
     * @param string $motivo Rejection reason
     * @param bool $isFisico Whether this is physical verification rejection
     * @return array ['success' => bool, 'error' => string|null]
     */
    public function rechazarRevision(int $userId, int $revisorId, string $motivo, bool $isFisico = false): array
    {
        $fecha = date('Y/m/d');

        // Get revisor name
        $datosRevisor = $this->datosPersonalesRepo->findByUserId($revisorId);
        $nombreRevisor = 'Administrador';
        if ($datosRevisor) {
            $nombreRevisor = trim(($datosRevisor['apellido'] ?? '') . ' ' . ($datosRevisor['nombre'] ?? ''));
            if ($nombreRevisor === ' ') $nombreRevisor = 'Administrador';
        }

        // Build observation text
        $tipo = $isFisico ? 'verificación física' : 'revisión';
        $observaciones = "Rechazo de {$tipo}: {$nombreRevisor}. Motivo: {$motivo}";

        // Register the tramite
        $result = $this->registrarTramite($userId, $observaciones, $fecha);

        if (!$result['success']) {
            return $result;
        }

        // Send email notification
        $user = $this->userRepo->find($userId);
        if ($user) {
            $subject = 'Revisión de documentación';
            $body = "Su solicitud de {$tipo} ha sido rechazada. Motivo: {$motivo}";

            $this->emails->sendGeneric($user['email'], $subject, $body);
        }

        return [
            'success' => true,
            'error' => null
        ];
    }

    /**
     * Approve physical verification
     *
     * @param int $userId User ID
     * @param int $verificadorId Verifier user ID
     * @param string $observaciones Optional observations
     * @return array ['success' => bool, 'error' => string|null]
     */
    public function aprobarVerificacionFisica(int $userId, int $verificadorId, string $observaciones = ''): array
    {
        $fecha = date('Y/m/d');

        // Get verifier name
        $datosVerificador = $this->datosPersonalesRepo->findByUserId($verificadorId);
        $nombreVerificador = 'Administrador';
        if ($datosVerificador) {
            $nombreVerificador = trim(($datosVerificador['apellido'] ?? '') . ' ' . ($datosVerificador['nombre'] ?? ''));
            if ($nombreVerificador === ' ') $nombreVerificador = 'Administrador';
        }

        // Build observation text
        $txtObservaciones = $observaciones ? ' ' . $observaciones : '';
        $textoTramite = "Verificación física aprobada por: {$nombreVerificador}{$txtObservaciones}";

        // Register the tramite
        $result = $this->registrarTramite($userId, $textoTramite, $fecha);

        if (!$result['success']) {
            return $result;
        }

        // Send email notification
        $user = $this->userRepo->find($userId);
        if ($user) {
            $subject = 'Verificación de documentación';
            $body = "La verificación física de la documentación fue satisfactoria. ";
            $body .= "Fecha: {$fecha}. Interviniente: {$nombreVerificador}.";

            $this->emails->sendGeneric($user['email'], $subject, $body);
        }

        return [
            'success' => true,
            'error' => null
        ];
    }

    /**
     * Execute custom query and return results
     *
     * @param string $query SQL query
     * @return array Query results
     */
    public function customQuery(string $query): array
    {
        return $this->tramiteRepo->query($query);
    }

    /**
     * Find tramite by ID
     *
     * @param int $id Tramite ID
     * @return array|null Tramite data or null
     */
    public function find(int $id): ?array
    {
        return $this->tramiteRepo->find($id);
    }

    /**
     * Update tramite by ID
     *
     * @param int $id Tramite ID
     * @param array $data Data to update
     * @return bool Success status
     */
    public function update(int $id, array $data): bool
    {
        return $this->tramiteRepo->update($id, $data);
    }

    /**
     * Delete tramite by ID
     *
     * @param int $id Tramite ID
     * @return bool Success status
     */
    public function delete(int $id): bool
    {
        return $this->tramiteRepo->delete($id);
    }
}
