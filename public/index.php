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

$modulePermissions = ['users' => 'users.manage', 'masters' => 'masters.manage', 'costs' => 'costs.manage', 'inventory' => 'inventory.manage', 'reports' => 'reports.view', 'labor' => 'labor.manage', 'settings' => 'setup.manage', 'audit' => 'reports.view'];
$module = (string) ($_GET['module'] ?? '');
if (isset($modulePermissions[$module])) {
    authorize($modulePermissions[$module]);
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
