<?php
declare(strict_types=1);

$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($requestUri, PHP_URL_PATH) ?: '/';
$path = trim($path, '/');
$currentModule = $path !== '' ? $path : (string) ($_GET['module'] ?? '');
$navigationAuth = new \AgroPCC\Services\Auth(database()->connection());
$navigationRole = (int) ($_SESSION['role_id'] ?? 0);
$navigationUserId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;
$navigationRoleDepartment = (string) ($_SESSION['role_department'] ?? 'general');
$navigationRoleIsSystem = (bool) ((int) ($_SESSION['role_is_system'] ?? 0) === 1);
$companyStmt = database()->connection()->prepare('SELECT trade_name, logo_path FROM companies WHERE id = ? LIMIT 1');
$companyStmt->execute([(int) ($_SESSION['company_id'] ?? 0)]);
$companyData = $companyStmt->fetch() ?: [];
$companyName = (string) ($companyData['trade_name'] ?? 'Empresa activa');
$companyLogo = !empty($companyData['logo_path']);
$currentUserName = (string) ($_SESSION['user_name'] ?? 'Admin');
$navConfig = require dirname(__DIR__, 2) . '/Config/Navigation.php';
$icon = static fn (string $key): string => $navConfig['icons'][$key] ?? $navConfig['icons']['boxes'];
$notifCount = (int) ($_SESSION['notifications_count'] ?? 0);

$visibleGroups = [];
foreach ($navConfig['groups'] as $group) {
    if (($group['visible'] ?? true) !== true) continue;
    $dept = (string) ($group['department'] ?? 'general');
    $allowed = $navigationRoleIsSystem || $navigationRoleDepartment === 'general' || $dept === 'general' || $dept === $navigationRoleDepartment || ($navigationRoleDepartment === 'bodega' && $dept === 'administracion');
    if (!$allowed) continue;
    $items = array_values(array_filter($group['items'], static function (array $item) use ($navigationAuth, $navigationRole, $navigationUserId) {
        if (($item['visible'] ?? true) !== true) return false;
        $perms = $item['permissions'] ?? [(string) ($item['permission'] ?? '')];
        foreach ($perms as $p) {
            if ($p !== '' && $navigationAuth->can($navigationRole, (string) $p, $navigationUserId)) return true;
        }
        return false;
    }));
    usort($items, static fn($a, $b) => (int)($a['order'] ?? 0) <=> (int)($b['order'] ?? 0));
    if ($items !== []) { $group['items'] = $items; $visibleGroups[] = $group; }
}
usort($visibleGroups, static fn($a, $b) => (int)($a['order'] ?? 0) <=> (int)($b['order'] ?? 0));
?>
<aside class="module-sidebar" data-navigation-sidebar>
    <div class="flex items-center gap-2 h-16 px-4 sidebar-brand">
        <div class="flex items-center justify-center h-9 w-9 rounded-lg dashboard-brand-icon"><?= $icon('plant') ?></div>
        <div class="min-w-0 leading-tight sidebar-brand-text">
            <p class="text-sm font-bold tracking-tight truncate"><?= htmlspecialchars($companyName, ENT_QUOTES, 'UTF-8') ?></p>
            <p class="text-[11px] truncate muted">Gestión Agrícola</p>
        </div>
    </div>

    <label class="navigation-search" aria-label="Buscar módulo"><span><?= $icon('search') ?></span><input type="search" data-navigation-search placeholder="Buscar módulo" autocomplete="off"></label>

    <nav class="dashboard-nav" aria-label="Navegación principal">
        <a class="dashboard-nav-item <?= ($currentModule === '' ? 'active' : '') ?>" href="./" data-navigation-item data-search-label="resumen">
            <span class="nav-icon"><?= $icon('home') ?></span>
            <span class="navigation-item-label">Resumen</span>
        </a>

        <?php foreach ($visibleGroups as $group):
            $groupId = htmlspecialchars($group['id'], ENT_QUOTES, 'UTF-8');
            $groupOpen = false;
            foreach ($group['items'] as $it) { if (($it['module'] ?? '') === $currentModule) { $groupOpen = true; break; } }
        ?>
            <section class="navigation-group <?= $groupOpen ? 'is-open' : '' ?>" data-navigation-group data-group-id="<?= $groupId ?>" data-search-label="<?= htmlspecialchars(strtolower($group['label'] . ' ' . ($group['description'] ?? '')), ENT_QUOTES, 'UTF-8') ?>">
                <button type="button" class="navigation-group-toggle" data-navigation-toggle aria-expanded="<?= $groupOpen ? 'true' : 'false' ?>" title="<?= htmlspecialchars($group['label'], ENT_QUOTES, 'UTF-8') ?>">
                    <span class="navigation-group-heading"><span class="navigation-group-icon"><?= $icon($group['icon'] ?? 'boxes') ?></span><span class="navigation-group-label"><?= htmlspecialchars($group['label'], ENT_QUOTES, 'UTF-8') ?></span></span>
                    <span class="navigation-chevron"><?= $icon('chevron') ?></span>
                </button>
                <div class="navigation-items" id="navigation-items-<?= $groupId ?>">
                    <?php foreach ($group['items'] as $item): $active = ($item['module'] ?? '') === $currentModule; $href = $item['route'] === '/' ? './' : $item['route']; ?>
                        <a class="dashboard-nav-item <?= $active ? 'active' : '' ?>" href="<?= htmlspecialchars($href, ENT_QUOTES, 'UTF-8') ?>" data-navigation-item data-search-label="<?= htmlspecialchars(strtolower($item['label'] . ' ' . $group['label']), ENT_QUOTES, 'UTF-8') ?>">
                            <span class="nav-icon"><?= $icon($item['icon'] ?? 'boxes') ?></span>
                            <span class="navigation-item-label"><?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endforeach; ?>

    </nav>

    <div class="p-2 border-t border-sidebar-border/80 shrink-0">
        <button class="w-full erp-topbar-toggle" type="button" data-navigation-collapse aria-label="Colapsar menú"><?= $icon('menu') ?> <span>Colapsar menú</span></button>
    </div>
</aside>

<header class="erp-topbar" data-navigation-topbar>
    <div class="erp-topbar-start">
        <button class="erp-topbar-toggle" type="button" data-navigation-collapse aria-label="Colapsar menú"><?= $icon('menu') ?></button>
        <div class="erp-topbar-context"><span class="muted">Gestión agrícola</span><strong><?= htmlspecialchars($companyName, ENT_QUOTES, 'UTF-8') ?></strong></div>
    </div>
    <label class="erp-global-search"><span><?= $icon('search') ?></span><input type="search" data-navigation-global-search placeholder="Buscar en el sistema" autocomplete="off"></label>
    <div class="erp-topbar-actions">
        <a class="erp-topbar-action" href="<?= htmlspecialchars(module_url('requests'), ENT_QUOTES, 'UTF-8') ?>" title="Solicitudes internas"><?= $icon('plus') ?></a>
        <a class="erp-topbar-action" href="<?= htmlspecialchars(module_url('notifications'), ENT_QUOTES, 'UTF-8') ?>" title="Notificaciones" aria-label="Notificaciones"><?= $icon('bell') ?><?php if ($notifCount > 0): ?><span class="navigation-badge"><?= $notifCount ?></span><?php endif; ?></a>
        <details class="erp-user-menu"><summary><span class="dashboard-avatar"><?= htmlspecialchars(strtoupper(substr($currentUserName, 0, 2)), ENT_QUOTES, 'UTF-8') ?></span><strong><?= htmlspecialchars($currentUserName, ENT_QUOTES, 'UTF-8') ?></strong></summary>
            <div class="erp-user-dropdown"><a href="<?= htmlspecialchars(module_url('profile'), ENT_QUOTES, 'UTF-8') ?>">Mi Perfil</a><a href="<?= htmlspecialchars(module_url('', ['logout' => 1]), ENT_QUOTES, 'UTF-8') ?>">Salir</a></div>
        </details>
    </div>
</header>
<script src="/assets/js/navigation.js" defer></script>
<script src="/assets/js/table-layout.js" defer></script>
