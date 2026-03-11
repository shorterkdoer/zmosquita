<?php
namespace App\Services;

use FPDF\FPDF;
use App\Repositories\MatriculaRepository;
use App\Repositories\DatosPersonalesRepository;
use App\Repositories\UserRepository;

/**
 * DocumentService - Handles PDF document generation
 *
 * Refactored to use Repository Pattern for data access
 */
class DocumentService
{
    protected MatriculaRepository $matriculaRepo;
    protected DatosPersonalesRepository $datosPersonalesRepo;
    protected UserRepository $userRepo;

    public function __construct(
        MatriculaRepository $matriculaRepo = null,
        DatosPersonalesRepository $datosPersonalesRepo = null,
        UserRepository $userRepo = null
    ) {
        $this->matriculaRepo = $matriculaRepo ?? new MatriculaRepository();
        $this->datosPersonalesRepo = $datosPersonalesRepo ?? new DatosPersonalesRepository();
        $this->userRepo = $userRepo ?? new UserRepository();
    }
    /**
     * Generate credential card (PDF)
     *
     * @param int $userId User ID
     * @param string $output Output mode: 'I' (inline), 'D' (download), 'F' (file)
     * @return array ['success' => bool, 'error' => string|null, 'path' => string|null]
     */
    public function generateCredential(int $userId, string $output = 'I'): array
    {
        // Get user and matricula data
        $matricula = $this->matriculaRepo->findByUserId($userId);

        if (!$matricula || empty($matricula['matriculaasignada'])) {
            return [
                'success' => false,
                'error' => 'Usuario no tiene matrícula asignada.',
                'path' => null
            ];
        }

        $datos = $this->datosPersonalesRepo->findByUserId($userId);

        if (!$datos) {
            return [
                'success' => false,
                'error' => 'Datos personales no encontrados.',
                'path' => null
            ];
        }

        try {
            $pdf = new FPDF('P', 'mm', [85.6, 53.98]); // Credit card size
            $pdf->AddPage();

            // Background design
            $this->addCredentialBackground($pdf);

            // Add matricula number
            $pdf->SetFont('Arial', 'B', 14);
            $pdf->SetXY(10, 15);
            $pdf->Cell(65, 5, 'MATRICULA', 0, 0, 'C');
            $pdf->SetFont('Arial', 'B', 18);
            $pdf->SetXY(10, 22);
            $pdf->Cell(65, 8, $matricula['matriculaasignada'], 0, 0, 'C');

            // Add name
            $pdf->SetFont('Arial', '', 8);
            $pdf->SetXY(5, 35);
            $nombreCompleto = trim(($datos['apellido'] ?? '') . ' ' . ($datos['nombre'] ?? ''));
            $pdf->Cell(75, 4, mb_strtoupper(utf8_decode($nombreCompleto)), 0, 0, 'C');

            // Add DNI
            if (!empty($datos['dni'])) {
                $pdf->SetXY(5, 40);
                $pdf->Cell(75, 4, 'DNI: ' . $datos['dni'], 0, 0, 'C');
            }

            // Add date
            $pdf->SetFont('Arial', 'I', 6);
            $pdf->SetXY(5, 48);
            $pdf->Cell(75, 3, 'Vencimiento: ' . date('m/Y', strtotime('+1 year')), 0, 0, 'C');

            // Generate path if saving to file
            $path = null;
            if ($output === 'F') {
                $path = $this->getCredentialPath($userId, $matricula['matriculaasignada']);
                $pdf->Output($path, 'F');
            } else {
                $pdf->Output('credencial_' . $matricula['matriculaasignada'] . '.pdf', $output);
            }

            return [
                'success' => true,
                'error' => null,
                'path' => $path
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Error generando credencial: ' . $e->getMessage(),
                'path' => null
            ];
        }
    }

    /**
     * Generate certificate of good standing
     *
     * @param int $userId User ID
     * @return array ['success' => bool, 'error' => string|null]
     */
    public function generateCertificate(int $userId): array
    {
        $matricula = $this->matriculaRepo->findByUserId($userId);

        if (!$matricula || empty($matricula['aprobado']) || !empty($matricula['baja'])) {
            return [
                'success' => false,
                'error' => 'Usuario no tiene matrícula activa.'
            ];
        }

        $datos = $this->datosPersonalesRepo->findByUserId($userId);

        try {
            $pdf = new FPDF('P', 'mm', 'A4');
            $pdf->AddPage();

            // Header
            $pdf->SetFont('Arial', 'B', 16);
            $pdf->Cell(0, 10, 'CERTIFICADO DE BUENA FE', 0, 1, 'C');
            $pdf->Ln(5);

            // Body text
            $pdf->SetFont('Arial', '', 12);
            $pdf->MultiCell(0, 7, "Por la presente se certifica que el/la profesional:");

            $pdf->SetFont('Arial', 'B', 12);
            $nombreCompleto = trim(($datos['apellido'] ?? '') . ', ' . ($datos['nombre'] ?? ''));
            $pdf->Cell(0, 7, mb_strtoupper(utf8_decode($nombreCompleto)), 0, 1, 'C');

            if (!empty($datos['dni'])) {
                $pdf->SetFont('Arial', '', 12);
                $pdf->Cell(0, 7, 'DNI: ' . $datos['dni'], 0, 1, 'C');
            }

            $pdf->Ln(5);

            $pdf->SetFont('Arial', '', 12);
            $pdf->MultiCell(0, 7, "Se encuentra matriculado/a bajo el numero: " . $matricula['matriculaasignada']);
            $pdf->MultiCell(0, 7, "en buen estado y con todas las obligaciones cumplidas.");

            $pdf->Ln(10);

            $pdf->SetFont('Arial', '', 10);
            $pdf->Cell(0, 5, 'Fecha de emision: ' . date('d/m/Y'), 0, 1, 'C');

            $pdf->Output('certificado_' . $matricula['matriculaasignada'] . '.pdf', 'I');

            return [
                'success' => true,
                'error' => null
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Error generando certificado: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Generate membership report
     *
     * @param array $filters Filters for the report
     * @return array ['success' => bool, 'error' => string|null]
     */
    public function generateMembershipReport(array $filters = []): array
    {
        try {
            $pdf = new FPDF('L', 'mm', 'A4');
            $pdf->AddPage();

            // Title
            $pdf->SetFont('Arial', 'B', 14);
            $pdf->Cell(0, 10, 'REPORDE DE MATRICULADOS', 0, 1, 'C');
            $pdf->Ln(5);

            // Table headers
            $headers = ['Matricula', 'Apellido', 'Nombre', 'DNI', 'Estado', 'Email'];
            $widths = [25, 35, 35, 25, 30, 50];

            $pdf->SetFont('Arial', 'B', 10);
            foreach ($headers as $i => $header) {
                $pdf->Cell($widths[$i], 7, $header, 1, 0, 'C');
            }
            $pdf->Ln();

            // Get data (simplified - in real implementation would use filters)
            $sql = "SELECT m.matriculaasignada, dp.apellido, dp.nombre, dp.dni,
                           CASE
                               WHEN m.baja IS NOT NULL THEN 'Baja'
                               WHEN m.estado = 'Inhabilitada' THEN 'Inhabilitada'
                               WHEN m.estado = 'Suspendida' THEN 'Suspendida'
                               ELSE 'Activa'
                           END as estado, u.email
                    FROM matriculas m
                    JOIN users u ON m.user_id = u.id
                    LEFT JOIN datospersonales dp ON m.user_id = dp.user_id
                    WHERE m.matriculaasignada IS NOT NULL
                    ORDER BY m.matriculaasignada";

            $rows = $this->matriculaRepo->query($sql);

            $pdf->SetFont('Arial', '', 9);
            foreach ($rows as $row) {
                $pdf->Cell($widths[0], 6, $row['matriculaasignada'] ?? '', 1, 0, 'L');
                $pdf->Cell($widths[1], 6, utf8_decode($row['apellido'] ?? ''), 1, 0, 'L');
                $pdf->Cell($widths[2], 6, utf8_decode($row['nombre'] ?? ''), 1, 0, 'L');
                $pdf->Cell($widths[3], 6, $row['dni'] ?? '', 1, 0, 'L');
                $pdf->Cell($widths[4], 6, $row['estado'] ?? '', 1, 0, 'C');
                $pdf->Cell($widths[5], 6, $row['email'] ?? '', 1, 0, 'L');
                $pdf->Ln();
            }

            $pdf->Output('reporte_matriculados_' . date('Ymd') . '.pdf', 'I');

            return [
                'success' => true,
                'error' => null
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Error generando reporte: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Add background design to credential
     */
    protected function addCredentialBackground(FPDF $pdf): void
    {
        // Add border
        $pdf->SetDrawColor(100, 100, 150);
        $pdf->SetLineWidth(0.5);
        $pdf->Rect(3, 3, 79.6, 47.98);

        // Add decorative header bar
        $pdf->SetFillColor(100, 100, 150);
        $pdf->Rect(3, 3, 79.6, 8, 'F');
    }

    /**
     * Get credential storage path
     */
    protected function getCredentialPath(int $userId, string $matriculaNumero): string
    {
        $baseFolder = ($_SESSION['directoriobase'] ?? '/var/www/zmosquita') . '/storage/credentials/';
        if (!file_exists($baseFolder)) {
            mkdir($baseFolder, 0755, true);
        }
        return $baseFolder . 'credencial_' . $matriculaNumero . '.pdf';
    }

    /**
     * Execute custom query for data retrieval
     *
     * @param string $query SQL query
     * @return array Query results
     */
    public function customQuery(string $query): array
    {
        return $this->datosPersonalesRepo->query($query);
    }

    /**
     * Find user by ID
     *
     * @param int $userId User ID
     * @return array|null User data or null
     */
    public function findUser(int $userId): ?array
    {
        return $this->userRepo->find($userId);
    }

    /**
     * Get user folder path
     *
     * @param int $userId User ID
     * @return string User folder path
     */
    public function getUserFolder(int $userId): string
    {
        $base = ($_SESSION['directoriobase'] ?? '/var/www/zmosquita') . '/storage/users/';
        $folder = $base . $userId . '/';

        if (!file_exists($folder)) {
            mkdir($folder, 0755, true);
        }

        return '/storage/users/' . $userId . '/';
    }
}
