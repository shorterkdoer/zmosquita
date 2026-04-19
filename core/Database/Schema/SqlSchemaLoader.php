<?php

declare(strict_types=1);

namespace ZMosquita\Core\Database\Schema;

use RuntimeException;

final class SqlSchemaLoader
{
    public function loadFile(string $path): string
    {
        if (!is_file($path)) {
            throw new RuntimeException("SQL schema file not found: {$path}");
        }

        $contents = file_get_contents($path);

        if ($contents === false) {
            throw new RuntimeException("Unable to read SQL schema file: {$path}");
        }

        return $contents;
    }

    /**
     * Divide un bloque SQL en statements ejecutables.
     *
     * Nota:
     * - ignora líneas vacías
     * - ignora comentarios simples iniciados con -- y #
     * - respeta strings delimitados por comillas simples y dobles
     *
     * @return string[]
     */
    public function splitStatements(string $sql): array
    {
        $statements = [];
        $buffer = '';
        $length = strlen($sql);

        $inSingleQuote = false;
        $inDoubleQuote = false;
        $inLineComment = false;
        $inBlockComment = false;

        for ($i = 0; $i < $length; $i++) {
            $char = $sql[$i];
            $next = $i + 1 < $length ? $sql[$i + 1] : null;

            if ($inLineComment) {
                if ($char === "\n") {
                    $inLineComment = false;
                }
                continue;
            }

            if ($inBlockComment) {
                if ($char === '*' && $next === '/') {
                    $inBlockComment = false;
                    $i++;
                }
                continue;
            }

            if (!$inSingleQuote && !$inDoubleQuote) {
                if ($char === '-' && $next === '-') {
                    $prev = $i > 0 ? $sql[$i - 1] : "\n";
                    if ($prev === "\n" || $prev === "\r" || ctype_space($prev)) {
                        $inLineComment = true;
                        $i++;
                        continue;
                    }
                }

                if ($char === '#') {
                    $inLineComment = true;
                    continue;
                }

                if ($char === '/' && $next === '*') {
                    $inBlockComment = true;
                    $i++;
                    continue;
                }
            }

            if ($char === "'" && !$inDoubleQuote) {
                $escaped = $i > 0 && $sql[$i - 1] === '\\';
                if (!$escaped) {
                    $inSingleQuote = !$inSingleQuote;
                }
                $buffer .= $char;
                continue;
            }

            if ($char === '"' && !$inSingleQuote) {
                $escaped = $i > 0 && $sql[$i - 1] === '\\';
                if (!$escaped) {
                    $inDoubleQuote = !$inDoubleQuote;
                }
                $buffer .= $char;
                continue;
            }

            if ($char === ';' && !$inSingleQuote && !$inDoubleQuote) {
                $statement = trim($buffer);
                if ($statement !== '') {
                    $statements[] = $statement;
                }
                $buffer = '';
                continue;
            }

            $buffer .= $char;
        }

        $tail = trim($buffer);
        if ($tail !== '') {
            $statements[] = $tail;
        }

        return $statements;
    }

    /**
     * @return string[]
     */
    public function loadStatements(string $path): array
    {
        return $this->splitStatements($this->loadFile($path));
    }
}