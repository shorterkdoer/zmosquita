<?php

namespace App\Helpers;

/**
 * Helper para generar datos y scripts de DataTables usando PDO.
 */
class ApiCrudHelper
{
    /**
     * Instancia PDO para las consultas.
     *
     * @var \PDO
     */
    protected static $pdo;

    /**
     * Establece la instancia PDO.
     *
     * @param \PDO $pdo
     */
    public static function setPdo(\PDO $pdo): void
    {
        self::$pdo = $pdo;
    }

    /**
     * Ejecuta y formatea la respuesta para DataTables (server-side).
     *
     * @param array      $config Parámetros de tabla y campos:
     *                            - table (string): Nombre de la tabla.
     *                            - fields (array): Cada elemento debe tener:
     *                                'name'       => 'columna',
     *                                'searchable' => bool,
     *                                'orderable'  => bool
     * @param array|null $params Parámetros de DataTables ($_GET o similar).
     * @return array ['draw'=>int,'recordsTotal'=>int,'recordsFiltered'=>int,'data'=>array]
     * @throws \InvalidArgumentException
     */
    public static function fetchData(array $config, array $params = null): array
    {
        if (!self::$pdo) {
            throw new \InvalidArgumentException('Debe establecer PDO con ApiCrudHelper::setPdo().');
        }
        $params = $params ?? $_GET;

        $table  = $config['table'] ?? null;
        $fields = $config['fields'] ?? [];

        if (!$table || empty($fields)) {
            throw new \InvalidArgumentException('Config debe contener "table" y "fields".');
        }

        // Columnas
        $cols = array_map(fn($f) => $f['name'], $fields);
        $selectCols = implode(', ', $cols);

        // Conteo total
        $stmtTotal = self::$pdo->prepare("SELECT COUNT(*) FROM {$table}");
        $stmtTotal->execute();
        $recordsTotal = (int) $stmtTotal->fetchColumn();

        // Búsqueda global
        $where    = '';
        $bindings = [];
        if (!empty($params['search']['value'])) {
            $search = '%' . $params['search']['value'] . '%';
            $parts  = [];
            foreach ($fields as $f) {
                if (!empty($f['searchable'])) {
                    $parts[] = "{$f['name']} LIKE :search";
                }
            }
            if ($parts) {
                $where       = ' WHERE ' . implode(' OR ', $parts);
                $bindings[':search'] = $search;
            }
        }

        // Conteo filtrado
        $sqlFiltered = "SELECT COUNT(*) FROM {$table}{$where}";
        $stmtFilt    = self::$pdo->prepare($sqlFiltered);
        foreach ($bindings as $key => $val) {
            $stmtFilt->bindValue($key, $val);
        }
        $stmtFilt->execute();
        $recordsFiltered = (int) $stmtFilt->fetchColumn();

        // Construir consulta de datos
        $sql = "SELECT {$selectCols} FROM {$table}{$where}";

        // Ordenamiento
        if (!empty($params['order'][0])) {
            $order = $params['order'][0];
            $idx   = (int) $order['column'];
            $dir   = strtolower($order['dir']) === 'desc' ? 'DESC' : 'ASC';
            $f     = $fields[$idx] ?? null;
            if ($f && !empty($f['orderable'])) {
                $sql .= " ORDER BY {$f['name']} {$dir}";
            }
        }

        // Paginación
        $start  = (int) ($params['start']  ?? 0);
        $length = (int) ($params['length'] ?? 10);

        $sql .= " LIMIT :start, :length";
        $bindings[':start']  = $start;
        $bindings[':length'] = $length;

        $stmt = self::$pdo->prepare($sql);
        foreach ($bindings as $key => $val) {
            $paramType = in_array($key, [':start', ':length']) ? \PDO::PARAM_INT : \PDO::PARAM_STR;
            $stmt->bindValue($key, $val, $paramType);
        }
        $stmt->execute();
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Formatear respuesta estándar para DataTables
        return [
            'draw'            => isset($params['draw']) ? (int) $params['draw'] : 0,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ];
    }

    /**
     * Genera el código JavaScript para inicializar DataTables en el frontend.
     *
     * @param array  $config        Misma configuración (tabla/campos).
     * @param string $ajaxUrl       URL para la petición Ajax.
     * @param string $tableSelector Selector CSS (p.ej. '#datatable').
     * @return string               Script listo para incrustar.
     */
    public static function buildDataTablesScript(array $config, string $ajaxUrl, string $tableSelector = '#datatable'): string
    {
        $cols = array_map(function($f) {
            $searchable = !empty($f['searchable']) ? 'true' : 'false';
            $orderable  = !empty($f['orderable'])  ? 'true' : 'false';
            return "{ data: '{$f['name']}', name: '{$f['name']}', searchable: {$searchable}, orderable: {$orderable} }";
        }, $config['fields']);
        $columnsJs = implode(",\n                ", $cols);

        return <<<JS
<script>
\$(document).ready(function() {
    \$('$tableSelector').DataTable({
        processing: true,
        serverSide: true,
        ajax: '$ajaxUrl',
        columns: [
                $columnsJs
        ]
    });
});
</script>
JS;
    }
}
