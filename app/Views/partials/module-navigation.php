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
$navigationGroups = [
    'Operación' => [
        ['masters', 'masters.view', '◫', 'Administración'],
        ['procurement', 'procurement.view', '▥', 'Compras'],
        ['receptions', 'procurement.receive', '⇩', 'Recepciones'],
        ['production', 'production.view', '◉', 'Producción'],
        ['labor', 'labor.view', '♟', 'Mano de obra'],
        ['costs', 'costs.view', '◒', 'Costos'],
        ['budgets', 'budgets.view', '▥', 'Presupuestos'],
        ['inventory', 'inventory.view', '▣', 'Inventario'],
        ['warehouses', 'warehouse.view', '⌂', 'Bodegas y lotes'],
        ['requests', 'requests.view', '≡', 'Solicitudes internas'],
        ['notifications', 'notifications.view', '◉', 'Notificaciones'],
        ['planning', 'tasks.view', '▣', 'Tareas y calendario'],
        ['documents', 'documents.view', '▤', 'Documentos'],
        ['api', 'api_tokens.manage', '↔', 'API e integraciones'],
        ['machinery', 'machinery.view', '⚙', 'Maquinaria'],
    ],
    'Gestión' => [
        ['reports', 'reports.view', '◌', 'Informes'],
        ['users', 'users.view', '♙', 'Usuarios y roles'],
        ['settings', 'setup.manage', '⚙', 'Configuración'],
        ['catalogs', 'setup.manage', '◫', 'Catálogos'],
        ['profile', 'dashboard.view', '◉', 'Mi perfil'],
        ['audit', 'reports.view', '◷', 'Actividad'],
    ],
    'Herramientas' => [
        ['demo', 'demo.manage', '▣', 'Demo Data Manager'],
    ],
];
?>
<aside class="dashboard-sidebar module-sidebar">
    <div class="dashboard-brand"><?php if ($navigationLogo): ?><img class="dashboard-brand-logo" src="?asset=logo" alt="Logo de <?= htmlspecialchars($navigationCompany, ENT_QUOTES, 'UTF-8') ?>"><?php else: ?><span class="dashboard-brand-mark">✦</span><?php endif; ?></div>
    <p class="dashboard-workspace">Empresa activa</p>
    <div class="farm-select"><b><?= htmlspecialchars($navigationCompany, ENT_QUOTES, 'UTF-8') ?></b><span>⌄</span></div>
    <nav class="dashboard-nav">
        <a class="dashboard-nav-item <?= $currentModule === '' ? 'active' : '' ?>" href="/"><span class="nav-icon">▦</span><span>Resumen ejecutivo</span></a>
        <?php foreach ($navigationGroups as $groupName => $items): ?>
            <p class="dashboard-nav-label"><?= htmlspecialchars($groupName, ENT_QUOTES, 'UTF-8') ?></p>
            <?php foreach ($items as [$moduleName, $permission, $icon, $label]): ?>
                <?php if ($navigationAuth->can($navigationRole, $permission)): ?>
                    <a class="dashboard-nav-item <?= $currentModule === $moduleName ? 'active' : '' ?>" href="?module=<?= urlencode($moduleName) ?>"><span class="nav-icon"><?= $icon ?></span><span><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></span></a>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </nav>
    <div class="dashboard-user"><span class="dashboard-avatar"><?= htmlspecialchars(strtoupper(substr($navigationUser, 0, 2)), ENT_QUOTES, 'UTF-8') ?></span><div><strong><?= htmlspecialchars($navigationUser, ENT_QUOTES, 'UTF-8') ?></strong><a href="?logout=1">Salir</a></div></div>
</aside>
