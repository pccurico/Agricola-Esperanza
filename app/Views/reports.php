<?php

declare(strict_types=1);

$reportSummary = $summary ?? [];
$money = static fn (mixed $value): string => '$' . number_format((float) $value, 0, ',', '.');
$number = static fn (mixed $value, int $decimals = 0): string => number_format((float) $value, $decimals, ',', '.');
$reportType = (string) ($report_type ?? $reportSummary['report_type'] ?? 'executive');
$reportFilters = $filters ?? [];
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
$reportBlueprint = $reportSummary['report_blueprint'] ?? [];
$reportFocus = $reportSummary['report_focus'] ?? [];
$reportBlueprintQuestion = (string) ($reportBlueprint['question'] ?? '¿Cómo está la operación hoy?');
$reportBlueprintAnswer = (string) ($reportBlueprint['answer'] ?? 'Resumen del informe.');
$reportFocusTags = (array) ($reportBlueprint['focus'] ?? []);
$visualRatio = static function (string $key): int {
    return match ($key) {
        'production' => 78,
        'cost_per_unit' => 64,
        'production_per_hectare' => 72,
        'jornadas' => 58,
        'total_cost' => 82,
        'centers' => 60,
        'cost_per_hectare' => 66,
        'budget' => 74,
        'labor_cost' => 70,
        'workers' => 54,
        'alerts_critical' => 48,
        'stock_minimum' => 52,
        'total_value' => 68,
        'orders_open' => 46,
        'suppliers' => 56,
        'execution' => 72,
        'profitability' => 62,
        default => 56,
    };
};
switch ($reportType) {
    case 'production':
        $reportSummaryCards = [
            ['key' => 'production', 'label' => 'Producción', 'value' => $number($reportSummary['production'] ?? 0, 2), 'detail' => $reportSummary['production_unit'] ?? 'unidades'],
            ['key' => 'cost_per_unit', 'label' => 'Costo por unidad', 'value' => $money($reportSummary['cost_per_unit'] ?? 0), 'detail' => 'Eficiencia productiva'],
            ['key' => 'production_per_hectare', 'label' => 'Producción/Ha', 'value' => $number($reportSummary['production_per_hectare'] ?? 0, 2), 'detail' => 'Kg por hectárea'],
            ['key' => 'jornadas', 'label' => 'Jornadas', 'value' => $number($laborData['quantity'] ?? 0, 2), 'detail' => 'Total jornadas'],
        ];
        break;
    case 'costs':
        $reportSummaryCards = [
            ['key' => 'total_cost', 'label' => 'Costo total', 'value' => $money($reportSummary['total'] ?? 0), 'detail' => 'Costos + mano de obra'],
            ['key' => 'centers', 'label' => 'Centros de gasto', 'value' => count($centers ?? []), 'detail' => 'Líneas activas'],
            ['key' => 'cost_per_hectare', 'label' => 'Costos por Ha', 'value' => $money($reportSummary['cost_per_hectare'] ?? 0), 'detail' => 'Costo por hectárea'],
            ['key' => 'budget', 'label' => 'Presupuesto', 'value' => $money($budgetData['planned'] ?? 0), 'detail' => 'Planificado'],
        ];
        break;
    case 'labor':
        $reportSummaryCards = [
            ['key' => 'labor_cost', 'label' => 'Costo laboral', 'value' => $money($laborData['total'] ?? 0), 'detail' => 'Total pagado'],
            ['key' => 'jornadas', 'label' => 'Jornadas', 'value' => $number($laborData['quantity'] ?? 0, 2), 'detail' => 'Total jornadas'],
            ['key' => 'workers', 'label' => 'Trabajadores', 'value' => (int) ($laborData['workers'] ?? 0), 'detail' => 'Activos'],
            ['key' => 'productivity', 'label' => 'Productividad', 'value' => $number($reportSummary['labor_productivity'] ?? 0, 2), 'detail' => 'Kg por jornada'],
        ];
        break;
    case 'inventory':
        $reportSummaryCards = [
            ['key' => 'alerts_critical', 'label' => 'Alertas críticas', 'value' => (int) ($reportSummary['alert_count'] ?? 0), 'detail' => 'Items por revisar'],
            ['key' => 'stock_minimum', 'label' => 'Stock mínimo', 'value' => count($alerts), 'detail' => 'Registros activos'],
            ['key' => 'total_value', 'label' => 'Valor total', 'value' => $money($reportSummary['total'] ?? 0), 'detail' => 'Costo estimado'],
            ['key' => 'centers', 'label' => 'Centros', 'value' => count($centers ?? []), 'detail' => 'Centros de costo'],
        ];
        break;
    case 'procurement':
        $reportSummaryCards = [
            ['key' => 'orders_open', 'label' => 'Órdenes abiertas', 'value' => $reportSummary['orders_open'] ?? 0, 'detail' => 'Pedidos sin recibir'],
            ['key' => 'total_cost', 'label' => 'Costo total', 'value' => $money($reportSummary['total'] ?? 0), 'detail' => 'Costos de compras'],
            ['key' => 'suppliers', 'label' => 'Proveedores', 'value' => count($processes ?? []), 'detail' => 'Activos'],
            ['key' => 'budget', 'label' => 'Presupuesto', 'value' => $money($budgetData['actual'] ?? 0), 'detail' => 'Ejecutado'],
        ];
        break;
    case 'finance':
        $reportSummaryCards = [
            ['key' => 'total_cost', 'label' => 'Gastos totales', 'value' => $money($reportSummary['total'] ?? 0), 'detail' => 'Costos y compras'],
            ['key' => 'budget', 'label' => 'Presupuesto ejecutado', 'value' => $money($budgetData['actual'] ?? 0), 'detail' => 'Saldo real'],
            ['key' => 'execution', 'label' => 'Ejecución', 'value' => $number($budgetData['execution'] ?? 0, 1) . '%', 'detail' => 'Porcentaje'],
            ['key' => 'centers', 'label' => 'Centros', 'value' => count($centers ?? []), 'detail' => 'Cuentas activas'],
        ];
        break;
    default:
        $reportSummaryCards = [
            ['key' => 'total_cost', 'label' => 'Costo total', 'value' => $money($reportSummary['total'] ?? 0), 'detail' => 'Costos + mano de obra'],
            ['key' => 'production', 'label' => 'Producción', 'value' => $number($reportSummary['production'] ?? 0, 2), 'detail' => $reportSummary['production_unit'] ?? 'unidades'],
            ['key' => 'cost_per_unit', 'label' => 'Costo por unidad', 'value' => $money($reportSummary['cost_per_unit'] ?? 0), 'detail' => 'Eficiencia productiva'],
            ['key' => 'profitability', 'label' => 'Rentabilidad', 'value' => $number($reportSummary['profitability'] ?? 0, 2), 'detail' => 'Producción/Costos'],
        ];
        break;
}
$reportSummaryCards = array_map(static function (array $card) use ($visualRatio): array {
    $card['bar_value'] = $visualRatio((string) ($card['key'] ?? 'default'));
    return $card;
}, $reportSummaryCards);
$reportHighlightCards = match ($reportType) {
    'production' => [
        ['key' => 'production_per_hectare', 'label' => 'Producción por hectárea', 'value' => $number($reportSummary['production_per_hectare'] ?? 0, 2), 'detail' => 'Rendimiento agrícola', 'bar_value' => 78],
        ['key' => 'labor_productivity', 'label' => 'Productividad laboral', 'value' => $number($reportSummary['labor_productivity'] ?? 0, 2), 'detail' => 'Kg por jornada', 'bar_value' => 70],
        ['key' => 'production_entries', 'label' => 'Registros de producción', 'value' => (int) ($reportSummary['production_entries'] ?? 0), 'detail' => 'Movimientos cargados', 'bar_value' => 64],
        ['key' => 'hectares', 'label' => 'Superficie', 'value' => $number($reportSummary['hectares'] ?? 0, 2) . ' Ha', 'detail' => 'Área considerada', 'bar_value' => 58],
    ],
    'costs' => [
        ['key' => 'total_cost', 'label' => 'Costo total', 'value' => $money($reportSummary['total'] ?? 0), 'detail' => 'Costos y mano de obra', 'bar_value' => 82],
        ['key' => 'cost_per_unit', 'label' => 'Costo por unidad', 'value' => $money($reportSummary['cost_per_unit'] ?? 0), 'detail' => 'Eficiencia operativa', 'bar_value' => 68],
        ['key' => 'cost_per_hectare', 'label' => 'Costo por hectárea', 'value' => $money($reportSummary['cost_per_hectare'] ?? 0), 'detail' => 'Costo por superficie', 'bar_value' => 72],
        ['key' => 'budget_variance', 'label' => 'Variación presupuestaria', 'value' => $money(abs((float) ($budgetData['variance'] ?? 0))), 'detail' => (($budgetData['variance'] ?? 0) >= 0 ? 'Saldo favorable' : 'Sobre ejecución'), 'bar_value' => 66],
    ],
    'inventory' => [
        ['key' => 'alert_count', 'label' => 'Alertas críticas', 'value' => (int) ($reportSummary['alert_count'] ?? 0), 'detail' => 'Items por revisar', 'bar_value' => 74],
        ['key' => 'stock_minimum', 'label' => 'Stock mínimo', 'value' => count($alerts), 'detail' => 'Registros bajo umbral', 'bar_value' => 62],
        ['key' => 'total_value', 'label' => 'Valor estimado', 'value' => $money($reportSummary['total'] ?? 0), 'detail' => 'Costo del inventario', 'bar_value' => 58],
        ['key' => 'production_entries', 'label' => 'Movimientos', 'value' => (int) ($reportSummary['production_entries'] ?? 0), 'detail' => 'Movimientos de stock', 'bar_value' => 54],
    ],
    'labor' => [
        ['key' => 'labor_cost', 'label' => 'Costo laboral', 'value' => $money($laborData['total'] ?? 0), 'detail' => 'Total pagado', 'bar_value' => 78],
        ['key' => 'jornadas', 'label' => 'Jornadas', 'value' => $number($laborData['quantity'] ?? 0, 2), 'detail' => 'Total registradas', 'bar_value' => 72],
        ['key' => 'workers', 'label' => 'Trabajadores', 'value' => (int) ($laborData['workers'] ?? 0), 'detail' => 'Activos', 'bar_value' => 64],
        ['key' => 'labor_productivity', 'label' => 'Productividad', 'value' => $number($reportSummary['labor_productivity'] ?? 0, 2), 'detail' => 'Kg por jornada', 'bar_value' => 68],
    ],
    'procurement' => [
        ['key' => 'orders_open', 'label' => 'Órdenes abiertas', 'value' => $reportSummary['orders_open'] ?? 0, 'detail' => 'Pendientes', 'bar_value' => 66],
        ['key' => 'total_cost', 'label' => 'Compras', 'value' => $money($reportSummary['total'] ?? 0), 'detail' => 'Costos de abastecimiento', 'bar_value' => 72],
        ['key' => 'suppliers', 'label' => 'Proveedores', 'value' => count($processes ?? []), 'detail' => 'Activos', 'bar_value' => 58],
        ['key' => 'budget', 'label' => 'Presupuesto', 'value' => $money($budgetData['actual'] ?? 0), 'detail' => 'Ejecutado', 'bar_value' => 64],
    ],
    'finance' => [
        ['key' => 'total_cost', 'label' => 'Gastos totales', 'value' => $money($reportSummary['total'] ?? 0), 'detail' => 'Costos y compras', 'bar_value' => 80],
        ['key' => 'budget', 'label' => 'Presupuesto', 'value' => $money($budgetData['planned'] ?? 0), 'detail' => 'Planificado', 'bar_value' => 74],
        ['key' => 'execution', 'label' => 'Ejecución', 'value' => $number($budgetData['execution'] ?? 0, 1) . '%', 'detail' => 'Porcentaje del plan', 'bar_value' => 70],
        ['key' => 'centers', 'label' => 'Centros de costo', 'value' => count($centers ?? []), 'detail' => 'Cuentas activas', 'bar_value' => 66],
    ],
    default => [
        ['key' => 'production_per_hectare', 'label' => 'Producción por hectárea', 'value' => $number($reportSummary['production_per_hectare'] ?? 0, 2), 'detail' => 'Indicador agrícola efectivo', 'bar_value' => 72],
        ['key' => 'labor_productivity', 'label' => 'Productividad laboral', 'value' => $number($reportSummary['labor_productivity'] ?? 0, 2), 'detail' => 'Kg por jornada', 'bar_value' => 68],
        ['key' => 'profitability', 'label' => 'Rentabilidad', 'value' => $number($reportSummary['profitability'] ?? 0, 2), 'detail' => 'Producción por costo', 'bar_value' => 62],
        ['key' => 'alert_count', 'label' => 'Alertas de stock', 'value' => (int) ($reportSummary['alert_count'] ?? 0), 'detail' => 'Crítico o bajo mínimo', 'bar_value' => 46],
        ['key' => 'planned_budget', 'label' => 'Presupuesto planificado', 'value' => $money($budgetData['planned'] ?? 0), 'detail' => 'Ejecutado ' . $number($budgetData['execution'] ?? 0, 1) . '%', 'bar_value' => 76],
        ['key' => 'budget_variance', 'label' => 'Ejecución presupuestaria', 'value' => $money($budgetData['actual'] ?? 0), 'detail' => (($budgetData['variance'] ?? 0) >= 0 ? 'Saldo' : 'Sobre ejecución') . ' ' . $money(abs((float) ($budgetData['variance'] ?? 0))), 'bar_value' => 70],
        ['key' => 'labor_cost', 'label' => 'Mano de obra', 'value' => $money($laborData['total'] ?? 0), 'detail' => $number($laborData['quantity'] ?? 0, 2) . ' jornadas · ' . (int) ($laborData['workers'] ?? 0) . ' trabajadores', 'bar_value' => 64],
    ],
};
$buildReportUrl = static function (?string $report = null, array $extraFilters = [], ?string $export = null): string {
    $params = [];
    foreach ($extraFilters as $key => $value) {
        if ($value === null || $value === '' || $value === '0' || $value === 0) {
            continue;
        }
        $params[$key] = $value;
    }
    foreach (['from' => $reportFilters['date_from'] ?? '', 'to' => $reportFilters['date_to'] ?? '', 'farm' => $reportFilters['farm_id'] ?? 0, 'block' => $reportFilters['block_id'] ?? 0, 'season' => $reportFilters['season_id'] ?? 0, 'cost_center' => $reportFilters['cost_center_id'] ?? 0, 'worker' => $reportFilters['worker_id'] ?? 0, 'supervisor' => $reportFilters['supervisor_id'] ?? 0, 'process' => $reportFilters['process'] ?? ''] as $key => $value) {
        if ($value === null || $value === '' || $value === '0' || $value === 0) {
            continue;
        }
        $params[$key] = $value;
    }
    if ($report !== null && $report !== '') {
        $path = '/reports/' . ltrim($report, '/');
    } else {
        $path = '/reports/' . $reportType;
    }
    if ($export !== null && $export !== '') {
        $path .= '/export';
        $params['format'] = $export;
    }
    if ($params === []) {
        return $path;
    }
    return $path . '?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
};
$maxTrend = static function (array $rows): float {
    return max([1, ...array_map(static fn (array $row): float => (float) ($row['value'] ?? 0), $rows)]);
};
$hasReportFilters = (string) ($reportFilters['process'] ?? '') !== '' || (int) ($reportFilters['season_id'] ?? 0) > 0 || (int) ($reportFilters['farm_id'] ?? 0) > 0 || (int) ($reportFilters['block_id'] ?? 0) > 0 || (int) ($reportFilters['cost_center_id'] ?? 0) > 0 || (int) ($reportFilters['worker_id'] ?? 0) > 0 || (int) ($reportFilters['supervisor_id'] ?? 0);
$reportUiLabels = [
    'view' => 'Ajusta tu vista',
    'filters' => 'Filtros del informe',
    'period' => 'Período',
    'visualization' => 'Visualización del informe',
    'comparison' => 'Mes seleccionado vs anterior',
    'seasons' => 'Temporadas',
    'trends' => 'Tendencias mensuales',
    'rankings' => 'Rankings ejecutivos',
    'farms' => 'Fundos con mayor costo',
    'processes' => 'Procesos más costosos',
    'blocks' => 'Cuarteles más productivos',
    'workers' => 'Trabajadores más productivos',
    'centers' => 'Centros de costo con mayor gasto',
    'alerts' => 'Alertas de inventario',
];
$reportEmptyStates = [
    'no_data' => 'Sin datos disponibles',
    'no_production' => 'Sin producción registrada',
    'no_labor' => 'Sin mano de obra registrada',
    'no_costs' => 'Sin costos registrados',
    'no_alerts' => 'Sin alertas registradas',
];
?>
<!doctype html>
<html lang="es">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title><?= htmlspecialchars($reportTitle, ENT_QUOTES, 'UTF-8') ?> | Informes</title><link rel="stylesheet" href="/assets/css/app.css"><link rel="stylesheet" href="/assets/css/reports.css"></head>
<body class="admin-page">
<main class="admin-shell">
    <?php require dirname(__DIR__) . '/Views/partials/module-navigation.php'; ?>
    <section class="module-content executive-reports">
        <header class="admin-header"><div><p class="eyebrow">Gerencia agrícola</p><h1><?= htmlspecialchars($reportTitle, ENT_QUOTES, 'UTF-8') ?></h1><p class="setup-copy"><?= htmlspecialchars($reportDescription, ENT_QUOTES, 'UTF-8') ?></p></div><div class="header-actions"><a class="secondary-link" href="<?= htmlspecialchars($buildReportUrl($reportType, [], 'csv'), ENT_QUOTES, 'UTF-8') ?>">CSV</a><a class="secondary-link" href="<?= htmlspecialchars($buildReportUrl($reportType, [], 'xlsx'), ENT_QUOTES, 'UTF-8') ?>">Excel</a><a class="secondary-link" href="<?= htmlspecialchars($buildReportUrl($reportType, [], 'pdf'), ENT_QUOTES, 'UTF-8') ?>">PDF</a><a class="secondary-link" href="/reports">Centro de inteligencia</a></div></header>

        <section class="admin-panel report-filter-bar">
            <div class="report-filter-header">
                <div>
                    <h2><?= htmlspecialchars($reportUiLabels['filters'], ENT_QUOTES, 'UTF-8') ?></h2>
                </div>
            </div>
            <form class="report-filter-form" method="get" data-report-form data-report-key="<?= htmlspecialchars($reportType, ENT_QUOTES, 'UTF-8') ?>" novalidate>
                <div class="report-filter-row">
                    <div class="report-filter-group report-filter-period">
                        <div class="date-inputs">
                            <label><span>Desde</span><input type="date" name="from" value="<?= htmlspecialchars((string) ($reportFilters['date_from'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></label>
                            <label><span>Hasta</span><input type="date" name="to" value="<?= htmlspecialchars((string) ($reportFilters['date_to'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></label>
                        </div>
                        <div class="filter-shortcuts" data-filter-shortcuts>
                            <button type="button" class="filter-shortcut" data-shortcut="last_7">Últimos 7 días</button>
                            <button type="button" class="filter-shortcut" data-shortcut="this_month">Mes actual</button>
                            <button type="button" class="filter-shortcut" data-shortcut="last_month">Mes anterior</button>
                            <button type="button" class="filter-shortcut" data-shortcut="ytd">Año</button>
                        </div>
                    </div>
                    <?php $renderFilter = static function (string $filterKey) use ($reportFilters, $options): string {
                        $value = $reportFilters[$filterKey . '_id'] ?? $reportFilters[$filterKey] ?? '';
                        $selected = static fn ($itemValue): string => ((string) $itemValue === (string) $value) ? 'selected' : '';
                        switch ($filterKey) {
                            case 'farm':
                                $items = $options['farms'] ?? [];
                                return '<label><span>Fundo</span><select name="farm"><option value="">Todos</option>' . implode('', array_map(static fn(array $item): string => '<option value="' . (int) $item['id'] . '" ' . $selected($item['id']) . '>' . htmlspecialchars((string) $item['name'], ENT_QUOTES, 'UTF-8') . '</option>', $items)) . '</select></label>';
                            case 'block':
                                $items = $options['blocks'] ?? [];
                                return '<label><span>Cuartel</span><select name="block"><option value="">Todos</option>' . implode('', array_map(static fn(array $item): string => '<option value="' . (int) $item['id'] . '" ' . $selected($item['id']) . '>' . htmlspecialchars((string) $item['code'] . ' · ' . (string) $item['name'], ENT_QUOTES, 'UTF-8') . '</option>', $items)) . '</select></label>';
                            case 'season':
                                $items = $options['seasons'] ?? [];
                                return '<label><span>Temporada</span><select name="season"><option value="">Todas</option>' . implode('', array_map(static fn(array $item): string => '<option value="' . (int) $item['id'] . '" ' . $selected($item['id']) . '>' . htmlspecialchars((string) $item['name'], ENT_QUOTES, 'UTF-8') . '</option>', $items)) . '</select></label>';
                            case 'cost_center':
                                $items = $options['centers'] ?? [];
                                return '<label><span>Centro de costo</span><select name="cost_center"><option value="">Todos</option>' . implode('', array_map(static fn(array $item): string => '<option value="' . (int) $item['id'] . '" ' . $selected($item['id']) . '>' . htmlspecialchars((string) $item['name'], ENT_QUOTES, 'UTF-8') . '</option>', $items)) . '</select></label>';
                            case 'process':
                                $items = $options['processes'] ?? [];
                                return '<label><span>Proceso</span><select name="process"><option value="">Todos</option>' . implode('', array_map(static fn(array $item): string => '<option value="' . htmlspecialchars((string) $item['process'], ENT_QUOTES, 'UTF-8') . '" ' . $selected($item['process']) . '>' . htmlspecialchars((string) $item['process'], ENT_QUOTES, 'UTF-8') . '</option>', $items)) . '</select></label>';
                            case 'worker':
                                $items = $options['workers'] ?? [];
                                return '<label><span>Trabajador</span><select name="worker"><option value="">Todos</option>' . implode('', array_map(static fn(array $item): string => '<option value="' . (int) $item['id'] . '" ' . $selected($item['id']) . '>' . htmlspecialchars((string) $item['full_name'], ENT_QUOTES, 'UTF-8') . '</option>', $items)) . '</select></label>';
                            case 'supervisor':
                                $items = $options['supervisors'] ?? [];
                                return '<label><span>Supervisor</span><select name="supervisor"><option value="">Todos</option>' . implode('', array_map(static fn(array $item): string => '<option value="' . (int) $item['id'] . '" ' . $selected($item['id']) . '>' . htmlspecialchars((string) $item['full_name'], ENT_QUOTES, 'UTF-8') . '</option>', $items)) . '</select></label>';
                            case 'supplier':
                                $items = $options['suppliers'] ?? [];
                                return '<label><span>Proveedor</span><select name="supplier_id"><option value="0">Todos</option>' . implode('', array_map(static fn(array $item): string => '<option value="' . (int) $item['id'] . '" ' . $selected($item['id']) . '>' . htmlspecialchars((string) $item['business_name'], ENT_QUOTES, 'UTF-8') . '</option>', $items)) . '</select></label>';
                            case 'warehouse':
                                $items = $options['warehouses'] ?? [];
                                return '<label><span>Bodega</span><select name="warehouse_id"><option value="0">Todas</option>' . implode('', array_map(static fn(array $item): string => '<option value="' . (int) $item['id'] . '" ' . $selected($item['id']) . '>' . htmlspecialchars((string) $item['name'], ENT_QUOTES, 'UTF-8') . '</option>', $items)) . '</select></label>';
                            case 'product':
                                $items = $options['products'] ?? [];
                                return '<label><span>Producto</span><select name="product_id"><option value="0">Todos</option>' . implode('', array_map(static fn(array $item): string => '<option value="' . (int) $item['id'] . '" ' . $selected($item['id']) . '>' . htmlspecialchars((string) $item['name'], ENT_QUOTES, 'UTF-8') . '</option>', $items)) . '</select></label>';
                            case 'category':
                                if (!empty($options['inventory_categories']) && $options['inventory_categories'] !== []) {
                                    $items = $options['inventory_categories'];
                                    return '<label><span>Categoría</span><select name="category"><option value="">Todas</option>' . implode('', array_map(static fn(string $item): string => '<option value="' . htmlspecialchars($item, ENT_QUOTES, 'UTF-8') . '" ' . $selected($item) . '>' . htmlspecialchars($item, ENT_QUOTES, 'UTF-8') . '</option>', $items)) . '</select></label>';
                                }
                                $items = array_unique(array_map(static fn(array $item): string => (string) ($item['category'] ?? ''), $options['centers'] ?? []));
                                return '<label><span>Categoría</span><select name="category"><option value="">Todas</option>' . implode('', array_map(static fn(string $item): string => '<option value="' . htmlspecialchars($item, ENT_QUOTES, 'UTF-8') . '" ' . $selected($item) . '>' . htmlspecialchars($item, ENT_QUOTES, 'UTF-8') . '</option>', array_filter($items))) . '</select></label>';
                            case 'family':
                                $items = $options['families'] ?? [];
                                return '<label><span>Familia</span><select name="family"><option value="">Todas</option>' . implode('', array_map(static fn(string $item): string => '<option value="' . htmlspecialchars($item, ENT_QUOTES, 'UTF-8') . '" ' . $selected($item) . '>' . htmlspecialchars($item, ENT_QUOTES, 'UTF-8') . '</option>', $items)) . '</select></label>';
                            case 'crop':
                                $items = $options['crops'] ?? [];
                                return '<label><span>Cultivo</span><select name="crop"><option value="">Todos</option>' . implode('', array_map(static fn(string $item): string => '<option value="' . htmlspecialchars($item, ENT_QUOTES, 'UTF-8') . '" ' . $selected($item) . '>' . htmlspecialchars($item, ENT_QUOTES, 'UTF-8') . '</option>', $items)) . '</select></label>';
                            case 'variety':
                                $items = $options['varieties'] ?? [];
                                return '<label><span>Variedad</span><select name="variety"><option value="">Todas</option>' . implode('', array_map(static fn(string $item): string => '<option value="' . htmlspecialchars($item, ENT_QUOTES, 'UTF-8') . '" ' . $selected($item) . '>' . htmlspecialchars($item, ENT_QUOTES, 'UTF-8') . '</option>', $items)) . '</select></label>';
                            case 'machine_type':
                                $items = $options['machine_types'] ?? [];
                                return '<label><span>Tipo de máquina</span><select name="machine_type"><option value="">Todos</option>' . implode('', array_map(static fn(string $item): string => '<option value="' . htmlspecialchars($item, ENT_QUOTES, 'UTF-8') . '" ' . $selected($item) . '>' . htmlspecialchars($item, ENT_QUOTES, 'UTF-8') . '</option>', $items)) . '</select></label>';
                            case 'crew':
                                $items = $options['crews'] ?? [];
                                return '<label><span>Cuadrilla</span><select name="crew_id"><option value="0">Todas</option>' . implode('', array_map(static fn(array $item): string => '<option value="' . (int) $item['id'] . '" ' . $selected($item['id']) . '>' . htmlspecialchars((string) $item['name'], ENT_QUOTES, 'UTF-8') . '</option>', $items)) . '</select></label>';
                        }
                        return '';
                    }; ?>
                    <?php foreach (($selectedReportConfig['filters'] ?? []) as $filterKey): ?>
                        <?php if ($filterKey !== 'year' && $filterKey !== 'month' && $filterKey !== 'week' && $filterKey !== 'day' && $filterKey !== 'date_range'): ?>
                            <?= $renderFilter($filterKey) ?>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
                <div class="report-filter-footer">
                    <div class="report-filter-pills" data-active-filters></div>
                    <div class="report-filter-buttons">
                        <button type="button" class="secondary-button" data-filter-reset>Limpiar</button>
                        <button type="submit" class="primary-button">Actualizar</button>
                    </div>
                </div>
            </form>
        </section>

        <section class="report-visual-grid">
            <?php foreach ($reportSummaryCards as $card): ?>
                <article class="report-visual-card" data-summary-key="<?= htmlspecialchars($card['key'] ?? $card['label'], ENT_QUOTES, 'UTF-8') ?>">
                    <div class="report-visual-meta"><small><?= htmlspecialchars($card['label'], ENT_QUOTES, 'UTF-8') ?></small><strong><?= htmlspecialchars((string) $card['value'], ENT_QUOTES, 'UTF-8') ?></strong></div>
                    <div class="progress-mini" aria-hidden="true"><div class="progress-mini-bar" style="width: <?= (int) ($card['bar_value'] ?? 55) ?>%"></div></div>
                </article>
            <?php endforeach; ?>
        </section>

        <section class="report-chart-grid">
            <article class="section-card report-chart-card"><div class="panel-header"><h2>Resumen</h2></div><div class="panel-body"><canvas id="reportOverviewChart" aria-label="Gráfico del informe"></canvas></div></article>
            <article class="section-card report-chart-card"><div class="panel-header"><h2>Tendencias</h2></div><div class="panel-body">
                <?php foreach (['costs' => 'Costos', 'production' => 'Producción', 'labor' => 'Mano de obra', 'budget' => 'Presupuesto'] as $key => $label): ?>
                    <?php $max = $maxTrend($trends[$key] ?? []); ?>
                    <div class="trend-block"><small><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></small><?php foreach (($trends[$key] ?? []) as $row): ?><div class="trend-row"><small><?= htmlspecialchars((string) $row['period'], ENT_QUOTES, 'UTF-8') ?></small><i data-trend="<?= min(100, ((float) $row['value'] / max(1, $max)) * 100) ?>"></i><b><?= $key === 'production' ? $number($row['value'] ?? 0, 2) : $money($row['value'] ?? 0) ?></b></div><?php endforeach; ?></div>
                <?php endforeach; ?>
            </div></article>
        </section>

        <?php if ($reportType === 'executive'): ?>
            <section class="executive-analysis-grid" data-executive-analysis>
                <article class="section-card executive-analysis-card"><div class="panel-header"><h2>Costo por proceso</h2><small>¿Dónde se está gastando más?</small></div><div class="panel-body"><canvas id="executiveProcessCostChart" aria-label="Costo total por proceso"></canvas></div></article>
                <article class="section-card executive-analysis-card"><div class="panel-header"><h2>Participación del costo</h2><small>¿Qué procesos concentran el presupuesto?</small></div><div class="panel-body"><canvas id="executiveProcessShareChart" aria-label="Participación del costo por proceso"></canvas></div></article>
                <article class="section-card executive-analysis-card"><div class="panel-header"><h2>Evolución de producción</h2><small>¿Cómo evoluciona la producción?</small></div><div class="panel-body"><canvas id="executiveProductionTrendChart" aria-label="Producción mensual"></canvas></div></article>
                <article class="section-card executive-analysis-card"><div class="panel-header"><h2>Uso de mano de obra</h2><small>¿Dónde se concentra el trabajo?</small></div><div class="panel-body"><canvas id="executiveLaborChart" aria-label="Jornadas por trabajador"></canvas></div></article>
            </section>
        <?php endif; ?>

        <?php if (!empty($reportFocus['tables'])): ?>
            <section class="section-card report-details">
                <?php foreach ($reportFocus['tables'] as $table): ?>
                    <div class="table-block"><h3><?= htmlspecialchars($table['title'] ?? 'Detalle', ENT_QUOTES, 'UTF-8') ?></h3><table class="data-table"><thead><tr><?php foreach ($table['columns'] ?? [] as $column): ?><th><?= htmlspecialchars((string) $column, ENT_QUOTES, 'UTF-8') ?></th><?php endforeach; ?></tr></thead><tbody><?php foreach ($table['rows'] ?? [] as $row): ?><tr><?php foreach ($table['columns'] ?? [] as $columnKey => $column): ?><td><?= htmlspecialchars((string) ($row[$columnKey] ?? $row[$column] ?? ''), ENT_QUOTES, 'UTF-8') ?></td><?php endforeach; ?></tr><?php endforeach; ?></tbody></table></div>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>
    </section>
</main>
<script>window.reportData = <?= json_encode($reportSummary, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>;</script>
<script src="/assets/js/chart.min.js" defer></script>
<script src="/assets/js/reports.js" defer></script>
</body>
</html>
