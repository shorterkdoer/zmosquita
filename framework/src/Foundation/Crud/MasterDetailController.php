<?php

namespace Foundation\Crud;

use PDO;
use Foundation\Core\Request;
use Foundation\Core\Session;

/**
 * Master-Detail Controller
 *
 * Provides functionality for managing parent-child table relationships.
 * Useful for invoices with line items, orders with products, etc.
 */
abstract class MasterDetailController extends Controller
{
    /**
     * Master table configuration.
     * Override in child class.
     *
     * @var array
     */
    protected array $masterConfig = [];

    /**
     * Detail table configuration.
     * Override in child class.
     *
     * @var array
     */
    protected array $detailConfig = [];

    /**
     * Foreign key field in detail table that references master.
     *
     * @var string
     */
    protected string $foreignKey = 'master_id';

    /**
     * Get master configuration.
     *
     * @return array
     */
    protected function getMasterConfig(): array
    {
        return $this->masterConfig;
    }

    /**
     * Get detail configuration.
     *
     * @return array
     */
    protected function getDetailConfig(): array
    {
        return $this->detailConfig;
    }

    /**
     * Display master records with their details.
     *
     * @param Request $request
     * @return void
     */
    public function index(Request $request): void
    {
        $master = $this->getMasterConfig();
        $detail = $this->getDetailConfig();

        // Fetch all master records with related detail count
        $sql = "SELECT m.*, COUNT(d.{$this->foreignKey}) as detail_count
                FROM {$master['table']} m
                LEFT JOIN {$detail['table']} d ON m.{$master['primaryKey']} = d.{$this->foreignKey}
                GROUP BY m.{$master['primaryKey']}
                ORDER BY m.{$master['displayField']}";

        $records = $this->executeQuery($sql);

        $this->view('cruds/master-detail/index', [
            'title' => $master['title'],
            'records' => $records,
            'master' => $master,
            'detail' => $detail,
        ]);
    }

    /**
     * Display form to create a new master record.
     *
     * @param Request $request
     * @return void
     */
    public function create(Request $request): void
    {
        $master = $this->getMasterConfig();
        $detail = $this->getDetailConfig();

        $this->view('cruds/master-detail/create', [
            'title' => "New {$master['singular']}",
            'master' => $master,
            'detail' => $detail,
        ]);
    }

    /**
     * Store a new master record.
     *
     * @param Request $request
     * @return void
     */
    public function store(Request $request): void
    {
        $master = $this->getMasterConfig();

        $data = $this->getMasterData($request);
        $id = $this->insert($master['table'], $data);

        Session::flash('success', "{$master['singular']} created successfully.");
        $this->redirect("/{$master['route']}/edit/$id");
    }

    /**
     * Display edit form for master record with details.
     *
     * @param Request $request
     * @param array $params
     * @return void
     */
    public function edit(Request $request, array $params): void
    {
        $id = $params[0] ?? null;
        if (!$id) {
            Session::flash('error', 'ID not specified.');
            $this->redirect($this->getMasterConfig()['route']);
        }

        $master = $this->getMasterConfig();
        $detail = $this->getDetailConfig();

        // Fetch master record
        $masterRecord = $this->findById($master['table'], $master['primaryKey'], $id);
        if (!$masterRecord) {
            Session::flash('error', 'Record not found.');
            $this->redirect($master['route']);
        }

        // Fetch detail records
        $detailRecords = $this->getDetailRecords($id);

        $this->view('cruds/master-detail/edit', [
            'title' => "Edit {$master['singular']}",
            'id' => $id,
            'master' => $master,
            'detail' => $detail,
            'masterRecord' => $masterRecord,
            'detailRecords' => $detailRecords,
        ]);
    }

    /**
     * Update master record and handle detail records.
     *
     * @param Request $request
     * @param array $params
     * @return void
     */
    public function update(Request $request, array $params): void
    {
        $id = $params[0] ?? null;
        if (!$id) {
            Session::flash('error', 'ID not specified.');
            $this->redirect($this->getMasterConfig()['route']);
        }

        $master = $this->getMasterConfig();
        $detail = $this->getDetailConfig();

        // Update master record
        $masterData = $this->getMasterData($request);
        $this->updateRecord($master['table'], $master['primaryKey'], $id, $masterData);

        // Handle detail records
        $this->handleDetailRecords($request, $id);

        Session::flash('success', "{$master['singular']} updated successfully.");
        $this->redirect("/{$master['route']}/edit/$id");
    }

    /**
     * Delete master record and related detail records.
     *
     * @param Request $request
     * @param array $params
     * @return void
     */
    public function delete(Request $request, array $params): void
    {
        $id = $params[0] ?? null;
        if (!$id) {
            Session::flash('error', 'ID not specified.');
            $this->redirect($this->getMasterConfig()['route']);
        }

        $master = $this->getMasterConfig();
        $detail = $this->getDetailConfig();

        // Delete detail records first (foreign key constraint)
        $this->deleteDetailRecords($id);

        // Delete master record
        $this->deleteRecord($master['table'], $master['primaryKey'], $id);

        Session::flash('success', "{$master['singular']} deleted successfully.");
        $this->redirect($master['route']);
    }

    /**
     * API endpoint for DataTables with detail count.
     *
     * @param Request $request
     * @return void
     */
    public function apiData(Request $request): void
    {
        header('Content-Type: application/json');

        $master = $this->getMasterConfig();
        $detail = $this->getDetailConfig();

        $draw = intval($_GET['draw'] ?? 0);
        $start = intval($_GET['start'] ?? 0);
        $length = intval($_GET['length'] ?? 10);
        $searchValue = $_GET['search']['value'] ?? '';

        // Build query with search
        $sql = "SELECT m.*, COUNT(d.{$this->foreignKey}) as detail_count
                FROM {$master['table']} m
                LEFT JOIN {$detail['table']} d ON m.{$master['primaryKey']} = d.{$this->foreignKey}";

        $params = [];
        if (!empty($searchValue)) {
            $sql .= " WHERE ";
            $conditions = [];
            foreach ($master['fields'] as $field) {
                if (!$field['hidden']) {
                    $conditions[] = "m.{$field['name']} LIKE ?";
                    $params[] = "%$searchValue%";
                }
            }
            $sql .= implode(' OR ', $conditions);
        }

        $sql .= " GROUP BY m.{$master['primaryKey']}";

        // Get total count
        $totalSql = "SELECT COUNT(*) FROM {$master['table']}";
        $total = $this->fetchColumn($totalSql);

        // Get filtered count
        $countSql = "SELECT COUNT(*) FROM ($sql) as subquery";
        $stmt = $this->getDB()->prepare($countSql);
        $stmt->execute($params);
        $recordsFiltered = $stmt->fetchColumn();

        // Add pagination
        $sql .= " ORDER BY m.{$master['displayField']} LIMIT $length OFFSET $start";

        $stmt = $this->getDB()->prepare($sql);
        $stmt->execute($params);
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Format output
        $data = [];
        foreach ($records as $row) {
            $item = [];
            foreach ($master['fields'] as $field) {
                if (!$field['hidden']) {
                    $item[$field['name']] = $row[$field['name']];
                }
            }

            // Action buttons
            $item['acciones'] = $this->buildActionButtons($row[$master['primaryKey']]);
            $item['detail_count'] = $row['detail_count'] ?? 0;

            $data[] = $item;
        }

        echo json_encode([
            'draw' => $draw,
            'recordsTotal' => $total,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data
        ]);
    }

    /**
     * Get detail records for a master record.
     *
     * @param int $masterId
     * @return array
     */
    protected function getDetailRecords(int $masterId): array
    {
        $detail = $this->getDetailConfig();
        $sql = "SELECT * FROM {$detail['table']}
                WHERE {$this->foreignKey} = ?
                ORDER BY {$detail['primaryKey']}";

        return $this->fetchQuery($sql, [$masterId]);
    }

    /**
     * Handle detail records (create, update, delete).
     *
     * @param Request $request
     * @param int $masterId
     * @return void
     */
    protected function handleDetailRecords(Request $request, int $masterId): void
    {
        $detail = $this->getDetailConfig();

        // Get posted detail records
        $details = $request->input('details') ?? [];

        // Delete existing details not in the posted list
        $postedIds = array_filter(array_column($details, $idField = $detail['primaryKey']));
        if (!empty($postedIds)) {
            $this->executeQuery(
                "DELETE FROM {$detail['table']}
                 WHERE {$this->foreignKey} = ? AND {$detail['primaryKey']} NOT IN ("
                 . str_repeat('?,', count($postedIds) - 1) . '?)',
                array_merge([$masterId], $postedIds)
            );
        } else {
            // Delete all details for this master
            $this->executeQuery(
                "DELETE FROM {$detail['table']} WHERE {$this->foreignKey} = ?",
                [$masterId]
            );
        }

        // Insert or update detail records
        foreach ($details as $detailData) {
            $detailData[$this->foreignKey] = $masterId;

            if (isset($detailData[$detail['primaryKey']]) && !empty($detailData[$detail['primaryKey']])) {
                // Update existing
                $id = $detailData[$detail['primaryKey']];
                unset($detailData[$detail['primaryKey']]);
                $this->updateRecord($detail['table'], $detail['primaryKey'], $id, $detailData);
            } else {
                // Insert new
                unset($detailData[$detail['primaryKey']]);
                $this->insert($detail['table'], $detailData);
            }
        }
    }

    /**
     * Delete all detail records for a master record.
     *
     * @param int $masterId
     * @return void
     */
    protected function deleteDetailRecords(int $masterId): void
    {
        $detail = $this->getDetailConfig();
        $this->executeQuery(
            "DELETE FROM {$detail['table']} WHERE {$this->foreignKey} = ?",
            [$masterId]
        );
    }

    /**
     * Extract master data from request.
     *
     * @param Request $request
     * @return array
     */
    protected function getMasterData(Request $request): array
    {
        $master = $this->getMasterConfig();
        $data = [];

        foreach ($master['fields'] as $field) {
            if ($field['name'] !== $master['primaryKey'] && !$field['readonly']) {
                $data[$field['name']] = $request->input($field['name']);
            }
        }

        return $data;
    }

    /**
     * Build action buttons for a record.
     *
     * @param int $id
     * @return string HTML of action buttons
     */
    protected function buildActionButtons(int $id): string
    {
        $master = $this->getMasterConfig();
        $route = $master['route'];

        $buttons = "<div class='btn-group btn-group-sm'>";
        $buttons .= "<a href='/$route/edit/$id' class='btn btn-warning' title='Edit'>
                     <i class='bi bi-pencil'></i>
                   </a>";
        $buttons .= "<a href='/$route/view/$id' class='btn btn-info' title='View'>
                     <i class='bi bi-eye'></i>
                   </a>";
        $buttons .= "<button type='button' class='btn btn-danger' onclick='confirmDelete($id)' title='Delete'>
                     <i class='bi bi-trash'></i>
                   </button>";
        $buttons .= "</div>";

        return $buttons;
    }

    /**
     * Insert a record into a table.
     *
     * @param string $table
     * @param array $data
     * @return int The inserted ID
     */
    protected function insert(string $table, array $data): int
    {
        $columns = array_keys($data);
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $columnsList = implode(', ', $columns);
        $sql = "INSERT INTO $table ($columnsList) VALUES ($placeholders)";

        $stmt = $this->getDB()->prepare($sql);
        $stmt->execute(array_values($data));

        return (int) $this->getDB()->lastInsertId();
    }

    /**
     * Update a record in a table.
     *
     * @param string $table
     * @param string $keyField
     * @param mixed $id
     * @param array $data
     * @return bool
     */
    protected function updateRecord(string $table, string $keyField, $id, array $data): bool
    {
        $set = [];
        foreach (array_keys($data) as $column) {
            $set[] = "$column = ?";
        }
        $setClause = implode(', ', $set);

        $stmt = $this->getDB()->prepare(
            "UPDATE $table SET $setClause WHERE $keyField = ?"
        );

        $values = array_values($data);
        $values[] = $id;

        return $stmt->execute($values);
    }

    /**
     * Delete a record from a table.
     *
     * @param string $table
     * @param string $keyField
     * @param mixed $id
     * @return bool
     */
    protected function deleteRecord(string $table, string $keyField, $id): bool
    {
        $stmt = $this->getDB()->prepare("DELETE FROM $table WHERE $keyField = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Find a record by ID.
     *
     * @param string $table
     * @param string $keyField
     * @param mixed $id
     * @return array|null
     */
    protected function findById(string $table, string $keyField, $id): ?array
    {
        $stmt = $this->getDB()->prepare("SELECT * FROM $table WHERE $keyField = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Execute a query and return all results.
     *
     * @param string $sql
     * @param array $params
     * @return array
     */
    protected function fetchQuery(string $sql, array $params = []): array
    {
        $stmt = $this->getDB()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Execute a query and return all results.
     *
     * @param string $sql
     * @param array $params
     * @return array
     */
    protected function executeQuery(string $sql, array $params = []): array
    {
        return $this->fetchQuery($sql, $params);
    }

    /**
     * Fetch a single column value.
     *
     * @param string $sql
     * @param array $params
     * @return mixed
     */
    protected function fetchColumn(string $sql, array $params = [])
    {
        $stmt = $this->getDB()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    /**
     * Get database connection.
     *
     * @return PDO
     */
    protected function getDB(): PDO
    {
        $config = require $_SESSION['directoriobase'] . '/config/settings.php';
        return new \PDO(
            $config['db']['dsn'],
            $config['db']['username'],
            $config['db']['password'],
            [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            ]
        );
    }
}
