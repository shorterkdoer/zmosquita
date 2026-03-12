<?php
namespace App\Services;

use App\Repositories\MatriculaRepository;
use App\Repositories\DatosPersonalesRepository;
use App\Repositories\UserRepository;

/**
 * MatriculaService - Handles matricula business logic
 *
 * Refactored to use Repository Pattern for data access
 */
class MatriculaService
{
    protected TramiteService $tramites;
    protected EmailService $emails;
    protected MatriculaRepository $matriculaRepo;
    protected DatosPersonalesRepository $datosPersonalesRepo;
    protected UserRepository $userRepo;

    public function __construct(
        TramiteService $tramites,
        EmailService $emails,
        ?MatriculaRepository $matriculaRepo = null,
        ?DatosPersonalesRepository $datosPersonalesRepo = null,
        ?UserRepository $userRepo = null
    ) {
        $this->tramites = $tramites;
        $this->emails = $emails;
        $this->matriculaRepo = $matriculaRepo ?? new MatriculaRepository();
        $this->datosPersonalesRepo = $datosPersonalesRepo ?? new DatosPersonalesRepository();
        $this->userRepo = $userRepo ?? new UserRepository();
    }

    /**
     * Get matricula status for a user
     *
     * Possible statuses: '', 'Baja', 'Revisión', 'Verificado', 'Activa', 'Solicitada'
     */
    public function getStatus(int $userId): string
    {
        return $this->matriculaRepo->getStatus($userId);
    }

    /**
     * Get detailed status info for a user
     *
     * @return array ['status' => string, 'matricula' => array|null]
     */
    public function getDetailedStatus(int $userId): array
    {
        $statusInfo = $this->matriculaRepo->getDetailedStatus($userId);

        $status = $statusInfo['status'];

        return [
            'status' => $status,
            'matricula' => $statusInfo['matricula'] ?? null,
            'canEdit' => $statusInfo['can_edit'],
            'canRequestRevision' => $statusInfo['can_request_revision'],
            'hasMatriculaNumber' => $statusInfo['has_matricula'],
        ];
    }

    /**
     * Request revision of matricula documentation
     *
     * @param int $userId User ID
     * @return array ['success' => bool, 'error' => string|null]
     */
    public function solicitarRevision(int $userId): array
    {
        $status = $this->getStatus($userId);

        if ($status !== '') {
            return [
                'success' => false,
                'error' => 'No puede solicitar revisión. Ya tiene un trámite en curso.'
            ];
        }

        // Freeze data
        $this->matriculaRepo->setFreezedata($userId);

        // Get user info for notifications
        $user = $this->userRepo->find($userId);
        $datos = $this->datosPersonalesRepo->findByUserId($userId);
        $nombre = 'Usuario';
        if ($datos) {
            $nombre = ucwords(($datos['apellido'] ?? '') . ' ' . ($datos['nombre'] ?? ''));
        }

        // Send notifications
        $this->emails->sendRevisionNotification($user['email'], $nombre);

        return [
            'success' => true,
            'error' => null
        ];
    }

    /**
     * Grant matricula to user
     *
     * @param int $userId User ID
     * @param int $numero Matricula number
     * @param int $comisionId Commission ID that granted the matricula
     * @return array ['success' => bool, 'error' => string|null]
     */
    public function otorgarMatricula(int $userId, int $numero, int $comisionId): array
    {
        if (!$this->matriculaRepo->grant($userId, $numero, $comisionId)) {
            return [
                'success' => false,
                'error' => 'Error otorgando matrícula.'
            ];
        }

        return [
            'success' => true,
            'error' => null
        ];
    }

    /**
     * Grant matricula by ID (direct update)
     *
     * @param int $matriculaId Matricula record ID
     * @param int $numero Matricula number
     * @param string $fecha Approval date
     * @return array ['success' => bool, 'error' => string|null]
     */
    public function otorgarMatriculaById(int $matriculaId, int $numero, string $fecha): array
    {
        $data = [
            'matriculaasignada' => $numero,
            'aprobado' => $fecha
        ];

        if (!$this->matriculaRepo->update($matriculaId, $data)) {
            return [
                'success' => false,
                'error' => 'Error otorgando matrícula.'
            ];
        }

        return [
            'success' => true,
            'error' => null
        ];
    }

    /**
     * Process baja (discharge) for matricula
     *
     * @param int $userId User ID
     * @param string $motivo Reason for discharge
     * @return array ['success' => bool, 'error' => string|null]
     */
    public function darDeBaja(int $userId, string $motivo): array
    {
        if (!$this->matriculaRepo->darDeBaja($userId, $motivo)) {
            return [
                'success' => false,
                'error' => 'Error procesando baja.'
            ];
        }

        return [
            'success' => true,
            'error' => null
        ];
    }

    /**
     * Assign reviewer to matricula
     *
     * @param int $userId User ID
     * @param int $reviewerId Reviewer user ID
     * @return array ['success' => bool, 'error' => string|null]
     */
    public function asignarRevisor(int $userId, int $reviewerId): array
    {
        if (!$this->matriculaRepo->assignRevisor($userId, $reviewerId)) {
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
     * Assign verifier for physical verification
     *
     * @param int $userId User ID
     * @param int $verifierId Verifier user ID
     * @return array ['success' => bool, 'error' => string|null]
     */
    public function asignarVerificador(int $userId, int $verifierId): array
    {
        if (!$this->matriculaRepo->assignVerificador($userId, $verifierId)) {
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
     * Update matricula state (suspendida/inhabilitada)
     *
     * @param int $userId User ID
     * @param string $estado New state
     * @return array ['success' => bool, 'error' => string|null]
     */
    public function actualizarEstado(int $userId, string $estado): array
    {
        if (!in_array($estado, ['', 'Suspendida', 'Inhabilitada'])) {
            return [
                'success' => false,
                'error' => 'Estado inválido.'
            ];
        }

        if (!$this->matriculaRepo->updateEstado($userId, $estado ?: null)) {
            return [
                'success' => false,
                'error' => 'Error actualizando estado.'
            ];
        }

        return [
            'success' => true,
            'error' => null
        ];
    }

    /**
     * Get matricula by user ID
     */
    public function findByUserId(int $userId): ?array
    {
        return $this->matriculaRepo->findByUserId($userId);
    }

    /**
     * Get matricula by assigned number
     */
    public function findByNumero(int $numero): ?array
    {
        return $this->matriculaRepo->findByNumero($numero);
    }

    /**
     * Get next available matricula number
     */
    public function getNextNumero(): int
    {
        return $this->matriculaRepo->getNextNumero();
    }

    /**
     * Clear revision status (reset revision, verificado, freezedata)
     *
     * @param int $userId User ID
     * @param int $intervinienteId Interviniente user ID
     * @return array ['success' => bool, 'error' => string|null]
     */
    public function clearRevisionStatus(int $userId, int $intervinienteId): array
    {
        $data = [
            'interviniente' => $intervinienteId,
            'revision' => null,
            'freezedata' => null
        ];

        if (!$this->matriculaRepo->updateByUserId($userId, $data)) {
            return [
                'success' => false,
                'error' => 'Error limpiando estado de revisión.'
            ];
        }

        return [
            'success' => true,
            'error' => null
        ];
    }

    /**
     * Approve physical verification and clear revision
     *
     * @param int $userId User ID
     * @param int $intervinienteId Interviniente user ID
     * @return array ['success' => bool, 'error' => string|null]
     */
    public function aprobarVerificacionFisica(int $userId, int $intervinienteId): array
    {
        $fecha = date('Y/m/d');

        $data = [
            'interviniente' => $intervinienteId,
            'revision' => null,
            'aprobado' => $fecha
        ];

        if (!$this->matriculaRepo->updateByUserId($userId, $data)) {
            return [
                'success' => false,
                'error' => 'Error aprobando verificación física.'
            ];
        }

        return [
            'success' => true,
            'error' => null
        ];
    }

    /**
     * Get user email by user ID
     *
     * @param int $userId User ID
     * @return string|null Email or null
     */
    public function getUserEmail(int $userId): ?string
    {
        $user = $this->userRepo->find($userId);
        return $user['email'] ?? null;
    }

    /**
     * Update credential files (carnet, carnetpdf)
     *
     * @param int $userId User ID
     * @param array $data Files data ['carnet' => string, 'carnetpdf' => string]
     * @return array ['success' => bool, 'error' => string|null]
     */
    public function updateCredentialFiles(int $userId, array $data): array
    {
        if (!$this->matriculaRepo->updateByUserId($userId, $data)) {
            return [
                'success' => false,
                'error' => 'Error actualizando archivos de credencial.'
            ];
        }

        return [
            'success' => true,
            'error' => null
        ];
    }

    /**
     * Find matricula by assigned number
     *
     * @param int $numero Matricula number
     * @return array|null Matricula data or null
     */
    public function findByAsignada(int $numero): ?array
    {
        return $this->matriculaRepo->findByNumero($numero);
    }

    /**
     * Clear all verification statuses (revision, freezedata, verificado)
     *
     * @param int $userId User ID
     * @param int $intervinienteId Interviniente user ID
     * @return array ['success' => bool, 'error' => string|null]
     */
    public function clearAllVerificationStatuses(int $userId, int $intervinienteId): array
    {
        $data = [
            'interviniente' => $intervinienteId,
            'revision' => null,
            'freezedata' => null,
            'verificado' => null
        ];

        if (!$this->matriculaRepo->updateByUserId($userId, $data)) {
            return [
                'success' => false,
                'error' => 'Error limpiando estados de verificación.'
            ];
        }

        return [
            'success' => true,
            'error' => null
        ];
    }
}
