<?php

declare(strict_types=1);

$reportSummary = $summary ?? [];
$money = static fn (mixed $value): string => '$' . number_format((float) $value, 0, ',', '.');
$number = static fn (mixed $value, int $decimals = 0): string => number_format((float) $value, $decimals, ',', '.');
$reportType = (string) ($report_type ?? $reportSummary['report_type'] ?? 'executive');
$reportFilters = $filters ?? [];
$reportFilters['report'] = $reportType;
$options = $filter_options ?? [];
$reportConfigList = $report_config ?? [];
$selectedReportConfig = $reportConfigList[$reportType] ?? [];
$reportTitle = $selectedReportConfig['title'] ?? 'Informe ejecutivo';
$reportDescription = $selectedReportConfig['description'] ?? 'Resumen del informe.';
$comparisons = $comparisons ?? ['periods' => [], 'seasons' => []];
$trends = $trends ?? ['costs' => [], 'labor' => [], 'production' => [], 'budget' => []];
$budgetData = $budget ?? ['planned' => 0, 'actual' => 0, 'variance' => 0, 'execution' => 0];
$laborData = $labor_summary ?? ['total' => 0, 'quantity' => 0, 'workers' => 0];
$alerts = $alerts ?? [];
$reportSummaryCards = [];
switch ($reportType) {
    case 'production':
        $reportSummaryCards = [
            ['label' => 'Producción', 'value' => $number($reportSummary['production'] ?? 0, 2), 'detail' => $reportSummary['production_unit'] ?? 'unidades'],
            ['label' => 'Costo por unidad', 'value' => $money($reportSummary['cost_per_unit'] ?? 0), 'detail' => 'Eficiencia productiva'],
            ['label' => 'Producción/Ha', 'value' => $number($reportSummary['production_per_hectare'] ?? 0, 2), 'detail' => 'Kg por hectárea'],
            ['label' => 'Jornadas', 'value' => $number($laborData['quantity'] ?? 0, 2), 'detail' => 'Total jornadas'],
        ];
        break;
    case 'costs':
        $reportSummaryCards = [
            ['label' => 'Costo total', 'value' => $money($reportSummary['total'] ?? 0), 'detail' => 'Costos + mano de obra'],
            ['label' => 'Centros de gasto', 'value' => count($centers ?? []), 'detail' => 'Líneas activas'],
            ['label' => 'Costos por Ha', 'value' => $money($reportSummary['cost_per_hectare'] ?? 0), 'detail' => 'Costo por hectárea'],
            ['label' => 'Presupuesto', 'value' => $money($budgetData['planned'] ?? 0), 'detail' => 'Planificado'],
        ];
        break;
    case 'labor':
        $reportSummaryCards = [
            ['label' => 'Costo laboral', 'value' => $money($laborData['total'] ?? 0), 'detail' => 'Total pagado'],
            ['label' => 'Jornadas', 'value' => $number($laborData['quantity'] ?? 0, 2), 'detail' => 'Total jornadas'],
            ['label' => 'Trabajadores', 'value' => (int) ($laborData['workers'] ?? 0), 'detail' => 'Activos'],
            ['label' => 'Productividad', 'value' => $number($reportSummary['labor_productivity'] ?? 0, 2), 'detail' => 'Kg por jornada'],
        ];
        break;
    case 'inventory':
        $reportSummaryCards = [
            ['label' => 'Alertas críticas', 'value' => (int) ($reportSummary['alert_count'] ?? 0), 'detail' => 'Items por revisar'],
            ['label' => 'Stock mínimo', 'value' => count($alerts), 'detail' => 'Registros activos'],
            ['label' => 'Valor total', 'value' => $money($reportSummary['total'] ?? 0), 'detail' => 'Costo estimado'],
            ['label' => 'Centros', 'value' => count($centers ?? []), 'detail' => 'Centros de costo'],
        ];
        break;
    case 'procurement':
        $reportSummaryCards = [
            ['label' => 'Órdenes abiertas', 'value' => $reportSummary['orders_open'] ?? 0, 'detail' => 'Pedidos sin recibir'],
            ['label' => 'Costo total', 'value' => $money($reportSummary['total'] ?? 0), 'detail' => 'Costos de compras'],
            ['label' => 'Proveedores', 'value' => count($processes ?? []), 'detail' => 'Activos'],
            ['label' => 'Presupuesto', 'value' => $money($budgetData['actual'] ?? 0), 'detail' => 'Ejecutado'],
        ];
        break;
    case 'finance':
        $reportSummaryCards = [
            ['label' => 'Gastos totales', 'value' => $money($reportSummary['total'] ?? 0), 'detail' => 'Costos y compras'],
            ['label' => 'Presupuesto ejecutado', 'value' => $money($budgetData['actual'] ?? 0), 'detail' => 'Saldo real'],
            ['label' => 'Ejecución', 'value' => $number($budgetData['execution'] ?? 0, 1) . '%', 'detail' => 'Porcentaje'],
            ['label' => 'Centros', 'value' => count($centers ?? []), 'detail' => 'Cuentas activas'],
        ];
        break;
    default:
        $reportSummaryCards = [
            ['label' => 'Costo total', 'value' => $money($reportSummary['total'] ?? 0), 'detail' => 'Costos + mano de obra'],
            ['label' => 'Producción', 'value' => $number($reportSummary['production'] ?? 0, 2), 'detail' => $reportSummary['production_unit'] ?? 'unidades'],
            ['label' => 'Costo por unidad', 'value' => $money($reportSummary['cost_per_unit'] ?? 0), 'detail' => 'Eficiencia productiva'],
            ['label' => 'Rentabilidad', 'value' => $number($reportSummary['profitability'] ?? 0, 2), 'detail' => 'Producción/Costos'],
        ];
        break;
}
$money = static fn (mixed $value): string => '$' . number_format((float) $value, 0, ',', '.');
$number = static fn (mixed $value, int $decimals = 0): string => number_format((float) $value, $decimals, ',', '.');
$filterQuery = static function (string $export = '') use ($reportFilters): string {
    $values = array_merge(['module' => 'reports'], $reportFilters, $export !== '' ? ['export' => $export] : []);
    return http_build_query(array_filter($values, static fn (mixed $value): bool => $value !== '' && $value !== 0));
};
$maxTrend = static function (array $rows): float {
    return max([1, ...array_map(static fn (array $row): float => (float) ($row['value'] ?? 0), $rows)]);
};
$hasReportFilters = (string) ($reportFilters['process'] ?? '') !== '' || (int) ($reportFilters['season_id'] ?? 0) > 0 || (int) ($reportFilters['farm_id'] ?? 0) > 0 || (int) ($reportFilters['block_id'] ?? 0) > 0 || (int) ($reportFilters['cost_center_id'] ?? 0) > 0 || (int) ($reportFilters['worker_id'] ?? 0) > 0 || (int) ($reportFilters['supervisor_id'] ?? 0);
?>
<!doctype html>
<html lang="es">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title><?= htmlspecialchars($reportTitle, ENT_QUOTES, 'UTF-8') ?> | Informes</title><link rel="stylesheet" href="/assets/css/app.css"><link rel="stylesheet" href="/assets/css/reports.css"></head>
<body class="admin-page">
<main class="admin-shell">
    <?php require dirname(__DIR__) . '/Views/partials/module-navigation.php'; ?>
    <section class="module-content executive-reports">
        <header class="admin-header"><div><p class="eyebrow">Gerencia agrícola</p><h1><?= htmlspecialchars($reportTitle, ENT_QUOTES, 'UTF-8') ?></h1><p class="setup-copy"><?= htmlspecialchars($reportDescription, ENT_QUOTES, 'UTF-8') ?></p></div><div class="header-actions"><a class="secondary-link" href="?<?= htmlspecialchars($filterQuery('csv'), ENT_QUOTES, 'UTF-8') ?>">CSV</a><a class="secondary-link" href="?<?= htmlspecialchars($filterQuery('xlsx'), ENT_QUOTES, 'UTF-8') ?>">Excel</a><a class="secondary-link" href="?<?= htmlspecialchars($filterQuery('pdf'), ENT_QUOTES, 'UTF-8') ?>">PDF</a><a class="secondary-link" href="/reports">Centro de inteligencia</a></div></header>

        <details class="admin-panel report-filter-panel" <?= $hasReportFilters ? 'open' : '' ?>><summary><span><b>Filtros del informe</b><small><?= $hasReportFilters ? 'Filtros activos aplicados' : 'Todos los registros' ?></small></span><i aria-hidden="true"></i></summary><form class="report-filter-grid executive-filter" method="get">
            <input type="hidden" name="module" value="reports">
            <label>Desde<input type="date" name="date_from" value="<?= htmlspecialchars((string) ($reportFilters['date_from'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></label>
            <label>Hasta<input type="date" name="date_to" value="<?= htmlspecialchars((string) ($reportFilters['date_to'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></label>
            <label>Tipo de informe<select name="report"><?php foreach ($reportConfigList as $reportKey => $reportConfigItem): ?><option value="<?= htmlspecialchars($reportKey, ENT_QUOTES, 'UTF-8') ?>" <?= $reportType === $reportKey ? 'selected' : '' ?>><?= htmlspecialchars((string) $reportConfigItem['title'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
            <label>Temporada<select name="season_id"><option value="0">Todas</option><?php foreach (($options['seasons'] ?? []) as $item): ?><option value="<?= (int) $item['id'] ?>" <?= (int) ($reportFilters['season_id'] ?? 0) === (int) $item['id'] ? 'selected' : '' ?>><?= htmlspecialchars((string) $item['name'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
            <label>Fundo<select name="farm_id"><option value="0">Todos</option><?php foreach (($options['farms'] ?? []) as $item): ?><option value="<?= (int) $item['id'] ?>" <?= (int) ($reportFilters['farm_id'] ?? 0) === (int) $item['id'] ? 'selected' : '' ?>><?= htmlspecialchars((string) $item['name'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
            <label>Cuartel<select name="block_id"><option value="0">Todos</option><?php foreach (($options['blocks'] ?? []) as $item): ?><option value="<?= (int) $item['id'] ?>" <?= (int) ($reportFilters['block_id'] ?? 0) === (int) $item['id'] ? 'selected' : '' ?>><?= htmlspecialchars((string) $item['code'] . ' · ' . (string) $item['name'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
            <label>Proceso<select name="process"><option value="">Todos</option><?php foreach (($options['processes'] ?? []) as $item): ?><option value="<?= htmlspecialchars((string) $item['process'], ENT_QUOTES, 'UTF-8') ?>" <?= (string) ($reportFilters['process'] ?? '') === (string) $item['process'] ? 'selected' : '' ?>><?= htmlspecialchars((string) $item['process'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
            <label>Centro de costo<select name="cost_center_id"><option value="0">Todos</option><?php foreach (($options['centers'] ?? []) as $item): ?><option value="<?= (int) $item['id'] ?>" <?= (int) ($reportFilters['cost_center_id'] ?? 0) === (int) $item['id'] ? 'selected' : '' ?>><?= htmlspecialchars((string) $item['name'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
            <label>Trabajador<select name="worker_id"><option value="0">Todos</option><?php foreach (($options['workers'] ?? []) as $item): ?><option value="<?= (int) $item['id'] ?>" <?= (int) ($reportFilters['worker_id'] ?? 0) === (int) $item['id'] ? 'selected' : '' ?>><?= htmlspecialchars((string) $item['full_name'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
            <label>Supervisor<select name="supervisor_id"><option value="0">Todos</option><?php foreach (($options['supervisors'] ?? []) as $item): ?><option value="<?= (int) $item['id'] ?>" <?= (int) ($reportFilters['supervisor_id'] ?? 0) === (int) $item['id'] ? 'selected' : '' ?>><?= htmlspecialchars((string) $item['full_name'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
            <button class="primary-button" type="submit">Actualizar</button>
        </form></details>

        <section class="report-summary-grid">
            <?php foreach ($reportSummaryCards as $card): ?>
                <article class="report-summary-card">
                    <small><?= htmlspecialchars($card['detail'], ENT_QUOTES, 'UTF-8') ?></small>
                    <strong><?= htmlspecialchars((string) $card['value'], ENT_QUOTES, 'UTF-8') ?></strong>
                    <span><?= htmlspecialchars($card['label'], ENT_QUOTES, 'UTF-8') ?></span>
                </article>
            <?php endforeach; ?>
        </section>

        <section class="admin-panel report-chart-panel">
            <header class="panel-header"><h2>Visualización del informe</h2></header>
            <div class="admin-panel-body report-chart-body">
                <canvas id="reportOverviewChart" aria-label="Gráfico del informe"></canvas>
            </div>
        </section>

        <?php if ($reportType === 'executive'): ?>
            <section class="finance-kpi-grid report-kpi-grid"><article class="finance-kpi finance-kpi-primary"><span>Costo total</span><strong><?= $money($reportSummary['total'] ?? 0) ?></strong><small>Costos + mano de obra</small></article><article class="finance-kpi finance-kpi-green"><span>Costo por unidad</span><strong><?= $money($reportSummary['cost_per_unit'] ?? 0) ?></strong><small>Indicador de eficiencia productiva</small></article><article class="finance-kpi finance-kpi-gold"><span>Producción</span><strong><?= $number($reportSummary['production'] ?? 0, 2) ?></strong><small><?= htmlspecialchars((string) ($reportSummary['production_unit'] ?? 'unidades'), ENT_QUOTES, 'UTF-8') ?></small></article><article class="finance-kpi finance-kpi-blue"><span>Costo por hectárea</span><strong><?= $money($reportSummary['cost_per_hectare'] ?? 0) ?></strong><small><?= $number($reportSummary['hectares'] ?? 0, 2) ?> Ha</small></article></section>
        <?php endif; ?>

        <section class="report-highlight-grid"><article class="admin-panel report-highlight-card"><span>Producción por hectárea</span><strong><?= $number($reportSummary['production_per_hectare'] ?? 0, 2) ?></strong><small>Indicador agrícola efectivo</small></article><article class="admin-panel report-highlight-card"><span>Productividad laboral</span><strong><?= $number($reportSummary['labor_productivity'] ?? 0, 2) ?></strong><small>Kg por jornada</small></article><article class="admin-panel report-highlight-card"><span>Rentabilidad</span><strong><?= $number($reportSummary['profitability'] ?? 0, 2) ?></strong><small>Producción por costo</small></article><article class="admin-panel report-highlight-card"><span>Alertas de stock</span><strong><?= (int) ($reportSummary['alert_count'] ?? 0) ?></strong><small>Crítico o bajo mínimo</small></article></section>

        <section class="report-highlight-grid"><article class="admin-panel report-highlight-card"><span>Presupuesto planificado</span><strong><?= $money($budgetData['planned'] ?? 0) ?></strong><small>Ejecutado <?= $number($budgetData['execution'] ?? 0, 1) ?>%</small></article><article class="admin-panel report-highlight-card"><span>Ejecución presupuestaria</span><strong><?= $money($budgetData['actual'] ?? 0) ?></strong><small><?= ($budgetData['variance'] ?? 0) >= 0 ? 'Saldo' : 'Sobre ejecución' ?> <?= $money(abs((float) ($budgetData['variance'] ?? 0))) ?></small></article><article class="admin-panel report-highlight-card"><span>Mano de obra</span><strong><?= $money($laborData['total'] ?? 0) ?></strong><small><?= $number($laborData['quantity'] ?? 0, 2) ?> jornadas · <?= (int) ($laborData['workers'] ?? 0) ?> trabajadores</small></article></section>

        <section class="admin-columns report-columns"><article class="admin-panel"><header class="panel-header"><h2>Mes seleccionado vs anterior</h2></header><div class="report-comparison-grid"><?php foreach (($comparisons['periods'] ?? []) as $item): ?><div><span><?= htmlspecialchars((string) $item['label'], ENT_QUOTES, 'UTF-8') ?></span><b><?= $money($item['metrics']['cost'] ?? 0) ?></b><small>Producción <?= $number($item['metrics']['production'] ?? 0, 2) ?> · Labor <?= $number($item['metrics']['labor'] ?? 0, 2) ?></small></div><?php endforeach; ?></div></article><article class="admin-panel"><header class="panel-header"><h2>Temporadas</h2></header><div class="report-comparison-grid"><?php foreach (($comparisons['seasons'] ?? []) as $item): ?><div><span><?= htmlspecialchars((string) $item['label'], ENT_QUOTES, 'UTF-8') ?></span><b><?= $money($item['metrics']['cost'] ?? 0) ?></b><small>Producción <?= $number($item['metrics']['production'] ?? 0, 2) ?></small></div><?php endforeach; ?><?php if (($comparisons['seasons'] ?? []) === []): ?><p class="empty-state">Sin temporadas disponibles.</p><?php endif; ?></div></article></section>

        <section class="admin-columns report-columns"><article class="admin-panel report-trend-panel"><header class="panel-header"><h2>Tendencias mensuales</h2></header><?php foreach (['costs' => 'Costos', 'production' => 'Producción', 'labor' => 'Mano de obra', 'budget' => 'Presupuesto ejecutado'] as $key => $label): ?><div class="report-trend"><span><?= $label ?></span><?php $max = $maxTrend($trends[$key] ?? []); ?><?php foreach (($trends[$key] ?? []) as $row): ?><div class="report-trend-row"><small><?= htmlspecialchars((string) $row['period'], ENT_QUOTES, 'UTF-8') ?></small><i style="--trend: <?= min(100, ((float) $row['value'] / $max) * 100) ?>%"></i><b><?= $key === 'production' ? $number($row['value'] ?? 0, 2) : $money($row['value'] ?? 0) ?></b></div><?php endforeach; ?><?php if (($trends[$key] ?? []) === []): ?><small class="empty-state">Sin datos.</small><?php endif; ?></div><?php endforeach; ?></article><article class="admin-panel"><header class="panel-header"><h2>Rankings ejecutivos</h2></header><div class="ranking-list"><h3>Fundos con mayor costo</h3><?php foreach (array_slice($farms ?? [], 0, 5) as $row): ?><div><span><?= htmlspecialchars((string) $row['name'], ENT_QUOTES, 'UTF-8') ?></span><b><?= $money($row['total']) ?></b></div><?php endforeach; ?><h3>Procesos más costosos</h3><?php foreach (array_slice($processes ?? [], 0, 5) as $row): ?><div><span><?= htmlspecialchars((string) $row['process'], ENT_QUOTES, 'UTF-8') ?></span><b><?= $money($row['total']) ?></b></div><?php endforeach; ?></div></article></section>

        <section class="admin-columns report-columns"><article class="admin-panel"><header class="panel-header"><h2>Cuarteles más productivos</h2></header><table class="data-table"><thead><tr><th>Ubicación</th><th>Producción</th></tr></thead><tbody><?php foreach (($blocks ?? []) as $row): ?><tr><td><?= htmlspecialchars((string) $row['farm_name'] . ' · ' . (string) $row['block_name'], ENT_QUOTES, 'UTF-8') ?></td><td><?= $number($row['quantity'], 2) ?> <?= htmlspecialchars((string) $row['unit'], ENT_QUOTES, 'UTF-8') ?></td></tr><?php endforeach; ?><?php if (($blocks ?? []) === []): ?><tr><td colspan="2" class="empty-state">Sin producción.</td></tr><?php endif; ?></tbody></table></article><article class="admin-panel"><header class="panel-header"><h2>Trabajadores más productivos</h2></header><table class="data-table"><thead><tr><th>Trabajador</th><th>Jornadas</th><th>Costo</th></tr></thead><tbody><?php foreach (($workers ?? []) as $row): ?><tr><td><?= htmlspecialchars((string) $row['full_name'], ENT_QUOTES, 'UTF-8') ?></td><td><?= $number($row['quantity'], 2) ?></td><td><?= $money($row['total']) ?></td></tr><?php endforeach; ?><?php if (($workers ?? []) === []): ?><tr><td colspan="3" class="empty-state">Sin mano de obra.</td></tr><?php endif; ?></tbody></table></article></section>

        <section class="admin-columns report-columns"><article class="admin-panel"><header class="panel-header"><h2>Centros de costo con mayor gasto</h2></header><table class="data-table"><thead><tr><th>Centro</th><th>Categoría</th><th>Total</th></tr></thead><tbody><?php foreach (($centers ?? []) as $row): ?><tr><td><?= htmlspecialchars((string) $row['name'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars((string) $row['category'], ENT_QUOTES, 'UTF-8') ?></td><td><?= $money($row['total']) ?></td></tr><?php endforeach; ?><?php if (($centers ?? []) === []): ?><tr><td colspan="3" class="empty-state">Sin costos.</td></tr><?php endif; ?></tbody></table></article><article class="admin-panel"><header class="panel-header"><h2>Alertas de inventario</h2></header><table class="data-table"><thead><tr><th>Insumo</th><th>Unidad</th><th>Stock</th><th>Mínimo</th></tr></thead><tbody><?php foreach ($alerts as $row): ?><tr><td><?= htmlspecialchars((string) $row['name'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars((string) $row['unit'], ENT_QUOTES, 'UTF-8') ?></td><td><?= $number($row['stock'] ?? 0, 2) ?></td><td><?= $number($row['minimum_stock'] ?? 0, 2) ?></td></tr><?php endforeach; ?><?php if ($alerts === []): ?><tr><td colspan="4" class="empty-state">Sin alertas.</td></tr><?php endif; ?></tbody></table></article></section>
    </section>
</main>
<script>
window.reportData = <?= json_encode([
    'report_type' => $reportType,
    'summary' => $reportSummary,
    'labor_summary' => $laborData,
    'budget' => $budgetData,
    'categories' => $categories ?? [],
    'processes' => $processes ?? [],
    'workers' => $workers ?? [],
    'blocks' => $blocks ?? [],
    'centers' => $centers ?? [],
    'alerts' => $alerts,
    'comparisons' => $comparisons,
    'trends' => $trends,
], JSON_UNESCAPED_UNICODE) ?>;
</script>
<script src="/assets/js/chart.min.js" defer></script>
<script src="/assets/js/reports.js" defer></script>
</body>
</html>
