<?php

declare(strict_types=1);

use AgroPCC\Core\Database;

require_once dirname(__DIR__) . '/vendor/autoload.php';
if (!headers_sent()) {
    ob_start();
}

$configPath = dirname(__DIR__) . '/config/config.php';
$config = file_exists($configPath)
    ? require $configPath
    : require dirname(__DIR__) . '/config/config.example.php';

date_default_timezone_set($config['app']['timezone']);
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
if (($config['app']['environment'] ?? 'production') === 'production') {
    $isSecureRequest = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https')
        || (isset($_SERVER['HTTP_X_FORWARDED_SSL']) && strtolower($_SERVER['HTTP_X_FORWARDED_SSL']) === 'on');

    if ($isSecureRequest) {
        ini_set('session.cookie_secure', '1');
    }
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name((string) ($config['security']['session_name'] ?? 'pccurico_session'));
    session_start();
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
    session_start();
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

function module_url(string $module, array $params = []): string
{
    $path = $module === '' || $module === '/' ? '/' : '/' . ltrim($module, '/');
    if ($params === []) {
        return $path;
    }
    return $path . '?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
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
    $roleId = (int) ($_SESSION['role_id'] ?? 0);
    $userId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
    if (!(new \AgroPCC\Services\Auth(database()->connection()))->can($roleId, $permission, $userId)) {
        http_response_code(403);
        exit('No tienes permisos para acceder a este módulo.');
    }
}

function authorize_any(array $permissions): void
{
    $auth = new \AgroPCC\Services\Auth(database()->connection());
    $roleId = (int) ($_SESSION['role_id'] ?? 0);
    $userId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
    foreach ($permissions as $permission) {
        if ($auth->can($roleId, $permission, $userId)) {
            return;
        }
    }
    http_response_code(403);
    exit('No tienes permisos para acceder a este módulo.');
}
