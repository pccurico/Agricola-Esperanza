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
    <section class="module-content dashboard-content">
        <header class="admin-header">
            <div>
                <p class="eyebrow">Panel de control</p>
                <h1>Resumen operativo</h1>
                <p class="setup-copy">Indicadores y alertas reales del ERP PCCURICO para operar sin datos simulados.</p>
            </div>
            <div class="header-actions">
                <a class="secondary-link" href="?module=reports">Informes</a>
                <a class="secondary-link" href="?module=settings">Configuración</a>
            </div>
        </header>

        <?php if (!empty($error)): ?>
            <div class="setup-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="setup-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <section class="dashboard-filter-bar admin-panel">
            <div class="dashboard-filter-brand">
                <span>Empresa</span>
                <strong><?= htmlspecialchars($companyName, ENT_QUOTES, 'UTF-8') ?></strong>
            </div>
            <form id="dashboard-filter-form" class="dashboard-filter-grid" method="get">
                <div class="dashboard-filter-item">
                    <label>Periodo</label>
                    <div class="dashboard-filter-range">
                        <input type="date" name="date_from" value="<?= htmlspecialchars($selectedFilters['date_from'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        <span>–</span>
                        <input type="date" name="date_to" value="<?= htmlspecialchars($selectedFilters['date_to'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                </div>
                <div class="dashboard-filter-item">
                    <label>Fundo</label>
                    <select name="farm_id">
                        <option value="0">Todos</option>
                        <?php foreach (($filterOptions['farms'] ?? []) as $farm): ?>
                            <option value="<?= (int) $farm['id'] ?>" <?= (int) $selectedFilters['farm_id'] === (int) $farm['id'] ? 'selected' : '' ?>><?= htmlspecialchars($farm['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="dashboard-filter-item">
                    <label>Cuartel</label>
                    <select name="block_id">
                        <option value="0">Todos</option>
                        <?php foreach (($filterOptions['blocks'] ?? []) as $block): ?>
                            <option value="<?= (int) $block['id'] ?>" <?= (int) $selectedFilters['block_id'] === (int) $block['id'] ? 'selected' : '' ?>><?= htmlspecialchars($block['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="dashboard-filter-item">
                    <label>Proceso</label>
                    <select name="process">
                        <option value="">Todos</option>
                        <?php foreach (($filterOptions['processes'] ?? []) as $p): $pv = (string) ($p['process'] ?? ''); ?>
                            <option value="<?= htmlspecialchars($pv, ENT_QUOTES, 'UTF-8') ?>" <?= $pv === ($selectedFilters['process'] ?? '') ? 'selected' : '' ?>><?= htmlspecialchars($pv, ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <input type="hidden" name="season_id" value="<?= (int) ($selectedFilters['season_id'] ?? 0) ?>">
                <input type="hidden" name="cost_center_id" value="<?= (int) ($selectedFilters['cost_center_id'] ?? 0) ?>">
                <div class="dashboard-filter-status">
                    <span class="dashboard-loading" id="dashboard-loading" hidden>Cargando...</span>
                    <button class="secondary-button" type="submit">Actualizar</button>
                </div>
            </form>
        </section>

        <div class="stats-row">
            <article class="stat-card">
                <span class="stat-label">Producción total</span>
                <strong id="kpi-production-total"><?= htmlspecialchars(number_format((float) ($productionKpi['value'] ?? $metrics['production'] ?? 0), 0, ',', '.'), ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars($productionKpi['unit'] ?? 'kg', ENT_QUOTES, 'UTF-8') ?></strong>
                <small>Volumen total registrado en el periodo</small>
            </article>
            <article class="stat-card">
                <span class="stat-label">Costo operativo</span>
                <strong id="kpi-total-cost"><?= htmlspecialchars(number_format((float) ($totals['total_cost'] ?? 0), 0, ',', '.'), ENT_QUOTES, 'UTF-8') ?> CLP</strong>
                <small>Costos directos y de mano de obra</small>
            </article>
            <article class="stat-card">
                <span class="stat-label">Presupuesto ejecutado</span>
                <strong id="kpi-budget-executed"><?= htmlspecialchars(number_format((float) ($budgetKpi['value'] ?? 0), 1, ',', '.'), ENT_QUOTES, 'UTF-8') ?>%</strong>
                <small>Avance del presupuesto planificado</small>
            </article>
            <article class="stat-card">
                <span class="stat-label">Alertas críticas</span>
                <strong><?= htmlspecialchars(number_format((int) ($alerts[0]['count'] ?? 0), 0, ',', '.'), ENT_QUOTES, 'UTF-8') ?></strong>
                <small>Incidentes activos en el ERP</small>
            </article>
        </div>

        <div class="page-grid">
            <main class="main-column">
                <section class="section-card">
                    <div class="section-head">
                        <div>
                            <h2>Producción vs presupuesto</h2>
                            <p>Comparativa del periodo con ejecución real del plan.</p>
                        </div>
                        <span class="badge badge-neutral">Comparativo</span>
                    </div>
                    <div class="dashboard-chart-body">
                        <canvas id="productionBudgetChart" aria-label="Costos vs Presupuesto" role="img"></canvas>
                        <?php if ($costSeries === []): ?>
                            <p class="empty-state">Sin información para el período seleccionado</p>
                        <?php endif; ?>
                    </div>
                </section>

                <section class="section-card">
                    <div class="section-head">
                        <div>
                            <h2>Evolución temporal</h2>
                            <p>Tendencias de producción y costos en el periodo actual.</p>
                        </div>
                        <span class="badge badge-neutral">Tendencia</span>
                    </div>
                    <div class="dashboard-chart-body">
                        <canvas id="trendChart" aria-label="Evolución temporal de producción y costos" role="img"></canvas>
                        <?php if ($productionSeries === [] && $costSeries === []): ?>
                            <p class="empty-state">Sin información para el período seleccionado</p>
                        <?php endif; ?>
                    </div>
                </section>

                <section class="section-card">
                    <div class="section-head">
                        <div>
                            <h2>Costos por proceso</h2>
                            <p>Distribución de costos por unidad de proceso.</p>
                        </div>
                        <span class="badge badge-neutral">Detalle</span>
                    </div>
                    <div class="dashboard-chart-body">
                        <canvas id="costProcessChart" aria-label="Costos por proceso" role="img"></canvas>
                        <?php if ($costProcesses === []): ?>
                            <p class="empty-state">Sin información para el período seleccionado</p>
                        <?php endif; ?>
                    </div>
                    <?php if ($costProcesses !== []): ?>
                        <div class="card-list">
                            <?php foreach (array_slice($costProcesses, 0, 6) as $row): ?>
                                <article class="item-card">
                                    <div class="item-card-head">
                                        <div>
                                            <h3><?= htmlspecialchars((string) ($row['process'] ?? $row['category'] ?? 'Sin dato'), ENT_QUOTES, 'UTF-8') ?></h3>
                                            <p>Costos asociados</p>
                                        </div>
                                        <span class="badge badge-secondary">CLP</span>
                                    </div>
                                    <div class="item-card-meta">
                                        <strong><?= htmlspecialchars(number_format((float) ($row['total'] ?? 0), 0, ',', '.'), ENT_QUOTES, 'UTF-8') ?> CLP</strong>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>
            </main>

            <aside class="sidebar-column">
                <section class="section-card">
                    <div class="section-head">
                        <div>
                            <h2>Contexto del periodo</h2>
                            <p>Filtros seleccionados y estado del período.</p>
                        </div>
                    </div>
                    <div class="card-list">
                        <article class="item-card">
                            <span class="stat-label">Período</span>
                            <strong id="selected-period"><?= htmlspecialchars($selectedPeriod, ENT_QUOTES, 'UTF-8') ?></strong>
                        </article>
                        <article class="item-card">
                            <span class="stat-label">Fundo</span>
                            <strong id="selected-farm"><?= htmlspecialchars($selectedFarm, ENT_QUOTES, 'UTF-8') ?></strong>
                        </article>
                        <article class="item-card">
                            <span class="stat-label">Cuartel</span>
                            <strong id="selected-block"><?= htmlspecialchars($selectedBlock, ENT_QUOTES, 'UTF-8') ?></strong>
                        </article>
                        <article class="item-card">
                            <span class="stat-label">Proceso</span>
                            <strong id="selected-process"><?= htmlspecialchars($selectedProcess, ENT_QUOTES, 'UTF-8') ?></strong>
                        </article>
                    </div>
                </section>

                <section class="section-card">
                    <div class="section-head">
                        <div>
                            <h2>Alertas críticas</h2>
                            <p>Incidencias que requieren atención inmediata.</p>
                        </div>
                    </div>
                    <div class="dashboard-section-list">
                        <?php if ($alerts !== []): ?>
                            <?php foreach ($alerts as $alert): ?>
                                <article class="item-card">
                                    <div class="item-card-head">
                                        <div>
                                            <h3><?= htmlspecialchars($alert['title'] ?? 'Alerta', ENT_QUOTES, 'UTF-8') ?></h3>
                                            <p><?= htmlspecialchars((string) (($alert['count'] ?? 0)) . ' items', ENT_QUOTES, 'UTF-8') ?></p>
                                        </div>
                                        <span class="badge <?= htmlspecialchars($alert['severity'] === 'warning' ? 'badge-secondary' : ($alert['severity'] === 'critical' ? 'badge-danger' : 'badge-neutral'), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(strtoupper((string) ($alert['severity'] ?? 'NORMAL')), ENT_QUOTES, 'UTF-8') ?></span>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="empty-state">No hay alertas críticas en este periodo.</p>
                        <?php endif; ?>
                    </div>
                </section>

                <section class="section-card">
                    <div class="section-head">
                        <div>
                            <h2>Indicadores operacionales</h2>
                            <p>Estado de recursos y tareas en el periodo.</p>
                        </div>
                    </div>
                    <div class="card-list">
                        <article class="item-card">
                            <span class="stat-label">Fincas activas</span>
                            <strong><?= htmlspecialchars(number_format((int) ($metrics['farms'] ?? 0), 0, ',', '.'), ENT_QUOTES, 'UTF-8') ?></strong>
                        </article>
                        <article class="item-card">
                            <span class="stat-label">Cuarteles activos</span>
                            <strong><?= htmlspecialchars(number_format((int) ($metrics['blocks'] ?? 0), 0, ',', '.'), ENT_QUOTES, 'UTF-8') ?></strong>
                        </article>
                        <article class="item-card">
                            <span class="stat-label">Trabajadores activos</span>
                            <strong><?= htmlspecialchars(number_format((int) ($metrics['workers'] ?? 0), 0, ',', '.'), ENT_QUOTES, 'UTF-8') ?></strong>
                        </article>
                        <article class="item-card">
                            <span class="stat-label">Órdenes pendientes</span>
                            <strong><?= htmlspecialchars(number_format((int) ($operational['pending_orders'] ?? 0), 0, ',', '.'), ENT_QUOTES, 'UTF-8') ?></strong>
                        </article>
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
