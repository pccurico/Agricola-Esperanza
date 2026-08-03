<?php

declare(strict_types=1);

$currentModule = (string) ($_GET['module'] ?? '');
$navigationAuth = new \CampoSur\Services\Auth(database()->connection());
$navigationRole = (int) ($_SESSION['role_id'] ?? 0);
$navigationCompanyQuery = database()->connection()->prepare('SELECT trade_name, logo_path FROM companies WHERE id = ? LIMIT 1');
$navigationCompanyQuery->execute([(int) ($_SESSION['company_id'] ?? 0)]);
$navigationCompanyData = $navigationCompanyQuery->fetch() ?: [];
$navigationCompany = (string) ($navigationCompanyData['trade_name'] ?? 'Empresa activa');
$navigationLogo = !empty($navigationCompanyData['logo_path']);
$navigationUser = (string) ($_SESSION['user_name'] ?? 'Administrador');
$navigationConfig = require dirname(__DIR__, 2) . '/Config/Navigation.php';
$navigationIcon = static fn (string $icon): string => $navigationConfig['icons'][$icon] ?? $navigationConfig['icons']['boxes'];
$navigationVisibleGroups = [];
foreach ($navigationConfig['groups'] as $group) {
    if (($group['visible'] ?? true) !== true) {
        continue;
    }
    $visibleItems = array_values(array_filter($group['items'], fn (array $item): bool => ($item['visible'] ?? true) === true && $navigationAuth->can($navigationRole, $item['permission'])));
    usort($visibleItems, static fn (array $left, array $right): int => (int) ($left['order'] ?? 0) <=> (int) ($right['order'] ?? 0));
    if ($visibleItems !== []) {
        $group['items'] = $visibleItems;
        $navigationVisibleGroups[] = $group;
    }
}
$currentGroups = [];
foreach ($navigationVisibleGroups as $group) {
    foreach ($group['items'] as $item) {
        if (($item['module'] ?? '') === $currentModule) {
            $currentGroups[$group['id']] = true;
        }
    }
}
?>
<aside class="module-sidebar" data-navigation-sidebar>
    <header class="sidebar-brand">
        <div class="dashboard-brand"><?php if ($navigationLogo): ?><img class="dashboard-brand-logo" src="?asset=logo" alt="Logo de <?= htmlspecialchars($navigationCompany, ENT_QUOTES, 'UTF-8') ?>"><?php else: ?><span class="dashboard-brand-mark">✦</span><?php endif; ?></div>
    </header>
    <p class="dashboard-workspace">Empresa activa</p>
    <div class="farm-select"><b><?= htmlspecialchars($navigationCompany, ENT_QUOTES, 'UTF-8') ?></b></div>
    <label class="navigation-search"><span><?= $navigationIcon('search') ?></span><input type="search" placeholder="Buscar módulo" aria-label="Buscar módulo" data-navigation-search autocomplete="off"></label>
    <nav class="dashboard-nav" aria-label="Navegación principal">
        <?php usort($navigationVisibleGroups, static fn (array $left, array $right): int => (int) ($left['order'] ?? 0) <=> (int) ($right['order'] ?? 0)); ?>
        <?php foreach ($navigationVisibleGroups as $group): $groupOpen = isset($currentGroups[$group['id']]); ?>
            <section class="navigation-group <?= $groupOpen ? 'is-open' : '' ?>" data-navigation-group data-group-id="<?= htmlspecialchars($group['id'], ENT_QUOTES, 'UTF-8') ?>" data-search-label="<?= htmlspecialchars(strtolower($group['label'] . ' ' . ($group['description'] ?? '')), ENT_QUOTES, 'UTF-8') ?>">
                <button class="navigation-group-toggle" type="button" aria-expanded="<?= $groupOpen ? 'true' : 'false' ?>" aria-controls="navigation-items-<?= htmlspecialchars($group['id'], ENT_QUOTES, 'UTF-8') ?>" data-navigation-toggle title="<?= htmlspecialchars((string) ($group['description'] ?? $group['label']), ENT_QUOTES, 'UTF-8') ?>">
                    <span class="navigation-group-heading"><span class="navigation-group-icon"><?= $navigationIcon($group['icon']) ?></span><span class="navigation-group-label"><?= htmlspecialchars($group['label'], ENT_QUOTES, 'UTF-8') ?></span><?php if (!empty($group['badge'])): ?><span class="navigation-badge"><?= htmlspecialchars((string) $group['badge'], ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?></span><span class="navigation-chevron"><?= $navigationIcon('chevron') ?></span>
                </button>
                <div class="navigation-items" id="navigation-items-<?= htmlspecialchars($group['id'], ENT_QUOTES, 'UTF-8') ?>">
                    <?php foreach ($group['items'] as $item): $active = ($item['module'] ?? '') === $currentModule; $href = $item['route'] === '/' ? './' : $item['route']; ?>
                        <a class="dashboard-nav-item <?= $active ? 'active' : '' ?>" href="<?= htmlspecialchars($href, ENT_QUOTES, 'UTF-8') ?>" data-navigation-item data-search-label="<?= htmlspecialchars(strtolower($item['label'] . ' ' . $group['label']), ENT_QUOTES, 'UTF-8') ?>" title="<?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?>">
                            <span class="nav-icon"><?= $navigationIcon($item['icon']) ?></span><span class="navigation-item-label"><?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?></span><?php if (!empty($item['badge'])): ?><span class="navigation-badge"><?= htmlspecialchars((string) $item['badge'], ENT_QUOTES, 'UTF-8') ?></span><?php elseif (isset($item['count'])): ?><span class="navigation-count"><?= (int) $item['count'] ?></span><?php endif; ?><span class="navigation-favorite" role="button" tabindex="0" aria-label="Marcar <?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?> como favorito" data-navigation-favorite data-favorite-id="<?= htmlspecialchars($item['id'], ENT_QUOTES, 'UTF-8') ?>"><?= $navigationIcon('star') ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endforeach; ?>
    </nav>
</aside>
<header class="erp-topbar" data-navigation-topbar>
    <div class="erp-topbar-start"><button class="erp-topbar-toggle" type="button" data-navigation-collapse aria-label="Colapsar menú" title="Mostrar u ocultar menú"><?= $navigationIcon('menu') ?></button><div class="erp-topbar-context"><span>Gestión agrícola</span><strong><?= htmlspecialchars($navigationCompany, ENT_QUOTES, 'UTF-8') ?></strong></div></div>
    <label class="erp-global-search"><span><?= $navigationIcon('search') ?></span><input type="search" placeholder="Buscar en el sistema" aria-label="Buscar en el sistema" data-navigation-global-search autocomplete="off"></label>
    <div class="erp-topbar-actions"><a class="erp-topbar-action" href="?module=requests" title="Solicitudes internas">+</a><a class="erp-topbar-action" href="?module=notifications" title="Notificaciones" aria-label="Notificaciones">●</a><details class="erp-user-menu"><summary><span class="dashboard-avatar"><?= htmlspecialchars(strtoupper(substr($navigationUser, 0, 2)), ENT_QUOTES, 'UTF-8') ?></span><strong><?= htmlspecialchars($navigationUser, ENT_QUOTES, 'UTF-8') ?></strong></summary><div class="erp-user-dropdown"><a href="?module=profile">Mi Perfil</a><a href="?logout=1">Salir</a></div></details></div>
</header>
<script src="assets/js/navigation.js" defer></script>
<script src="assets/js/table-layout.js" defer></script>
