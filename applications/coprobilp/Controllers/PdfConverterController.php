<?php

namespace App\Controllers;

use App\Core\Controller;
use Foundation\Core\Request;
use Foundation\Core\Session;
use App\Core\Helpers\PdfToPngConverter;
use Exception;

class PdfConverterController extends Controller
{
    private string $uploadDir;
    private string $convertedDir;
    private array $allowedMimeTypes = [
        'application/pdf',
        'application/x-pdf',
        'application/acrobat',
        'applications/vnd.pdf',
        'text/pdf',
        'text/x-pdf'
    ];

    public function __construct()
    {
        parent::__construct();
        
        // Set up directories
        $baseDir = $_SESSION['directoriobase'] ?? dirname(__DIR__, 2);
        $this->uploadDir = $baseDir . '/storage/pdf_uploads';
        $this->convertedDir = $baseDir . '/storage/converted_png';
        
        // Create directories if they don't exist
        $this->ensureDirectoryExists($this->uploadDir);
        $this->ensureDirectoryExists($this->convertedDir);
    }

    /**
     * Show the PDF to PNG converter interface
     */
    public function index(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->view('pdf_converter/index', [
            'title' => 'Convertir PDF a PNG',
            'maxFileSize' => $this->getMaxFileSize()
        ]);
    }

    /**
     * Handle PDF upload and conversion
     */
    public function convert(Request $request): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        try {
            // Validate request method
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Método no permitido');
            }

            // Check if file was uploaded
            if (!isset($_FILES['pdf_file']) || $_FILES['pdf_file']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('Error al subir el archivo PDF');
            }

            $uploadedFile = $_FILES['pdf_file'];

            // Validate file
            $this->validateUploadedFile($uploadedFile);

            // Move uploaded file to upload directory
            $originalName = pathinfo($uploadedFile['name'], PATHINFO_FILENAME);
            $timestamp = time();
            $uploadedFileName = "pdf_{$timestamp}_{$originalName}.pdf";
            $uploadedFilePath = $this->uploadDir . DIRECTORY_SEPARATOR . $uploadedFileName;

            if (!move_uploaded_file($uploadedFile['tmp_name'], $uploadedFilePath)) {
                throw new Exception('Error al guardar el archivo PDF');
            }

            // Get conversion options
            $convertAllPages = isset($_POST['convert_all_pages']) && $_POST['convert_all_pages'] === '1';
            $resolution = (int)($_POST['resolution'] ?? 150);
            $quality = (int)($_POST['quality'] ?? 90);

            // Validate options
            $resolution = max(72, min(300, $resolution)); // Limit resolution between 72-300 DPI
            $quality = max(50, min(100, $quality)); // Limit quality between 50-100

            // Initialize converter
            $converter = new PdfToPngConverter($resolution, $quality, $this->convertedDir);

            // Convert PDF to PNG
            $convertedFiles = $converter->convert($uploadedFilePath, $convertAllPages, $originalName);

            // Clean up uploaded PDF
            unlink($uploadedFilePath);

            // Prepare response data
            $responseData = [
                'success' => true,
                'message' => 'PDF convertido exitosamente',
                'files' => [],
                'total_pages' => count($convertedFiles)
            ];

            foreach ($convertedFiles as $index => $filePath) {
                $fileName = basename($filePath);
                $responseData['files'][] = [
                    'name' => $fileName,
                    'page' => $index + 1,
                    'download_url' => '/pdf-converter/download/' . urlencode($fileName),
                    'size' => $this->formatFileSize(filesize($filePath))
                ];
            }

            // Store conversion data in session for download access
            $_SESSION['converted_files'] = $responseData['files'];

            // Return JSON response for AJAX requests
            if ($this->isAjaxRequest()) {
                header('Content-Type: application/json');
                echo json_encode($responseData);
                exit;
            }

            // For regular form submission, redirect with success message
            Session::flash('success', $responseData['message']);
            Session::flash('converted_files', $responseData['files']);
            $this->redirect('/pdf-converter');

        } catch (Exception $e) {
            // Clean up uploaded file if it exists
            if (isset($uploadedFilePath) && file_exists($uploadedFilePath)) {
                unlink($uploadedFilePath);
            }

            $errorMessage = 'Error: ' . $e->getMessage();

            // Return JSON response for AJAX requests
            if ($this->isAjaxRequest()) {
                header('Content-Type: application/json');
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => $errorMessage
                ]);
                exit;
            }

            // For regular form submission, redirect with error message
            Session::flash('error', $errorMessage);
            $this->redirect('/pdf-converter');
        }
    }

    /**
     * Handle file download
     */
    public function download(Request $request, array $params): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        try {
            $fileName = $params[0] ?? '';
            if (empty($fileName)) {
                throw new Exception('Nombre de archivo no especificado');
            }

            // Decode filename
            $fileName = urldecode($fileName);

            // Validate filename (security check)
            if (!preg_match('/^converted_\d+_[a-zA-Z0-9_-]+_page_\d+\.png$/', $fileName)) {
                throw new Exception('Nombre de archivo inválido');
            }

            $filePath = $this->convertedDir . DIRECTORY_SEPARATOR . $fileName;

            if (!file_exists($filePath)) {
                throw new Exception('Archivo no encontrado');
            }

            // Set headers for file download
            header('Content-Type: image/png');
            header('Content-Disposition: attachment; filename="' . $fileName . '"');
            header('Content-Length: ' . filesize($filePath));
            header('Cache-Control: no-cache, must-revalidate');
            header('Expires: 0');

            // Output file
            readfile($filePath);
            exit;

        } catch (Exception $e) {
            Session::flash('error', 'Error al descargar archivo: ' . $e->getMessage());
            $this->redirect('/pdf-converter');
        }
    }

    /**
     * Clean up old converted files (can be called via cron or manually)
     */
    public function cleanup(): void
    {
        try {
            $converter = new PdfToPngConverter(150, 90, $this->convertedDir);
            $converter->cleanupOldFiles(3600); // Clean files older than 1 hour

            echo json_encode([
                'success' => true,
                'message' => 'Archivos antiguos eliminados'
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error en limpieza: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Validate uploaded file
     */
    private function validateUploadedFile(array $file): void
    {
        // Check file size
        $maxSize = $this->getMaxFileSizeBytes();
        if ($file['size'] > $maxSize) {
            throw new Exception('El archivo es demasiado grande. Tamaño máximo: ' . $this->formatFileSize($maxSize));
        }

        // Check file extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($extension !== 'pdf') {
            throw new Exception('Solo se permiten archivos PDF');
        }

        // Check MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $this->allowedMimeTypes)) {
            throw new Exception('Tipo de archivo no válido. Solo se permiten archivos PDF');
        }

        // Validate PDF file structure
        if (!PdfToPngConverter::isValidPdf($file['tmp_name'])) {
            throw new Exception('El archivo no es un PDF válido');
        }
    }

    /**
     * Ensure directory exists and is writable
     */
    private function ensureDirectoryExists(string $dir): void
    {
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755, true)) {
                throw new Exception("No se pudo crear el directorio: {$dir}");
            }
        }

        if (!is_writable($dir)) {
            throw new Exception("El directorio no es escribible: {$dir}");
        }
    }

    /**
     * Get maximum file size in bytes
     */
    private function getMaxFileSizeBytes(): int
    {
        $maxSize = ini_get('upload_max_filesize');
        return $this->parseSize($maxSize);
    }

    /**
     * Get maximum file size formatted
     */
    private function getMaxFileSize(): string
    {
        return ini_get('upload_max_filesize');
    }

    /**
     * Parse size string to bytes
     */
    private function parseSize(string $size): int
    {
        $unit = preg_replace('/[^bkmgtpezy]/i', '', $size);
        $size = preg_replace('/[^0-9\.]/', '', $size);
        
        if ($unit) {
            return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
        }
        
        return round($size);
    }

    /**
     * Format file size
     */
    private function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * Check if request is AJAX
     */
    private function isAjaxRequest(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}
