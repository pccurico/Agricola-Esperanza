<?php

declare(strict_types=1);

use CampoSur\Core\Database;

require_once __DIR__ . '/Core/Database.php';
require_once __DIR__ . '/Services/Installer.php';
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

$configPath = dirname(__DIR__) . '/config/config.php';
$config = file_exists($configPath)
    ? require $configPath
    : require dirname(__DIR__) . '/config/config.example.php';

date_default_timezone_set($config['app']['timezone']);

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
