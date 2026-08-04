<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$configPath = dirname(__DIR__) . '/config/config.php';
$setupRequired = !file_exists($configPath);

if (!$setupRequired) {
    try {
        $setupRequired = !(new AgroPCC\Services\InstallationStatus(database()->connection()))->isComplete();
    } catch (\Throwable) {
        $setupRequired = true;
    }
}

if (!$setupRequired && isset($_GET['api'])) {
    $headers = function_exists('getallheaders') ? getallheaders() : [];
    $authorization = (string) ($headers['Authorization'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? '');
    $identity = (new AgroPCC\Services\ApiAuthenticator(database()->connection()))->authenticate($authorization);
    if (!$identity) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(401);
        echo json_encode(['error' => 'Credenciales API inválidas'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    (new AgroPCC\Controllers\ApiController())->handle($identity);
    exit;
}

if ($setupRequired) {
    $setup = (new AgroPCC\Controllers\SetupController())->handle();
    extract($setup, EXTR_SKIP);
    require dirname(__DIR__) . '/app/Views/setup.php';
    exit;
}

(new AgroPCC\Services\MigrationRunner(database()->connection(), dirname(__DIR__)))->run();

if (($_GET['asset'] ?? '') === 'logo') {
    $companyId = (int) ($_SESSION['company_id'] ?? 0);
    if ($companyId > 0) {
        $query = database()->connection()->prepare('SELECT logo_path FROM companies WHERE id = ? LIMIT 1');
        $query->execute([$companyId]);
    } else {
        $query = database()->connection()->query('SELECT logo_path FROM companies WHERE active = 1 ORDER BY id LIMIT 1');
    }
    $relativePath = (string) $query->fetchColumn();
    $file = realpath(dirname(__DIR__) . '/' . $relativePath);
    $uploads = realpath(dirname(__DIR__) . '/storage/uploads');
    if (!$file || !$uploads || !str_starts_with($file, $uploads) || !is_file($file)) {
        http_response_code(404);
        exit;
    }
    header('Content-Type: ' . mime_content_type($file));
    header('Cache-Control: private, max-age=3600');
    readfile($file);
    exit;
}

$auth = (new AgroPCC\Controllers\AuthController())->handle();
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

if (($_GET['asset'] ?? '') === 'attachment') {
    authorize('documents.view');
    $attachment = (new AgroPCC\Services\DocumentManagement(database()->connection(), (int) $_SESSION['company_id'], dirname(__DIR__)))->attachment((int) ($_GET['id'] ?? 0));
    if (!$attachment) {
        http_response_code(404);
        exit;
    }
    header('Content-Type: ' . $attachment['mime_type']);
    header('Content-Disposition: attachment; filename="' . str_replace('"', '', $attachment['original_name']) . '"');
    readfile($attachment['path']);
    exit;
}

$modulePermissions = ['users' => 'users.view', 'roles' => 'roles.manage', 'role' => 'roles.manage', 'masters' => 'masters.view', 'production' => 'production.view', 'profile' => 'dashboard.view', 'procurement' => 'procurement.view', 'budgets' => 'budgets.view', 'machinery' => 'machinery.view', 'costs' => 'costs.view', 'inventory' => 'inventory.view', 'reports' => 'reports.view', 'labor' => 'labor.view', 'settings' => 'setup.manage', 'audit' => 'reports.view', 'catalogs' => 'setup.manage', 'receptions' => 'procurement.receive', 'warehouses' => 'warehouse.view', 'requests' => 'requests.view', 'notifications' => 'notifications.view', 'planning' => 'tasks.view', 'documents' => 'documents.view', 'api' => 'api_tokens.manage', 'demo' => 'demo.manage', 'tools' => 'setup.manage', 'dashboard_data' => 'dashboard.view'];
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$scriptDir = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
$path = parse_url($requestUri, PHP_URL_PATH) ?: '';
if ($scriptDir !== '' && $scriptDir !== '/') {
    $path = preg_replace('#^' . preg_quote($scriptDir, '#') . '#', '', $path);
}
$path = trim($path, '/');
if ($path === 'index.php') {
    $path = '';
}
$module = $path !== '' ? $path : (string) ($_GET['module'] ?? '');
if ($module === 'users') {
    authorize_any(['users.view', 'users.manage']);
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        match ($_POST['action'] ?? '') {
            'create_user', 'update_user', 'delete_user', 'toggle_user' => authorize('users.manage'),
            default => null,
        };
    }
} elseif ($module === 'roles' || $module === 'role') {
    authorize_any(['roles.manage', 'users.manage']);
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        match ($_POST['action'] ?? '') {
            'create_role', 'update_role', 'delete_role' => authorize('roles.manage'),
            default => null,
        };
    }
} elseif ($module === 'planning') {
    authorize_any(['tasks.view', 'calendar.view']);
} elseif (isset($modulePermissions[$module])) {
    authorize($modulePermissions[$module]);
}
if ($module === 'reports' && isset($_GET['export'])) {
    authorize('reports.export');
}
$createPermissions = ['masters' => 'masters.create', 'production' => 'production.create', 'procurement' => 'procurement.create', 'budgets' => 'budgets.create', 'machinery' => 'machinery.create', 'costs' => 'costs.create', 'inventory' => 'inventory.create', 'labor' => 'labor.create'];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($createPermissions[$module])) {
    authorize($createPermissions[$module]);
}
if (in_array($module, ['procurement', 'receptions'], true) && $_SERVER['REQUEST_METHOD'] === 'POST' && in_array(($_POST['action'] ?? ''), ['receive_order', 'update_reception', 'delete_reception'], true)) {
    authorize('procurement.receive');
}
if ($module === 'procurement' && $_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create_invoice') {
    authorize('purchase_invoices.create');
}

if ($module === 'receptions') {
    $receptions = (new AgroPCC\Controllers\ProcurementController())->handle();
    extract($receptions, EXTR_SKIP);
    require dirname(__DIR__) . '/app/Views/purchase_receptions.php';
    exit;
}

if ($module === 'notifications') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        authorize('notifications.update');
    }
    $notificationsData = (new AgroPCC\Controllers\NotificationController())->handle();
    extract($notificationsData, EXTR_SKIP);
    require dirname(__DIR__) . '/app/Views/notifications.php';
    exit;
}

if ($module === 'planning') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        authorize(($_POST['action'] ?? '') === 'create_event' ? 'calendar.create' : (($_POST['action'] ?? '') === 'update_task' ? 'tasks.update' : 'tasks.create'));
    }
    $planning = (new AgroPCC\Controllers\TaskCalendarController())->handle();
    extract($planning, EXTR_SKIP);
    require dirname(__DIR__) . '/app/Views/tasks_calendar.php';
    exit;
}

if ($module === 'documents') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        authorize('documents.create');
    }
    $documents = (new AgroPCC\Controllers\DocumentController())->handle();
    extract($documents, EXTR_SKIP);
    require dirname(__DIR__) . '/app/Views/documents.php';
    exit;
}

if ($module === 'tools') {
    authorize('setup.manage');
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        authorize('setup.manage');
    }
    $tools = (new AgroPCC\Controllers\ToolsController())->handle();
    extract($tools, EXTR_SKIP);
    require dirname(__DIR__) . '/app/Views/tools.php';
    exit;
}

if ($module === 'demo') {
    authorize('demo.manage');
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        authorize('demo.manage');
    }
    $demo = (new AgroPCC\Controllers\DemoDataController())->handle();
    extract($demo, EXTR_SKIP);
    require dirname(__DIR__) . '/app/Views/demo_data_manager.php';
    exit;
}

if ($module === 'api') {
    authorize('api_tokens.manage');
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        authorize('api_tokens.manage');
    }
    $api = (new AgroPCC\Controllers\ApiTokenController())->handle();
    extract($api, EXTR_SKIP);
    require dirname(__DIR__) . '/app/Views/api_tokens.php';
    exit;
}

if ($module === 'requests') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $actionPermission = match ($_POST['action'] ?? '') {
            'approve_request' => 'requests.approve',
            'fulfill_request' => 'requests.fulfill',
            default => 'requests.create',
        };
        authorize($actionPermission);
    }
    $requests = (new AgroPCC\Controllers\InternalRequestController())->handle();
    extract($requests, EXTR_SKIP);
    require dirname(__DIR__) . '/app/Views/internal_requests.php';
    exit;
}

if ($module === 'warehouses') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $actionPermission = match ($_POST['action'] ?? '') {
            'approve_transfer' => 'transfer.approve',
            'create_transfer' => 'transfer.create',
            'create_lot' => 'lot.create',
            default => 'warehouse.create',
        };
        authorize($actionPermission);
    }
    $warehouses = (new AgroPCC\Controllers\WarehouseController())->handle();
    extract($warehouses, EXTR_SKIP);
    require dirname(__DIR__) . '/app/Views/warehouses.php';
    exit;
}

if ($module === 'catalogs') {
    $catalogsData = (new AgroPCC\Controllers\CatalogController())->handle();
    extract($catalogsData, EXTR_SKIP);
    require dirname(__DIR__) . '/app/Views/catalogs.php';
    exit;
}

if ($module === 'machinery') {
    $machinery = (new AgroPCC\Controllers\MachineryController())->handle();
    extract($machinery, EXTR_SKIP);
    require dirname(__DIR__) . '/app/Views/machinery.php';
    exit;
}

if ($module === 'budgets') {
    $budgets = (new AgroPCC\Controllers\BudgetController())->handle();
    extract($budgets, EXTR_SKIP);
    require dirname(__DIR__) . '/app/Views/budgets.php';
    exit;
}

if ($module === 'production') {
    $production = (new AgroPCC\Controllers\ProductionController())->handle();
    extract($production, EXTR_SKIP);
    require dirname(__DIR__) . '/app/Views/production.php';
    exit;
}

if ($module === 'profile') {
    $profile = (new AgroPCC\Controllers\ProfileController())->handle();
    extract($profile, EXTR_SKIP);
    require dirname(__DIR__) . '/app/Views/profile.php';
    exit;
}

if ($module === 'procurement') {
    $procurement = (new AgroPCC\Controllers\ProcurementController())->handle();
    extract($procurement, EXTR_SKIP);
    require dirname(__DIR__) . '/app/Views/procurement.php';
    exit;
}

if ($module === 'users') {
    $usersData = (new AgroPCC\Controllers\UsersController())->handle();
    extract($usersData, EXTR_SKIP);
    require dirname(__DIR__) . '/app/Views/users.php';
    exit;
}

if ($module === 'roles' || $module === 'role') {
    $rolesData = (new AgroPCC\Controllers\RolesController())->handle();
    extract($rolesData, EXTR_SKIP);
    require dirname(__DIR__) . '/app/Views/roles.php';
    exit;
}

if ($module === 'masters') {
    $masters = (new AgroPCC\Controllers\MastersController())->handle();
    extract($masters, EXTR_SKIP);
    require dirname(__DIR__) . '/app/Views/masters.php';
    exit;
}

if ($module === 'costs') {
    $costs = (new AgroPCC\Controllers\CostsController())->handle();
    extract($costs, EXTR_SKIP);
    require dirname(__DIR__) . '/app/Views/costs.php';
    exit;
}

if ($module === 'inventory') {
    $inventory = (new AgroPCC\Controllers\InventoryController())->handle();
    extract($inventory, EXTR_SKIP);
    require dirname(__DIR__) . '/app/Views/inventory.php';
    exit;
}

if ($module === 'reports') {
    $report = (new AgroPCC\Controllers\ReportsController())->handle();
    extract($report, EXTR_SKIP);
    require dirname(__DIR__) . '/app/Views/reports.php';
    exit;
}

if ($module === 'dashboard_data') {
    authorize('dashboard.view');
    $dashboardData = (new AgroPCC\Controllers\DashboardController())->data();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($dashboardData, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($module === 'labor') {
    $labor = (new AgroPCC\Controllers\LaborController())->handle();
    extract($labor, EXTR_SKIP);
    if (($view_name ?? '') === 'worker-form') {
        require dirname(__DIR__) . '/app/Views/labor_worker_form.php';
        exit;
    }
    require dirname(__DIR__) . '/app/Views/labor.php';
    exit;
}

if ($module === 'settings') {
    $settings = (new AgroPCC\Controllers\SettingsController())->handle();
    extract($settings, EXTR_SKIP);
    require dirname(__DIR__) . '/app/Views/settings.php';
    exit;
}

if ($module === 'audit') {
    $audit = (new AgroPCC\Controllers\AuditController())->handle();
    extract($audit, EXTR_SKIP);
    require dirname(__DIR__) . '/app/Views/audit.php';
    exit;
}

$dashboardResponse = (new AgroPCC\Controllers\DashboardController())->handle();
$dashboard = $dashboardResponse['dashboard'] ?? [];
$error = $dashboardResponse['error'] ?? null;
$success = $dashboardResponse['success'] ?? null;
require dirname(__DIR__) . '/app/Views/dashboard.php';
