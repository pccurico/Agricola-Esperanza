<?php

declare(strict_types=1);

use CampoSur\Core\Database;

require_once __DIR__ . '/Core/Database.php';
require_once __DIR__ . '/Services/InputNormalizer.php';
require_once __DIR__ . '/Services/Installer.php';
require_once __DIR__ . '/Services/InstallationStatus.php';
require_once __DIR__ . '/Controllers/SetupController.php';
require_once __DIR__ . '/Services/Auth.php';
require_once __DIR__ . '/Controllers/AuthController.php';
require_once __DIR__ . '/Services/UserManagement.php';
require_once __DIR__ . '/Controllers/UsersController.php';
require_once __DIR__ . '/Services/MasterData.php';
require_once __DIR__ . '/Controllers/MastersController.php';
require_once __DIR__ . '/Services/CostManagement.php';
require_once __DIR__ . '/Controllers/CostsController.php';
require_once __DIR__ . '/Services/InventoryManagement.php';
require_once __DIR__ . '/Controllers/InventoryController.php';
require_once __DIR__ . '/Services/ReportService.php';
require_once __DIR__ . '/Controllers/ReportsController.php';
require_once __DIR__ . '/Services/LaborManagement.php';
require_once __DIR__ . '/Controllers/LaborController.php';
require_once __DIR__ . '/Services/CompanySettings.php';
require_once __DIR__ . '/Controllers/SettingsController.php';
require_once __DIR__ . '/Services/AuditLog.php';
require_once __DIR__ . '/Controllers/AuditController.php';
require_once __DIR__ . '/Services/DashboardService.php';
require_once __DIR__ . '/Services/ProductionManagement.php';
require_once __DIR__ . '/Controllers/ProductionController.php';
require_once __DIR__ . '/Services/MigrationRunner.php';
require_once __DIR__ . '/Services/ProfileService.php';
require_once __DIR__ . '/Controllers/ProfileController.php';
require_once __DIR__ . '/Services/ProcurementManagement.php';
require_once __DIR__ . '/Controllers/ProcurementController.php';
require_once __DIR__ . '/Services/BudgetManagement.php';
require_once __DIR__ . '/Controllers/BudgetController.php';
require_once __DIR__ . '/Services/MachineryManagement.php';
require_once __DIR__ . '/Controllers/MachineryController.php';
require_once __DIR__ . '/Services/CatalogManagement.php';
require_once __DIR__ . '/Services/CatalogLookup.php';
require_once __DIR__ . '/Controllers/CatalogController.php';
require_once __DIR__ . '/Services/WarehouseManagement.php';
require_once __DIR__ . '/Controllers/WarehouseController.php';
require_once __DIR__ . '/Services/InternalRequestManagement.php';
require_once __DIR__ . '/Controllers/InternalRequestController.php';
require_once __DIR__ . '/Services/NotificationManagement.php';
require_once __DIR__ . '/Controllers/NotificationController.php';
require_once __DIR__ . '/Services/TaskCalendarManagement.php';
require_once __DIR__ . '/Controllers/TaskCalendarController.php';
require_once __DIR__ . '/Services/DocumentManagement.php';
require_once __DIR__ . '/Controllers/DocumentController.php';
require_once __DIR__ . '/Services/ApiTokenManagement.php';
require_once __DIR__ . '/Controllers/ApiTokenController.php';
require_once __DIR__ . '/Services/ApiAuthenticator.php';
require_once __DIR__ . '/Controllers/ApiController.php';

$configPath = dirname(__DIR__) . '/config/config.php';
$config = file_exists($configPath)
    ? require $configPath
    : require dirname(__DIR__) . '/config/config.example.php';

date_default_timezone_set($config['app']['timezone']);
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
if (($config['app']['environment'] ?? 'production') === 'production') {
    ini_set('session.cookie_secure', '1');
}
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');

function app_config(string $key, mixed $default = null): mixed
{
    global $config;

    $value = $config;
    foreach (explode('.', $key) as $segment) {
        if (!is_array($value) || !array_key_exists($segment, $value)) {
            return $default;
        }
        $value = $value[$segment];
    }

    return $value;
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name((string) ($config['security']['session_name'] ?? 'camposur_session'));
}

function database(): Database
{
    static $database;

    return $database ??= new Database(app_config('database'));
}

function csrf_token(): string
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    return $_SESSION['csrf_token'] ??= bin2hex(random_bytes(32));
}

function verify_csrf(): void
{
    if (!hash_equals(csrf_token(), (string) ($_POST['csrf'] ?? ''))) {
        http_response_code(419);
        exit('Solicitud no válida.');
    }
}

function authorize(string $permission): void
{
    if (!(new \CampoSur\Services\Auth(database()->connection()))->can((int) $_SESSION['role_id'], $permission)) {
        http_response_code(403);
        exit('No tienes permisos para acceder a este módulo.');
    }
}
