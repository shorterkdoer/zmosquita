<?php
namespace App\Services;

use App\Repositories\ComprobanteRepository;
use App\Repositories\MatriculaRepository;

/**
 * PaymentService - Handles payment receipts and comprobantes
 *
 * Refactored to use Repository Pattern for data access
 */
class PaymentService
{
    protected ComprobanteRepository $comprobanteRepo;
    protected MatriculaRepository $matriculaRepo;

    public function __construct(
        ComprobanteRepository $comprobanteRepo = null,
        MatriculaRepository $matriculaRepo = null
    ) {
        $this->comprobanteRepo = $comprobanteRepo ?? new ComprobanteRepository();
        $this->matriculaRepo = $matriculaRepo ?? new MatriculaRepository();
    }
    /**
     * Upload payment receipt for a user
     *
     * @param int $userId User ID
     * @param array $file $_FILES array element
     * @param string $mes Month for the payment
     * @param string $anio Year for the payment
     * @param string $observaciones Optional observations
     * @return array ['success' => bool, 'error' => string|null]
     */
    public function uploadComprobante(int $userId, array $file, string $mes, string $anio, string $observaciones = ''): array
    {
        // Validate file upload
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return [
                'success' => false,
                'error' => 'Error al subir el archivo.'
            ];
        }

        $allowedExtensions = ['pdf', 'png', 'jpg', 'jpeg'];
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($extension, $allowedExtensions)) {
            return [
                'success' => false,
                'error' => 'El archivo debe ser PDF, PNG o JPG.'
            ];
        }

        // Get upload folder for user
        $uploadFolder = $this->getUserUploadFolder($userId);

        // Generate unique filename
        $bytesAleatorios = random_bytes(10);
        $postname = bin2hex($bytesAleatorios);
        $baseName = pathinfo($file['name'], PATHINFO_FILENAME);
        $baseName = preg_replace('/[ %()#@$!&+-]/', '_', $baseName);
        $newFileName = $baseName . '_' . $postname . '.' . $extension;
        $destination = $uploadFolder . $newFileName;

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            return [
                'success' => false,
                'error' => 'Error al guardar el archivo.'
            ];
        }

        // Save to database
        $data = [
            'user_id' => $userId,
            'comprobante' => $newFileName,
            'fecha' => date('Y-m-d'),
            'mes' => $mes,
            'anio' => $anio,
            'observaciones' => $observaciones
        ];

        if (!$this->comprobanteRepo->create($data)) {
            return [
                'success' => false,
                'error' => 'Error al guardar en la base de datos.'
            ];
        }

        return [
            'success' => true,
            'error' => null
        ];
    }

    /**
     * Get payment receipts by user
     *
     * @param int $userId User ID
     * @return array Payment receipts
     */
    public function getByUser(int $userId): array
    {
        return $this->comprobanteRepo->findByUserId($userId);
    }

    /**
     * Get payment receipts by month and year
     *
     * @param string $mes Month
     * @param string $anio Year
     * @return array Payment receipts
     */
    public function getByPeriod(string $mes, string $anio): array
    {
        return $this->comprobanteRepo->findByPeriod($mes, $anio);
    }

    /**
     * Get payment receipt by ID
     *
     * @param int $id Comprobante ID
     * @return array|null Comprobante data or null
     */
    public function findById(int $id): ?array
    {
        return $this->comprobanteRepo->find($id);
    }

    /**
     * Delete payment receipt
     *
     * @param int $id Comprobante ID
     * @return array ['success' => bool, 'error' => string|null]
     */
    public function delete(int $id): array
    {
        $comprobante = $this->findById($id);

        if (!$comprobante) {
            return [
                'success' => false,
                'error' => 'Comprobante no encontrado.'
            ];
        }

        // Delete file from storage
        $uploadFolder = $this->getUserUploadFolder($comprobante['user_id']);
        $filePath = $uploadFolder . $comprobante['comprobante'];

        if (file_exists($filePath)) {
            unlink($filePath);
        }

        // Delete from database
        if (!$this->comprobanteRepo->delete($id)) {
            return [
                'success' => false,
                'error' => 'Error al eliminar de la base de datos.'
            ];
        }

        return [
            'success' => true,
            'error' => null
        ];
    }

    /**
     * Get user upload folder path
     */
    protected function getUserUploadFolder(int $userId): string
    {
        $baseFolder = ($_SESSION['directoriobase'] ?? '/var/www/zmosquita') . '/storage/uploads/';

        $configPath = $_SESSION['directoriobase'] ?? '/var/www/zmosquita';
        $config = require $configPath . '/config/settings.php';
        $secretword = $config['basellave'] ?? 'default-secret';

        $folderName = md5($userId . $secretword);
        $fullPath = $baseFolder . $folderName . DIRECTORY_SEPARATOR;

        if (!file_exists($fullPath)) {
            $oldUmask = umask(0);
            mkdir($fullPath, 0777, true);
            umask($oldUmask);
        }

        return $fullPath;
    }

    /**
     * Get upload folder as relative path (for views)
     *
     * @param int $userId User ID
     * @return string Relative path
     */
    public function getUserFolderRelative(int $userId): string
    {
        $configPath = $_SESSION['directoriobase'] ?? '/var/www/zmosquita';
        $config = require $configPath . '/config/settings.php';
        $secretword = $config['basellave'] ?? 'default-secret';

        $folderName = md5($userId . $secretword);
        return '/storage/uploads/' . $folderName . '/';
    }

    /**
     * Execute custom query
     *
     * @param string $query SQL query
     * @return array Query results
     */
    public function customQuery(string $query): array
    {
        return $this->comprobanteRepo->query($query);
    }

    /**
     * Update comprobante
     *
     * @param int $id Comprobante ID
     * @param array $data Data to update
     * @return array ['success' => bool, 'error' => string|null]
     */
    public function update(int $id, array $data): array
    {
        if (!$this->comprobanteRepo->update($id, $data)) {
            return [
                'success' => false,
                'error' => 'Error actualizando comprobante.'
            ];
        }

        return [
            'success' => true,
            'error' => null
        ];
    }

    /**
     * Get months list for dropdown
     *
     * @return array Months list
     */
    public function getMonths(): array
    {
        return [
            'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
            'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
        ];
    }

    /**
     * Create comprobante record (without file upload)
     *
     * @param array $data Comprobante data
     * @return array ['success' => bool, 'error' => string|null]
     */
    public function create(array $data): array
    {
        if (!$this->comprobanteRepo->create($data)) {
            return [
                'success' => false,
                'error' => 'Error creando comprobante.'
            ];
        }

        return [
            'success' => true,
            'error' => null
        ];
    }

    /**
     * Upload and create payment receipt with file
     *
     * @param int $userId User ID
     * @param array $file $_FILES array element
     * @param string|null $monto Payment amount
     * @param string|null $fecha Payment date
     * @param string|null $observaciones Optional observations
     * @return array ['success' => bool, 'error' => string|null, 'filename' => string|null]
     */
    public function uploadWithFile(int $userId, array $file, ?string $monto = null, ?string $fecha = null, ?string $observaciones = null): array
    {
        // Validate file upload
        if ($file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
            return [
                'success' => false,
                'error' => 'No se ha seleccionado ningún archivo válido.',
                'filename' => null
            ];
        }

        // Config
        $allowedExt = ['pdf', 'png', 'jpg', 'jpeg'];
        $maxBytes = 15 * 1024 * 1024; // 15 MB

        $origName = $file['name'] ?? 'archivo';
        $tmpPath = $file['tmp_name'];
        $uploadDir = $this->getUserUploadFolder($userId);

        // Create dir if not exists
        if (!is_dir($uploadDir)) {
            $oldUmask = umask(0);
            if (!mkdir($uploadDir, 0777, true) && !is_dir($uploadDir)) {
                umask($oldUmask);
                return [
                    'success' => false,
                    'error' => 'No se pudo crear el directorio de subida.',
                    'filename' => null
                ];
            }
            @chmod($uploadDir, 0777);
            umask($oldUmask);
        }

        // Check size
        if ($file['size'] > $maxBytes) {
            return [
                'success' => false,
                'error' => 'El archivo supera el tamaño máximo permitido (15MB).',
                'filename' => null
            ];
        }

        // Sanitize filename
        $sanitized = $this->sanitizeFilename($origName);
        $ext = strtolower(pathinfo($sanitized, PATHINFO_EXTENSION));

        if ($ext === '') {
            return [
                'success' => false,
                'error' => 'El archivo no tiene extensión.',
                'filename' => null
            ];
        }

        if (!in_array($ext, $allowedExt, true)) {
            return [
                'success' => false,
                'error' => 'Tipo de archivo no permitido. Use PDF, PNG o JPG.',
                'filename' => null
            ];
        }

        // Generate unique filename
        $uniqueName = $this->generateUniqueFilename($uploadDir, $sanitized);
        $destPath = rtrim($uploadDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $uniqueName;

        // Move uploaded file
        if (!move_uploaded_file($tmpPath, $destPath)) {
            return [
                'success' => false,
                'error' => 'Error al subir el archivo.',
                'filename' => null
            ];
        }

        @chmod($destPath, 0644);

        // Prepare data to insert
        $data = [
            'user_id' => $userId,
            'comprobante' => $uniqueName,
            'monto' => $monto,
            'fecha' => $fecha,
            'observaciones' => $observaciones,
        ];

        // Insert in DB; delete file if fails
        try {
            $result = $this->create($data);
            if (!$result['success']) {
                @unlink($destPath);
                return $result;
            }
        } catch (\Throwable $e) {
            @unlink($destPath);
            return [
                'success' => false,
                'error' => 'Error guardando en base de datos: ' . $e->getMessage(),
                'filename' => null
            ];
        }

        return [
            'success' => true,
            'error' => null,
            'filename' => $uniqueName
        ];
    }

    /**
     * Sanitize filename: remove path, normalize spaces/chars, keep extension
     *
     * @param string $name Original filename
     * @return string Sanitized filename
     */
    protected function sanitizeFilename(string $name): string
    {
        // Remove any path component
        $name = basename($name);

        // Separate base/ext
        $ext = pathinfo($name, PATHINFO_EXTENSION);
        $base = pathinfo($name, PATHINFO_FILENAME);

        // Normalize: remove accents, spaces->_, only [a-z0-9._-]
        $base = iconv('UTF-8', 'ASCII//TRANSLIT', $base);
        $base = preg_replace('/[^A-Za-z0-9._-]+/', '_', $base);
        $base = trim($base, '._-');
        if ($base === '') {
            $base = 'archivo';
        }

        $ext = strtolower($ext);
        return $ext ? ($base . '.' . $ext) : $base;
    }

    /**
     * Generate unique filename preserving extension
     * Format: {base}-{YYYYMMDD-HHMMSS}-{8hex}.{ext}
     *
     * @param string $dir Target directory
     * @param string $sanitized Sanitized filename
     * @return string Unique filename
     */
    protected function generateUniqueFilename(string $dir, string $sanitized): string
    {
        $ext = pathinfo($sanitized, PATHINFO_EXTENSION);
        $base = pathinfo($sanitized, PATHINFO_FILENAME);

        // Prefix with timestamp + random suffix
        $suffix = date('Ymd-His') . '-' . bin2hex(random_bytes(4));
        $candidate = $base . '-' . $suffix . ($ext ? ('.' . $ext) : '');

        // Handle rare collision case
        $full = rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $candidate;
        $i = 0;
        while (file_exists($full)) {
            $i++;
            $candidate = $base . '-' . $suffix . '-' . $i . ($ext ? ('.' . $ext) : '');
            $full = rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $candidate;
        }
        return $candidate;
    }

    /**
     * Batch create payment receipts from colegio data
     *
     * @param string $fecha Payment date
     * @param float $monto Payment amount
     * @param array $rows Array of ['matricula' => int, 'nombre' => string, 'dni' => string]
     * @return array ['success' => bool, 'created' => int, 'errors' => array]
     */
    public function batchCreateFromColegio(string $fecha, float $monto, array $rows): array
    {
        $ok = 0;
        $errores = [];

        foreach ($rows as $r) {
            $matriculaNumero = (int)($r['matricula'] ?? 0);
            if (!$matriculaNumero) {
                $errores[] = "Fila con matrícula vacía para {$r['nombre']} ({$r['dni']})";
                continue;
            }

            $matr = $this->matriculaRepo->findByNumero($matriculaNumero);
            if (!$matr) {
                $errores[] = "No se encontró matrícula asignada {$matriculaNumero} ({$r['nombre']}, DNI {$r['dni']})";
                continue;
            }

            $userId = (int)($matr['user_id'] ?? 0);
            if (!$userId) {
                $errores[] = "Matrícula {$matriculaNumero} no tiene user_id asociado.";
                continue;
            }

            $data = [
                'user_id' => $userId,
                'comprobante' => 'cobrocolegio.png',
                'fecha' => $fecha,
                'monto' => $monto,
                'observaciones' => 'Informado por el Colegio',
            ];

            try {
                $this->comprobanteRepo->create($data);
                $ok++;
            } catch (\Throwable $e) {
                $errores[] = "Error guardando matrícula {$matriculaNumero}: " . $e->getMessage();
            }
        }

        return [
            'success' => count($errores) === 0,
            'created' => $ok,
            'total' => count($rows),
            'errors' => $errores
        ];
    }
}
