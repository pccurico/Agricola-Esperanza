<?php

declare(strict_types=1);

$dashboard = (new CampoSur\Services\DashboardService(database()->connection(), (int) $_SESSION['company_id']))->summary();
$companyName = $dashboard['company']['trade_name'] ?: 'Empresa activa';
$totalCost = number_format((float) $dashboard['totals']['total_cost'], 0, ',', '.');
$hectares = number_format((float) $dashboard['totals']['hectares'], 2, ',', '.');
$movements = number_format((int) $dashboard['totals']['movements'], 0, ',', '.');
$currentUser = $_SESSION['user_name'] ?? 'Administrador';
require dirname(__DIR__, 2) . '/index.php';
