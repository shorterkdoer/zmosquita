<?php
namespace App\Services;

use DateTime;

class BankCsvImporter
{
    public static function parseAndStage(string $filepath, int $batchId, \PDO $db): array
    {
        // 1) Abrir archivo, detectar BOM y encoding
        $contents = file_get_contents($filepath);
        // quitar BOM si existe
        $contents = preg_replace('/^\xEF\xBB\xBF/', '', $contents);
        // intentar detectar cp1252 y convertir a UTF-8 (fallback: dejar)
        if (!mb_check_encoding($contents, 'UTF-8')) {
            $contents = mb_convert_encoding($contents, 'UTF-8', 'Windows-1252');
        }

        $tmp = tmpfile();
        fwrite($tmp, $contents);
        $meta = stream_get_meta_data($tmp);
        $fh = fopen($meta['uri'], 'r');

        // 2) Leer cabecera
        $header = self::readCsvRow($fh);
        // Esperamos estas columnas (en este orden):
        // Fecha;Descripción;Origen;Créditos;Número de Terminal;Observaciones Cliente;Número de Comprobante;Leyendas Adicionales1;Leyendas Adicionales2;Leyendas Adicionales3;Leyendas Adicionales4;Tipo de Movimiento

        $inserted = 0; $skippedDup = 0; $rows = [];

        while (($row = self::readCsvRow($fh)) !== false) {
            if (count(array_filter($row, fn($v) => trim((string)$v) !== '')) === 0) continue; // fila vacía

            $rec = self::normalizeRow($header, $row);

            // 3) Hash de deduplicación (fuerte pero razonable)
            $hash = sha1( implode('|', [
                $rec['fecha'] ?? '',
                number_format((float)($rec['importe'] ?? 0), 2, '.', ''),
                preg_replace('/\D+/', '', (string)($rec['cuit'] ?? '')),
                $rec['terminal'] ?? '',
                $rec['nro_comprobante'] ?? ''
            ]) );

            // 4) Intento de match sugerido
            [$matchUserId, $matchPagoId, $status, $notes] = self::suggestMatch($rec, $db);

            // 5) Insert staging (ignorar si hash ya existe)
            $stmt = $db->prepare("
                INSERT IGNORE INTO import_bank_staging
                (batch_id, raw_json, fecha, descripcion, origen, importe, terminal, observaciones_cliente, nro_comprobante,
                 leyenda1, cuit, leyenda3, leyenda4, tipo_mov, hash_op, match_user_id, match_pago_id, status, notes)
                VALUES
                (:batch_id, :raw_json, :fecha, :descripcion, :origen, :importe, :terminal, :obs, :nro_comp,
                 :ly1, :cuit, :ly3, :ly4, :tipo, :hash, :m_user, :m_pago, :status, :notes)
            ");

            $ok = $stmt->execute([
                ':batch_id' => $batchId,
                ':raw_json' => json_encode($rec, JSON_UNESCAPED_UNICODE),
                ':fecha'    => $rec['fecha'] ?? null,
                ':descripcion' => $rec['descripcion'] ?? null,
                ':origen'   => $rec['origen'] ?? null,
                ':importe'  => $rec['importe'] ?? null,
                ':terminal' => $rec['terminal'] ?? null,
                ':obs'      => $rec['observaciones_cliente'] ?? null,
                ':nro_comp' => $rec['nro_comprobante'] ?? null,
                ':ly1'      => $rec['leyenda1'] ?? null,
                ':cuit'     => $rec['cuit'] ?? null,
                ':ly3'      => $rec['leyenda3'] ?? null,
                ':ly4'      => $rec['leyenda4'] ?? null,
                ':tipo'     => $rec['tipo_mov'] ?? null,
                ':hash'     => $hash,
                ':m_user'   => $matchUserId,
                ':m_pago'   => $matchPagoId,
                ':status'   => $status,
                ':notes'    => $notes,
            ]);

            if ($ok && $stmt->rowCount() > 0) $inserted++; else $skippedDup++;
        }

        fclose($fh);
        fclose($tmp);

        return ['inserted' => $inserted, 'duplicates_ignored' => $skippedDup];
    }

    private static function readCsvRow($fh)
    {
        // Usa ; como delimitador y " como enclosure
        return fgetcsv($fh, 0, ';', '"', '\\');
    }

    private static function normalizeRow(array $header, array $row): array
    {
        $m = array_combine($header, $row);

        $fecha = null;
        if (!empty($m['Fecha'])) {
            $dt = DateTime::createFromFormat('d/m/Y', trim($m['Fecha']));
            if ($dt) $fecha = $dt->format('Y-m-d');
        }

        // Importes tipo "20103,00" → 20103.00
        $importe = null;
        if (isset($m['Créditos'])) {
            $raw = trim($m['Créditos']);
            // quitar separadores de miles si existieran, mantener coma como decimal y pasar a punto
            $raw = str_replace(['.', ' '], '', $raw);
            $raw = str_replace(',', '.', $raw);
            $importe = is_numeric($raw) ? (float)$raw : null;
        }

        $cuit = isset($m['Leyendas Adicionales2']) ? preg_replace('/\D+/', '', $m['Leyendas Adicionales2']) : null;

        return [
            'fecha' => $fecha,
            'descripcion' => trim((string)($m['Descripción'] ?? '')),
            'origen' => trim((string)($m['Origen'] ?? '')),
            'importe' => $importe,
            'terminal' => trim((string)($m['Número de Terminal'] ?? '')),
            'observaciones_cliente' => trim((string)($m['Observaciones Cliente'] ?? '')),
            'nro_comprobante' => trim((string)($m['Número de Comprobante'] ?? '')),
            'leyenda1' => trim((string)($m['Leyendas Adicionales1'] ?? '')),
            'cuit' => $cuit ?: null,
            'leyenda3' => trim((string)($m['Leyendas Adicionales3'] ?? '')),
            'leyenda4' => trim((string)($m['Leyendas Adicionales4'] ?? '')),
            'tipo_mov' => trim((string)($m['Tipo de Movimiento'] ?? '')),
        ];
    }

    private static function suggestMatch(array $rec, \PDO $db): array
    {
        $status = 'nuevo'; $notes = null; $matchUserId = null; $matchPagoId = null;

        // A) Intentar encontrar user por CUIT (ajustá a tu esquema)
        if (!empty($rec['cuit'])) {
            $q = $db->prepare("SELECT id FROM users WHERE REPLACE(COALESCE(cuit,''), '-', '') = :c LIMIT 1");
            $q->execute([':c' => $rec['cuit']]);
            $matchUserId = $q->fetchColumn() ?: null;
        }

        // B) Intentar match a pago existente por (fecha±2) + importe + (user si lo tenemos)
        if (!empty($rec['importe']) && !empty($rec['fecha'])) {
            $q = $db->prepare("
                SELECT id FROM comprobantespago
                WHERE importe = :imp
                  AND fecha BETWEEN DATE_SUB(:f, INTERVAL 2 DAY) AND DATE_ADD(:f, INTERVAL 2 DAY)
                  " . ($matchUserId ? "AND user_id = :uid" : "") . "
                LIMIT 1
            ");
            $params = [':imp' => $rec['importe'], ':f' => $rec['fecha']];
            if ($matchUserId) $params[':uid'] = $matchUserId;
            $q->execute($params);
            $matchPagoId = $q->fetchColumn() ?: null;
        }

        if ($matchPagoId) { $status = 'duplicado'; $notes = 'Coincide con pago ya cargado.'; }
        elseif ($matchUserId) { $status = 'posible_match'; $notes = 'Coincide CUIT con usuario.'; }
        else { $status = 'nuevo'; }

        return [$matchUserId, $matchPagoId, $status, $notes];
    }
}
