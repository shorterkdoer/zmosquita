<?php
namespace App\Services;

use App\Repositories\UserRepository;
use App\Repositories\MatriculaRepository;
use App\Repositories\TramiteRepository;

/**
 * AdminService - Handles admin-specific queries and operations
 *
 * Refactored to use Repository Pattern for data access
 */
class AdminService
{
    protected UserRepository $userRepo;
    protected MatriculaRepository $matriculaRepo;
    protected TramiteRepository $tramiteRepo;

    public function __construct(
        ?UserRepository $userRepo = null,
        ?MatriculaRepository $matriculaRepo = null,
        ?TramiteRepository $tramiteRepo = null
    ) {
        $this->userRepo = $userRepo ?? new UserRepository();
        $this->matriculaRepo = $matriculaRepo ?? new MatriculaRepository();
        $this->tramiteRepo = $tramiteRepo ?? new TramiteRepository();
    }

    /**
     * Execute custom query for admin views
     *
     * @param string $query SQL query
     * @return array Query results
     */
    public function customQuery(string $query): array
    {
        return $this->userRepo->query($query);
    }

    /**
     * Get aspirantes data for admin view
     *
     * @param array $config Query configuration from config file
     * @return array Aspirantes data
     */
    public function getAspirantes(array $config): array
    {
        $tables = $config['QrySpec']['tables'] ?? [];
        $campos = $config['campos'] ?? [];
        $actividades = $config['actividades'] ?? [];
        $filter = $config['QrySpec']['filter'] ?? '';
        $joinconditions = $config['QrySpec']['joincond'] ?? '';
        $order = $config['QrySpec']['order'] ?? [];
        $id_field = $config['config']['field_id'] ?? 'id';

        require_once $_SESSION['directoriobase'] . '/app/Core/Helpers/string4query.php';
        $query = str4qry($tables, $campos, $actividades, $filter, $joinconditions, $order, $id_field);

        return $this->customQuery($query);
    }

    /**
     * Get dashboard statistics
     *
     * @return array ['total_users' => int, 'active_matriculas' => int, 'pending_tramites' => int]
     */
    public function getDashboardStats(): array
    {
        // Total users
        $totalUsers = $this->userRepo->count();

        // Active matriculas
        $activeMatriculas = count($this->matriculaRepo->getActive());

        // Pending tramites
        $pendingTramites = count($this->tramiteRepo->getPendingRevision());

        return [
            'total_users' => $totalUsers,
            'active_matriculas' => $activeMatriculas,
            'pending_tramites' => $pendingTramites,
        ];
    }

    /**
     * Get recent activity log
     *
     * @param int $limit Number of entries to return
     * @return array Recent activity entries
     */
    public function getRecentActivity(int $limit = 20): array
    {
        // This would typically query an activity log table
        // For now, return empty array as placeholder
        return [];
    }
}
