<?php

declare(strict_types=1);

$dashboard = (new CampoSur\Services\DashboardService(database()->connection(), (int) $_SESSION['company_id']))->summary();
$company = $dashboard['company'] ?? [];
$totals = $dashboard['totals'] ?? [];
$recent = $dashboard['recent'] ?? [];
$metrics = $dashboard['metrics'] ?? [];
$operational = $dashboard['operational'] ?? [];
$costSeries = $dashboard['cost_series'] ?? [];
$productionSeries = $dashboard['production_series'] ?? [];
$inventoryAlerts = $dashboard['inventory_alerts'] ?? [];
$companyName = (string) ($company['trade_name'] ?? 'Empresa activa');
$totalCost = number_format((float) ($totals['total_cost'] ?? 0), 0, ',', '.');
$hectares = number_format((float) ($totals['hectares'] ?? 0), 2, ',', '.');
$movements = number_format((int) ($totals['movements'] ?? 0), 0, ',', '.');
$production = number_format((float) ($metrics['production'] ?? 0), 0, ',', '.');
$maxCost = max(1, ...array_map(static fn (array $row): float => (float) $row['value'], $costSeries));
$maxProduction = max(1, ...array_map(static fn (array $row): float => (float) $row['value'], $productionSeries));
$userName = (string) ($_SESSION['user_name'] ?? 'Administrador');
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Panel ejecutivo | <?= htmlspecialchars($companyName, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="assets/css/app.css">
</head>
<body class="dashboard-page">
    <main class="dashboard-shell">
        <?php require dirname(__DIR__) . '/Views/partials/module-navigation.php'; ?>
        <section class="dashboard-content">
            <header class="dashboard-topbar"><p class="dashboard-crumb"><?= htmlspecialchars($companyName, ENT_QUOTES, 'UTF-8') ?> <span>/</span> <strong>Panel ejecutivo</strong></p><div class="dashboard-actions"><a class="secondary-link" href="?module=costs">+ Registrar movimiento</a><a class="secondary-link" href="?logout=1">Salir</a></div></header>
            <header class="admin-header"><div><p class="eyebrow">Resumen de la agrícola</p><h1>Panel principal</h1><p class="setup-copy">Revisa costos, producción, inventario y tareas pendientes en un solo lugar.</p></div><p class="dashboard-updated">Actualizado <?= date('d/m/Y H:i') ?></p></header>
            <section class="kpis">
                <article class="kpi"><div class="kpi-top"><span>Costo acumulado</span><span class="kpi-symbol">$</span></div><p class="kpi-value">$<?= $totalCost ?></p><small>Costos y mano de obra registrados</small></article>
                <article class="kpi"><div class="kpi-top"><span>Superficie activa</span><span class="kpi-symbol">Ha</span></div><p class="kpi-value"><?= $hectares ?> Ha</p><small><?= (int) ($metrics['farms'] ?? 0) ?> fundos activos</small></article>
                <article class="kpi"><div class="kpi-top"><span>Producción registrada</span><span class="kpi-symbol">Kg</span></div><p class="kpi-value"><?= $production ?> Kg</p><small><?= (int) ($metrics['blocks'] ?? 0) ?> cuarteles activos</small></article>
                <article class="kpi"><div class="kpi-top"><span>Movimientos</span><span class="kpi-symbol">#</span></div><p class="kpi-value"><?= $movements ?></p><small>Costos, labores e inventario</small></article>
            </section>
            <section class="dashboard-stat-row"><article class="dashboard-stat"><strong><?= (int) ($metrics['workers'] ?? 0) ?></strong><span>Trabajadores activos</span></article><article class="dashboard-stat"><strong><?= (int) ($metrics['items'] ?? 0) ?></strong><span>Insumos registrados</span></article><article class="dashboard-stat"><strong><?= (int) ($metrics['machinery'] ?? 0) ?></strong><span>Equipos activos</span></article><article class="dashboard-stat"><strong><?= (int) ($operational['pending_tasks'] ?? 0) ?></strong><span>Tareas pendientes</span></article><article class="dashboard-stat"><strong><?= (int) ($operational['open_requests'] ?? 0) ?></strong><span>Solicitudes abiertas</span></article></section>
            <section class="dashboard-chart-grid">
                <article class="admin-panel dashboard-chart"><header class="panel-header"><h2>Costos por mes</h2><p>Gastos registrados durante los últimos meses.</p></header><?php if ($costSeries === []): ?><p class="empty-state">Todavía no hay costos suficientes para mostrar este gráfico.</p><?php else: ?><div class="bar-chart"><?php foreach ($costSeries as $row): ?><div class="bar-item"><div class="bar-value">$<?= number_format((float) $row['value'], 0, ',', '.') ?></div><div class="bar-track"><span style="height: <?= min(100, max(4, ((float) $row['value'] / $maxCost) * 100)) ?>%"></span></div><small><?= htmlspecialchars(substr((string) $row['period'], 5), ENT_QUOTES, 'UTF-8') ?></small></div><?php endforeach; ?></div><?php endif; ?></article>
                <article class="admin-panel dashboard-chart"><header class="panel-header"><h2>Producción por mes</h2><p>Cantidad producida según los registros ingresados.</p></header><?php if ($productionSeries === []): ?><p class="empty-state">Todavía no hay producción suficiente para mostrar este gráfico.</p><?php else: ?><div class="bar-chart production-chart"><?php foreach ($productionSeries as $row): ?><div class="bar-item"><div class="bar-value"><?= number_format((float) $row['value'], 0, ',', '.') ?> Kg</div><div class="bar-track"><span style="height: <?= min(100, max(4, ((float) $row['value'] / $maxProduction) * 100)) ?>%"></span></div><small><?= htmlspecialchars(substr((string) $row['period'], 5), ENT_QUOTES, 'UTF-8') ?></small></div><?php endforeach; ?></div><?php endif; ?></article>
            </section>
            <section class="admin-columns">
                <article class="admin-panel"><header class="panel-header"><h2>Alertas de inventario</h2><p>Insumos que están bajo o cerca del mínimo configurado.</p></header><?php if ($inventoryAlerts === []): ?><p class="empty-state">No hay alertas de inventario en este momento.</p><?php else: ?><div class="role-list"><?php foreach ($inventoryAlerts as $alert): ?><div class="role-row"><div><b><?= htmlspecialchars($alert['name'], ENT_QUOTES, 'UTF-8') ?></b><small>Stock actual: <?= number_format((float) $alert['stock'], 2, ',', '.') ?> <?= htmlspecialchars($alert['unit'], ENT_QUOTES, 'UTF-8') ?></small></div><span>Mínimo <?= number_format((float) $alert['minimum_stock'], 2, ',', '.') ?></span></div><?php endforeach; ?></div><?php endif; ?></article>
                <article class="admin-panel"><header class="panel-header"><h2>Últimos movimientos</h2><p>Los registros más recientes de costos y labores.</p></header><?php if ($recent === []): ?><p class="empty-state">Aún no hay registros. Comienza desde los módulos de operación.</p><?php else: ?><div class="table-wrap"><table class="data-table"><thead><tr><th>Tipo</th><th>Descripción</th><th>Fecha</th><th>Valor</th></tr></thead><tbody><?php foreach ($recent as $entry): ?><tr><td><?= htmlspecialchars((string) $entry['type'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars((string) $entry['label'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars((string) $entry['date'], ENT_QUOTES, 'UTF-8') ?></td><td>$<?= number_format((float) $entry['value'], 0, ',', '.') ?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></article>
            </section>
            <section class="admin-panel"><header class="panel-header"><h2>Qué requiere atención</h2><p>Accesos rápidos para continuar con la operación.</p></header><nav class="module-links"><a href="?module=planning">Tareas y calendario <small><?= (int) ($operational['pending_tasks'] ?? 0) ?> pendientes</small></a><a href="?module=requests">Solicitudes internas <small><?= (int) ($operational['open_requests'] ?? 0) ?> abiertas</small></a><a href="?module=inventory">Revisar inventario <small><?= count($inventoryAlerts) ?> alertas</small></a><a href="?module=reports">Ver informes <small>Costos y producción</small></a></nav></section>
        </section>
    </main>
</body>
</html>
