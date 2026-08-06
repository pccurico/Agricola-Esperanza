<?php
// Evolucion del dashboard usando componentes existentes del ERP
$company = $dashboard['company'] ?? [];
$companyName = (string) ($company['trade_name'] ?? 'Empresa activa');
$customization = $dashboard['customization'] ?? [];
$savedViews = $customization['saved_views'] ?? [];
$activeView = (string) ($customization['active_view'] ?? '');
$kpis = $dashboard['kpis'] ?? [];
$alerts = $dashboard['alerts'] ?? [];
$filterOptions = $dashboard['filter_options'] ?? [];
$selectedFilters = $dashboard['filters'] ?? ['process' => '', 'farm_id' => 0, 'block_id' => 0, 'date_from' => date('Y-m-01'), 'date_to' => date('Y-m-d')];
$sections = $dashboard['sections'] ?? [];
$analyses = $dashboard['analyses'] ?? [];
$chartDefinitions = $dashboard['charts'] ?? [];
$productionSeries = $dashboard['production_series'] ?? [];
$costSeries = $dashboard['cost_series'] ?? [];
$recentActivities = $dashboard['recent'] ?? [];
$selectName = static function (array $items, int $selected, string $default): string {
    foreach ($items as $item) {
        if ((int) ($item['id'] ?? 0) === $selected) {
            return (string) ($item['name'] ?? $item['label'] ?? $default);
        }
    }
    return $default;
};
$selectedFarm = $selectName($filterOptions['farms'] ?? [], (int) ($selectedFilters['farm_id'] ?? 0), 'Todos');
$selectedBlock = $selectName($filterOptions['blocks'] ?? [], (int) ($selectedFilters['block_id'] ?? 0), 'Todos');
$selectedSeason = $selectName($filterOptions['seasons'] ?? [], (int) ($selectedFilters['season_id'] ?? 0), 'Todas');
$selectedProcess = trim((string) ($selectedFilters['process'] ?? '')) !== '' ? trim((string) ($selectedFilters['process'] ?? '')) : 'Todos';
$selectedPeriod = trim((string) ($selectedFilters['date_from'] ?? '')) . ' – ' . trim((string) ($selectedFilters['date_to'] ?? ''));
$budgetKpi = null;
$productionKpi = null;
foreach ($kpis as $kpi) {
    if ($budgetKpi === null && stripos((string) ($kpi['label'] ?? ''), 'presupuesto') !== false) {
        $budgetKpi = $kpi;
    }
    if ($productionKpi === null && stripos((string) ($kpi['label'] ?? ''), 'producción') !== false) {
        $productionKpi = $kpi;
    }
}
$chartWidgets = array_values(array_filter($dashboard['widgets'] ?? [], static fn (array $widget): bool => ($widget['type'] ?? '') === 'chart'));
$costProcesses = $sections['costs']['by_process'] ?? [];
$operational = $dashboard['operational'] ?? [];
$metrics = $dashboard['metrics'] ?? [];
$totals = $dashboard['totals'] ?? [];
$dashboardJson = json_encode([
    'production_series' => $productionSeries,
    'cost_series' => $costSeries,
    'cost_by_process' => $costProcesses,
    'totals' => $totals,
    'metrics' => $metrics,
    'budget' => $dashboard['budget'] ?? [],
    'filters' => $selectedFilters,
], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard | Sistema de Gestión Agrícola PCCURICO</title>
    <link rel="stylesheet" href="assets/css/app.css">
</head>
<body class="admin-page">
<main class="admin-shell">
    <?php require dirname(__DIR__) . '/Views/partials/module-navigation.php'; ?>
    <section class="module-content dashboard-v2">
        <div class="page-hero">
            <div class="hero-meta">
                <div class="hero-title">
                    <p class="eyebrow">Panel de control</p>
                    <h1>Resumen operativo — <?= htmlspecialchars($companyName, ENT_QUOTES, 'UTF-8') ?></h1>
                    <p class="lead-text">Indicadores clave y accesos rápidos del ERP PCCURICO.</p>
                </div>
                <div class="hero-actions">
                    <nav class="hero-nav">
                        <a class="btn" href="/reports">Informes</a>
                        <a class="btn btn-outline" href="?module=settings">Configuración</a>
                    </nav>
                </div>
            </div>

            <div class="hero-kpis">
                <div class="kpi-grid v2">
                    <article class="stat-card">
                        <span>Producción</span>
                        <strong id="kpi-production-total"><?= htmlspecialchars(number_format((float) ($productionKpi['value'] ?? $metrics['production'] ?? 0), 0, ',', '.'), ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars($productionKpi['unit'] ?? 'kg', ENT_QUOTES, 'UTF-8') ?></strong>
                        <small>Volumen total</small>
                    </article>
                    <article class="stat-card">
                        <span>Costos</span>
                        <strong id="kpi-total-cost"><?= htmlspecialchars(number_format((float) ($totals['total_cost'] ?? 0), 0, ',', '.'), ENT_QUOTES, 'UTF-8') ?> CLP</strong>
                        <small>Costo operativo</small>
                    </article>
                    <article class="stat-card">
                        <span>Presupuesto</span>
                        <strong id="kpi-budget-executed"><?= htmlspecialchars(number_format((float) ($budgetKpi['value'] ?? 0), 1, ',', '.'), ENT_QUOTES, 'UTF-8') ?>%</strong>
                        <small>Ejecución</small>
                    </article>
                    <article class="stat-card">
                        <span>Alertas</span>
                        <strong><?= htmlspecialchars(number_format((int) ($alerts[0]['count'] ?? 0), 0, ',', '.'), ENT_QUOTES, 'UTF-8') ?></strong>
                        <small>Críticas</small>
                    </article>
                </div>
            </div>
        </div>

        <div class="page-grid v2">
            <main class="main-column">
                <div class="grid-2-columns">
                    <section class="section-card report-visual">
                        <div class="panel-header"><div><h2>Producción vs presupuesto</h2><small>Comparativa del periodo</small></div><span class="badge">Comparativo</span></div>
                        <div class="panel-body"><canvas id="productionBudgetChart" aria-label="Costos vs Presupuesto"></canvas></div>
                    </section>

                    <section class="section-card report-visual">
                        <div class="panel-header"><div><h2>Evolución temporal</h2><small>Tendencias</small></div><span class="badge">Tendencia</span></div>
                        <div class="panel-body"><canvas id="trendChart" aria-label="Evolución temporal"></canvas></div>
                    </section>
                </div>

                <section class="section-card">
                    <div class="panel-header"><div><h2>Costos por proceso</h2><p>Distribución por unidad de proceso</p></div></div>
                    <div class="panel-body">
                        <div class="dashboard-chart-body"><canvas id="costProcessChart" aria-label="Costos por proceso"></canvas></div>
                        <div class="card-list">
                        <?php foreach (array_slice($costProcesses, 0, 6) as $row): ?>
                            <article class="stat-card">
                                <strong><?= htmlspecialchars((string) ($row['process'] ?? $row['category'] ?? 'Sin dato'), ENT_QUOTES, 'UTF-8') ?></strong>
                                <small><?= htmlspecialchars(number_format((float) ($row['total'] ?? 0), 0, ',', '.'), ENT_QUOTES, 'UTF-8') ?> CLP</small>
                            </article>
                        <?php endforeach; ?>
                        </div>
                    </div>
                </section>
            </main>

                <aside class="sidebar-column v2">
                    <section class="section-card">
                        <div class="panel-header"><h3>Filtros activos</h3></div>
                        <div class="panel-body">
                            <div class="card-list compact">
                                <div class="item-card"><small>Período</small><strong><?= htmlspecialchars($selectedPeriod, ENT_QUOTES, 'UTF-8') ?></strong></div>
                                <div class="item-card"><small>Fundo</small><strong><?= htmlspecialchars($selectedFarm, ENT_QUOTES, 'UTF-8') ?></strong></div>
                                <div class="item-card"><small>Cuartel</small><strong><?= htmlspecialchars($selectedBlock, ENT_QUOTES, 'UTF-8') ?></strong></div>
                                <div class="item-card"><small>Proceso</small><strong><?= htmlspecialchars($selectedProcess, ENT_QUOTES, 'UTF-8') ?></strong></div>
                            </div>
                        </div>
                    </section>

                    <section class="section-card">
                        <div class="panel-header"><h3>Alertas críticas</h3></div>
                        <div class="panel-body">
                        <?php if ($alerts !== []): foreach ($alerts as $alert): ?>
                            <article class="item-card"><strong><?= htmlspecialchars($alert['title'] ?? 'Alerta', ENT_QUOTES, 'UTF-8') ?></strong><small><?= htmlspecialchars((string) ($alert['count'] ?? 0) . ' items', ENT_QUOTES, 'UTF-8') ?></small></article>
                        <?php endforeach; else: ?>
                            <p class="empty-state">No hay alertas críticas.</p>
                        <?php endif; ?>
                        </div>
                    </section>

                    <section class="section-card">
                        <div class="panel-header"><h3>Indicadores</h3></div>
                        <div class="panel-body">
                            <div class="card-list compact">
                                <div class="item-card"><small>Fincas</small><strong><?= htmlspecialchars(number_format((int) ($metrics['farms'] ?? 0), 0, ',', '.'), ENT_QUOTES, 'UTF-8') ?></strong></div>
                                <div class="item-card"><small>Cuarteles</small><strong><?= htmlspecialchars(number_format((int) ($metrics['blocks'] ?? 0), 0, ',', '.'), ENT_QUOTES, 'UTF-8') ?></strong></div>
                                <div class="item-card"><small>Trabajadores</small><strong><?= htmlspecialchars(number_format((int) ($metrics['workers'] ?? 0), 0, ',', '.'), ENT_QUOTES, 'UTF-8') ?></strong></div>
                            </div>
                        </div>
                    </section>
                </aside>
        </div>
    </section>
</main>

<script>
    window.dashboardData = <?= $dashboardJson ?>;
</script>
<script src="assets/js/chart.min.js" defer></script>
<script src="assets/js/dashboard.js" defer></script>
</body>
</html>
