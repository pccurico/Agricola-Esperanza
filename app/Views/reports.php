<?php

declare(strict_types=1);

$reportSummary = $summary ?? ['total' => 0, 'entries' => 0, 'production' => 0, 'production_unit' => 'unidades', 'hectares' => 0, 'cost_per_hectare' => 0, 'cost_per_unit' => 0, 'production_entries' => 0];
$reportFilters = $filters ?? ['date_from' => date('Y-m-01'), 'date_to' => date('Y-m-d'), 'farm_id' => 0, 'block_id' => 0, 'process' => ''];
$options = $filter_options ?? ['farms' => [], 'blocks' => [], 'processes' => []];
$budgetData = $budget ?? ['planned' => 0, 'actual' => 0, 'variance' => 0, 'execution' => 0];
$exportQuery = http_build_query(array_filter(array_merge(['module' => 'reports'], $reportFilters, ['export' => 'csv']), static fn (mixed $value): bool => $value !== '' && $value !== 0));
$money = static fn (mixed $value): string => '$' . number_format((float) $value, 0, ',', '.');
$number = static fn (mixed $value, int $decimals = 0): string => number_format((float) $value, $decimals, ',', '.');
?>
<!doctype html>
<html lang="es">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Informes agrícolas</title><link rel="stylesheet" href="assets/css/app.css"></head>
<body class="admin-page">
<main class="admin-shell">
    <?php require dirname(__DIR__) . '/Views/partials/module-navigation.php'; ?>
    <section class="module-content">
        <header class="admin-header">
            <div><p class="eyebrow">Control y resultados</p><h1>Informes agrícolas</h1><p class="setup-copy">Analiza costos, rendimiento y productividad con la misma información operativa del sistema.</p></div>
            <div class="header-actions"><a class="secondary-link" href="?<?= htmlspecialchars($exportQuery, ENT_QUOTES, 'UTF-8') ?>">Descargar CSV</a><a class="secondary-link" href="./">Dashboard</a></div>
        </header>

        <form class="admin-panel report-filter-grid" method="get">
            <input type="hidden" name="module" value="reports">
            <label>Desde<input type="date" name="date_from" value="<?= htmlspecialchars((string) $reportFilters['date_from'], ENT_QUOTES, 'UTF-8') ?>"></label>
            <label>Hasta<input type="date" name="date_to" value="<?= htmlspecialchars((string) $reportFilters['date_to'], ENT_QUOTES, 'UTF-8') ?>"></label>
            <label>Proceso<select name="process"><option value="">Todos</option><?php foreach (($options['processes'] ?? []) as $option): ?><option value="<?= htmlspecialchars((string) ($option['process'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" <?= (string) $reportFilters['process'] === (string) ($option['process'] ?? '') ? 'selected' : '' ?>><?= htmlspecialchars((string) ($option['process'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
            <label>Fundo<select name="farm_id"><option value="0">Todos</option><?php foreach (($options['farms'] ?? []) as $farm): ?><option value="<?= (int) $farm['id'] ?>" <?= (int) $reportFilters['farm_id'] === (int) $farm['id'] ? 'selected' : '' ?>><?= htmlspecialchars((string) $farm['name'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
            <label>Cuartel<select name="block_id"><option value="0">Todos</option><?php foreach (($options['blocks'] ?? []) as $block): ?><option value="<?= (int) $block['id'] ?>" <?= (int) $reportFilters['block_id'] === (int) $block['id'] ? 'selected' : '' ?>><?= htmlspecialchars((string) $block['code'] . ' · ' . (string) $block['name'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
            <button class="primary-button" type="submit">Actualizar informe</button>
        </form>

        <section class="kpi-grid report-kpi-grid">
            <article class="report-kpi"><span>Costo total</span><b><?= $money($reportSummary['total'] ?? 0) ?></b><small>Costos y mano de obra</small></article>
            <article class="report-kpi"><span>Costo por hectárea</span><b><?= $money($reportSummary['cost_per_hectare'] ?? 0) ?></b><small><?= $number($reportSummary['hectares'] ?? 0, 2) ?> Ha consideradas</small></article>
            <article class="report-kpi"><span>Producción</span><b><?= $number($reportSummary['production'] ?? 0, 2) ?></b><small><?= htmlspecialchars((string) ($reportSummary['production_unit'] ?? 'unidades'), ENT_QUOTES, 'UTF-8') ?></small></article>
            <article class="report-kpi"><span>Costo por unidad</span><b><?= $money($reportSummary['cost_per_unit'] ?? 0) ?></b><small><?= (int) ($reportSummary['production_entries'] ?? 0) ?> registros productivos</small></article>
        </section>

        <section class="report-highlight-grid">
            <article class="admin-panel report-highlight-card"><span>Presupuesto planificado</span><strong><?= $money($budgetData['planned'] ?? 0) ?></strong><small>Ejecución: <?= $number($budgetData['execution'] ?? 0, 1) ?>%</small></article>
            <article class="admin-panel report-highlight-card"><span>Ejecución presupuestaria</span><strong><?= $money($budgetData['actual'] ?? 0) ?></strong><small><?= ($budgetData['variance'] ?? 0) >= 0 ? 'Saldo disponible' : 'Sobre ejecución' ?>: <?= $money(abs((float) ($budgetData['variance'] ?? 0))) ?></small></article>
            <article class="admin-panel report-highlight-card"><span>Mano de obra</span><strong><?= $money($labor_summary['total'] ?? 0) ?></strong><small><?= $number($labor_summary['quantity'] ?? 0, 2) ?> jornadas · <?= (int) ($labor_summary['workers'] ?? 0) ?> trabajadores</small></article>
        </section>

        <section class="admin-columns report-columns">
            <article class="admin-panel"><header class="panel-header"><h2>Costos por fundo</h2><p>Comparación del costo registrado por unidad agrícola.</p></header><div class="report-table-wrap"><table class="data-table"><thead><tr><th>Fundo</th><th>Total</th></tr></thead><tbody><?php foreach (($farms ?? []) as $row): ?><tr><td><?= htmlspecialchars((string) ($row['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td><td><?= $money($row['total'] ?? 0) ?></td></tr><?php endforeach; ?><?php if (($farms ?? []) === []): ?><tr><td colspan="2" class="empty-state">Sin datos para el filtro seleccionado.</td></tr><?php endif; ?></tbody></table></div></article>
            <article class="admin-panel"><header class="panel-header"><h2>Costos por proceso</h2><p>Identifica dónde se concentra el gasto.</p></header><div class="report-table-wrap"><table class="data-table"><thead><tr><th>Proceso</th><th>Total</th></tr></thead><tbody><?php foreach (($processes ?? []) as $row): ?><tr><td><?= htmlspecialchars((string) ($row['process'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td><td><?= $money($row['total'] ?? 0) ?></td></tr><?php endforeach; ?><?php if (($processes ?? []) === []): ?><tr><td colspan="2" class="empty-state">Sin datos para el filtro seleccionado.</td></tr><?php endif; ?></tbody></table></div></article>
        </section>

        <section class="admin-columns report-columns">
            <article class="admin-panel"><header class="panel-header"><h2>Rendimiento por cuartel</h2><p>Producción registrada por fundo y cuartel.</p></header><div class="report-table-wrap"><table class="data-table"><thead><tr><th>Ubicación</th><th>Producción</th></tr></thead><tbody><?php foreach (($blocks ?? []) as $row): ?><tr><td><b><?= htmlspecialchars((string) ($row['farm_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></b><small><?= htmlspecialchars((string) ($row['block_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></small></td><td><?= $number($row['quantity'] ?? 0, 2) ?> <?= htmlspecialchars((string) ($row['unit'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td></tr><?php endforeach; ?><?php if (($blocks ?? []) === []): ?><tr><td colspan="2" class="empty-state">Sin producción para el filtro seleccionado.</td></tr><?php endif; ?></tbody></table></div></article>
            <article class="admin-panel"><header class="panel-header"><h2>Productividad de mano de obra</h2><p>Participación de trabajadores en el periodo.</p></header><div class="report-table-wrap"><table class="data-table"><thead><tr><th>Trabajador</th><th>Jornadas</th><th>Total</th></tr></thead><tbody><?php foreach (($workers ?? []) as $row): ?><tr><td><?= htmlspecialchars((string) ($row['full_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td><td><?= $number($row['quantity'] ?? 0, 2) ?></td><td><?= $money($row['total'] ?? 0) ?></td></tr><?php endforeach; ?><?php if (($workers ?? []) === []): ?><tr><td colspan="3" class="empty-state">Sin mano de obra para el filtro seleccionado.</td></tr><?php endif; ?></tbody></table></div></article>
        </section>
    </section>
</main>
</body>
</html>
