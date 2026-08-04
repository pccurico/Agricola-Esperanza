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

        <section class="dashboard-context-row">
            <div class="dashboard-context-pill">
                <span>Período seleccionado</span>
                <strong id="selected-period"><?= htmlspecialchars($selectedPeriod, ENT_QUOTES, 'UTF-8') ?></strong>
            </div>
            <div class="dashboard-context-pill">
                <span>Fundo</span>
                <strong id="selected-farm"><?= htmlspecialchars($selectedFarm, ENT_QUOTES, 'UTF-8') ?></strong>
            </div>
            <div class="dashboard-context-pill">
                <span>Cuartel</span>
                <strong id="selected-block"><?= htmlspecialchars($selectedBlock, ENT_QUOTES, 'UTF-8') ?></strong>
            </div>
            <div class="dashboard-context-pill">
                <span>Proceso</span>
                <strong id="selected-process"><?= htmlspecialchars($selectedProcess, ENT_QUOTES, 'UTF-8') ?></strong>
            </div>
        </section>

        <?php if ($kpis !== []): ?>
            <section class="dashboard-kpi-row">
                <?php foreach ($kpis as $widget): ?>
                    <?php $trend = strtolower((string) ($widget['metadata']['trend'] ?? $widget['trend'] ?? '')); ?>
                    <?php $direction = $trend === 'up' ? '▲' : ($trend === 'down' || $trend === 'warning' ? '▼' : '→'); ?>
                    <?php $statusClass = $trend === 'up' ? 'positive' : ($trend === 'down' ? 'negative' : 'neutral'); ?>
                    <?php $iconLabel = strtoupper(substr((string) ($widget['module'] ?? $widget['label'] ?? 'BI'), 0, 2)); ?>
                    <article class="dashboard-kpi-card">
                        <div class="dashboard-kpi-card-head">
                            <div class="dashboard-kpi-card-icon"><?= htmlspecialchars($iconLabel, ENT_QUOTES, 'UTF-8') ?></div>
                            <span class="dashboard-kpi-chip"><?= htmlspecialchars(strtoupper((string) ($widget['module'] ?? 'GENERAL')), ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                        <div class="dashboard-kpi-card-body">
                            <p><?= htmlspecialchars($widget['title'] ?? $widget['label'] ?? 'Indicador', ENT_QUOTES, 'UTF-8') ?></p>
                            <strong class="dashboard-kpi-card-value">
                                <?= htmlspecialchars(is_numeric($widget['value']) ? number_format((float) $widget['value'], ($widget['unit'] ?? '') === '%' ? 1 : (((float) abs($widget['value']) >= 1000 || floor((float) abs($widget['value'])) === (float) abs($widget['value'])) ? 0 : 2), ',', '.') : (string) $widget['value'], ENT_QUOTES, 'UTF-8') ?>
                                <?= !empty($widget['unit']) ? htmlspecialchars($widget['unit'], ENT_QUOTES, 'UTF-8') : '' ?>
                            </strong>
                        </div>
                        <div class="dashboard-kpi-card-meta">
                            <span class="dashboard-kpi-trend <?= $statusClass ?>"><?= $direction ?> <?= htmlspecialchars((string) ($widget['metadata']['note'] ?? $widget['metadata']['detail'] ?? '')) ?></span>
                            <?php if (!empty($widget['module'])): ?>
                                <a class="dashboard-kpi-link" href="?module=<?= htmlspecialchars($widget['module'], ENT_QUOTES, 'UTF-8') ?>">Ver módulo</a>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>

        <section class="dashboard-section-grid">
            <article class="dashboard-section-card">
                <header class="dashboard-section-header">
                    <div>
                        <h2>Producción vs presupuesto</h2>
                        <small>Comparativa del periodo con ejecución real del plan</small>
                    </div>
                </header>
                <div class="dashboard-compare-row">
                    <div class="dashboard-compare-metric">
                        <span>Total producción</span>
                        <strong id="kpi-production-total"><?= htmlspecialchars(number_format((float) ($productionKpi['value'] ?? $metrics['production'] ?? 0), 0, ',', '.'), ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars($productionKpi['unit'] ?? 'kg', ENT_QUOTES, 'UTF-8') ?></strong>
                    </div>
                    <div class="dashboard-compare-metric">
                        <span>Costo operativo</span>
                        <strong id="kpi-total-cost"><?= htmlspecialchars(number_format((float) ($totals['total_cost'] ?? 0), 0, ',', '.'), ENT_QUOTES, 'UTF-8') ?> CLP</strong>
                    </div>
                    <div class="dashboard-compare-metric">
                        <span>Presupuesto ejecutado</span>
                        <strong id="kpi-budget-executed"><?= htmlspecialchars(number_format((float) ($budgetKpi['value'] ?? 0), 1, ',', '.'), ENT_QUOTES, 'UTF-8') ?>%</strong>
                    </div>
                </div>
                <div class="dashboard-chart-frame">
                    <?php if ($costSeries !== []): ?>
                        <div class="dashboard-mini-chart">
                            <span>Costos vs Presupuesto</span>
                            <div class="dashboard-chart-body">
                                <canvas id="productionBudgetChart" aria-label="Costos vs Presupuesto" role="img"></canvas>
                            </div>
                        </div>
                    <?php else: ?>
                        <p class="empty-state">No hay datos suficientes para mostrar la comparativa.</p>
                    <?php endif; ?>
                </div>
            </article>

            <article class="dashboard-section-card">
                <header class="dashboard-section-header">
                    <div>
                        <h2>Costos por proceso</h2>
                        <small>Principales componentes de costo en el periodo</small>
                    </div>
                </header>
                <div class="dashboard-chart-body">
                    <?php if ($costProcesses !== []): ?>
                        <canvas id="costProcessChart" aria-label="Costos por proceso" role="img"></canvas>
                    <?php else: ?>
                        <p class="empty-state">No hay costos categorizados en este periodo.</p>
                    <?php endif; ?>
                </div>
                <div class="dashboard-section-list" id="cost-process-list">
                    <?php if ($costProcesses !== []): ?>
                        <?php foreach (array_slice($costProcesses, 0, 6) as $row): ?>
                            <div class="dashboard-list-row">
                                <span><?= htmlspecialchars((string) ($row['process'] ?? $row['category'] ?? 'Sin dato'), ENT_QUOTES, 'UTF-8') ?></span>
                                <strong><?= htmlspecialchars(number_format((float) ($row['total'] ?? 0), 0, ',', '.'), ENT_QUOTES, 'UTF-8') ?> CLP</strong>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="empty-state">No hay costos categorizados en este periodo.</p>
                    <?php endif; ?>
                </div>
            </article>

            <article class="dashboard-section-card dashboard-chart-group">
                <header class="dashboard-section-header">
                    <div>
                        <h2>Evolución temporal</h2>
                        <small>Tendencias de producción y costos en el periodo</small>
                    </div>
                </header>
                <?php if ($productionSeries !== [] || $costSeries !== []): ?>
                    <div class="dashboard-chart-body">
                        <canvas id="trendChart" aria-label="Evolución temporal de producción y costos" role="img"></canvas>
                    </div>
                <?php else: ?>
                    <p class="empty-state">No hay indicadores temporales definidos en este dashboard.</p>
                <?php endif; ?>
            </article>
        </section>

        <section class="dashboard-bottom-grid">
            <article class="dashboard-section-card">
                <header class="dashboard-section-header">
                    <div>
                        <h2>Alertas críticas</h2>
                        <small>Incidencias que requieren atención inmediata</small>
                    </div>
                </header>
                <div class="dashboard-section-list">
                    <?php if ($alerts !== []): ?>
                        <?php foreach ($alerts as $alert): ?>
                            <div class="dashboard-list-row">
                                <div>
                                    <strong><?= htmlspecialchars($alert['title'] ?? 'Alerta', ENT_QUOTES, 'UTF-8') ?></strong>
                                    <small><?= htmlspecialchars((string) ((int) ($alert['count'] ?? 0)) . ' items', ENT_QUOTES, 'UTF-8') ?></small>
                                </div>
                                <span class="dashboard-status-pill <?= htmlspecialchars((string) ($alert['severity'] ?? 'normal'), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(strtoupper((string) ($alert['severity'] ?? 'normal')), ENT_QUOTES, 'UTF-8') ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="empty-state">No hay alertas críticas en este periodo.</p>
                    <?php endif; ?>
                </div>
            </article>

            <article class="dashboard-section-card">
                <header class="dashboard-section-header">
                    <div>
                        <h2>Actividad reciente</h2>
                        <small>Movimientos y transacciones recientes del ERP</small>
                    </div>
                </header>
                <div class="dashboard-section-list">
                    <?php if ($recentActivities !== []): ?>
                        <?php foreach (array_slice($recentActivities, 0, 8) as $row): ?>
                            <div class="dashboard-list-row">
                                <div>
                                    <strong><?= htmlspecialchars((string) ($row['label'] ?? $row['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
                                    <small><?= htmlspecialchars((string) ($row['type'] ?? $row['status'] ?? ''), ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars((string) ($row['date'] ?? ''), ENT_QUOTES, 'UTF-8') ?></small>
                                </div>
                                <strong><?= htmlspecialchars(is_numeric($row['value']) ? number_format((float) $row['value'], 0, ',', '.') : (string) $row['value'], ENT_QUOTES, 'UTF-8') ?></strong>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="empty-state">No hay actividad reciente para mostrar.</p>
                    <?php endif; ?>
                </div>
            </article>

            <article class="dashboard-section-card dashboard-operational-panel">
                <header class="dashboard-section-header">
                    <div>
                        <h2>Indicadores operacionales</h2>
                        <small>Estado real de tareas, órdenes y recursos</small>
                    </div>
                </header>
                <div class="dashboard-operational-grid">
                    <div class="dashboard-value-card">
                        <span>Fincas activas</span>
                        <strong><?= htmlspecialchars(number_format((int) ($metrics['farms'] ?? 0), 0, ',', '.'), ENT_QUOTES, 'UTF-8') ?></strong>
                    </div>
                    <div class="dashboard-value-card">
                        <span>Cuarteles activos</span>
                        <strong><?= htmlspecialchars(number_format((int) ($metrics['blocks'] ?? 0), 0, ',', '.'), ENT_QUOTES, 'UTF-8') ?></strong>
                    </div>
                    <div class="dashboard-value-card">
                        <span>Trabajadores activos</span>
                        <strong><?= htmlspecialchars(number_format((int) ($metrics['workers'] ?? 0), 0, ',', '.'), ENT_QUOTES, 'UTF-8') ?></strong>
                    </div>
                    <div class="dashboard-value-card">
                        <span>Tareas abiertas</span>
                        <strong><?= htmlspecialchars(number_format((int) ($operational['pending_tasks'] ?? 0), 0, ',', '.'), ENT_QUOTES, 'UTF-8') ?></strong>
                    </div>
                    <div class="dashboard-value-card">
                        <span>Órdenes pendientes</span>
                        <strong><?= htmlspecialchars(number_format((int) ($operational['pending_orders'] ?? 0), 0, ',', '.'), ENT_QUOTES, 'UTF-8') ?></strong>
                    </div>
                    <div class="dashboard-value-card">
                        <span>Solicitudes abiertas</span>
                        <strong><?= htmlspecialchars(number_format((int) ($operational['open_requests'] ?? 0), 0, ',', '.'), ENT_QUOTES, 'UTF-8') ?></strong>
                    </div>
                </div>
            </article>
        </section>
    </section>
</main>

<script>
    window.dashboardData = <?= $dashboardJson ?>;
</script>
<script src="assets/js/chart.min.js" defer></script>
<script src="assets/js/dashboard.js" defer></script>
</body>
</html>
