<?php

declare(strict_types=1);

namespace ZMosquita\Core\Generators\Shared;

use RuntimeException;
use ZMosquita\Core\Database\DataDefMetaResolver;
use ZMosquita\Core\Database\DataDefResolver;
use ZMosquita\Core\Database\Schema\SqlSchemaLoader;

final class DefinitionNormalizer
{
    public function __construct(
        private DataDefResolver $dataDefResolver,
        private DataDefMetaResolver $metaResolver,
        private SqlSchemaLoader $sqlLoader
    ) {
    }

    public function fromCore(string $resourceName): TableDefinition
    {
        $path = $this->dataDefResolver->core($resourceName);
        $sql = $this->sqlLoader->loadFile($path);
        $meta = $this->metaResolver->loadCore($resourceName);

        return $this->normalizeSql($sql, $resourceName, 'core', null, $meta);
    }

    public function fromApp(string $appCode, string $resourceName): TableDefinition
    {
        $path = $this->dataDefResolver->app($appCode, $resourceName);
        $sql = $this->sqlLoader->loadFile($path);
        $meta = $this->metaResolver->loadApp($appCode, $resourceName);

        return $this->normalizeSql($sql, $resourceName, 'app', $appCode, $meta);
    }

    public function normalizeSql(
        string $sql,
        string $resourceName,
        string $scope,
        ?string $appCode = null,
        array $meta = []
    ): TableDefinition {
        $create = $this->extractCreateTableStatement($sql);

        $tableName = $this->extractTableName($create);
        $body = $this->extractTableBody($create);

        [$columnLines, $constraintLines] = $this->splitDefinitions($body);

        $columns = [];
        $primaryColumns = [];
        $foreignKeys = [];

        foreach ($columnLines as $line) {
            $column = $this->parseColumnLine($line);
            if ($column) {
                $columns[$column->name] = $column;
            }
        }

        foreach ($constraintLines as $line) {
            $pkColumns = $this->parsePrimaryKeyLine($line);
            foreach ($pkColumns as $pk) {
                $primaryColumns[] = $pk;
            }

            $fk = $this->parseForeignKeyLine($line);
            if ($fk) {
                $foreignKeys[] = $fk;

                if (isset($columns[$fk->column])) {
                    $columns[$fk->column]->meta['foreign_key'] = true;
                    $columns[$fk->column]->meta['references'] = [
                        'table' => $fk->referencedTable,
                        'column' => $fk->referencedColumn,
                    ];
                }
            }
        }

        foreach ($primaryColumns as $pkColumn) {
            if (isset($columns[$pkColumn])) {
                $columns[$pkColumn]->primaryKey = true;
            }
        }

        $tableMeta = $this->mergeMetaWithDefaults($meta);

        return new TableDefinition(
            resourceName: $resourceName,
            tableName: $tableName,
            scope: $scope,
            appCode: $appCode,
            columns: array_values($columns),
            foreignKeys: $foreignKeys,
            meta: $tableMeta,
        );
    }

    private function extractCreateTableStatement(string $sql): string
    {
        $statements = $this->sqlLoader->splitStatements($sql);

        foreach ($statements as $statement) {
            if (preg_match('/^\s*CREATE\s+TABLE/i', $statement)) {
                return $statement;
            }
        }

        throw new RuntimeException('No CREATE TABLE statement found in SQL.');
    }

    private function extractTableName(string $createSql): string
    {
        if (preg_match('/CREATE\s+TABLE\s+`?([a-zA-Z0-9_]+)`?\s*\(/i', $createSql, $matches)) {
            return $matches[1];
        }

        throw new RuntimeException('Unable to extract table name from CREATE TABLE statement.');
    }

    private function extractTableBody(string $createSql): string
    {
        $start = strpos($createSql, '(');
        $end = strrpos($createSql, ')');

        if ($start === false || $end === false || $end <= $start) {
            throw new RuntimeException('Unable to extract CREATE TABLE body.');
        }

        return substr($createSql, $start + 1, $end - $start - 1);
    }

    /**
     * @return array{0: string[], 1: string[]}
     */
    private function splitDefinitions(string $body): array
    {
        $parts = [];
        $buffer = '';
        $length = strlen($body);
        $depth = 0;
        $inSingleQuote = false;
        $inDoubleQuote = false;

        for ($i = 0; $i < $length; $i++) {
            $char = $body[$i];
            $prev = $i > 0 ? $body[$i - 1] : '';

            if ($char === "'" && !$inDoubleQuote && $prev !== '\\') {
                $inSingleQuote = !$inSingleQuote;
            } elseif ($char === '"' && !$inSingleQuote && $prev !== '\\') {
                $inDoubleQuote = !$inDoubleQuote;
            }

            if (!$inSingleQuote && !$inDoubleQuote) {
                if ($char === '(') {
                    $depth++;
                } elseif ($char === ')') {
                    $depth--;
                } elseif ($char === ',' && $depth === 0) {
                    $parts[] = trim($buffer);
                    $buffer = '';
                    continue;
                }
            }

            $buffer .= $char;
        }

        $tail = trim($buffer);
        if ($tail !== '') {
            $parts[] = $tail;
        }

        $columnLines = [];
        $constraintLines = [];

        foreach ($parts as $part) {
            if (preg_match('/^(PRIMARY\s+KEY|CONSTRAINT|FOREIGN\s+KEY|UNIQUE\s+KEY|KEY|INDEX)/i', ltrim($part))) {
                $constraintLines[] = $part;
            } else {
                $columnLines[] = $part;
            }
        }

        return [$columnLines, $constraintLines];
    }

    private function parseColumnLine(string $line): ?ColumnDefinition
    {
        $line = trim($line);

        if ($line === '') {
            return null;
        }

        if (!preg_match('/^`?([a-zA-Z0-9_]+)`?\s+([a-zA-Z0-9]+)(\(([0-9]+)\))?/i', $line, $matches)) {
            return null;
        }

        $name = $matches[1];
        $type = strtolower($matches[2]);
        $length = isset($matches[4]) ? (int)$matches[4] : null;

        $nullable = !preg_match('/NOT\s+NULL/i', $line);
        $autoIncrement = preg_match('/AUTO_INCREMENT/i', $line) === 1;

        $default = null;
        if (preg_match('/DEFAULT\s+([^\s,]+)/i', $line, $defaultMatches)) {
            $default = trim($defaultMatches[1], "'\"");
        }

        return new ColumnDefinition(
            name: $name,
            type: $type,
            nullable: $nullable,
            primaryKey: false,
            autoIncrement: $autoIncrement,
            default: $default,
            length: $length,
            meta: []
        );
    }

    /**
     * @return string[]
     */
    private function parsePrimaryKeyLine(string $line): array
    {
        if (!preg_match('/PRIMARY\s+KEY\s*\((.+)\)/i', $line, $matches)) {
            return [];
        }

        $columns = array_map(
            static fn (string $part): string => trim($part, " `\t\n\r\0\x0B"),
            explode(',', $matches[1])
        );

        return array_values(array_filter($columns));
    }

    private function parseForeignKeyLine(string $line): ?ForeignKeyDefinition
    {
        if (
            preg_match(
                '/(?:CONSTRAINT\s+`?([a-zA-Z0-9_]+)`?\s+)?FOREIGN\s+KEY\s*\(`?([a-zA-Z0-9_]+)`?\)\s+REFERENCES\s+`?([a-zA-Z0-9_]+)`?\s*\(`?([a-zA-Z0-9_]+)`?\)/i',
                $line,
                $matches
            )
        ) {
            return new ForeignKeyDefinition(
                column: $matches[2],
                referencedTable: $matches[3],
                referencedColumn: $matches[4],
                constraintName: $matches[1] ?? null,
                meta: []
            );
        }

        return null;
    }

    private function mergeMetaWithDefaults(array $meta): array
    {
        return array_replace_recursive([
            'labels' => [],
            'form' => [
                'fields' => null,
            ],
            'table' => [
                'columns' => null,
            ],
            'relations' => [],
            'generator' => [],
        ], $meta);
    }
}