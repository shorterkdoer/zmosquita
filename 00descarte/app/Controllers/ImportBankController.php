<?php
namespace App\Controllers;

use App\Services\BankCsvImporter;
use PDO;

class ImportBankController
{
    public function uploadForm()
    {
        // Renderizá un form simple: <input type="file" name="csv" accept=".csv">
        include __DIR__ . '/../Views/import/upload.php';
    }

    public function uploadStore()
    {
        if (empty($_FILES['csv']['tmp_name'])) {
            $_SESSION['error'] = 'Subí un CSV primero.'; header('Location: /import/upload'); exit;
        }

        $db = $this->db();
        $db->beginTransaction();
        try {
            // Crear batch
            $stmt = $db->prepare("INSERT INTO import_batch (filename, created_by) VALUES (:f, :u)");
            $stmt->execute([
                ':f' => $_FILES['csv']['name'],
                ':u' => $_SESSION['user_id'] ?? null
            ]);
            $batchId = (int)$db->lastInsertId();

            $res = BankCsvImporter::parseAndStage($_FILES['csv']['tmp_name'], $batchId, $db);

            $db->commit();
            $_SESSION['ok'] = "Importados al staging: {$res['inserted']}, duplicados ignorados: {$res['duplicates_ignored']}";
            header('Location: /import/review/' . $batchId); exit;
        } catch (\Throwable $e) {
            $db->rollBack();
            $_SESSION['error'] = 'Error importando: ' . $e->getMessage();
            header('Location: /import/upload'); exit;
        }
    }

    public function review(int $batchId)
    {
        $db = $this->db();
        $stmt = $db->prepare("SELECT * FROM import_bank_staging WHERE batch_id = :b ORDER BY id ASC");
        $stmt->execute([':b' => $batchId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Render: una grilla con color por status y acciones:
        // [ ] Importar   [ ] Omitir   [selector usuario si status=posible_match]   [ver detalles]
        include __DIR__ . '/../Views/import/review.php';
    }

    public function commit(int $batchId)
    {
        $db = $this->db();
        // Espera arrays tipo decision[id] = 'importar'|'omitir', y opcional user_id[id]
        $decision = $_POST['decision'] ?? [];
        $chosenUser = $_POST['user_id'] ?? [];

        $db->beginTransaction();
        try {
            $sel = $db->prepare("SELECT * FROM import_bank_staging WHERE id = :id AND batch_id = :b FOR UPDATE");
            $upd = $db->prepare("UPDATE import_bank_staging SET status = :s, notes = :n WHERE id = :id");

            foreach ($decision as $id => $what) {
                $id = (int)$id;
                $sel->execute([':id' => $id, ':b' => $batchId]);
                $r = $sel->fetch(PDO::FETCH_ASSOC);
                if (!$r) continue;

                if ($what === 'importar' && $r['status'] !== 'duplicado') {
                    $uid = $chosenUser[$id] ?? $r['match_user_id'] ?? null;

                    // Crear pago en tu tabla real (ajustá campos)
                    $pid = self::createPago($db, [
                        'user_id' => $uid,
                        'fecha' => $r['fecha'],
                        'importe' => $r['importe'],
                        'descripcion' => $r['descripcion'],
                        'origen' => $r['origen'],
                        'terminal' => $r['terminal'],
                        'nro_comprobante' => $r['nro_comprobante'],
                        'cuit' => $r['cuit'],
                        'nombre' => $r['leyenda1'],
                        'tipo' => $r['tipo_mov'],
                    ]);

                    // Generar comprobante genérico si no hay imagen/archivo
                    $path = self::crearComprobanteGenerico([
                        'fecha' => $r['fecha'],
                        'importe' => $r['importe'],
                        'nombre' => $r['leyenda1'],
                        'cuit' => $r['cuit'],
                        'terminal' => $r['terminal'],
                        'nro' => $r['nro_comprobante'],
                        'origen' => $r['origen'],
                    ]);
                    if ($path) {
                        self::adjuntarArchivoAlPago($db, $pid, $path);
                    }

                    $upd->execute([':s' => 'importado', ':n' => 'Importado OK (pago id '.$pid.')', ':id' => $id]);
                } else {
                    $upd->execute([':s' => 'descartado', ':n' => 'Descartado por operador', ':id' => $id]);
                }
            }

            $db->commit();
            $_SESSION['ok'] = 'Operación finalizada.';
            header('Location: /import/review/' . $batchId); exit;
        } catch (\Throwable $e) {
            $db->rollBack();
            $_SESSION['error'] = 'Error al confirmar: ' . $e->getMessage();
            header('Location: /import/review/' . $batchId); exit;
        }
    }

    private static function createPago(\PDO $db, array $data): int
    {
        $stmt = $db->prepare("
            INSERT INTO comprobantespago
            (user_id, fecha, importe, descripcion, origen, terminal, nro_comprobante, cuit, nombre, tipo, created_at)
            VALUES (:u, :f, :i, :d, :o, :t, :n, :c, :nom, :ti, NOW())
        ");
        $stmt->execute([
            ':u' => $data['user_id'],
            ':f' => $data['fecha'],
            ':i' => $data['importe'],
            ':d' => $data['descripcion'],
            ':o' => $data['origen'],
            ':t' => $data['terminal'],
            ':n' => $data['nro_comprobante'],
            ':c' => $data['cuit'],
            ':nom'=> $data['nombre'],
            ':ti' => $data['tipo'],
        ]);
        return (int)$db->lastInsertId();
    }

    private static function adjuntarArchivoAlPago(\PDO $db, int $pagoId, string $filePath): void
    {
        // Ajustá a tu esquema de archivos/adjuntos
        $stmt = $db->prepare("UPDATE comprobantespago SET adjunto_path = :p WHERE id = :id");
        $stmt->execute([':p' => $filePath, ':id' => $pagoId]);
    }

    /**
     * Genera un PNG simple con los datos del movimiento.
     * (Usa GD, sin dependencias externas)
     */
    private static function crearComprobanteGenerico(array $d): ?string
    {
        $w = 1200; $h = 700;
        $im = imagecreatetruecolor($w, $h);
        $white = imagecolorallocate($im, 255,255,255);
        $black = imagecolorallocate($im, 0,0,0);
        imagefilledrectangle($im, 0,0, $w,$h, $white);

        $lines = [
            'Comprobante de Movimiento (generado automáticamente)',
            'Fecha: ' . ($d['fecha'] ?? ''),
            'Importe: $ ' . number_format((float)($d['importe'] ?? 0), 2, ',', '.'),
            'Ordenante: ' . ($d['nombre'] ?? ''),
            'CUIT: ' . ($d['cuit'] ?? ''),
            'Origen: ' . ($d['origen'] ?? ''),
            'Terminal: ' . ($d['terminal'] ?? ''),
            'N° Comprobante: ' . ($d['nro'] ?? ''),
        ];

        $y = 60; $x = 60;
        imagestring($im, 5, $x, $y, $lines[0], $black); $y += 40;
        for ($i=1; $i<count($lines); $i++) {
            imagestring($im, 4, $x, $y, $lines[$i], $black); $y += 30;
        }

        // Guardar
        $dir = __DIR__ . '/../../public/uploads/comprobantes_genericos/' . date('Y/m');
        if (!is_dir($dir)) mkdir($dir, 0775, true);
        $fname = 'comp_' . uniqid() . '.png';
        $path = $dir . '/' . $fname;
        if (imagepng($im, $path)) {
            imagedestroy($im);
            // devolvé una ruta web si necesitás mostrarlo
            return str_replace(__DIR__ . '/../../public', '', $path);
        }
        imagedestroy($im);
        return null;
    }

    private function db(): \PDO { return \App\Core\DB::conn(); } // ajustá a tu helper
}
