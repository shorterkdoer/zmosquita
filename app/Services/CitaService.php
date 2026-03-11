<?php
namespace App\Services;

use App\Repositories\CitaRepository;
use App\Repositories\UserRepository;
use App\Repositories\DatosPersonalesRepository;

/**
 * CitaService - Handles appointment (cita) scheduling and management
 *
 * Refactored to use Repository Pattern for data access
 */
class CitaService
{
    protected CitaRepository $citaRepo;
    protected UserRepository $userRepo;
    protected DatosPersonalesRepository $datosPersonalesRepo;

    public function __construct(
        CitaRepository $citaRepo = null,
        UserRepository $userRepo = null,
        DatosPersonalesRepository $datosPersonalesRepo = null
    ) {
        $this->citaRepo = $citaRepo ?? new CitaRepository();
        $this->userRepo = $userRepo ?? new UserRepository();
        $this->datosPersonalesRepo = $datosPersonalesRepo ?? new DatosPersonalesRepository();
    }
    /**
     * Create a new appointment
     *
     * @param array $data Appointment data
     * @return array ['success' => bool, 'error' => string|null, 'cita_id' => int|null]
     */
    public function create(array $data): array
    {
        // Validate required fields
        if (empty($data['fecha']) || empty($data['hora'])) {
            return [
                'success' => false,
                'error' => 'Fecha y hora son obligatorias.',
                'cita_id' => null
            ];
        }

        // Check if slot is available
        if (!$this->isSlotAvailable($data['fecha'], $data['hora'])) {
            return [
                'success' => false,
                'error' => 'El horario seleccionado no está disponible.',
                'cita_id' => null
            ];
        }

        $citaId = $this->citaRepo->create($data);

        if (!$citaId) {
            return [
                'success' => false,
                'error' => 'Error al crear la cita.',
                'cita_id' => null
            ];
        }

        return [
            'success' => true,
            'error' => null,
            'cita_id' => $citaId
        ];
    }

    /**
     * Update appointment
     *
     * @param int $citaId Appointment ID
     * @param array $data New data
     * @return array ['success' => bool, 'error' => string|null]
     */
    public function update(int $citaId, array $data): array
    {
        if (!$this->citaRepo->update($citaId, $data)) {
            return [
                'success' => false,
                'error' => 'Error al actualizar la cita.'
            ];
        }

        return [
            'success' => true,
            'error' => null
        ];
    }

    /**
     * Delete appointment
     *
     * @param int $citaId Appointment ID
     * @return array ['success' => bool, 'error' => string|null]
     */
    public function delete(int $citaId): array
    {
        if (!$this->citaRepo->delete($citaId)) {
            return [
                'success' => false,
                'error' => 'Error al eliminar la cita.'
            ];
        }

        return [
            'success' => true,
            'error' => null
        ];
    }

    /**
     * Get appointment by ID
     *
     * @param int $citaId Appointment ID
     * @return array|null Appointment data or null
     */
    public function findById(int $citaId): ?array
    {
        return $this->citaRepo->find($citaId);
    }

    /**
     * Get appointments by date range
     *
     * @param string $fechaFrom Start date (Y-m-d)
     * @param string $fechaTo End date (Y-m-d)
     * @return array Appointments in range
     */
    public function getByDateRange(string $fechaFrom, string $fechaTo): array
    {
        return $this->citaRepo->findByDateRange($fechaFrom, $fechaTo);
    }

    /**
     * Get available slots for a specific date
     *
     * @param string $fecha Date (Y-m-d)
     * @param string $horaFrom Start time (H:i:s)
     * @param string $horaTo End time (H:i:s)
     * @return array Available time slots
     */
    public function getAvailableSlots(string $fecha, string $horaFrom = '08:00:00', string $horaTo = '18:00:00'): array
    {
        // Get all booked slots for the date
        $bookedSlots = $this->citaRepo->getBookedSlots($fecha, ['confirmada']);

        // Generate all possible slots (30-minute intervals)
        $slots = [];
        $current = strtotime($horaFrom);
        $end = strtotime($horaTo);

        while ($current < $end) {
            $time = date('H:i:s', $current);
            if (!in_array($time, $bookedSlots)) {
                $slots[] = $time;
            }
            $current += 1800; // Add 30 minutes
        }

        return $slots;
    }

    /**
     * Check if a specific slot is available
     *
     * @param string $fecha Date (Y-m-d)
     * @param string $hora Time (H:i:s)
     * @return bool True if available
     */
    public function isSlotAvailable(string $fecha, string $hora): bool
    {
        return $this->citaRepo->isSlotAvailable($fecha, $hora);
    }

    /**
     * Confirm appointment
     *
     * @param int $citaId Appointment ID
     * @return array ['success' => bool, 'error' => string|null]
     */
    public function confirmar(int $citaId): array
    {
        if (!$this->citaRepo->confirmar($citaId)) {
            return [
                'success' => false,
                'error' => 'Error al confirmar la cita.'
            ];
        }

        return [
            'success' => true,
            'error' => null
        ];
    }

    /**
     * Cancel appointment
     *
     * @param int $citaId Appointment ID
     * @return array ['success' => bool, 'error' => string|null]
     */
    public function cancelar(int $citaId): array
    {
        if (!$this->citaRepo->cancelar($citaId)) {
            return [
                'success' => false,
                'error' => 'Error al cancelar la cita.'
            ];
        }

        return [
            'success' => true,
            'error' => null
        ];
    }

    /**
     * Complete appointment
     *
     * @param int $citaId Appointment ID
     * @param string $observaciones Optional observations
     * @return array ['success' => bool, 'error' => string|null]
     */
    public function completar(int $citaId, string $observaciones = ''): array
    {
        if (!$this->citaRepo->completar($citaId, $observaciones)) {
            return [
                'success' => false,
                'error' => 'Error al completar la cita.'
            ];
        }

        return [
            'success' => true,
            'error' => null
        ];
    }

    /**
     * Get appointments by user
     *
     * @param int $userId User ID
     * @return array User appointments
     */
    public function getByUser(int $userId): array
    {
        return $this->citaRepo->findByUserId($userId);
    }

    /**
     * Get upcoming appointments for a user
     *
     * @param int $userId User ID
     * @return array Upcoming appointments
     */
    public function getUpcomingByUser(int $userId): array
    {
        return $this->citaRepo->getUpcomingByUserId($userId);
    }

    /**
     * Execute custom query
     *
     * @param string $query SQL query
     * @return array Query results
     */
    public function customQuery(string $query): array
    {
        return $this->citaRepo->query($query);
    }

    /**
     * Get last custom error (placeholder for compatibility)
     *
     * @return string Error message
     */
    public function getLastError(): string
    {
        return '';
    }

    /**
     * Create appointment and send notification email
     *
     * @param array $data Appointment data ['funcionario', 'matriculado', 'fecha', 'hora', 'motivo']
     * @param EmailService $emailService Email service for notifications
     * @return array ['success' => bool, 'error' => string|null, 'cita_id' => int|null]
     */
    public function createWithNotification(array $data, EmailService $emailService): array
    {
        // Validate required fields
        if (empty($data['matriculado']) || empty($data['fecha']) || empty($data['hora'])) {
            return [
                'success' => false,
                'error' => 'Todos los campos son obligatorios.',
                'cita_id' => null
            ];
        }

        // Validate date format
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['fecha'])) {
            return [
                'success' => false,
                'error' => 'Formato de fecha inválido.',
                'cita_id' => null
            ];
        }

        // Validate time format
        if (!preg_match('/^\d{2}:\d{2}$/', $data['hora'])) {
            return [
                'success' => false,
                'error' => 'Formato de hora inválido.',
                'cita_id' => null
            ];
        }

        // Create the appointment
        $result = $this->create($data);

        if (!$result['success']) {
            return $result;
        }

        // Send notification email
        $user = $this->userRepo->find($data['matriculado']);
        $recipientEmail = $user['email'] ?? null;
        if ($recipientEmail) {
            $subject = 'Nueva cita agendada';
            $body = "Se ha agendado una nueva cita para el día {$data['fecha']} a las {$data['hora']}.";
            if (!empty($data['motivo'])) {
                $body .= " Motivo: {$data['motivo']}";
            }

            $emailService->sendGeneric($recipientEmail, $subject, $body);
        }

        return $result;
    }

    /**
     * Resend notification email for an existing appointment
     *
     * @param int $citaId Appointment ID
     * @param EmailService $emailService Email service for notifications
     * @return array ['success' => bool, 'error' => string|null]
     */
    public function resendNotification(int $citaId, EmailService $emailService): array
    {
        $cita = $this->findById($citaId);

        if (!$cita) {
            return [
                'success' => false,
                'error' => 'Cita no encontrada.'
            ];
        }

        $matriculado = $cita['matriculado'] ?? null;
        if (!$matriculado) {
            return [
                'success' => false,
                'error' => 'La cita no tiene matriculado asociado.'
            ];
        }

        $user = $this->userRepo->find($matriculado);
        $recipientEmail = $user['email'] ?? null;
        if (!$recipientEmail) {
            return [
                'success' => false,
                'error' => 'No se encontró email del matriculado.'
            ];
        }

        $fecha = $cita['fecha'] ?? '';
        $hora = $cita['hora'] ?? '';
        $motivo = $cita['motivo'] ?? '';

        $subject = 'Nueva cita agendada';
        $body = "Se ha agendado una nueva cita para el día {$fecha} a las {$hora}.";
        if (!empty($motivo)) {
            $body .= " Motivo: {$motivo}";
        }

        $emailService->sendGeneric($recipientEmail, $subject, $body);

        return [
            'success' => true,
            'error' => null
        ];
    }

    /**
     * Get matriculados dropdown list
     *
     * @param array $options Options for dropdown
     * @return array Dropdown list
     */
    public function getMatriculadosDropdown(array $options): array
    {
        return $this->datosPersonalesRepo->getDropdownOptions(['has_matricula' => true]);
    }

    /**
     * Find appointment by ID (alias for findById)
     *
     * @param int $citaId Appointment ID
     * @return array|null Appointment data or null
     */
    public function find(int $citaId): ?array
    {
        return $this->findById($citaId);
    }
}
