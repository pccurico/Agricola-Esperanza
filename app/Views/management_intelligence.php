<?php
$dashboard = $intelligence ?? [];
$summary = $dashboard['summary'] ?? [];
$filters = $dashboard['filters'] ?? [];
$options = $dashboard['filter_options'] ?? [];
$metrics = [
    'production' => $summary['production'] ?? 0,
    'cost_per_unit' => $summary['cost_per_unit'] ?? 0,
    'profitability' => $summary['profitability'] ?? 0,
    'inventory_alert_count' => $summary['alert_count'] ?? 0,
];
$totals = ['total_cost' => $summary['total'] ?? 0];
$budget = $dashboard['budget'] ?? [];
$kpis = [];
$alerts = array_map(static fn (array $alert): array => [
    'title' => $alert['name'] ?? 'Stock crítico',
    'count' => 1,
], array_slice($dashboard['alerts'] ?? [], 0, 6));
$processes = $dashboard['processes'] ?? [];
$farms = $dashboard['farms'] ?? [];
$categories = $dashboard['categories'] ?? [];
$workers = $dashboard['workers'] ?? [];
$comparisons = $dashboard['comparisons']['periods'] ?? [];
$productionSeries = $dashboard['trends']['production'] ?? [];
$costSeries = $dashboard['trends']['costs'] ?? [];
$kpis = $kpis !== [] ? $kpis : [
    ['label' => 'Costo total', 'value' => $totals['total_cost'] ?? 0, 'detail' => 'Costos + mano de obra'],
    ['label' => 'Producción', 'value' => $metrics['production'] ?? 0, 'detail' => 'Volumen registrado'],
    ['label' => 'Costo por hectárea', 'value' => $metrics['cost_per_unit'] ?? 0, 'detail' => 'Eficiencia agrícola'],
    ['label' => 'Rentabilidad', 'value' => $metrics['profitability'] ?? 0, 'detail' => 'Producción sobre costos'],
    ['label' => 'Stock crítico', 'value' => $metrics['inventory_alert_count'] ?? 0, 'detail' => 'Insumos por revisar'],
    ['label' => 'Presupuesto ejecutado', 'value' => $budget['execution'] ?? 0, 'detail' => 'Porcentaje del plan'],
];
$dashboardData = json_encode([
    'processes' => $processes,
    'farms' => $farms,
    'categories' => $categories,
    'workers' => $workers,
    'comparisons' => $comparisons,
    'production_series' => $productionSeries,
    'cost_series' => $costSeries,
    'metrics' => $metrics,
    'totals' => $totals,
    'budget' => $budget,
], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);
$number = static fn (mixed $value, int $decimals = 0): string => number_format((float) $value, $decimals, ',', '.');
$money = static fn (mixed $value): string => '$' . $number($value);
$formatKpi = static function (array $kpi) use ($number, $money): string {
    $label = strtolower((string) ($kpi['label'] ?? ''));
    $value = (float) ($kpi['value'] ?? 0);
    if (str_contains($label, 'rentabilidad')) return $number($value * 100, 1) . '%';
    if (str_contains($label, 'presupuesto ejecutado')) return $number($value, 1) . '%';
    if (str_contains($label, 'costo')) return $money($value);
    return $number($value, 1);
};
$currentComparison = $comparisons[0]['metrics'] ?? [];
$previousComparison = $comparisons[1]['metrics'] ?? [];
$variation = static function (mixed $current, mixed $previous): ?float {
    $previous = (float) $previous;
    return $previous == 0.0 ? null : (((float) $current - $previous) / abs($previous)) * 100;
};
$costVariation = $variation($currentComparison['cost'] ?? 0, $previousComparison['cost'] ?? 0);
$productionVariation = $variation($currentComparison['production'] ?? 0, $previousComparison['production'] ?? 0);
$topProcess = $processes[0] ?? null;
$budgetExecution = (float) ($budget['execution'] ?? 0);
$budgetStatus = $budgetExecution >= 100 ? 'Sobre el presupuesto' : ($budgetExecution >= 85 ? 'Presupuesto bajo presión' : 'Presupuesto controlado');
$laborProductivity = (float) ($summary['labor_productivity'] ?? 0);
$productionPerHectare = (float) ($summary['production_per_hectare'] ?? 0);
$costPerUnit = (float) ($summary['cost_per_unit'] ?? 0);
$recommendations = [];
if ($costVariation !== null && $costVariation > 0 && $productionVariation !== null && $productionVariation < 0) {
    $recommendations[] = ['title' => 'Mejorar eficiencia antes de invertir', 'text' => 'Los costos suben mientras la producción cae. Prioriza revisar los procesos con mayor costo antes de aumentar recursos.'];
}
if ($budgetExecution >= 100) {
    $recommendations[] = ['title' => 'Controlar nuevas inversiones', 'text' => 'El presupuesto está sobre ejecutado. Enfoca la inversión en acciones correctivas con retorno medible.'];
} elseif ($topProcess) {
    $recommendations[] = ['title' => 'Revisar el proceso de mayor impacto', 'text' => 'Analiza ' . (string) ($topProcess['process'] ?? 'el proceso principal') . ', que concentra el mayor costo registrado.'];
}
if ($alerts !== []) {
    $recommendations[] = ['title' => 'Proteger continuidad operacional', 'text' => 'Existen alertas de inventario. Prioriza insumos críticos antes de expandir la operación.'];
}
if ($recommendations === []) {
    $recommendations[] = ['title' => 'Buscar crecimiento productivo', 'text' => 'La operación no presenta señales críticas. Evalúa inversiones que mejoren productividad por hectárea o jornada.'];
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inteligencia Gerencial | Empresa</title>
    <link rel="stylesheet" href="/assets/css/app.css">
    <link rel="stylesheet" href="/assets/css/dashboard.css">
</head>
<body class="admin-page">
<main class="admin-shell">
    <?php require dirname(__DIR__) . '/Views/partials/module-navigation.php'; ?>
    <section class="module-content management-intelligence">
        <header class="management-intelligence-hero">
            <div>
                <p class="eyebrow">Gerencia · Dueños y encargados</p>
                <h1>Centro de Inteligencia Gerencial</h1>
                <p>Una vista clara para decidir dónde crecer, corregir costos y proteger la rentabilidad.</p>
            </div>
            <div class="management-intelligence-status"><span class="status-dot"></span><span>Información operacional consolidada</span></div>
            <div class="management-hero-summary">
                <div><span>Principal foco de costo</span><strong><?= htmlspecialchars((string) ($topProcess['process'] ?? 'Sin datos'), ENT_QUOTES, 'UTF-8') ?></strong><small><?= $topProcess ? $money($topProcess['total'] ?? 0) : 'No disponible' ?></small></div>
                <div><span>Estado presupuestario</span><strong><?= htmlspecialchars($budgetStatus, ENT_QUOTES, 'UTF-8') ?></strong><small><?= $number($budgetExecution, 1) ?>% ejecutado</small></div>
            </div>
        </header>

        <section class="section-card management-filter-card">
            <div class="panel-header"><div><h2>Contexto de negocio</h2><p>Explora resultados por temporada, campo, proceso y centro de costo.</p></div></div>
            <form method="get" id="management-filter-form" class="management-filter-grid" data-management-filter>
                <label>Temporada<select name="season_id"><option value="">Todas</option><?php foreach ($options['seasons'] ?? [] as $season): ?><option value="<?= (int) $season['id'] ?>" <?= (int) ($filters['season_id'] ?? 0) === (int) $season['id'] ? 'selected' : '' ?>><?= htmlspecialchars((string) $season['name'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
                <label>Campo<select name="farm_id"><option value="">Todos</option><?php foreach ($options['farms'] ?? [] as $farm): ?><option value="<?= (int) $farm['id'] ?>" <?= (int) ($filters['farm_id'] ?? 0) === (int) $farm['id'] ? 'selected' : '' ?>><?= htmlspecialchars((string) $farm['name'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
                <label>Cuartel<select name="block_id"><option value="">Todos</option><?php foreach ($options['blocks'] ?? [] as $block): ?><option value="<?= (int) $block['id'] ?>" <?= (int) ($filters['block_id'] ?? 0) === (int) $block['id'] ? 'selected' : '' ?>><?= htmlspecialchars((string) $block['code'] . ' · ' . (string) $block['name'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
                <label>Proceso<select name="process"><option value="">Todos</option><?php foreach ($options['processes'] ?? [] as $process): ?><option value="<?= htmlspecialchars((string) $process['process'], ENT_QUOTES, 'UTF-8') ?>" <?= (string) ($filters['process'] ?? '') === (string) $process['process'] ? 'selected' : '' ?>><?= htmlspecialchars((string) $process['process'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
                <label>Centro de costo<select name="cost_center_id"><option value="">Todos</option><?php foreach ($options['centers'] ?? [] as $center): ?><option value="<?= (int) $center['id'] ?>" <?= (int) ($filters['cost_center_id'] ?? 0) === (int) $center['id'] ? 'selected' : '' ?>><?= htmlspecialchars((string) $center['category'] . ' · ' . (string) $center['name'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
                <div class="management-filter-actions"><button class="primary-button" type="submit">Actualizar visión</button><a class="secondary-button" href="/intelligence">Restablecer</a></div>
            </form>
        </section>

        <section class="management-kpi-grid">
            <?php foreach (array_slice($kpis, 0, 6) as $kpi): ?><article class="management-kpi-card"><span><?= htmlspecialchars((string) ($kpi['label'] ?? 'Indicador'), ENT_QUOTES, 'UTF-8') ?></span><strong><?= htmlspecialchars($formatKpi($kpi), ENT_QUOTES, 'UTF-8') ?></strong><small><?= htmlspecialchars((string) ($kpi['detail'] ?? ''), ENT_QUOTES, 'UTF-8') ?></small></article><?php endforeach; ?>
        </section>

        <section class="section-card management-comparison-card">
            <div class="panel-header"><div><h2>Comparativo de gestión</h2><p>Variación del período actual frente al período anterior disponible.</p></div></div>
            <div class="management-comparison-grid">
                <div><span>Costos</span><strong><?= $money($currentComparison['cost'] ?? 0) ?></strong><small class="<?= $costVariation !== null && $costVariation > 0 ? 'comparison-negative' : 'comparison-positive' ?>"><?= $costVariation === null ? 'Sin base comparativa' : ($costVariation >= 0 ? '+' : '') . $number($costVariation, 1) . '%' ?></small></div>
                <div><span>Producción</span><strong><?= $number($currentComparison['production'] ?? 0, 2) ?></strong><small class="<?= $productionVariation !== null && $productionVariation < 0 ? 'comparison-negative' : 'comparison-positive' ?>"><?= $productionVariation === null ? 'Sin base comparativa' : ($productionVariation >= 0 ? '+' : '') . $number($productionVariation, 1) . '%' ?></small></div>
                <div><span>Mano de obra</span><strong><?= $number($currentComparison['labor'] ?? 0, 2) ?> jornadas</strong><small>Período anterior: <?= $number($previousComparison['labor'] ?? 0, 2) ?></small></div>
                <div><span>Lectura ejecutiva</span><strong><?= $costVariation !== null && $costVariation > 0 && $productionVariation !== null && $productionVariation < 0 ? 'Riesgo de eficiencia' : 'Evolución operativa estable' ?></strong><small>Revisar procesos y recursos con mayor impacto.</small></div>
            </div>
        </section>

        <section class="management-performance-grid">
            <article class="section-card management-performance-card"><div class="panel-header"><div><h2>Resultado económico</h2><p>Lectura financiera basada en la información disponible.</p></div></div><div class="management-performance-value"><span>Ganancia / pérdida</span><strong>No disponible</strong><small>No existen ingresos registrados para calcular utilidad real.</small></div><div class="management-performance-value"><span>Costo por unidad</span><strong><?= $money($costPerUnit) ?></strong><small>Costo operativo por unidad producida.</small></div></article>
            <article class="section-card management-performance-card"><div class="panel-header"><div><h2>Productividad</h2><p>Indicadores para decidir dónde mejorar capacidad.</p></div></div><div class="management-productivity-list"><div><span>Producción por hectárea</span><strong><?= $number($productionPerHectare, 2) ?></strong></div><div><span>Producción por jornada</span><strong><?= $number($laborProductivity, 2) ?></strong></div><div><span>Mano de obra registrada</span><strong><?= $number($currentComparison['labor'] ?? 0, 2) ?> jornadas</strong></div></div></article>
            <article class="section-card management-performance-card"><div class="panel-header"><div><h2>Prioridades sugeridas</h2><p>Acciones derivadas de los indicadores actuales.</p></div></div><div class="management-recommendation-list"><?php foreach ($recommendations as $recommendation): ?><div><span class="recommendation-dot"></span><div><strong><?= htmlspecialchars($recommendation['title'], ENT_QUOTES, 'UTF-8') ?></strong><small><?= htmlspecialchars($recommendation['text'], ENT_QUOTES, 'UTF-8') ?></small></div></div><?php endforeach; ?></div></article>
        </section>

        <div class="management-intelligence-grid">
            <section class="section-card management-chart-card"><div class="panel-header"><div><h2>Decisión de rentabilidad</h2><p>Costos acumulados y producción durante el período.</p></div></div><div class="management-chart-frame"><canvas id="managementTrendChart" aria-label="Tendencia de costos y producción"></canvas></div></section>
            <aside class="management-owner-panel"><div class="panel-header"><div><h2>Panel del propietario</h2><p>Señales que requieren atención.</p></div></div><div class="owner-signal-list"><?php if ($alerts !== []): foreach ($alerts as $alert): ?><div class="owner-signal"><span class="signal-warning"></span><div><strong><?= htmlspecialchars((string) ($alert['title'] ?? 'Alerta'), ENT_QUOTES, 'UTF-8') ?></strong><small><?= $number($alert['count'] ?? 0) ?> pendientes</small></div></div><?php endforeach; else: ?><div class="owner-signal owner-signal-positive"><span class="signal-positive"></span><div><strong>Operación estable</strong><small>No hay alertas críticas activas.</small></div></div><?php endif; ?></div><div class="owner-summary"><span>Presupuesto ejecutado</span><strong><?= $number($budget['execution'] ?? 0, 1) ?>%</strong><small><?= $money($budget['actual'] ?? 0) ?> de <?= $money($budget['planned'] ?? 0) ?></small></div></aside>
        </div>

        <section class="section-card management-process-card"><div class="panel-header"><div><h2>Procesos que explican el resultado</h2><p>Prioriza conversaciones y acciones con impacto económico.</p></div></div><div class="management-chart-frame management-process-frame"><canvas id="managementProcessChart" aria-label="Costos por proceso"></canvas></div><?php if ($processes !== []): ?><div class="management-process-table"><div class="management-process-table-header"><span>Proceso</span><span>Costo acumulado</span></div><?php foreach (array_slice($processes, 0, 10) as $process): ?><div class="management-process-row"><strong><?= htmlspecialchars((string) ($process['process'] ?? 'Sin proceso'), ENT_QUOTES, 'UTF-8') ?></strong><span><?= $money($process['total'] ?? 0) ?></span></div><?php endforeach; ?></div><?php else: ?><p class="empty-state">No hay costos por proceso para los filtros seleccionados.</p><?php endif; ?></section>

        <section class="management-analytics-grid">
            <article class="section-card management-chart-card"><div class="panel-header"><div><h2>Distribución de costos</h2><p>¿Qué categorías explican el gasto?</p></div></div><div class="management-chart-frame"><canvas id="managementCategoryChart" aria-label="Distribución de costos por categoría"></canvas></div></article>
            <article class="section-card management-chart-card"><div class="panel-header"><div><h2>Costos por campo</h2><p>¿Dónde se concentra el gasto?</p></div></div><div class="management-chart-frame"><canvas id="managementFarmChart" aria-label="Producción por campo"></canvas></div></article>
            <article class="section-card management-chart-card"><div class="panel-header"><div><h2>Comparación ejecutiva</h2><p>Costos y producción frente al período anterior.</p></div></div><div class="management-chart-frame"><canvas id="managementComparisonChart" aria-label="Comparación ejecutiva"></canvas></div></article>
        </section>

        <section class="management-ranking-grid">
            <article class="section-card management-ranking-card"><div class="panel-header"><h2>Top procesos por costo</h2></div><div class="management-ranking-list"><?php foreach (array_slice($processes, 0, 5) as $process): ?><div><strong><?= htmlspecialchars((string) ($process['process'] ?? 'Sin proceso'), ENT_QUOTES, 'UTF-8') ?></strong><span><?= $money($process['total'] ?? 0) ?></span></div><?php endforeach; ?></div></article>
            <article class="section-card management-ranking-card"><div class="panel-header"><h2>Top campos por costo</h2></div><div class="management-ranking-list"><?php foreach (array_slice($farms, 0, 5) as $farm): ?><div><strong><?= htmlspecialchars((string) ($farm['name'] ?? 'Sin campo'), ENT_QUOTES, 'UTF-8') ?></strong><span><?= $money($farm['total'] ?? 0) ?></span></div><?php endforeach; ?></div></article>
            <article class="section-card management-ranking-card"><div class="panel-header"><h2>Top mano de obra</h2></div><div class="management-ranking-list"><?php foreach (array_slice($workers, 0, 5) as $worker): ?><div><strong><?= htmlspecialchars((string) ($worker['full_name'] ?? 'Sin trabajador'), ENT_QUOTES, 'UTF-8') ?></strong><span><?= $number($worker['quantity'] ?? 0, 2) ?> jornadas</span></div><?php endforeach; ?></div></article>
        </section>
    </section>
</main>
<script>window.managementIntelligenceData = <?= $dashboardData ?>;</script>
<script src="/assets/js/chart.min.js" defer></script>
<script src="/assets/js/management-intelligence.js" defer></script>
</body>
</html>
