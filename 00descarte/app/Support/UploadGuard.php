<?php
declare(strict_types=1);

namespace App\Support;

final class UploadGuard
{
    private const EXT_MIME = [
        'png'  => ['image/png'],
        'jpg'  => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'gif'  => ['image/gif'],
        'pdf'  => ['application/pdf'],
        'txt'  => ['text/plain'],
    ];

    public static function validate(array $file, array $allowExt=['png','jpg','jpeg','gif','pdf'], int $maxBytes=8_000_000): array
    {
        if (!isset($file['error']) || is_array($file['error'])) return [false, 'Carga inválida'];
        if ($file['error'] !== UPLOAD_ERR_OK) return [false, 'Error de subida: '.$file['error']];
        if (($file['size'] ?? 0) <= 0 || $file['size'] > $maxBytes) return [false, 'Tamaño no permitido'];

        $origName = (string)($file['name'] ?? '');
        $safeName = Sanitizer::filename($origName, $allowExt);

        $ext = strtolower(pathinfo($safeName, PATHINFO_EXTENSION));
        if ($ext === '' || !in_array($ext, $allowExt, true)) return [false, 'Extensión no permitida'];

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($file['tmp_name']) ?: 'application/octet-stream';
        $validMimes = self::EXT_MIME[$ext] ?? [];
        if (!in_array($mime, $validMimes, true)) return [false, "Tipo de archivo no válido ($mime)"];

        return [true, $safeName];
    }

    public static function move(array $file, string $destDir, string $finalBaseName): array
    {
        if (!is_dir($destDir) && !mkdir($destDir, 0775, true)) {
            return [false, 'No se pudo crear el directorio destino'];
        }
        $ext  = strtolower(pathinfo($finalBaseName, PATHINFO_EXTENSION));
        $base = pathinfo($finalBaseName, PATHINFO_FILENAME);
        $unique = $base.'-'.bin2hex(random_bytes(6)).($ext?".$ext":'');
        $dest = rtrim($destDir,'/\\').DIRECTORY_SEPARATOR.$unique;

        if (!move_uploaded_file($file['tmp_name'], $dest)) return [false, 'No se pudo mover el archivo'];
        @chmod($dest, 0644);
        return [true, $dest];
    }
}
