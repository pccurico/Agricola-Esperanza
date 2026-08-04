<?php

require_once __DIR__ . '/app/bootstrap.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Use likely existing demo/admin IDs from backup
$_SESSION['company_id'] = 1;
$_SESSION['user_id'] = 1;
$_SESSION['role_id'] = 1;
$_SESSION['role_department'] = 'general';

$controller = new \CampoSur\Controllers\DashboardController();
$data = $controller->data();
echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
