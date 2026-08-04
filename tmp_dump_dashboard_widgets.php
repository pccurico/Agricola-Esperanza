<?php
require 'app/bootstrap.php';
session_start();
$_SESSION['company_id'] = 1;
$_SESSION['user_id'] = 1;
$_SESSION['role_id'] = 1;
$_SESSION['role_is_system'] = 1;
$_SESSION['role_department'] = 'gerencia';
$dashboard = (new CampoSur\Controllers\DashboardController())->handle();
$widgets = $dashboard['dashboard']['widgets'] ?? [];
$payload = [
    'types' => array_map(static fn($widget) => $widget['type'] ?? null, $widgets),
    'titles' => array_map(static fn($widget) => $widget['title'] ?? null, $widgets),
    'count' => count($widgets),
];
print_r($payload);
