<?php

declare(strict_types=1);

namespace ZMosquita\Core\Storage;

use RuntimeException;
use ZMosquita\Core\Auth\ContextManager;
use ZMosquita\Core\Support\Paths;

final class FileStorageService
{
    private const CHUNK_SIZE = 8192; // 8KB chunks for reading

    public function __construct(
        private ContextManager $context,
        private string $basePath
    ) {
        $this->basePath = rtrim($basePath, DIRECTORY_SEPARATOR);
    }

    /**
     * Get the storage path for current tenant/app
     * Structure: storage/{tenant}/{app}/{yyyy}/{mm}/
     */
    public function getStoragePath(?string $subpath = null): string
    {
        $tenant = $this->context->currentTenant();
        $app = $this->context->currentApp();

        if (!$tenant) {
            throw new RuntimeException('No active tenant context');
        }

        if (!$app) {
            throw new RuntimeException('No active app context');
        }

        $tenantCode = $tenant['code'] ?? 'unknown';
        $appCode = $app['code'] ?? 'unknown';

        // Create date-based path
        $datePath = date('Y/m');

        // Build full path
        $path = $this->basePath
            . DIRECTORY_SEPARATOR . $this->sanitizePathComponent($tenantCode)
            . DIRECTORY_SEPARATOR . $this->sanitizePathComponent($appCode)
            . DIRECTORY_SEPARATOR . $datePath;

        if ($subpath) {
            $path .= DIRECTORY_SEPARATOR . $this->sanitizePathComponent(ltrim($subpath, '/\\'));
        }

        return $path;
    }

    /**
     * Ensure storage directory exists with proper permissions
     */
    public function ensureDirectory(?string $subpath = null): void
    {
        $path = $this->getStoragePath($subpath);

        if (!is_dir($path)) {
            $oldUmask = umask(0);
            try {
                mkdir($path, 0775, true);
            } finally {
                umask($oldUmask);
            }
        }

        // Create .htaccess to prevent web access
        $this->protectDirectory($path);
    }

    /**
     * Store an uploaded file
     *
     * @param array $file $_FILES element
     * @param string $subpath Optional subdirectory (e.g., 'documents', 'avatars')
     * @param string|null $customFilename Custom filename (null = generate)
     * @return array ['path' => string, 'filename' => string, 'size' => int, 'mime' => string]
     */
    public function store(array $file, ?string $subpath = null, ?string $customFilename = null): array
    {
        $this->validateUpload($file);

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = $customFilename ?? $this->generateUniqueFilename($file['name']);
        if ($customFilename && !str_ends_with($customFilename, '.' . $extension)) {
            $filename = $customFilename . '.' . $extension;
        }

        $this->ensureDirectory($subpath);
        $targetPath = $this->getStoragePath($subpath) . DIRECTORY_SEPARATOR . $filename;

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new RuntimeException('Failed to move uploaded file');
        }

        // Set secure permissions
        chmod($targetPath, 0644);

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($targetPath, $finfo);
        finfo_close($finfo);

        return [
            'path' => $this->getRelativePath($subpath, $filename),
            'filename' => $filename,
            'size' => filesize($targetPath),
            'mime' => $mime,
            'original_name' => $file['name'],
        ];
    }

    /**
     * Store content as a file
     *
     * @param string $content File content
     * @param string $filename Filename
     * @param string|null $subpath Optional subdirectory
     * @return array ['path' => string, 'filename' => string, 'size' => int]
     */
    public function storeContent(string $content, string $filename, ?string $subpath = null): array
    {
        $this->ensureDirectory($subpath);
        $targetPath = $this->getStoragePath($subpath) . DIRECTORY_SEPARATOR . $filename;

        $bytesWritten = file_put_contents($targetPath, $content);
        if ($bytesWritten === false) {
            throw new RuntimeException('Failed to write file content');
        }

        chmod($targetPath, 0644);

        return [
            'path' => $this->getRelativePath($subpath, $filename),
            'filename' => $filename,
            'size' => $bytesWritten,
        ];
    }

    /**
     * Get a file's full path
     */
    public function getFilePath(string $filename, ?string $subpath = null): string
    {
        return $this->getStoragePath($subpath) . DIRECTORY_SEPARATOR . $filename;
    }

    /**
     * Check if a file exists
     */
    public function exists(string $filename, ?string $subpath = null): bool
    {
        return file_exists($this->getFilePath($filename, $subpath));
    }

    /**
     * Get file contents
     */
    public function get(string $filename, ?string $subpath = null): string
    {
        $path = $this->getFilePath($filename, $subpath);

        if (!$this->exists($filename, $subpath)) {
            throw new RuntimeException("File not found: {$filename}");
        }

        $content = file_get_contents($path);
        if ($content === false) {
            throw new RuntimeException("Failed to read file: {$filename}");
        }

        return $content;
    }

    /**
     * Output file to browser (for downloads)
     */
    public function output(string $filename, ?string $subpath = null, ?string $downloadName = null): void
    {
        $path = $this->getFilePath($filename, $subpath);

        if (!$this->exists($filename, $subpath)) {
            http_response_code(404);
            echo 'File not found';
            exit;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($path, $finfo);
        finfo_close($finfo);

        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($path));
        header('Content-Disposition: inline; filename="' . ($downloadName ?? $filename) . '"');
        header('Cache-Control: private, max-age=31536000');
        header('Pragma: private');

        readfile($path);
        exit;
    }

    /**
     * Delete a file
     */
    public function delete(string $filename, ?string $subpath = null): bool
    {
        $path = $this->getFilePath($filename, $subpath);

        if (!file_exists($path)) {
            return true;
        }

        return unlink($path);
    }

    /**
     * Copy a file within storage
     */
    public function copy(string $filename, string $newFilename, ?string $fromSubpath = null, ?string $toSubpath = null): bool
    {
        $sourcePath = $this->getFilePath($filename, $fromSubpath);
        $targetPath = $this->getFilePath($newFilename, $toSubpath);

        $this->ensureDirectory($toSubpath);

        return copy($sourcePath, $targetPath);
    }

    /**
     * Move a file within storage
     */
    public function move(string $filename, string $newFilename, ?string $fromSubpath = null, ?string $toSubpath = null): bool
    {
        $sourcePath = $this->getFilePath($filename, $fromSubpath);
        $targetPath = $this->getFilePath($newFilename, $toSubpath);

        $this->ensureDirectory($toSubpath);

        return rename($sourcePath, $targetPath);
    }

    /**
     * List files in a directory
     */
    public function listFiles(?string $subpath = null, ?array $extensions = null): array
    {
        $path = $this->getStoragePath($subpath);

        if (!is_dir($path)) {
            return [];
        }

        $files = [];
        $iterator = new \DirectoryIterator($path);

        foreach ($iterator as $fileInfo) {
            if ($fileInfo->isFile() && !$fileInfo->isDot()) {
                if ($extensions === null || in_array($fileInfo->getExtension(), $extensions)) {
                    $files[] = [
                        'filename' => $fileInfo->getFilename(),
                        'size' => $fileInfo->getSize(),
                        'modified' => $fileInfo->getMTime(),
                        'mime' => mime_content_type($fileInfo->getPathname()),
                    ];
                }
            }
        }

        return $files;
    }

    /**
     * Get relative path for database storage
     */
    public function getRelativePath(?string $subpath, string $filename): string
    {
        $tenant = $this->context->currentTenant();
        $app = $this->context->currentApp();

        $parts = array_filter([
            $tenant['code'] ?? null,
            $app['code'] ?? null,
            date('Y'),
            date('m'),
            $subpath,
            $filename,
        ]);

        return implode('/', $parts);
    }

    /**
     * Parse relative path back to components
     */
    public function parseRelativePath(string $relativePath): array
    {
        $parts = explode('/', $relativePath);

        return [
            'tenant' => $parts[0] ?? null,
            'app' => $parts[1] ?? null,
            'year' => $parts[2] ?? null,
            'month' => $parts[3] ?? null,
            'subpath' => $parts[4] ?? null,
            'filename' => $parts[count($parts) - 1] ?? null,
        ];
    }

    /**
     * Create a URL to access a file (requires a route handler)
     */
    public function getUrl(string $relativePath): string
    {
        // This would be handled by a route like /file/{path}
        // The route would use the service to validate access and serve the file
        return '/file/' . $relativePath;
    }

    /**
     * Validate uploaded file
     */
    private function validateUpload(array $file): void
    {
        if (!isset($file['error']) || is_array($file['error'])) {
            throw new RuntimeException('Invalid file upload');
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException($this->getUploadErrorMessage($file['error']));
        }

        if (!is_uploaded_file($file['tmp_name'])) {
            throw new RuntimeException('File upload validation failed');
        }
    }

    /**
     * Generate unique filename
     */
    private function generateUniqueFilename(string $originalName): string
    {
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $baseName = pathinfo($originalName, PATHINFO_FILENAME);

        // Sanitize basename
        $baseName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $baseName);
        $baseName = mb_substr($baseName, 0, 100, 'UTF-8'); // Limit length

        // Add timestamp and random suffix
        $timestamp = date('YmdHis');
        $random = bin2hex(random_bytes(4));

        return $baseName . '_' . $timestamp . '_' . $random . '.' . $extension;
    }

    /**
     * Sanitize path component to prevent directory traversal
     */
    private function sanitizePathComponent(string $component): string
    {
        // Remove any directory separators and special characters
        $component = preg_replace('/[^a-zA-Z0-9._-]/', '_', $component);

        // Limit length
        return mb_substr($component, 0, 50, 'UTF-8');
    }

    /**
     * Protect directory with .htaccess
     */
    private function protectDirectory(string $path): void
    {
        $htaccessPath = $path . DIRECTORY_SEPARATOR . '.htaccess';

        if (!is_file($htaccessPath)) {
            file_put_contents($htaccessPath, "Deny from all\n");
        }
    }

    /**
     * Get upload error message
     */
    private function getUploadErrorMessage(int $errorCode): string
    {
        return match ($errorCode) {
            UPLOAD_ERR_INI_SIZE => 'File exceeds maximum upload size',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds maximum form size',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'File upload stopped by extension',
            default => 'Unknown upload error',
        };
    }

    /**
     * Clean up old files (for maintenance/cron jobs)
     */
    public function cleanupOldFiles(int $daysOld = 90, ?string $subpath = null): int
    {
        $path = $this->getStoragePath($subpath);

        if (!is_dir($path)) {
            return 0;
        }

        $cutoff = time() - ($daysOld * 86400);
        $deleted = 0;

        $iterator = new \DirectoryIterator($path);
        foreach ($iterator as $fileInfo) {
            if ($fileInfo->isFile() && $fileInfo->getMTime() < $cutoff) {
                if (unlink($fileInfo->getPathname())) {
                    $deleted++;
                }
            }
        }

        return $deleted;
    }

    /**
     * Get storage usage statistics
     */
    public function getUsage(?string $subpath = null): array
    {
        $path = $this->getStoragePath($subpath);

        if (!is_dir($path)) {
            return [
                'count' => 0,
                'size' => 0,
                'size_human' => '0 B',
            ];
        }

        $count = 0;
        $size = 0;

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($iterator as $fileInfo) {
            if ($fileInfo->isFile()) {
                $count++;
                $size += $fileInfo->getSize();
            }
        }

        return [
            'count' => $count,
            'size' => $size,
            'size_human' => $this->formatBytes($size),
        ];
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
