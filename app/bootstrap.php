<?php

declare(strict_types=1);

use CampoSur\Core\Database;

require_once dirname(__DIR__) . '/vendor/autoload.php';

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
    session_name((string) ($config['security']['session_name'] ?? 'pccurico_session'));
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

function authorize_any(array $permissions): void
{
    $auth = new \CampoSur\Services\Auth(database()->connection());
    foreach ($permissions as $permission) {
        if ($auth->can((int) $_SESSION['role_id'], $permission)) {
            return;
        }
    }
    http_response_code(403);
    exit('No tienes permisos para acceder a este módulo.');
}
