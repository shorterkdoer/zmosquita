<?php

namespace App\Core\Helpers;

use App\Enums\UserRole;

/**
 * DashboardMenuHelper
 *
 * Provides methods for retrieving and rendering dashboard menus
 * based on user roles.
 */
class DashboardMenuHelper
{
    /**
     * Get menu configuration for a specific role
     *
     * @param string|UserRole $role The role to get menu for
     * @return array The menu configuration for the role
     */
    public static function getMenuForRole(string|UserRole $role): array
    {
        // Convert string to UserRole enum if needed
        if (is_string($role)) {
            $role = UserRole::isValid($role)
                ? UserRole::from($role)
                : UserRole::GUEST;
        }

        $roleKey = $role->value;

        // Load menu configuration
        $config = self::loadMenuConfig();

        // Return menu for role, default to guest if not found
        return $config[$roleKey] ?? $config['guest'];
    }

    /**
     * Get menu configuration for the current authenticated user
     *
     * @return array The menu configuration for current user
     */
    public static function getMenuForCurrentUser(): array
    {
        $user = $_SESSION['user'] ?? null;

        if (!$user) {
            return self::getMenuForRole(UserRole::GUEST);
        }

        $role = $user['role'] ?? 'guest';
        return self::getMenuForRole($role);
    }

    /**
     * Render menu items as HTML
     *
     * @param array $menuItems The menu items to render
     * @param int|null $userId Optional user ID for URL substitution
     * @return string The rendered HTML
     */
    public static function renderMenu(array $menuItems, ?int $userId = null): string
    {
        if (empty($menuItems['botones'])) {
            return '';
        }

        $html = '<div class="container my-4">';
        $html .= '<div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-4 g-3">';

        foreach ($menuItems['botones'] as $boton) {
            $html .= self::renderMenuItem($boton, $userId);
        }

        $html .= '</div></div>';

        return $html;
    }

    /**
     * Render a single menu item
     *
     * @param array $item The menu item configuration
     * @param int|null $userId Optional user ID for URL substitution
     * @return string The rendered HTML for the item
     */
    public static function renderMenuItem(array $item, ?int $userId = null): string
    {
        $link = $item['link'] ?? '';
        $icon = $item['icon'] ?? 'bi-circle';
        $text = $item['text'] ?? 'Sin título';
        $hint = $item['hint'] ?? '';
        $urlId = $item['url_id'] ?? false;

        // Replace {id} placeholder with actual user ID if needed
        if ($urlId && $userId !== null) {
            $link = str_replace('{id}', (string) $userId, $link);
        }

        // Determine button style based on link
        $btnClass = ($link === '/logout')
            ? 'btn-outline-success'
            : 'btn-outline-primary';

        // Render disabled button if no link
        if (empty($link)) {
            return '
                <div class="col text-center">
                    <button class="btn ' . $btnClass . ' d-flex flex-column align-items-center py-3 h-100" disabled title="' . htmlspecialchars($hint) . '">
                        <i class="bi ' . htmlspecialchars($icon) . ' mb-2" style="font-size: 3rem;"></i>
                        <strong>' . htmlspecialchars($text) . '</strong>
                    </button>
                </div>';
        }

        return '
            <div class="col text-center">
                <a href="' . htmlspecialchars($link) . '" class="btn ' . $btnClass . ' d-flex flex-column align-items-center py-3 h-100" title="' . htmlspecialchars($hint) . '">
                    <i class="bi ' . htmlspecialchars($icon) . ' mb-2" style="font-size: 3rem;"></i>
                    <strong>' . htmlspecialchars($text) . '</strong>
                </a>
            </div>';
    }

    /**
     * Render menu header
     *
     * @param array $menuItems The menu items containing header config
     * @return string The rendered HTML header
     */
    public static function renderHeader(array $menuItems): string
    {
        if (empty($menuItems['header'])) {
            return '';
        }

        $header = $menuItems['header'];
        $titulo = $header['titulo'] ?? '';
        $subtitulo = $header['subtitulo'] ?? '';

        return '
            <div class="text-center mb-4">
                <h1 class="text-primary font-weight-bold">' . htmlspecialchars($titulo) . '</h1>
                <h3 class="text-primary font-weight-bold">' . htmlspecialchars($subtitulo) . '</h3>
            </div>';
    }

    /**
     * Get the title for a specific role
     *
     * @param string|UserRole $role The role to get title for
     * @return string The title
     */
    public static function getTitleForRole(string|UserRole $role): string
    {
        $menu = self::getMenuForRole($role);
        return $menu['header']['titulo'] ?? 'Dashboard';
    }

    /**
     * Get all available roles
     *
     * @return array<string> Array of role values
     */
    public static function getAvailableRoles(): array
    {
        return array_column(UserRole::cases(), 'value');
    }

    /**
     * Check if a role has access to a specific menu item
     *
     * @param string $role The role to check
     * @param string $link The link to check access for
     * @return bool True if the role has access to the link
     */
    public static function roleHasAccessTo(string $role, string $link): bool
    {
        $menu = self::getMenuForRole($role);

        foreach ($menu['botones'] ?? [] as $item) {
            if ($item['link'] === $link) {
                return true;
            }
        }

        return false;
    }

    /**
     * Load menu configuration file
     *
     * @return array The menu configuration
     */
    private static function loadMenuConfig(): array
    {
        $configPath = $_SESSION['directoriobase'] . '/config/dashboard_menu.php';

        if (file_exists($configPath)) {
            return require $configPath;
        }

        // Return empty config if file doesn't exist
        return [
            'guest' => [
                'header' => [
                    'titulo' => 'Dashboard',
                    'subtitulo' => '',
                ],
                'botones' => [],
            ],
        ];
    }

    /**
     * Get menu items as an array (for API/JSON responses)
     *
     * @param string|UserRole $role The role to get menu for
     * @param int|null $userId Optional user ID for URL substitution
     * @return array The menu items array
     */
    public static function getMenuItemsAsArray(string|UserRole $role, ?int $userId = null): array
    {
        $menu = self::getMenuForRole($role);
        $items = [];

        foreach ($menu['botones'] ?? [] as $item) {
            $link = $item['link'] ?? '';

            // Replace {id} placeholder with actual user ID if needed
            if (($item['url_id'] ?? false) && $userId !== null) {
                $link = str_replace('{id}', (string) $userId, $link);
            }

            $items[] = [
                'link' => $link,
                'icon' => $item['icon'] ?? '',
                'text' => $item['text'] ?? '',
                'hint' => $item['hint'] ?? '',
            ];
        }

        return [
            'header' => $menu['header'] ?? [],
            'items' => $items,
        ];
    }
}
