<?php
$config = require 'config.php';

function getColumns(PDO $pdo, $dbname) {
    $stmt = $pdo->prepare("
        SELECT TABLE_NAME, COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT, EXTRA
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = ?
        ORDER BY TABLE_NAME, ORDINAL_POSITION
    ");
    $stmt->execute([$dbname]);
    $columns = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $columns[$row['TABLE_NAME']][$row['COLUMN_NAME']] = $row;
    }
    return $columns;
}

function extractDbName($dsn) {
    if (preg_match('/dbname=([^;]+)/', $dsn, $matches)) {
        return $matches[1];
    }
    return null;
}

$dev = new PDO($config['dev']['dsn'], $config['dev']['user'], $config['dev']['pass']);
$prod = new PDO($config['prod']['dsn'], $config['prod']['user'], $config['prod']['pass']);

$devDbName = extractDbName($config['dev']['dsn']);
$prodDbName = extractDbName($config['prod']['dsn']);

$devColumns  = getColumns($dev,  $devDbName);
$prodColumns = getColumns($prod, $prodDbName);

$output = "-- Script para sincronizar estructura de BD: $prodDbName <- $devDbName\n\n";

foreach ($devColumns as $table => $columns) {
    if (!isset($prodColumns[$table])) {
        $stmt = $dev->query("SHOW CREATE TABLE `$table`");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $output .= "-- Crear tabla $table\n";
        $output .= $row['Create Table'] . ";\n\n";
        continue;
    }

    foreach ($columns as $col => $def) {
        if (!isset($prodColumns[$table][$col])) {
            $colDef = "{$def['COLUMN_TYPE']}"
                . ($def['IS_NULLABLE'] === 'NO' ? " NOT NULL" : "")
                . ($def['COLUMN_DEFAULT'] !== null ? " DEFAULT " . $prod->quote($def['COLUMN_DEFAULT']) : "")
                . ($def['EXTRA'] ? " {$def['EXTRA']}" : "");
            $output .= "ALTER TABLE `$table` ADD COLUMN `$col` $colDef;\n";
        } else {
            $pcol = $prodColumns[$table][$col];
            if (
                $pcol['COLUMN_TYPE'] !== $def['COLUMN_TYPE'] ||
                $pcol['IS_NULLABLE'] !== $def['IS_NULLABLE'] ||
                $pcol['COLUMN_DEFAULT'] !== $def['COLUMN_DEFAULT'] ||
                $pcol['EXTRA'] !== $def['EXTRA']
            ) {
                $colDef = "{$def['COLUMN_TYPE']}"
                    . ($def['IS_NULLABLE'] === 'NO' ? " NOT NULL" : "")
                    . ($def['COLUMN_DEFAULT'] !== null ? " DEFAULT " . $prod->quote($def['COLUMN_DEFAULT']) : "")
                    . ($def['EXTRA'] ? " {$def['EXTRA']}" : "");
                $output .= "ALTER TABLE `$table` MODIFY COLUMN `$col` $colDef;\n";
            }
        }
    }
}

file_put_contents('diff_output.sql', $output);
echo "Archivo generado: diff_output.sql\n";
