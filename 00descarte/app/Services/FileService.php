<?php
namespace App\Services;

use App\Support\UploadGuard;

/**
 * FileService - Handles file uploads and validation
 *
 * Centralized file handling logic for all file uploads
 */
class FileService
{
    protected array $config;

    public function __construct()
    {
        $configPath = $_SESSION['directoriobase'] ?? '/var/www/zmosquita';
        $this->config = require $configPath . '/config/settings.php';
    }

    /**
     * Allowed file extensions for uploads
     */
    protected array $allowedExtensions = ['pdf', 'png', 'jpg', 'jpeg'];

    /**
     * Maximum file size in bytes (default: 10MB)
     */
    protected int $maxFileSize = 10485760;

    /**
     * Upload a file for a specific user
     *
     * @param int $userId User ID
     * @param array $file $_FILES array element
     * @param string $fieldName Field name for the file
     * @return array ['success' => bool, 'error' => string|null, 'filename' => string|null]
     */
    public function uploadForUser(int $userId, array $file, string $fieldName): array
    {
        // Validate file upload
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return $this->error($this->getUploadErrorMessage($file['error']));
        }

        // Validate file size
        if ($file['size'] > $this->maxFileSize) {
            return $this->error('El archivo excede el tamaño máximo permitido (10MB).');
        }

        // Validate extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowedExtensions)) {
            return $this->error('Tipo de archivo no permitido. Use PDF, PNG o JPG.');
        }

        // Additional security validation using UploadGuard
        $uploadGuard = new UploadGuard();
        if (!$uploadGuard->validate($file)) {
            return $this->error('El archivo no pasó la validación de seguridad.');
        }

        // Get upload folder
        $uploadFolder = $this->getUserUploadFolder($userId);

        // Generate unique filename
        $newFileName = $this->generateUniqueFilename($file['name'], $fieldName);
        $destination = $uploadFolder . $newFileName;

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            return $this->error('Error al guardar el archivo.');
        }

        return [
            'success' => true,
            'error' => null,
            'filename' => $newFileName
        ];
    }

    /**
     * Upload multiple files for a user
     *
     * @param int $userId User ID
     * @param array $files $_FILES array (multiple files)
     * @param array $fieldNames Array of field names
     * @return array ['success' => bool, 'results' => array, 'errors' => array]
     */
    public function uploadMultipleForUser(int $userId, array $files, array $fieldNames): array
    {
        $results = [];
        $errors = [];

        foreach ($fieldNames as $fieldName) {
            if (isset($files[$fieldName])) {
                $result = $this->uploadForUser($userId, $files[$fieldName], $fieldName);
                $results[$fieldName] = $result;

                if (!$result['success']) {
                    $errors[$fieldName] = $result['error'];
                }
            }
        }

        return [
            'success' => empty($errors),
            'results' => $results,
            'errors' => $errors
        ];
    }

    /**
     * Delete a file from storage
     *
     * @param int $userId User ID
     * @param string $filename Filename to delete
     * @return bool True if deleted or doesn't exist, false on error
     */
    public function deleteFile(int $userId, string $filename): bool
    {
        $uploadFolder = $this->getUserUploadFolder($userId);
        $filePath = $uploadFolder . $filename;

        if (file_exists($filePath)) {
            return unlink($filePath);
        }

        return true; // File doesn't exist, consider it "deleted"
    }

    /**
     * Get file path for a user's file
     *
     * @param int $userId User ID
     * @param string $filename Filename
     * @return string Full path to file
     */
    public function getFilePath(int $userId, string $filename): string
    {
        $uploadFolder = $this->getUserUploadFolder($userId);
        return $uploadFolder . $filename;
    }

    /**
     * Check if file exists for user
     *
     * @param int $userId User ID
     * @param string $filename Filename
     * @return bool True if file exists
     */
    public function fileExists(int $userId, string $filename): bool
    {
        $filePath = $this->getFilePath($userId, $filename);
        return file_exists($filePath);
    }

    /**
     * Get user upload folder path
     */
    protected function getUserUploadFolder(int $userId): string
    {
        $baseFolder = ($_SESSION['directoriobase'] ?? '/var/www/zmosquita') . '/storage/uploads/';
        $secretword = $this->config['basellave'] ?? 'default-secret';

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
     * Generate unique filename
     */
    protected function generateUniqueFilename(string $originalName, string $fieldName): string
    {
        // Clean filename
        $baseName = pathinfo($originalName, PATHINFO_FILENAME);
        $baseName = preg_replace('/[ %()#@$!&+-]/', '_', $baseName);
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        // Generate random suffix
        $bytesAleatorios = random_bytes(10);
        $postname = bin2hex($bytesAleatorios);

        return $baseName . '_' . $postname . '.' . $extension;
    }

    /**
     * Get error message for upload error code
     */
    protected function getUploadErrorMessage(int $errorCode): string
    {
        return match ($errorCode) {
            UPLOAD_ERR_INI_SIZE => 'El archivo excede el tamaño máximo permitido por PHP.',
            UPLOAD_ERR_FORM_SIZE => 'El archivo excede el tamaño máximo permitido por el formulario.',
            UPLOAD_ERR_PARTIAL => 'El archivo solo se subió parcialmente.',
            UPLOAD_ERR_NO_FILE => 'No se subió ningún archivo.',
            UPLOAD_ERR_NO_TMP_DIR => 'Falta la carpeta temporal.',
            UPLOAD_ERR_CANT_WRITE => 'No se pudo escribir el archivo en disco.',
            UPLOAD_ERR_EXTENSION => 'Una extensión de PHP detuvo la subida.',
            default => 'Error desconocido al subir el archivo.',
        };
    }

    /**
     * Create error response array
     */
    protected function error(string $message): array
    {
        return [
            'success' => false,
            'error' => $message,
            'filename' => null
        ];
    }

    /**
     * Set allowed file extensions
     */
    public function setAllowedExtensions(array $extensions): void
    {
        $this->allowedExtensions = $extensions;
    }

    /**
     * Set maximum file size
     */
    public function setMaxFileSize(int $bytes): void
    {
        $this->maxFileSize = $bytes;
    }
}
