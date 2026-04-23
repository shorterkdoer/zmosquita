<?php

declare(strict_types=1);

namespace ZMosquita\Core\Storage;

use ZMosquita\Core\Support\Facades\Storage;

/**
 * Storage - Facade for easy file operations with automatic tenant/app context
 *
 * Usage:
 * Storage::upload($_FILES['avatar'], 'avatars');
 * Storage::get($filename, 'documents');
 * Storage::delete($filename, 'documents');
 */
final class StorageHelper
{
    /**
     * Upload a file with automatic path resolution
     *
     * @param array $file $_FILES element
     * @param string|null $subpath Optional subdirectory (e.g., 'documents', 'avatars')
     * @param string|null $customFilename Optional custom filename
     * @return array ['path' => string, 'filename' => string, 'size' => int, 'mime' => string]
     */
    public static function upload(array $file, ?string $subpath = null, ?string $customFilename = null): array
    {
        $storage = Storage::getInstance();
        return $storage->store($file, $subpath, $customFilename);
    }

    /**
     * Store content as a file
     *
     * @param string $content File content
     * @param string $filename Filename
     * @param string|null $subpath Optional subdirectory
     * @return array ['path' => string, 'filename' => string, 'size' => int]
     */
    public static function put(string $content, string $filename, ?string $subpath = null): array
    {
        $storage = Storage::getInstance();
        return $storage->storeContent($content, $filename, $subpath);
    }

    /**
     * Get file contents
     *
     * @param string $filename Filename
     * @param string|null $subpath Optional subdirectory
     * @return string File contents
     */
    public static function get(string $filename, ?string $subpath = null): string
    {
        $storage = Storage::getInstance();
        return $storage->get($filename, $subpath);
    }

    /**
     * Check if file exists
     */
    public static function exists(string $filename, ?string $subpath = null): bool
    {
        $storage = Storage::getInstance();
        return $storage->exists($filename, $subpath);
    }

    /**
     * Delete a file
     */
    public static function delete(string $filename, ?string $subpath = null): bool
    {
        $storage = Storage::getInstance();
        return $storage->delete($filename, $subpath);
    }

    /**
     * Get URL to access a file
     *
     * @param string $relativePath Relative path from storage
     * @return string URL to access the file
     */
    public static function url(string $relativePath): string
    {
        $storage = Storage::getInstance();
        return $storage->getUrl($relativePath);
    }

    /**
     * List files in a directory
     *
     * @param string|null $subpath Optional subdirectory
     * @param array|null $extensions Filter by extensions (e.g., ['pdf', 'png'])
     * @return array List of files with metadata
     */
    public static function list(?string $subpath = null, ?array $extensions = null): array
    {
        $storage = Storage::getInstance();
        return $storage->listFiles($subpath, $extensions);
    }

    /**
     * Get storage path (for system operations)
     *
     * @param string|null $subpath Optional subdirectory
     * @return string Full filesystem path
     */
    public static function path(?string $subpath = null): string
    {
        $storage = Storage::getInstance();
        return $storage->getStoragePath($subpath);
    }

    /**
     * Generate unique filename for upload
     *
     * @param string $originalName Original filename
     * @param string|null $prefix Optional prefix
     * @return string Generated unique filename
     */
    public static function generateFilename(string $originalName, ?string $prefix = null): string
    {
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $baseName = pathinfo($originalName, PATHINFO_FILENAME);

        // Sanitize
        $baseName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $baseName);
        $baseName = mb_substr($baseName, 0, 80, 'UTF-8');

        $timestamp = date('YmdHis');
        $random = bin2hex(random_bytes(6));
        $prefix = $prefix ? $prefix . '_' : '';

        return $prefix . $baseName . '_' . $timestamp . '_' . $random . '.' . $extension;
    }

    /**
     * Format file size for display
     *
     * @param int $bytes Size in bytes
     * @return string Formatted size (e.g., "2.5 MB")
     */
    public static function formatSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
