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
$recentActivities = $dashboard['recent'] ?? [];
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

        <details class="admin-panel report-filter-panel" open>
            <summary>
                <span>
                    <b>Filtros del dashboard</b>
                    <small><?= ((int) ($selectedFilters['farm_id'] ?? 0) || (int) ($selectedFilters['block_id'] ?? 0) || trim((string) ($selectedFilters['process'] ?? '')) !== '') ? 'Filtros activos aplicados' : 'Todos los registros' ?></small>
                </span>
                <i aria-hidden="true"></i>
            </summary>
            <form class="report-filter-grid" method="get">
                <label>Empresa<span><?= htmlspecialchars($companyName, ENT_QUOTES, 'UTF-8') ?></span></label>
                <label>Desde<input type="date" name="date_from" value="<?= htmlspecialchars($selectedFilters['date_from'] ?? '', ENT_QUOTES, 'UTF-8') ?>"></label>
                <label>Hasta<input type="date" name="date_to" value="<?= htmlspecialchars($selectedFilters['date_to'] ?? '', ENT_QUOTES, 'UTF-8') ?>"></label>
                <label>Fundo<select name="farm_id"><option value="0">Todos</option><?php foreach (($filterOptions['farms'] ?? []) as $farm): ?><option value="<?= (int) $farm['id'] ?>" <?= (int) $selectedFilters['farm_id'] === (int) $farm['id'] ? 'selected' : '' ?>><?= htmlspecialchars($farm['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
                <label>Cuartel<select name="block_id"><option value="0">Todos</option><?php foreach (($filterOptions['blocks'] ?? []) as $block): ?><option value="<?= (int) $block['id'] ?>" <?= (int) $selectedFilters['block_id'] === (int) $block['id'] ? 'selected' : '' ?>><?= htmlspecialchars($block['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
                <label>Proceso<select name="process"><option value="">Todos</option><?php foreach (($filterOptions['processes'] ?? []) as $p): $pv = (string) ($p['process'] ?? ''); ?><option value="<?= htmlspecialchars($pv, ENT_QUOTES, 'UTF-8') ?>" <?= $pv === ($selectedFilters['process'] ?? '') ? 'selected' : '' ?>><?= htmlspecialchars($pv, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
                <button class="primary-button" type="submit">Aplicar</button>
            </form>
        </details>

        <section class="report-kpi-grid">
            <?php foreach (array_slice($kpis, 0, 4) as $kpi): ?>
                <article class="report-kpi">
                    <span><?= htmlspecialchars($kpi['label'] ?? '-', ENT_QUOTES, 'UTF-8') ?></span>
                    <strong><?= htmlspecialchars(number_format((float) ($kpi['value'] ?? 0), 0, ',', '.'), ENT_QUOTES, 'UTF-8') ?><?= !empty($kpi['unit']) ? ' ' . htmlspecialchars($kpi['unit'], ENT_QUOTES, 'UTF-8') : '' ?></strong>
                    <?php if (!empty($kpi['note'])): ?><small><?= htmlspecialchars($kpi['note'], ENT_QUOTES, 'UTF-8') ?></small><?php endif; ?>
                </article>
            <?php endforeach; ?>
        </section>

        <?php if (!empty($analyses)): ?>
            <section class="admin-columns">
                <article class="admin-panel">
                    <header class="panel-header"><h2>Hallazgos</h2></header>
                    <div class="panel-body">
                        <ul class="simple-list">
                            <?php foreach ($analyses as $analysis): ?><li><?= htmlspecialchars($analysis, ENT_QUOTES, 'UTF-8') ?></li><?php endforeach; ?>
                        </ul>
                    </div>
                </article>
            </section>
        <?php endif; ?>

        <?php if (!empty($chartDefinitions)): ?>
            <?php $mainChart = $chartDefinitions[0]; ?>
            <section class="admin-columns">
                <article class="admin-panel">
                    <header class="panel-header"><h2><?= htmlspecialchars($mainChart['title'] ?? 'Tendencia principal', ENT_QUOTES, 'UTF-8') ?></h2></header>
                    <div class="panel-body report-bars">
                        <?php
                            $labels = $mainChart['data']['labels'] ?? [];
                            $dataset = $mainChart['data']['datasets'][0] ?? ['data' => []];
                            $values = is_array($dataset['data'] ?? null) ? $dataset['data'] : [];
                            $maxValue = $values !== [] ? max(array_map('floatval', $values)) : 0;
                        ?>
                        <?php if ($values !== [] && $maxValue > 0): ?>
                            <?php foreach ($values as $index => $value): ?>
                                <div class="report-bar-row">
                                    <small><?= htmlspecialchars($labels[$index] ?? '', ENT_QUOTES, 'UTF-8') ?></small>
                                    <div class="report-bar"><i style="width: <?= htmlspecialchars((string) min(100, max(0, ((float) $value / $maxValue) * 100)), ENT_QUOTES, 'UTF-8') ?>%;"></i></div>
                                    <b><?= htmlspecialchars(number_format((float) $value, 0, ',', '.'), ENT_QUOTES, 'UTF-8') ?></b>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="empty-state">No hay datos de tendencia para el período seleccionado.</p>
                        <?php endif; ?>
                    </div>
                </article>
            </section>
            <?php if (count($chartDefinitions) > 1): ?>
                <section class="dashboard-chart-grid">
                    <?php foreach (array_slice($chartDefinitions, 1) as $chart): ?>
                        <article class="admin-panel">
                            <header class="panel-header"><h2><?= htmlspecialchars($chart['title'] ?? 'Análisis', ENT_QUOTES, 'UTF-8') ?></h2></header>
                            <div class="panel-body report-bars">
                                <?php
                                    $labels = $chart['data']['labels'] ?? [];
                                    $dataset = $chart['data']['datasets'][0] ?? ['data' => []];
                                    $values = is_array($dataset['data'] ?? null) ? $dataset['data'] : [];
                                    $maxValue = $values !== [] ? max(array_map('floatval', $values)) : 0;
                                ?>
                                <?php if ($values !== [] && $maxValue > 0): ?>
                                    <?php foreach ($values as $index => $value): ?>
                                        <div class="report-bar-row">
                                            <small><?= htmlspecialchars($labels[$index] ?? '', ENT_QUOTES, 'UTF-8') ?></small>
                                            <div class="report-bar"><i style="width: <?= htmlspecialchars((string) min(100, max(0, ((float) $value / $maxValue) * 100)), ENT_QUOTES, 'UTF-8') ?>%;"></i></div>
                                            <b><?= htmlspecialchars(number_format((float) $value, 0, ',', '.'), ENT_QUOTES, 'UTF-8') ?></b>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="empty-state">No hay datos de tendencia adicionales.</p>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </section>
            <?php endif; ?>
        <?php endif; ?>

        <section class="admin-columns report-columns">
            <?php if (!empty($sections['production'])): $prod = $sections['production']; ?>
                <article class="admin-panel">
                    <header class="panel-header"><h2>Producción</h2></header>
                    <div class="panel-body simple-list">
                        <?php foreach (array_slice($prod['by_farm'] ?? [], 0, 5) as $row): ?>
                            <div><?= htmlspecialchars($row['farm'] ?? '-', ENT_QUOTES, 'UTF-8') ?>: <?= htmlspecialchars(number_format((float) ($row['total'] ?? 0), 0, ',', '.'), ENT_QUOTES, 'UTF-8') ?> kg</div>
                        <?php endforeach; ?>
                    </div>
                </article>
            <?php endif; ?>

            <?php if (!empty($sections['warehouse'])): $warehouse = $sections['warehouse']; ?>
                <article class="admin-panel">
                    <header class="panel-header"><h2>Bodega</h2></header>
                    <div class="panel-body simple-list">
                        <?php foreach (array_slice($warehouse['critical_stock'] ?? [], 0, 5) as $row): ?>
                            <div><?= htmlspecialchars($row['name'] ?? '-', ENT_QUOTES, 'UTF-8') ?>: <?= htmlspecialchars(number_format((float) ($row['stock'] ?? 0), 0, ',', '.'), ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars($row['unit'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                        <?php endforeach; ?>
                    </div>
                </article>
            <?php endif; ?>

            <?php if (!empty($sections['accounting'])): $acc = $sections['accounting']; ?>
                <article class="admin-panel">
                    <header class="panel-header"><h2>Contabilidad</h2></header>
                    <div class="report-kpi-grid">
                        <article class="report-kpi"><span>Gastos</span><strong><?= htmlspecialchars(number_format((float) ($acc['expenses'] ?? 0), 0, ',', '.'), ENT_QUOTES, 'UTF-8') ?> CLP</strong></article>
                        <article class="report-kpi"><span>Costo laboral</span><strong><?= htmlspecialchars(number_format((float) ($acc['labor_cost'] ?? 0), 0, ',', '.'), ENT_QUOTES, 'UTF-8') ?> CLP</strong></article>
                        <article class="report-kpi"><span>Facturas</span><strong><?= htmlspecialchars(number_format(count($acc['purchase_invoices'] ?? []), 0, ',', '.'), ENT_QUOTES, 'UTF-8') ?></strong><small>Compras registradas</small></article>
                    </div>
                </article>
            <?php endif; ?>
        </section>

        <section class="admin-columns">
            <article class="admin-panel">
                <header class="panel-header"><h2>Visión de alertas</h2></header>
                <div class="panel-body">
                    <?php if (!empty($alerts)): ?>
                        <ul class="simple-list">
                            <?php foreach ($alerts as $alert): ?>
                                <li>
                                    <strong><?= htmlspecialchars($alert['title'] ?? 'Alerta', ENT_QUOTES, 'UTF-8') ?></strong>
                                    <?php if (!empty($alert['count'])): ?> - <?= htmlspecialchars((int) $alert['count'], ENT_QUOTES, 'UTF-8') ?> registros<?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="setup-copy">No hay alertas activas en el periodo seleccionado.</p>
                    <?php endif; ?>
                </div>
            </article>

            <article class="admin-panel">
                <header class="panel-header"><h2>Acciones</h2></header>
                <div class="panel-body admin-form">
                    <form method="post">
                        <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="action" value="save_dashboard_view">
                        <label>Nombre de vista<input type="text" name="view_name" placeholder="Mi vista personal"></label>
                        <label>Desde<input type="date" name="date_from" value="<?= htmlspecialchars($selectedFilters['date_from'] ?? '', ENT_QUOTES, 'UTF-8') ?>"></label>
                        <label>Hasta<input type="date" name="date_to" value="<?= htmlspecialchars($selectedFilters['date_to'] ?? '', ENT_QUOTES, 'UTF-8') ?>"></label>
                        <label>Fundo<select name="farm_id"><option value="0">Todos</option><?php foreach (($filterOptions['farms'] ?? []) as $farm): ?><option value="<?= (int) $farm['id'] ?>" <?= (int) $selectedFilters['farm_id'] === (int) $farm['id'] ? 'selected' : '' ?>><?= htmlspecialchars($farm['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
                        <label>Cuartel<select name="block_id"><option value="0">Todos</option><?php foreach (($filterOptions['blocks'] ?? []) as $block): ?><option value="<?= (int) $block['id'] ?>" <?= (int) $selectedFilters['block_id'] === (int) $block['id'] ? 'selected' : '' ?>><?= htmlspecialchars($block['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
                        <button class="primary-button" type="submit">Guardar vista</button>
                    </form>
                    <form method="post" onsubmit="return confirm('Restablecer layout por defecto?')">
                        <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="action" value="reset_dashboard">
                        <button class="secondary-button" type="submit">Restablecer dashboard</button>
                    </form>
                </div>
            </article>
        </section>
    </section>
</main>

</body>
</html>
