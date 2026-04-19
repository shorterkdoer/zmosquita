<?php
declare(strict_types=1);
namespace App\Support;

final class Sanitizer
{
    /** Normaliza Unicode (NFKC), recorta, colapsa espacios y quita controles no imprimibles. */
    public static function text(?string $v, int $maxLen = 0): string
    {
        $v = (string)$v;
        // Normalizar Unicode (si hay intl)
        if (class_exists(\Normalizer::class)) {
            $v = \Normalizer::normalize($v, \Normalizer::FORM_KC) ?? $v;
        }
        // Eliminar controles (excepto \n \r \t)
        $v = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $v) ?? $v;
        // Colapsar espacios
        $v = preg_replace('/[ \t\x{00A0}]{2,}/u', ' ', trim($v)) ?? $v;
        if ($maxLen > 0) {
            $v = mb_strimwidth($v, 0, $maxLen, '', 'UTF-8');
        }
        return $v;
    }

    /** Texto de una sola línea (sin saltos de línea). */
    public static function oneline(?string $v, int $maxLen = 0): string
    {
        $v = self::text($v, $maxLen);
        return str_replace(["\r","\n"], ' ', $v);
    }

    public static function email(?string $v): string
    {
        $v = self::oneline($v, 254);
        return filter_var($v, FILTER_VALIDATE_EMAIL) ? $v : '';
    }

    public static function url(?string $v): string
    {
        $v = self::oneline($v, 2048);
        return filter_var($v, FILTER_VALIDATE_URL) ? $v : '';
    }

    public static function int(?string $v, ?int $min = null, ?int $max = null): ?int
    {
        if (!preg_match('/^-?\d+$/', (string)$v)) return null;
        $i = (int)$v;
        if ($min !== null && $i < $min) return null;
        if ($max !== null && $i > $max) return null;
        return $i;
    }

    public static function float(?string $v, ?float $min = null, ?float $max = null): ?float
    {
        if (!is_numeric($v)) return null;
        $f = (float)$v;
        if ($min !== null && $f < $min) return null;
        if ($max !== null && $f > $max) return null;
        return $f;
    }

    public static function bool(mixed $v): bool
    {
        return filter_var($v, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
    }

    /**
     * Sanitiza un nombre de archivo “presentable” (para mostrar o registrar).
     * - Quita rutas, traversal, caracteres raros
     * - Preserva extensión en minúsculas (si está permitida)
     * - Translitera a ASCII (si hay intl)
     */
    public static function filename(string $name, array $allowedExt = []): string
    {
        $name = trim($name);
        $name = str_replace(["\\", "/"], ' ', $name);           // no rutas
        $name = preg_replace('/^\.+/', '', $name) ?? $name;      // sin leading dots
        $name = preg_replace('/\s+/', ' ', $name) ?? $name;

        // separar extensión (la última)
        $dotPos = strrpos($name, '.');
        $base = $dotPos === false ? $name : substr($name, 0, $dotPos);
        $ext  = $dotPos === false ? ''   : strtolower(substr($name, $dotPos + 1));

        // eliminar dobles extensiones peligrosas (e.g. .php.jpg)
        if ($base && preg_match('/\.(php|phar|phtml|pl|py|rb|sh|exe|dll|js|html?)$/i', $base)) {
            $base = preg_replace('/\.(php|phar|phtml|pl|py|rb|sh|exe|dll|js|html?)$/i', '', $base) ?? $base;
        }

        // transliterar base (opcional)
        if (function_exists('iconv')) {
            $base = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $base) ?: $base;
        }
        $base = preg_replace('/[^A-Za-z0-9 _.-]/', '', $base) ?? $base;
        $base = trim($base, " .-_");
        if ($base === '') $base = 'file';

        if (!empty($allowedExt)) {
            if (!in_array($ext, $allowedExt, true)) $ext = '';
        }

        $full = $ext ? ($base . '.' . $ext) : $base;
        // limitar longitud total
        return mb_strimwidth($full, 0, 120, '', 'UTF-8');
    }

    /** Slug seguro para URLs o claves “human-friendly”. */
    public static function slug(string $v, int $maxLen = 80): string
    {
        $v = self::oneline($v);
        if (function_exists('iconv')) {
            $v = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $v) ?: $v;
        }
        $v = strtolower($v);
        $v = preg_replace('/[^a-z0-9]+/i', '-', $v) ?? $v;
        $v = trim($v, '-');
        if ($maxLen > 0) $v = mb_strimwidth($v, 0, $maxLen, '', 'UTF-8');
        return $v ?: 'n-a';
    }

    /**
     * Sanitiza un array usando un esquema: ['campo' => ['filter' => 'text|email|int|float|bool|oneline|slug', 'maxLen'=>.., ...]]
     */
    public static function fromArray(array $input, array $schema): array
    {
        $out = [];
        foreach ($schema as $key => $rules) {
            $val  = $input[$key] ?? null;
            $type = $rules['filter'] ?? 'text';
            $max  = (int)($rules['maxLen'] ?? 0);
            $min  = $rules['min'] ?? null;
            $maxv = $rules['max'] ?? null;

            switch ($type) {
                case 'email': $out[$key] = self::email($val); break;
                case 'url':   $out[$key] = self::url($val); break;
                case 'int':   $out[$key] = self::int($val, $min, $maxv); break;
                case 'float': $out[$key] = self::float($val, $min, $maxv); break;
                case 'bool':  $out[$key] = self::bool($val); break;
                case 'oneline': $out[$key] = self::oneline($val, $max); break;
                case 'slug':  $out[$key] = self::slug((string)$val, $max ?: 80); break;
                default:      $out[$key] = self::text($val, $max); break;
            }
        }
        return $out;
    }
}
