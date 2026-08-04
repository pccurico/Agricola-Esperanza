<?php

declare(strict_types=1);

namespace CampoSur\Services;

use DateTimeImmutable;
use PDO;

final class DashboardService extends BaseService implements Dashboard\DashboardDataProviderInterface
{
    public function __construct(protected readonly PDO $connection, protected readonly int $companyId)
    {
    }

    public function summary(string $period = 'month', ?string $referenceDate = null, array $filters = [], ?string $activeView = null, string $department = 'general', ?int $userId = null): array
    {
        $periodConfig = [
            'day' => ['cost_format' => '%Y-%m-%d', 'production_format' => '%Y-%m-%d'],
            'week' => ['cost_format' => '%x-W%v', 'production_format' => '%x-W%v'],
            'month' => ['cost_format' => '%Y-%m', 'production_format' => '%Y-%m'],
            'year' => ['cost_format' => '%Y', 'production_format' => '%Y'],
        ];
        $period = array_key_exists($period, $periodConfig) ? $period : 'month';
        $config = $periodConfig[$period];
        $today = new DateTimeImmutable('today');
        $customization = $this->dashboardSettings($department, $userId);
        $selectedWidgetIds = array_values(array_filter(array_map('trim', (array) ($customization['widgets'] ?? []))));
        $activeView = $activeView !== null ? trim($activeView) : $customization['active_view'] ?? '';
        if ($activeView !== '') {
            foreach ($customization['saved_views'] as $view) {
                if (($view['name'] ?? '') === $activeView) {
                    $savedFilters = $view['layout']['filters'] ?? [];
                    $filters = array_merge($savedFilters, $filters);
                    $selectedWidgetIds = $view['layout']['widgets'] ?? $selectedWidgetIds;
                    break;
                }
            }
        }
        $fromDate = DateTimeImmutable::createFromFormat('!Y-m-d', (string) ($filters['date_from'] ?? '')) ?: $today->modify('first day of this month');
        $toDate = DateTimeImmutable::createFromFormat('!Y-m-d', (string) ($filters['date_to'] ?? '')) ?: $today;
        if ($toDate < $fromDate) {
            [$fromDate, $toDate] = [$toDate, $fromDate];
        }
        $periodStart = $fromDate->format('Y-m-d');
        $periodEnd = $toDate->format('Y-m-d');
        $farmId = max(0, (int) ($filters['farm_id'] ?? 0));
        $blockId = max(0, (int) ($filters['block_id'] ?? 0));
        $process = trim((string) ($filters['process'] ?? ''));
        $processValue = $process !== '' ? $this->connection->quote($process) : null;
        $expenseFilters = ($farmId ? " AND farm_id = {$farmId}" : '') . ($blockId ? " AND block_id = {$blockId}" : '') . ($processValue ? " AND description = {$processValue}" : '');
        $laborFilters = ($farmId ? " AND farm_id = {$farmId}" : '') . ($blockId ? " AND block_id = {$blockId}" : '') . ($processValue ? " AND labor_type = {$processValue}" : '');
        $productionFilters = ($farmId ? " AND farm_id = {$farmId}" : '') . ($blockId ? " AND block_id = {$blockId}" : '') . ($processValue ? " AND activity = {$processValue}" : '');

        $company = $this->connection->prepare('SELECT trade_name, logo_path FROM companies WHERE id = ?');
        $company->execute([$this->companyId]);
        $totals = $this->connection->prepare("SELECT COALESCE((SELECT SUM(amount) FROM expense_entries WHERE company_id = ? AND status = 'POSTED' AND entry_date BETWEEN '{$periodStart}' AND '{$periodEnd}'{$expenseFilters}), 0) + COALESCE((SELECT SUM(total_amount) FROM labor_entries WHERE company_id = ? AND status = 'POSTED' AND labor_date BETWEEN '{$periodStart}' AND '{$periodEnd}'{$laborFilters}), 0) AS total_cost, COALESCE((SELECT SUM(hectares) FROM farms WHERE company_id = ? AND active = 1), 0) AS hectares, COALESCE((SELECT COUNT(*) FROM expense_entries WHERE company_id = ? AND entry_date BETWEEN '{$periodStart}' AND '{$periodEnd}'{$expenseFilters}), 0) + COALESCE((SELECT COUNT(*) FROM labor_entries WHERE company_id = ? AND labor_date BETWEEN '{$periodStart}' AND '{$periodEnd}'{$laborFilters}), 0) + COALESCE((SELECT COUNT(*) FROM inventory_movements WHERE company_id = ? AND movement_date BETWEEN '{$periodStart}' AND '{$periodEnd}'), 0) AS movements");
        $totals->execute([$this->companyId, $this->companyId, $this->companyId, $this->companyId, $this->companyId, $this->companyId]);
        $recent = $this->connection->prepare("(SELECT description AS label, amount AS value, entry_date AS date, 'Costo' AS type FROM expense_entries WHERE company_id = ? AND status = 'POSTED' AND entry_date BETWEEN '{$periodStart}' AND '{$periodEnd}'{$expenseFilters} ORDER BY entry_date DESC, id DESC LIMIT 5) UNION ALL (SELECT labor_type AS label, total_amount AS value, labor_date AS date, 'Labor' AS type FROM labor_entries WHERE company_id = ? AND status = 'POSTED' AND labor_date BETWEEN '{$periodStart}' AND '{$periodEnd}'{$laborFilters} ORDER BY labor_date DESC, id DESC LIMIT 5) ORDER BY date DESC LIMIT 8");
        $recent->execute([$this->companyId, $this->companyId]);
        $recentRows = $recent->fetchAll();
        $metrics = $this->connection->prepare("SELECT (SELECT COUNT(*) FROM farms WHERE company_id = ? AND active = 1) AS farms, (SELECT COUNT(*) FROM blocks WHERE company_id = ? AND active = 1) AS blocks, (SELECT COUNT(*) FROM workers WHERE company_id = ? AND active = 1) AS workers, (SELECT COUNT(*) FROM inventory_items WHERE company_id = ? AND active = 1) AS items, (SELECT COUNT(*) FROM machinery WHERE company_id = ? AND status = 'ACTIVE') AS machinery, (SELECT COALESCE(SUM(quantity), 0) FROM production_entries WHERE company_id = ? AND production_date BETWEEN '{$periodStart}' AND '{$periodEnd}'{$productionFilters}) AS production");
        $metrics->execute([$this->companyId, $this->companyId, $this->companyId, $this->companyId, $this->companyId, $this->companyId]);
        $operational = $this->connection->prepare('SELECT (SELECT COUNT(*) FROM tasks WHERE company_id = ? AND status NOT IN ("DONE", "CANCELLED")) AS pending_tasks, (SELECT COUNT(*) FROM internal_requests WHERE company_id = ? AND status IN ("REQUESTED", "APPROVED")) AS open_requests, (SELECT COUNT(*) FROM purchase_orders WHERE company_id = ? AND status IN ("SENT", "PARTIAL")) AS pending_orders');
        $operational->execute([$this->companyId, $this->companyId, $this->companyId]);
        $operationalData = $operational->fetch() ?: ['pending_tasks' => 0, 'open_requests' => 0, 'pending_orders' => 0];

        $costLimit = $period === 'day' ? 14 : ($period === 'week' ? 12 : ($period === 'month' ? 12 : 5));
        $costFormat = $config['cost_format'];
        $productionFormat = $config['production_format'];
        $costSeries = $this->series("SELECT DATE_FORMAT(entry_date, '{$costFormat}') AS period, SUM(amount) AS value FROM expense_entries WHERE company_id = ? AND status = 'POSTED' AND entry_date BETWEEN '{$periodStart}' AND '{$periodEnd}'{$expenseFilters} GROUP BY period ORDER BY period DESC LIMIT {$costLimit}");
        $productionSeries = $this->series("SELECT DATE_FORMAT(production_date, '{$productionFormat}') AS period, SUM(quantity) AS value FROM production_entries WHERE company_id = ? AND production_date BETWEEN '{$periodStart}' AND '{$periodEnd}'{$productionFilters} GROUP BY period ORDER BY period DESC LIMIT {$costLimit}");
        $inventoryAlerts = $this->connection->prepare('SELECT i.name, i.unit, i.minimum_stock, COALESCE(SUM(CASE WHEN m.movement_type = "IN" THEN m.quantity WHEN m.movement_type = "OUT" THEN -m.quantity ELSE m.quantity END), 0) AS stock FROM inventory_items i LEFT JOIN inventory_movements m ON m.item_id = i.id AND m.company_id = i.company_id WHERE i.company_id = ? AND i.active = 1 GROUP BY i.id, i.name, i.unit, i.minimum_stock HAVING stock <= i.minimum_stock ORDER BY stock ASC, i.name LIMIT 6');
        $inventoryAlerts->execute([$this->companyId]);

        $totalsData = $totals->fetch() ?: ['total_cost' => 0, 'hectares' => 0, 'movements' => 0];
        $metricsData = $metrics->fetch() ?: [];
        $inventoryAlertRows = $inventoryAlerts->fetchAll();
        $costPerUnit = (float) ($metricsData['production'] ?? 0) > 0 ? (float) ($totalsData['total_cost'] ?? 0) / (float) ($metricsData['production'] ?? 0) : 0;
        $productionPerHectare = (float) ($totalsData['hectares'] ?? 0) > 0 ? (float) ($metricsData['production'] ?? 0) / (float) ($totalsData['hectares'] ?? 0) : 0;
        $profitability = (float) ($totalsData['total_cost'] ?? 0) > 0 ? (float) ($metricsData['production'] ?? 0) / (float) ($totalsData['total_cost'] ?? 0) : 0;
        $inventoryAlertCount = count($inventoryAlertRows);

        // --- Periodo anterior (mismo largo que el seleccionado)
        $days = (int) $toDate->diff($fromDate)->days + 1;
        $prevFrom = $fromDate->modify('-' . $days . ' days');
        $prevTo = $toDate->modify('-' . $days . ' days');
        $prevStart = $prevFrom->format('Y-m-d');
        $prevEnd = $prevTo->format('Y-m-d');

        $fetchScalar = static function (PDO $conn, string $sql, array $params = []) {
            $q = $conn->prepare($sql);
            $q->execute($params);
            $v = $q->fetchColumn();
            return $v === false ? 0 : $v;
        };

        // Totales periodo anterior
        $prevTotalCost = (float) $fetchScalar($this->connection, "SELECT COALESCE((SELECT SUM(amount) FROM expense_entries WHERE company_id = ? AND status = 'POSTED' AND entry_date BETWEEN ? AND ?{$expenseFilters}), 0) + COALESCE((SELECT SUM(total_amount) FROM labor_entries WHERE company_id = ? AND status = 'POSTED' AND labor_date BETWEEN ? AND ?{$laborFilters}), 0)", [$this->companyId, $prevStart, $prevEnd, $this->companyId, $prevStart, $prevEnd]);
        $prevProduction = (float) $fetchScalar($this->connection, "SELECT COALESCE(SUM(quantity),0) FROM production_entries WHERE company_id = ? AND production_date BETWEEN ? AND ?{$productionFilters}", [$this->companyId, $prevStart, $prevEnd]);

        $computeVariation = static function (float $current, float $previous) {
            $abs = $current - $previous;
            $pct = $previous == 0 ? null : ($abs / $previous) * 100.0;
            $trend = $abs > 0 ? 'up' : ($abs < 0 ? 'down' : 'stable');
            return ['current' => $current, 'previous' => $previous, 'abs' => $abs, 'pct' => $pct, 'trend' => $trend];
        };

        $variationCost = $computeVariation((float) ($totalsData['total_cost'] ?? 0), $prevTotalCost);
        $variationProduction = $computeVariation((float) ($metricsData['production'] ?? 0), $prevProduction);

        // Generar análisis automáticos (sin IA) basados en comparativas simples
        $analyses = [];
        if ($variationCost['pct'] !== null && abs($variationCost['pct']) >= 1.0) {
            $analyses[] = sprintf('El costo operativo %s %.2f%% respecto al período anterior.', $variationCost['trend'] === 'up' ? 'aumentó' : 'disminuyó', abs($variationCost['pct']));
        }
        if ($variationProduction['pct'] !== null && abs($variationProduction['pct']) >= 1.0) {
            $analyses[] = sprintf('La producción %s %.2f%% respecto al período anterior.', $variationProduction['trend'] === 'up' ? 'aumentó' : 'disminuyó', abs($variationProduction['pct']));
        }

        // Estructura de secciones con KPIs reales
        $sections = [];

        // 1. Resumen Ejecutivo
        $sections['executive'] = [
            'title' => 'Resumen ejecutivo',
            'kpis' => [
                ['key' => 'operating_cost', 'label' => 'Costo operativo', 'value' => (float) ($totalsData['total_cost'] ?? 0), 'variation' => $variationCost, 'unit' => 'CLP'],
                ['key' => 'production', 'label' => 'Producción', 'value' => (float) ($metricsData['production'] ?? 0), 'variation' => $variationProduction, 'unit' => 'kg'],
                ['key' => 'profitability', 'label' => 'Rentabilidad', 'value' => round($profitability * 100, 2), 'variation' => null, 'unit' => '%'],
                ['key' => 'cost_per_unit', 'label' => 'Costo por unidad', 'value' => round($costPerUnit, 2), 'variation' => null, 'unit' => 'CLP/kg'],
                ['key' => 'production_per_hectare', 'label' => 'Producción por ha', 'value' => round($productionPerHectare, 2), 'variation' => null, 'unit' => 'kg/ha'],
                ['key' => 'cash_flow', 'label' => 'Flujo de caja (aprox.)', 'value' => null, 'variation' => null, 'unit' => 'CLP', 'note' => 'No disponible: datos de ingresos no identificados'],
            ],
            'analyses' => $analyses,
        ];

        // 2. Producción
        $prodBySeason = $this->fetchRows('SELECT s.name, COALESCE(SUM(p.quantity),0) AS total FROM production_entries p INNER JOIN seasons s ON s.id = p.season_id WHERE p.company_id = ? AND p.production_date BETWEEN ? AND ? GROUP BY s.id ORDER BY s.starts_on DESC', [$this->companyId, $periodStart, $periodEnd]);
        $prodByFarm = $this->fetchRows('SELECT COALESCE(f.name, "Sin fundo") AS farm, COALESCE(SUM(p.quantity),0) AS total FROM production_entries p LEFT JOIN farms f ON f.id = p.farm_id WHERE p.company_id = ? AND p.production_date BETWEEN ? AND ? GROUP BY p.farm_id, f.name ORDER BY total DESC LIMIT 20', [$this->companyId, $periodStart, $periodEnd]);
        $prodByBlock = $this->fetchRows('SELECT COALESCE(b.name, "Sin cuartel") AS block, COALESCE(SUM(p.quantity),0) AS total FROM production_entries p LEFT JOIN blocks b ON b.id = p.block_id WHERE p.company_id = ? AND p.production_date BETWEEN ? AND ? GROUP BY p.block_id, b.name ORDER BY total DESC LIMIT 20', [$this->companyId, $periodStart, $periodEnd]);
        $prodBySpecies = $this->fetchRows('SELECT COALESCE(sp.name, "Sin especie") AS species, COALESCE(SUM(p.quantity),0) AS total FROM production_entries p LEFT JOIN species sp ON sp.id = p.species_id WHERE p.company_id = ? AND p.production_date BETWEEN ? AND ? GROUP BY p.species_id, sp.name ORDER BY total DESC LIMIT 20', [$this->companyId, $periodStart, $periodEnd]);
        $sections['production'] = ['title' => 'Producción', 'by_season' => $prodBySeason, 'by_farm' => $prodByFarm, 'by_block' => $prodByBlock, 'by_species' => $prodBySpecies, 'cost_per_hectare' => $costPerUnit, 'cost_per_ton' => $costPerUnit];

        // 3. Contabilidad (utiliza expense_entries, labor_entries, purchase_invoices si existen)
        $expenses = (float) $fetchScalar($this->connection, "SELECT COALESCE(SUM(amount),0) FROM expense_entries WHERE company_id = ? AND status = 'POSTED' AND entry_date BETWEEN ? AND ?", [$this->companyId, $periodStart, $periodEnd]);
        $laborCost = (float) $fetchScalar($this->connection, "SELECT COALESCE(SUM(total_amount),0) FROM labor_entries WHERE company_id = ? AND status = 'POSTED' AND labor_date BETWEEN ? AND ?", [$this->companyId, $periodStart, $periodEnd]);
        $purchaseInvoices = $this->fetchRows('SELECT id, invoice_number, issue_date, total_amount, status FROM purchase_invoices WHERE company_id = ? AND issue_date BETWEEN ? AND ? ORDER BY issue_date DESC LIMIT 20', [$this->companyId, $periodStart, $periodEnd]);
        $sections['accounting'] = ['title' => 'Contabilidad', 'expenses' => $expenses, 'labor_cost' => $laborCost, 'purchase_invoices' => $purchaseInvoices, 'budgets' => $this->fetchRows('SELECT id, season_id, cost_center_id, period_start, period_end, amount, status FROM budgets WHERE company_id = ? AND period_start >= ? AND period_end <= ? ORDER BY period_start', [$this->companyId, $periodStart, $periodEnd])];

        // 4. Costos
        $costByProcess = $this->fetchRows('SELECT process, COALESCE(SUM(total),0) AS total FROM (SELECT description AS process, SUM(amount) AS total FROM expense_entries WHERE company_id = ? AND entry_date BETWEEN ? AND ? GROUP BY description UNION ALL SELECT labor_type AS process, SUM(total_amount) AS total FROM labor_entries WHERE company_id = ? AND labor_date BETWEEN ? AND ? GROUP BY labor_type) scoped GROUP BY process ORDER BY total DESC LIMIT 30', [$this->companyId, $periodStart, $periodEnd, $this->companyId, $periodStart, $periodEnd]);
        $costByMachinery = $this->fetchRows('SELECT m.id, m.name, COALESCE(SUM(mm.cost),0) AS maintenance_cost FROM machinery m LEFT JOIN machinery_maintenance mm ON mm.machinery_id = m.id AND mm.company_id = ? AND mm.maintenance_date BETWEEN ? AND ? WHERE m.company_id = ? GROUP BY m.id, m.name ORDER BY maintenance_cost DESC LIMIT 30', [$this->companyId, $periodStart, $periodEnd, $this->companyId]);
        $costByWorker = $this->fetchRows('SELECT w.id, w.full_name, COALESCE(SUM(l.total_amount),0) AS total FROM workers w LEFT JOIN labor_entries l ON l.worker_id = w.id AND l.company_id = ? AND l.labor_date BETWEEN ? AND ? WHERE w.company_id = ? GROUP BY w.id, w.full_name ORDER BY total DESC LIMIT 30', [$this->companyId, $periodStart, $periodEnd, $this->companyId]);
        $sections['costs'] = ['title' => 'Costos', 'by_process' => $costByProcess, 'by_machinery' => $costByMachinery, 'by_worker' => $costByWorker];

        // 5. Bodega
        $criticalStock = $inventoryAlertRows;
        $inventoryValued = $this->fetchRows('SELECT i.id, i.sku, i.name, COALESCE(SUM(CASE WHEN m.movement_type = "IN" THEN m.quantity WHEN m.movement_type = "OUT" THEN -m.quantity ELSE m.quantity END),0) AS stock, COALESCE(AVG(m.unit_cost),0) AS avg_cost FROM inventory_items i LEFT JOIN inventory_movements m ON m.item_id = i.id AND m.company_id = i.company_id WHERE i.company_id = ? GROUP BY i.id ORDER BY stock ASC LIMIT 100', [$this->companyId]);
        $rotation = $this->fetchRows('SELECT i.id, i.name, COALESCE(SUM(m.quantity),0) AS moved FROM inventory_items i INNER JOIN inventory_movements m ON m.item_id = i.id WHERE i.company_id = ? AND m.movement_date BETWEEN ? AND ? GROUP BY i.id ORDER BY moved DESC LIMIT 30', [$this->companyId, $periodStart, $periodEnd]);
        $sections['warehouse'] = ['title' => 'Bodega', 'critical_stock' => $criticalStock, 'inventory_valued' => $inventoryValued, 'rotation' => $rotation];

        // 6. RRHH
        $workersActive = (int) $fetchScalar($this->connection, 'SELECT COUNT(*) FROM workers WHERE company_id = ? AND active = 1', [$this->companyId]);
        $laborEntries = $this->fetchRows('SELECT worker_id, SUM(quantity) AS quantity, SUM(total_amount) AS total FROM labor_entries WHERE company_id = ? AND labor_date BETWEEN ? AND ? GROUP BY worker_id ORDER BY total DESC LIMIT 50', [$this->companyId, $periodStart, $periodEnd]);
        $totalHours = array_sum(array_map(static fn($r) => (float) ($r['quantity'] ?? 0), $laborEntries));
        $totalLaborCost = array_sum(array_map(static fn($r) => (float) ($r['total'] ?? 0), $laborEntries));
        $sections['hr'] = ['title' => 'RRHH', 'workers_active' => $workersActive, 'labor_entries' => $laborEntries, 'total_hours' => $totalHours, 'total_labor_cost' => $totalLaborCost];

        // 7. Maquinaria
        $machineryList = $this->fetchRows('SELECT id, code, name, status, meter FROM machinery WHERE company_id = ? ORDER BY name', [$this->companyId]);
        $machineryMaintenance = $this->fetchRows('SELECT machinery_id, maintenance_type, maintenance_date, cost FROM machinery_maintenance WHERE company_id = ? AND maintenance_date BETWEEN ? AND ? ORDER BY maintenance_date DESC LIMIT 50', [$this->companyId, $periodStart, $periodEnd]);
        $fuelUsage = $this->fetchRows('SELECT machinery_id, SUM(liters) AS liters, SUM(liters * unit_cost) AS cost FROM fuel_movements WHERE company_id = ? AND fuel_date BETWEEN ? AND ? GROUP BY machinery_id', [$this->companyId, $periodStart, $periodEnd]);
        $sections['machinery'] = ['title' => 'Maquinaria', 'list' => $machineryList, 'maintenance' => $machineryMaintenance, 'fuel' => $fuelUsage];

        // 8. Compras
        $purchases = $this->fetchRows('SELECT id, order_number, order_date, status FROM purchase_orders WHERE company_id = ? AND order_date BETWEEN ? AND ? ORDER BY order_date DESC LIMIT 50', [$this->companyId, $periodStart, $periodEnd]);
        $receptions = $this->fetchRows('SELECT id, purchase_order_id, received_on, status FROM purchase_receptions WHERE company_id = ? AND received_on BETWEEN ? AND ? ORDER BY received_on DESC LIMIT 50', [$this->companyId, $periodStart, $periodEnd]);
        $sections['procurement'] = ['title' => 'Compras', 'orders' => $purchases, 'receptions' => $receptions, 'suppliers' => $this->fetchRows('SELECT id, business_name FROM suppliers WHERE company_id = ? ORDER BY business_name LIMIT 50', [$this->companyId])];

        if ($process !== '' || $farmId > 0 || $blockId > 0) {
            $scope = $this->connection->prepare("SELECT farm_id, block_id, NULL AS worker_id FROM expense_entries WHERE company_id = ? AND entry_date BETWEEN '{$periodStart}' AND '{$periodEnd}'{$expenseFilters} UNION ALL SELECT farm_id, block_id, worker_id FROM labor_entries WHERE company_id = ? AND labor_date BETWEEN '{$periodStart}' AND '{$periodEnd}'{$laborFilters} UNION ALL SELECT farm_id, block_id, NULL AS worker_id FROM production_entries WHERE company_id = ? AND production_date BETWEEN '{$periodStart}' AND '{$periodEnd}'{$productionFilters}");
            $scope->execute([$this->companyId, $this->companyId, $this->companyId]);
            $scopeRows = $scope->fetchAll();
            $farmIds = array_values(array_unique(array_filter(array_map(static fn (array $row): int => (int) ($row['farm_id'] ?? 0), $scopeRows))));
            $blockIds = array_values(array_unique(array_filter(array_map(static fn (array $row): int => (int) ($row['block_id'] ?? 0), $scopeRows))));
            $workerIds = array_values(array_unique(array_filter(array_map(static fn (array $row): int => (int) ($row['worker_id'] ?? 0), $scopeRows))));
            $metricsData['farms'] = count($farmIds);
            $metricsData['blocks'] = count($blockIds);
            $metricsData['workers'] = count($workerIds);
            $metricsData['scoped_hectares'] = 0;
            if ($farmIds !== []) {
                $placeholders = implode(',', array_fill(0, count($farmIds), '?'));
                $hectaresQuery = $this->connection->prepare('SELECT COALESCE(SUM(hectares), 0) FROM farms WHERE company_id = ? AND id IN (' . $placeholders . ')');
                $hectaresQuery->execute(array_merge([$this->companyId], $farmIds));
                $metricsData['scoped_hectares'] = (float) $hectaresQuery->fetchColumn();
            }
        }

        $kpis = [
            ['label' => 'Costo operativo', 'value' => (float) ($totalsData['total_cost'] ?? 0), 'trend' => 'up', 'color' => 'indigo', 'detail' => 'Costos + mano de obra'],
            ['label' => 'Producción', 'value' => (float) ($metricsData['production'] ?? 0), 'trend' => 'up', 'color' => 'emerald', 'detail' => 'Kg registrados'],
            ['label' => 'Rentabilidad', 'value' => (float) ($profitability * 100), 'trend' => 'stable', 'color' => 'amber', 'detail' => 'Producción por costo'],
            ['label' => 'Stock crítico', 'value' => $inventoryAlertCount, 'trend' => 'warning', 'color' => 'rose', 'detail' => 'Insumos por reabastecer'],
            ['label' => 'Pendientes', 'value' => (int) (($operationalData['pending_tasks'] ?? 0) + ($operationalData['open_requests'] ?? 0) + ($operationalData['pending_orders'] ?? 0)), 'trend' => 'warning', 'color' => 'slate', 'detail' => 'Tareas, solicitudes y órdenes'],
        ];
        $alerts = [
            ['title' => 'Stock crítico', 'count' => $inventoryAlertCount, 'module' => 'inventory', 'link' => '?module=inventory', 'severity' => $inventoryAlertCount > 0 ? 'critical' : 'normal'],
            ['title' => 'Tareas pendientes', 'count' => (int) ($operationalData['pending_tasks'] ?? 0), 'module' => 'planning', 'link' => '?module=planning', 'severity' => (int) ($operationalData['pending_tasks'] ?? 0) > 0 ? 'warning' : 'normal'],
            ['title' => 'Solicitudes abiertas', 'count' => (int) ($operationalData['open_requests'] ?? 0), 'module' => 'requests', 'link' => '?module=requests', 'severity' => (int) ($operationalData['open_requests'] ?? 0) > 0 ? 'warning' : 'normal'],
            ['title' => 'Órdenes pendientes', 'count' => (int) ($operationalData['pending_orders'] ?? 0), 'module' => 'procurement', 'link' => '?module=procurement', 'severity' => (int) ($operationalData['pending_orders'] ?? 0) > 0 ? 'warning' : 'normal'],
        ];
        $widgets = [
            ['id' => 'cost-trend', 'title' => 'Evolución de costos', 'type' => 'bars', 'period' => $period, 'data' => array_reverse($costSeries)],
            ['id' => 'production-trend', 'title' => 'Producción', 'type' => 'bars', 'period' => $period, 'data' => array_reverse($productionSeries)],
            ['id' => 'inventory-alerts', 'title' => 'Alertas de inventario', 'type' => 'list', 'data' => $inventoryAlertRows],
            ['id' => 'recent-activity', 'title' => 'Actividad reciente', 'type' => 'list', 'data' => $recentRows],
        ];

        $alerts = [
            ['title' => 'Stock crítico', 'count' => $inventoryAlertCount, 'module' => 'inventory', 'link' => '?module=inventory', 'severity' => $inventoryAlertCount > 0 ? 'critical' : 'normal'],
            ['title' => 'Tareas pendientes', 'count' => (int) ($operationalData['pending_tasks'] ?? 0), 'module' => 'planning', 'link' => '?module=planning', 'severity' => (int) ($operationalData['pending_tasks'] ?? 0) > 0 ? 'warning' : 'normal'],
            ['title' => 'Solicitudes abiertas', 'count' => (int) ($operationalData['open_requests'] ?? 0), 'module' => 'requests', 'link' => '?module=requests', 'severity' => (int) ($operationalData['open_requests'] ?? 0) > 0 ? 'warning' : 'normal'],
            ['title' => 'Órdenes pendientes', 'count' => (int) ($operationalData['pending_orders'] ?? 0), 'module' => 'procurement', 'link' => '?module=procurement', 'severity' => (int) ($operationalData['pending_orders'] ?? 0) > 0 ? 'warning' : 'normal'],
        ];

        $sections['alerts'] = ['title' => 'Alertas', 'items' => $alerts];

        if ($selectedWidgetIds === []) {
            $selectedWidgetIds = array_map(static fn (array $widget): string => (string) ($widget['id'] ?? ''), $widgets);
        }

        return [
            'company' => $company->fetch() ?: [],
            'totals' => $totalsData,
            'recent' => $recentRows,
            'metrics' => array_merge($metricsData, [
                'cost_per_unit' => $costPerUnit,
                'production_per_hectare' => $productionPerHectare,
                'profitability' => $profitability,
                'inventory_alert_count' => $inventoryAlertCount,
            ]),
            'operational' => $operationalData,
            'cost_series' => array_reverse($costSeries),
            'production_series' => array_reverse($productionSeries),
            'inventory_alerts' => $inventoryAlertRows,
            'kpis' => $kpis,
            'alerts' => $alerts,
            'sections' => $sections,
            'analyses' => $analyses,
            'widgets' => $widgets,
            'selected_widgets' => $selectedWidgetIds,
            'customization' => $customization,
            'period' => $period,
            'reference_date' => $fromDate->format('Y-m-d'),
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'filters' => ['process' => $process, 'farm_id' => $farmId, 'block_id' => $blockId, 'date_from' => $periodStart, 'date_to' => $periodEnd],
            'filter_options' => $this->filterOptions(),
            'activity_dates' => $this->activityDates($periodStart, $periodEnd, $expenseFilters, $laborFilters, $productionFilters),
        ];
    }

    public function saveView(string $name, array $layout, ?int $userId = null): void
    {
        $name = trim($name);
        if ($name === '') {
            throw new \RuntimeException('El nombre de la vista es obligatorio.');
        }
        $prefix = $userId !== null ? 'dashboard.user.' . $userId . '.view.' : 'dashboard.view.';
        $key = $prefix . $this->slug($name);
        $layout['label'] = $name;
        $this->connection->prepare('INSERT INTO company_settings (company_id, setting_key, setting_value) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)')->execute([
            $this->companyId,
            $key,
            json_encode($layout, JSON_UNESCAPED_UNICODE),
        ]);
        $activeKey = $userId !== null ? 'dashboard.user.' . $userId . '.active_view' : 'dashboard.active_view';
        $this->connection->prepare('INSERT INTO company_settings (company_id, setting_key, setting_value) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)')->execute([
            $this->companyId,
            $activeKey,
            $this->slug($name),
        ]);
    }

    public function defaultDateRange(): array
    {
        $query = $this->connection->prepare(
            'SELECT GREATEST(' .
            "COALESCE((SELECT MAX(entry_date) FROM expense_entries WHERE company_id = ?), '0000-00-00')," .
            "COALESCE((SELECT MAX(labor_date) FROM labor_entries WHERE company_id = ?), '0000-00-00')," .
            "COALESCE((SELECT MAX(production_date) FROM production_entries WHERE company_id = ?), '0000-00-00')," .
            "COALESCE((SELECT MAX(movement_date) FROM inventory_movements WHERE company_id = ?), '0000-00-00')" .
            ') AS latest_date'
        );
        $query->execute([$this->companyId, $this->companyId, $this->companyId, $this->companyId]);
        $latestDate = (string) $query->fetchColumn();

        if ($latestDate === '' || $latestDate === '0000-00-00') {
            $today = new DateTimeImmutable('today');
            return [
                'date_from' => $today->format('Y-m-01'),
                'date_to' => $today->format('Y-m-d'),
            ];
        }

        $latest = DateTimeImmutable::createFromFormat('!Y-m-d', $latestDate) ?: new DateTimeImmutable('today');
        return [
            'date_from' => $latest->modify('first day of this month')->format('Y-m-d'),
            'date_to' => $latest->format('Y-m-d'),
        ];
    }

    private function slug(string $value): string
    {
        $slug = preg_replace('/[^a-z0-9]+/i', '-', strtolower(trim($value)));
        return trim($slug, '-');
    }

    private function activityDates(string $periodStart, string $periodEnd, string $expenseFilters, string $laborFilters, string $productionFilters): array
    {
        $query = $this->connection->prepare("SELECT activity_date FROM (SELECT entry_date AS activity_date FROM expense_entries WHERE company_id = ?{$expenseFilters} UNION SELECT labor_date AS activity_date FROM labor_entries WHERE company_id = ?{$laborFilters} UNION SELECT production_date AS activity_date FROM production_entries WHERE company_id = ?{$productionFilters} UNION SELECT movement_date AS activity_date FROM inventory_movements WHERE company_id = ?) activity_dates ORDER BY activity_date");
        $query->execute([$this->companyId, $this->companyId, $this->companyId, $this->companyId]);
        return array_values(array_map(static fn (array $row): string => (string) $row['activity_date'], $query->fetchAll()));
    }

    public function filterOptions(): array
    {
        $farms = $this->connection->prepare('SELECT id, name FROM farms WHERE company_id = ? AND active = 1 ORDER BY name');
        $farms->execute([$this->companyId]);
        $blocks = $this->connection->prepare('SELECT id, code, name FROM blocks WHERE company_id = ? AND active = 1 ORDER BY name, code');
        $blocks->execute([$this->companyId]);
        $processes = $this->connection->prepare("SELECT DISTINCT process FROM (SELECT description AS process FROM expense_entries WHERE company_id = ? UNION SELECT labor_type AS process FROM labor_entries WHERE company_id = ? UNION SELECT activity AS process FROM production_entries WHERE company_id = ?) process_options WHERE process <> '' ORDER BY process");
        $processes->execute([$this->companyId, $this->companyId, $this->companyId]);
        return ['processes' => $processes->fetchAll(), 'farms' => $farms->fetchAll(), 'blocks' => $blocks->fetchAll()];
    }

    private function dashboardSettings(string $department = 'general', ?int $userId = null): array
    {
        $query = $this->connection->prepare('SELECT setting_key, setting_value FROM company_settings WHERE company_id = ? ORDER BY setting_key');
        $query->execute([$this->companyId]);
        $rows = $query->fetchAll();
        $savedViews = [];
        $activeView = '';
        $activeUserKey = $userId !== null ? 'dashboard.user.' . $userId . '.active_view' : '';
        $userViewPrefix = $userId !== null ? 'dashboard.user.' . $userId . '.view.' : '';
        foreach ($rows as $row) {
            $key = (string) ($row['setting_key'] ?? '');
            $value = (string) ($row['setting_value'] ?? '');
            if ($userId !== null && str_starts_with($key, $userViewPrefix)) {
                $layout = json_decode($value, true) ?: [];
                $savedViews[] = [
                    'name' => substr($key, strlen($userViewPrefix)),
                    'label' => $layout['label'] ?? substr($key, strlen($userViewPrefix)),
                    'layout' => $layout,
                ];
                continue;
            }
            if (str_starts_with($key, 'dashboard.view.')) {
                $layout = json_decode($value, true) ?: [];
                $savedViews[] = [
                    'name' => substr($key, strlen('dashboard.view.')),
                    'label' => $layout['label'] ?? substr($key, strlen('dashboard.view.')),
                    'layout' => $layout,
                ];
                continue;
            }
            if ($userId !== null && $key === $activeUserKey) {
                $activeView = trim($value);
                continue;
            }
            if ($key === 'dashboard.active_view' && $activeView === '') {
                $activeView = trim($value);
            }
        }
        if ($activeView === '') {
            $defaults = [
                'gerencia' => 'gerencia',
                'administracion' => 'control',
                'produccion' => 'operaciones',
                'bodega' => 'logistica',
            ];
            $activeView = $defaults[$department] ?? 'gerencia';
        }
        if ($activeView === '' && $savedViews !== []) {
            $activeView = $savedViews[0]['name'];
        }
        $savedViews = array_values($savedViews);
        return [
            'saved_views' => $savedViews,
            'active_view' => $activeView,
            'widgets' => ['cost-trend', 'production-trend', 'inventory-alerts', 'recent-activity'],
        ];
    }

    private function series(string $sql): array
    {
        $query = $this->connection->prepare($sql);
        $query->execute([$this->companyId]);
        return $query->fetchAll();
    }
}

