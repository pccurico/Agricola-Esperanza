<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

$setupRequired = !file_exists(dirname(__DIR__) . '/config/config.php');

if ($setupRequired) {
    $setup = (new CampoSur\Controllers\SetupController())->handle();
    extract($setup, EXTR_SKIP);
    require dirname(__DIR__) . '/app/Views/setup.php';
    exit;
}

(new CampoSur\Services\MigrationRunner(database()->connection(), dirname(__DIR__)))->run();

$auth = (new CampoSur\Controllers\AuthController())->handle();
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (empty($_SESSION['user_id'])) {
    extract($auth, EXTR_SKIP);
    require dirname(__DIR__) . '/app/Views/login.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
}

if (($_GET['asset'] ?? '') === 'logo') {
    $query = database()->connection()->prepare('SELECT logo_path FROM companies WHERE id = ? LIMIT 1');
    $query->execute([(int) $_SESSION['company_id']]);
    $relativePath = (string) $query->fetchColumn();
    $file = realpath(dirname(__DIR__) . '/' . $relativePath);
    $uploads = realpath(dirname(__DIR__) . '/storage/uploads');
    if (!$file || !$uploads || !str_starts_with($file, $uploads) || !is_file($file)) {
        http_response_code(404);
        exit;
    }
    $mime = mime_content_type($file);
    header('Content-Type: ' . $mime);
    header('Cache-Control: private, max-age=3600');
    readfile($file);
    exit;
}

$modulePermissions = ['users' => 'users.manage', 'masters' => 'masters.view', 'production' => 'production.view', 'profile' => 'dashboard.view', 'procurement' => 'procurement.view', 'budgets' => 'budgets.view', 'machinery' => 'machinery.view', 'costs' => 'costs.view', 'inventory' => 'inventory.view', 'reports' => 'reports.view', 'labor' => 'labor.view', 'settings' => 'setup.manage', 'audit' => 'reports.view', 'catalogs' => 'setup.manage'];
$module = (string) ($_GET['module'] ?? '');
if (isset($modulePermissions[$module])) {
    authorize($modulePermissions[$module]);
}
$createPermissions = ['masters' => 'masters.create', 'production' => 'production.create', 'procurement' => 'procurement.create', 'budgets' => 'budgets.create', 'machinery' => 'machinery.create', 'costs' => 'costs.create', 'inventory' => 'inventory.create', 'labor' => 'labor.create'];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($createPermissions[$module])) {
    authorize($createPermissions[$module]);
}

if ($module === 'catalogs') {
    $catalogs = (new CampoSur\Controllers\CatalogController())->handle();
    extract($catalogs, EXTR_SKIP);
    require dirname(__DIR__) . '/app/Views/catalogs.php';
    exit;
}

if ($module === 'machinery') {
    $machinery = (new CampoSur\Controllers\MachineryController())->handle();
    extract($machinery, EXTR_SKIP);
    require dirname(__DIR__) . '/app/Views/machinery.php';
    exit;
}

if ($module === 'budgets') {
    $budgets = (new CampoSur\Controllers\BudgetController())->handle();
    extract($budgets, EXTR_SKIP);
    require dirname(__DIR__) . '/app/Views/budgets.php';
    exit;
}

if ($module === 'production') {
    $production = (new CampoSur\Controllers\ProductionController())->handle();
    extract($production, EXTR_SKIP);
    require dirname(__DIR__) . '/app/Views/production.php';
    exit;
}

if ($module === 'profile') {
    $profile = (new CampoSur\Controllers\ProfileController())->handle();
    extract($profile, EXTR_SKIP);
    require dirname(__DIR__) . '/app/Views/profile.php';
    exit;
}

if ($module === 'procurement') {
    $procurement = (new CampoSur\Controllers\ProcurementController())->handle();
    extract($procurement, EXTR_SKIP);
    require dirname(__DIR__) . '/app/Views/procurement.php';
    exit;
}

if ($module === 'users') {
    $users = (new CampoSur\Controllers\UsersController())->handle();
    extract($users, EXTR_SKIP);
    require dirname(__DIR__) . '/app/Views/users.php';
    exit;
}

if (($_GET['module'] ?? '') === 'masters') {
    $masters = (new CampoSur\Controllers\MastersController())->handle();
    extract($masters, EXTR_SKIP);
    require dirname(__DIR__) . '/app/Views/masters.php';
    exit;
}

if (($_GET['module'] ?? '') === 'costs') {
    $costs = (new CampoSur\Controllers\CostsController())->handle();
    extract($costs, EXTR_SKIP);
    require dirname(__DIR__) . '/app/Views/costs.php';
    exit;
}

if (($_GET['module'] ?? '') === 'inventory') {
    $inventory = (new CampoSur\Controllers\InventoryController())->handle();
    extract($inventory, EXTR_SKIP);
    require dirname(__DIR__) . '/app/Views/inventory.php';
    exit;
}

if (($_GET['module'] ?? '') === 'reports') {
    $report = (new CampoSur\Controllers\ReportsController())->handle();
    extract($report, EXTR_SKIP);
    require dirname(__DIR__) . '/app/Views/reports.php';
    exit;
}

if (($_GET['module'] ?? '') === 'labor') {
    $labor = (new CampoSur\Controllers\LaborController())->handle();
    extract($labor, EXTR_SKIP);
    require dirname(__DIR__) . '/app/Views/labor.php';
    exit;
}

if (($_GET['module'] ?? '') === 'settings') {
    $settings = (new CampoSur\Controllers\SettingsController())->handle();
    extract($settings, EXTR_SKIP);
    require dirname(__DIR__) . '/app/Views/settings.php';
    exit;
}

if (($_GET['module'] ?? '') === 'audit') {
    $audit = (new CampoSur\Controllers\AuditController())->handle();
    extract($audit, EXTR_SKIP);
    require dirname(__DIR__) . '/app/Views/audit.php';
    exit;
}

require dirname(__DIR__) . '/app/Views/dashboard.php';
