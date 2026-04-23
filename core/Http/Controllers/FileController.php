<?php

declare(strict_types=1);

namespace ZMosquita\Core\Http\Controllers;

use ZMosquita\Core\Auth\ContextManager;
use ZMosquita\Core\Auth\AuthorizationManager;
use ZMosquita\Core\Storage\FileStorageService;

final class FileController
{
    public function __construct(
        private ContextManager $context,
        private AuthorizationManager $auth,
        private FileStorageService $storage
    ) {
    }

    /**
     * Serve a file from storage
     * GET /file/{path}
     */
    public function serve(string $path): void
    {
        // Parse path to validate tenant/app access
        $parts = $this->storage->parseRelativePath($path);

        // Verify user has access to this tenant/app
        if (!$this->canAccessTenantApp($parts['tenant'], $parts['app'])) {
            http_response_code(403);
            echo 'Access denied';
            exit;
        }

        // Output file
        $filename = $parts['filename'] ?? basename($path);
        $subpath = $parts['subpath'] ?? null;

        $this->storage->output($filename, $subpath, $filename);
    }

    /**
     * Upload a file
     * POST /file/upload
     */
    public function upload(): void
    {
        if (!isset($_FILES['file'])) {
            http_response_code(400);
            echo json_encode(['error' => 'No file uploaded']);
            exit;
        }

        $file = $_FILES['file'];
        $subpath = $_POST['subpath'] ?? null;

        try {
            $result = $this->storage->store($file, $subpath);
            echo json_encode(['success' => true, 'file' => $result]);
        } catch (\Throwable $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Upload multiple files
     * POST /file/upload/multiple
     */
    public function uploadMultiple(): void
    {
        if (!isset($_FILES['files'])) {
            http_response_code(400);
            echo json_encode(['error' => 'No files uploaded']);
            exit;
        }

        $files = $_FILES['files'];
        $subpath = $_POST['subpath'] ?? null;
        $results = [];

        try {
            $this->storage->ensureDirectory($subpath);

            foreach ($files['name'] as $index => $name) {
                $fileData = [
                    'name' => $name,
                    'type' => $files['type'][$index],
                    'tmp_name' => $files['tmp_name'][$index],
                    'error' => $files['error'][$index],
                    'size' => $files['size'][$index],
                ];

                $result = $this->storage->store($fileData, $subpath);
                $results[] = $result;
            }

            echo json_encode(['success' => true, 'files' => $results]);
        } catch (\Throwable $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Delete a file
     * DELETE /file/{path}
     */
    public function delete(string $path): void
    {
        $parts = $this->storage->parseRelativePath($path);

        if (!$this->canAccessTenantApp($parts['tenant'], $parts['app'])) {
            http_response_code(403);
            echo json_encode(['error' => 'Access denied']);
            exit;
        }

        $filename = $parts['filename'] ?? basename($path);
        $subpath = $parts['subpath'] ?? null;

        try {
            $deleted = $this->storage->delete($filename, $subpath);
            echo json_encode(['success' => $deleted]);
        } catch (\Throwable $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * List files in a directory
     * GET /file/list
     */
    public function list(): void
    {
        $subpath = $_GET['path'] ?? null;

        // Validate access to this tenant/app path
        if ($subpath) {
            $parts = explode('/', $subpath);
            if (count($parts) >= 2) {
                $tenant = $parts[0];
                $app = $parts[1];

                if (!$this->canAccessTenantApp($tenant, $app)) {
                    http_response_code(403);
                    echo json_encode(['error' => 'Access denied']);
                    exit;
                }
            }
        }

        try {
            $files = $this->storage->listFiles($subpath);
            echo json_encode(['success' => true, 'files' => $files]);
        } catch (\Throwable $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    private function canAccessTenantApp(?string $tenantCode, ?string $appCode): bool
    {
        if (!$tenantCode || !$appCode) {
            return false;
        }

        $currentTenant = $this->context->currentTenant();
        $currentApp = $this->context->currentApp();

        // User must be in the tenant and have access to the app
        if ($currentTenant['code'] !== $tenantCode) {
            return false;
        }

        if ($currentApp['code'] !== $appCode) {
            return false;
        }

        return true;
    }
}
