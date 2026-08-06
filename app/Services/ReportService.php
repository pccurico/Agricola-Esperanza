<?php

declare(strict_types=1);

namespace AgroPCC\Services;

use DateTimeImmutable;
use PDO;

final class ReportService extends BaseService
{
    public function __construct(protected readonly PDO $connection, protected readonly int $companyId)
    {
    }

    public function summary(array $filters = [], ?string $reportType = null): array
    {
        $reportType = $this->normalizeReportType($reportType);
        [$dateFrom, $dateTo] = $this->dateRange($filters);
        $farmId = max(0, (int) ($filters['farm_id'] ?? 0));
        $blockId = max(0, (int) ($filters['block_id'] ?? 0));
        $seasonId = max(0, (int) ($filters['season_id'] ?? 0));
        $centerId = max(0, (int) ($filters['cost_center_id'] ?? 0));
        $workerId = max(0, (int) ($filters['worker_id'] ?? 0));
        $supervisorId = max(0, (int) ($filters['supervisor_id'] ?? 0));
        $process = trim((string) ($filters['process'] ?? ''));

        $scopeFilters = compact('farmId', 'blockId', 'seasonId', 'centerId', 'workerId', 'supervisorId');
        $expense = $this->scope('e', 'e.entry_date', 'e.description', $dateFrom, $dateTo, $process, $scopeFilters);
        $labor = $this->scope('l', 'l.labor_date', 'l.labor_type', $dateFrom, $dateTo, $process, $scopeFilters);
        $production = $this->scope('p', 'p.production_date', 'p.activity', $dateFrom, $dateTo, $process, $scopeFilters);

        $base = $this->connection->prepare('SELECT COALESCE((SELECT SUM(e.amount) FROM expense_entries e WHERE ' . $expense['where'] . '), 0) + COALESCE((SELECT SUM(l.total_amount) FROM labor_entries l WHERE ' . $labor['where'] . '), 0) AS total, COALESCE((SELECT COUNT(*) FROM expense_entries e WHERE ' . $expense['where'] . '), 0) + COALESCE((SELECT COUNT(*) FROM labor_entries l WHERE ' . $labor['where'] . '), 0) AS entries');
        $base->execute(array_merge($expense['params'], $labor['params'], $expense['params'], $labor['params']));
        $summary = $base->fetch() ?: ['total' => 0, 'entries' => 0];

        $productionQuery = $this->connection->prepare('SELECT COALESCE(SUM(p.quantity), 0) AS quantity, COUNT(*) AS entries, COUNT(DISTINCT p.unit) AS units, MIN(p.unit) AS unit FROM production_entries p WHERE ' . $production['where']);
        $productionQuery->execute($production['params']);
        $productionData = $productionQuery->fetch() ?: ['quantity' => 0, 'entries' => 0, 'units' => 0, 'unit' => ''];

        $hectares = $this->hectares($farmId, $blockId);
        $totalCost = (float) ($summary['total'] ?? 0);
        $productionQuantity = (float) ($productionData['quantity'] ?? 0);
        $summary['total'] = $totalCost;
        $summary['production'] = $productionQuantity;
        $summary['production_unit'] = (int) ($productionData['units'] ?? 0) === 1 ? (string) ($productionData['unit'] ?? '') : 'unidades';
        $summary['production_entries'] = (int) ($productionData['entries'] ?? 0);
        $summary['hectares'] = $hectares;
        $summary['cost_per_hectare'] = $hectares > 0 ? $totalCost / $hectares : 0;
        $summary['cost_per_unit'] = $productionQuantity > 0 ? $totalCost / $productionQuantity : 0;

        $laborSummary = $this->connection->prepare('SELECT COALESCE(SUM(l.quantity), 0) AS quantity, COALESCE(SUM(l.total_amount), 0) AS total, COUNT(DISTINCT l.worker_id) AS workers FROM labor_entries l WHERE ' . $labor['where']);
        $laborSummary->execute($labor['params']);
        $laborData = $laborSummary->fetch() ?: ['quantity' => 0, 'total' => 0, 'workers' => 0];

        $budget = $this->budgetSummary($dateFrom, $dateTo, $farmId, $process, $seasonId, $centerId);
        $comparisons = $this->comparisons($dateFrom, $dateTo, $process, $scopeFilters);
        $trends = $this->trends($dateFrom, $dateTo, $process, $scopeFilters);
        $alerts = $this->inventoryAlerts($farmId, $blockId, $dateFrom, $dateTo);
        $summary['production_per_hectare'] = $hectares > 0 ? $productionQuantity / $hectares : 0;
        $summary['labor_productivity'] = $laborData['quantity'] > 0 ? $productionQuantity / $laborData['quantity'] : 0;
        $summary['alert_count'] = count($alerts);
        $summary['profitability'] = $totalCost > 0 ? $productionQuantity / $totalCost : 0;

        return [
            'summary' => $summary,
            'labor_summary' => $laborData,
            'budget' => $budget,
            'categories' => $this->fetchRows('SELECT c.category, COALESCE(SUM(e.amount), 0) AS total FROM expense_entries e INNER JOIN cost_centers c ON c.id = e.cost_center_id WHERE ' . $expense['where'] . ' GROUP BY c.category ORDER BY total DESC', $expense['params']),
            'seasons' => $this->fetchRows('SELECT s.name, COALESCE(SUM(e.amount), 0) AS total FROM expense_entries e INNER JOIN seasons s ON s.id = e.season_id WHERE ' . $expense['where'] . ' GROUP BY s.id ORDER BY s.starts_on DESC', $expense['params']),
            'farms' => $this->costByFarm($expense, $labor),
            'processes' => $this->costByProcess($expense, $labor),
            'workers' => $this->fetchRows('SELECT w.full_name, COALESCE(SUM(l.quantity), 0) AS quantity, COALESCE(SUM(l.total_amount), 0) AS total FROM labor_entries l INNER JOIN workers w ON w.id = l.worker_id WHERE ' . $labor['where'] . ' GROUP BY w.id, w.full_name ORDER BY total DESC LIMIT 10', $labor['params']),
            'blocks' => $this->fetchRows('SELECT COALESCE(f.name, "Sin fundo") AS farm_name, COALESCE(b.name, "Sin cuartel") AS block_name, COALESCE(SUM(p.quantity), 0) AS quantity, MIN(p.unit) AS unit FROM production_entries p LEFT JOIN farms f ON f.id = p.farm_id LEFT JOIN blocks b ON b.id = p.block_id WHERE ' . $production['where'] . ' GROUP BY p.farm_id, p.block_id, f.name, b.name ORDER BY quantity DESC LIMIT 12', $production['params']),
            'centers' => $this->fetchRows('SELECT c.name, c.category, COALESCE(SUM(e.amount), 0) AS total FROM expense_entries e INNER JOIN cost_centers c ON c.id = e.cost_center_id WHERE ' . $expense['where'] . ' GROUP BY c.id, c.name, c.category ORDER BY total DESC LIMIT 12', $expense['params']),
            'alerts' => $alerts,
            'comparisons' => $comparisons,
            'trends' => $trends,
            'report_blueprint' => $this->reportBlueprint($reportType),
            'report_focus' => $this->buildReportFocus($reportType, $dateFrom, $dateTo, $process, $scopeFilters),
            'filters' => ['date_from' => $dateFrom, 'date_to' => $dateTo, 'farm_id' => $farmId, 'block_id' => $blockId, 'season_id' => $seasonId, 'cost_center_id' => $centerId, 'worker_id' => $workerId, 'supervisor_id' => $supervisorId, 'process' => $process],
            'filter_options' => $this->filterOptions(),
        ];
    }

    private function normalizeReportType(?string $reportType): string
    {
        $value = strtolower(trim((string) ($reportType ?? 'executive')));
        return in_array($value, ['executive', 'production', 'costs', 'profitability', 'labor', 'inventory', 'procurement', 'finance', 'budgets', 'machinery', 'productivity', 'comparatives', 'trends', 'kpis'], true) ? $value : 'executive';
    }

    private function reportBlueprint(string $reportType): array
    {
        return match ($reportType) {
            'production' => [
                'question' => '¿Qué estoy produciendo y dónde?',
                'answer' => 'Enfoque en producción por predio, cuartel, cultivo, variedad y proceso.',
                'focus' => ['production', 'yield', 'location'],
            ],
            'costs' => [
                'question' => '¿Dónde estoy gastando el dinero?',
                'answer' => 'Enfoque en distribución de costos, centros, procesos y top gastos.',
                'focus' => ['costs', 'centers', 'processes'],
            ],
            'inventory' => [
                'question' => '¿Qué riesgo tiene mi stock?',
                'answer' => 'Enfoque en stock crítico, valor del inventario, rotación y consumo.',
                'focus' => ['stock', 'rotation', 'consumption'],
            ],
            'labor' => [
                'question' => '¿Cómo está rindiendo el personal?',
                'answer' => 'Enfoque en jornadas, productividad, costos laborales y rendimiento por trabajador.',
                'focus' => ['labor', 'productivity', 'workers'],
            ],
            'procurement' => [
                'question' => '¿Cómo está el abastecimiento?',
                'answer' => 'Enfoque en órdenes, recepciones, proveedores y cumplimiento.',
                'focus' => ['orders', 'suppliers', 'delivery'],
            ],
            'finance' => [
                'question' => '¿Cuál es la salud financiera?',
                'answer' => 'Enfoque en gastos, presupuesto ejecutado y variación de caja.',
                'focus' => ['budget', 'expenses', 'variance'],
            ],
            default => [
                'question' => '¿Cómo está la empresa hoy?',
                'answer' => 'Enfoque en KPIs globales, resultados y comparativos de negocio.',
                'focus' => ['overview', 'finance', 'performance'],
            ],
        };
    }

    private function buildReportFocus(string $reportType, string $dateFrom, string $dateTo, string $process, array $scopeFilters): array
    {
        $expense = $this->scope('e', 'e.entry_date', 'e.description', $dateFrom, $dateTo, $process, $scopeFilters);
        $labor = $this->scope('l', 'l.labor_date', 'l.labor_type', $dateFrom, $dateTo, $process, $scopeFilters);
        $production = $this->scope('p', 'p.production_date', 'p.activity', $dateFrom, $dateTo, $process, $scopeFilters);

        return match ($reportType) {
            'production' => $this->productionFocus($production),
            'costs' => $this->costFocus($expense, $labor),
            'inventory' => $this->inventoryFocus($dateFrom, $dateTo, $scopeFilters),
            'labor' => $this->laborFocus($labor),
            'procurement' => $this->procurementFocus($dateFrom, $dateTo),
            'finance' => $this->financeFocus($dateFrom, $dateTo, $expense, $labor),
            default => [
                'summary' => [],
                'details' => [],
            ],
        };
    }

    private function productionFocus(array $productionScope): array
    {
        return [
            'by_farm' => $this->tryFetchRows('SELECT COALESCE(f.name, "Sin fundo") AS label, COALESCE(SUM(p.quantity), 0) AS value FROM production_entries p LEFT JOIN farms f ON f.id = p.farm_id WHERE ' . $productionScope['where'] . ' GROUP BY p.farm_id, f.name ORDER BY value DESC LIMIT 8', $productionScope['params']),
            'by_block' => $this->tryFetchRows('SELECT COALESCE(b.name, "Sin cuartel") AS label, COALESCE(SUM(p.quantity), 0) AS value FROM production_entries p LEFT JOIN blocks b ON b.id = p.block_id WHERE ' . $productionScope['where'] . ' GROUP BY p.block_id, b.name ORDER BY value DESC LIMIT 8', $productionScope['params']),
            'by_species' => $this->tryFetchRows('SELECT COALESCE(sp.name, "Sin especie") AS label, COALESCE(SUM(p.quantity), 0) AS value FROM production_entries p LEFT JOIN species sp ON sp.id = p.species_id WHERE ' . $productionScope['where'] . ' GROUP BY p.species_id, sp.name ORDER BY value DESC LIMIT 8', $productionScope['params']),
            'by_season' => $this->tryFetchRows('SELECT COALESCE(s.name, "Sin temporada") AS label, COALESCE(SUM(p.quantity), 0) AS value FROM production_entries p LEFT JOIN seasons s ON s.id = p.season_id WHERE ' . $productionScope['where'] . ' GROUP BY p.season_id, s.name ORDER BY value DESC LIMIT 8', $productionScope['params']),
        ];
    }

    private function costFocus(array $expenseScope, array $laborScope): array
    {
        return [
            'by_center' => $this->tryFetchRows('SELECT c.name AS label, COALESCE(SUM(e.amount), 0) AS value FROM expense_entries e INNER JOIN cost_centers c ON c.id = e.cost_center_id WHERE ' . $expenseScope['where'] . ' GROUP BY c.id, c.name ORDER BY value DESC LIMIT 8', $expenseScope['params']),
            'by_process' => $this->tryFetchRows('SELECT e.description AS label, COALESCE(SUM(e.amount), 0) AS value FROM expense_entries e WHERE ' . $expenseScope['where'] . ' GROUP BY e.description ORDER BY value DESC LIMIT 8', $expenseScope['params']),
            'by_farm' => $this->tryFetchRows('SELECT COALESCE(f.name, "Sin fundo") AS label, COALESCE(SUM(e.amount), 0) AS value FROM expense_entries e LEFT JOIN farms f ON f.id = e.farm_id WHERE ' . $expenseScope['where'] . ' GROUP BY e.farm_id, f.name ORDER BY value DESC LIMIT 8', $expenseScope['params']),
            'labor_cost' => $this->tryFetchRows('SELECT COALESCE(w.full_name, "Sin trabajador") AS label, COALESCE(SUM(l.total_amount), 0) AS value FROM labor_entries l LEFT JOIN workers w ON w.id = l.worker_id WHERE ' . $laborScope['where'] . ' GROUP BY l.worker_id, w.full_name ORDER BY value DESC LIMIT 8', $laborScope['params']),
        ];
    }

    private function inventoryFocus(string $dateFrom, string $dateTo, array $scopeFilters): array
    {
        $params = [$this->companyId, $dateFrom, $dateTo, $this->companyId];
        $where = 'i.company_id = ? AND i.active = 1';
        if ((int) ($scopeFilters['farmId'] ?? 0) > 0) {
            $where .= ' AND EXISTS (SELECT 1 FROM inventory_movements m LEFT JOIN blocks b ON b.id = m.block_id WHERE m.company_id = i.company_id AND m.item_id = i.id AND b.farm_id = ?)';
            $params[] = (int) ($scopeFilters['farmId'] ?? 0);
        }
        if ((int) ($scopeFilters['blockId'] ?? 0) > 0) {
            $where .= ' AND EXISTS (SELECT 1 FROM inventory_movements m WHERE m.company_id = i.company_id AND m.item_id = i.id AND m.block_id = ?)';
            $params[] = (int) ($scopeFilters['blockId'] ?? 0);
        }
        return [
            'critical_stock' => $this->tryFetchRows('SELECT i.name AS label, i.unit AS unit, COALESCE(SUM(CASE WHEN m.movement_type = "IN" THEN m.quantity WHEN m.movement_type = "OUT" THEN -m.quantity ELSE m.quantity END), 0) AS value, i.minimum_stock AS target FROM inventory_items i LEFT JOIN inventory_movements m ON m.item_id = i.id AND m.company_id = i.company_id AND m.movement_date BETWEEN ? AND ? WHERE ' . $where . ' GROUP BY i.id, i.name, i.unit, i.minimum_stock HAVING value <= i.minimum_stock ORDER BY value ASC, i.name LIMIT 8', array_merge([$dateFrom, $dateTo], $params)),
            'rotation' => $this->tryFetchRows('SELECT i.name AS label, COALESCE(SUM(m.quantity), 0) AS value FROM inventory_items i INNER JOIN inventory_movements m ON m.item_id = i.id AND m.company_id = i.company_id WHERE i.company_id = ? AND m.movement_date BETWEEN ? AND ? GROUP BY i.id, i.name ORDER BY value DESC LIMIT 8', [$this->companyId, $dateFrom, $dateTo]),
            'value' => $this->tryFetchRows('SELECT i.name AS label, COALESCE(SUM(CASE WHEN m.movement_type = "IN" THEN m.quantity WHEN m.movement_type = "OUT" THEN -m.quantity ELSE m.quantity END), 0) AS value FROM inventory_items i LEFT JOIN inventory_movements m ON m.item_id = i.id AND m.company_id = i.company_id WHERE i.company_id = ? AND m.movement_date BETWEEN ? AND ? GROUP BY i.id, i.name ORDER BY value DESC LIMIT 8', [$this->companyId, $dateFrom, $dateTo]),
        ];
    }

    private function laborFocus(array $laborScope): array
    {
        return [
            'by_worker' => $this->tryFetchRows('SELECT COALESCE(w.full_name, "Sin trabajador") AS label, COALESCE(SUM(l.quantity), 0) AS quantity, COALESCE(SUM(l.total_amount), 0) AS value FROM labor_entries l LEFT JOIN workers w ON w.id = l.worker_id WHERE ' . $laborScope['where'] . ' GROUP BY l.worker_id, w.full_name ORDER BY value DESC LIMIT 8', $laborScope['params']),
            'by_supervisor' => $this->tryFetchRows('SELECT COALESCE(w.full_name, "Sin supervisor") AS label, COALESCE(SUM(l.quantity), 0) AS value FROM labor_entries l LEFT JOIN workers w ON w.id = l.worker_id LEFT JOIN crews cr ON cr.company_id = l.company_id AND cr.active = 1 AND cr.supervisor_id = w.id WHERE ' . $laborScope['where'] . ' GROUP BY cr.supervisor_id, w.full_name ORDER BY value DESC LIMIT 8', $laborScope['params']),
        ];
    }

    private function procurementFocus(string $dateFrom, string $dateTo): array
    {
        return [
            'orders' => $this->tryFetchRows('SELECT order_number AS label, status AS status, order_date AS date FROM purchase_orders WHERE company_id = ? AND order_date BETWEEN ? AND ? ORDER BY order_date DESC LIMIT 8', [$this->companyId, $dateFrom, $dateTo]),
            'receptions' => $this->tryFetchRows('SELECT COALESCE(pr.order_number, "Sin orden") AS label, status AS status, received_on AS date FROM purchase_receptions pr LEFT JOIN purchase_orders po ON po.id = pr.purchase_order_id WHERE pr.company_id = ? AND pr.received_on BETWEEN ? AND ? ORDER BY pr.received_on DESC LIMIT 8', [$this->companyId, $dateFrom, $dateTo]),
            'suppliers' => $this->tryFetchRows('SELECT COALESCE(s.business_name, "Sin proveedor") AS label, COUNT(*) AS value FROM purchase_orders po LEFT JOIN suppliers s ON s.id = po.supplier_id WHERE po.company_id = ? AND po.order_date BETWEEN ? AND ? GROUP BY po.supplier_id, s.business_name ORDER BY value DESC LIMIT 8', [$this->companyId, $dateFrom, $dateTo]),
        ];
    }

    private function financeFocus(string $dateFrom, string $dateTo, array $expenseScope, array $laborScope): array
    {
        return [
            'by_category' => $this->tryFetchRows('SELECT c.category AS label, COALESCE(SUM(e.amount), 0) AS value FROM expense_entries e INNER JOIN cost_centers c ON c.id = e.cost_center_id WHERE ' . $expenseScope['where'] . ' GROUP BY c.category ORDER BY value DESC LIMIT 8', $expenseScope['params']),
            'budget' => $this->tryFetchRows('SELECT COALESCE(b.amount, 0) AS value, b.period_start AS date FROM budgets b WHERE b.company_id = ? AND b.period_start <= ? AND b.period_end >= ? ORDER BY b.period_start DESC LIMIT 8', [$this->companyId, $dateTo, $dateFrom]),
            'labor' => $this->tryFetchRows('SELECT COALESCE(SUM(l.total_amount), 0) AS value FROM labor_entries l WHERE ' . $laborScope['where'], $laborScope['params']),
        ];
    }

    private function dateRange(array $filters): array
    {
        $today = new DateTimeImmutable('today');
        $from = DateTimeImmutable::createFromFormat('!Y-m-d', (string) ($filters['date_from'] ?? '')) ?: $today->modify('first day of this month');
        $to = DateTimeImmutable::createFromFormat('!Y-m-d', (string) ($filters['date_to'] ?? '')) ?: $today;
        if ($to < $from) {
            [$from, $to] = [$to, $from];
        }
        return [$from->format('Y-m-d'), $to->format('Y-m-d')];
    }

    private function scope(string $alias, string $dateColumn, string $processColumn, string $dateFrom, string $dateTo, string $process, array $filters): array
    {
        $where = $alias . '.company_id = ? AND ' . $dateColumn . ' BETWEEN ? AND ?';
        $params = [$this->companyId, $dateFrom, $dateTo];
        $farmId = (int) ($filters['farmId'] ?? 0);
        $blockId = (int) ($filters['blockId'] ?? 0);
        $seasonId = (int) ($filters['seasonId'] ?? 0);
        $centerId = (int) ($filters['centerId'] ?? 0);
        $workerId = (int) ($filters['workerId'] ?? 0);
        $supervisorId = (int) ($filters['supervisorId'] ?? 0);
        if ($alias !== 'p') {
            $where .= " AND {$alias}.status = 'POSTED'";
        }
        if ($farmId > 0) {
            $where .= ' AND ' . $alias . '.farm_id = ?';
            $params[] = $farmId;
        }
        if ($blockId > 0) {
            $where .= ' AND ' . $alias . '.block_id = ?';
            $params[] = $blockId;
        }
        if ($seasonId > 0) {
            $where .= ' AND ' . $alias . '.season_id = ?';
            $params[] = $seasonId;
        }
        if ($alias === 'e' && $centerId > 0) {
            $where .= ' AND ' . $alias . '.cost_center_id = ?';
            $params[] = $centerId;
        }
        if ($alias === 'l' && $workerId > 0) {
            $where .= ' AND ' . $alias . '.worker_id = ?';
            $params[] = $workerId;
        }
        if ($alias === 'l' && $supervisorId > 0) {
            $where .= ' AND EXISTS (SELECT 1 FROM crew_workers cw INNER JOIN crews cr ON cr.id = cw.crew_id WHERE cw.worker_id = l.worker_id AND cr.company_id = l.company_id AND cr.supervisor_id = ?)';
            $params[] = $supervisorId;
        }
        if ($process !== '') {
            $where .= ' AND ' . $processColumn . ' = ?';
            $params[] = $process;
        }
        return ['where' => $where, 'params' => $params];
    }

    private function hectares(int $farmId, int $blockId): float
    {
        if ($blockId > 0) {
            $query = $this->connection->prepare('SELECT COALESCE(hectares, 0) FROM blocks WHERE id = ? AND company_id = ?');
            $query->execute([$blockId, $this->companyId]);
            return (float) $query->fetchColumn();
        }
        if ($farmId > 0) {
            $query = $this->connection->prepare('SELECT COALESCE(hectares, 0) FROM farms WHERE id = ? AND company_id = ?');
            $query->execute([$farmId, $this->companyId]);
            return (float) $query->fetchColumn();
        }
        $query = $this->connection->prepare('SELECT COALESCE(SUM(hectares), 0) FROM farms WHERE company_id = ? AND active = 1');
        $query->execute([$this->companyId]);
        return (float) $query->fetchColumn();
    }

    private function budgetSummary(string $dateFrom, string $dateTo, int $farmId, string $process, int $seasonId, int $centerId): array
    {
        $params = [$this->companyId, $dateTo, $dateFrom];
        $where = 'b.company_id = ? AND b.period_start <= ? AND b.period_end >= ?';
        if ($seasonId > 0) {
            $where .= ' AND b.season_id = ?';
            $params[] = $seasonId;
        }
        if ($centerId > 0) {
            $where .= ' AND b.cost_center_id = ?';
            $params[] = $centerId;
        }
        if ($farmId > 0) {
            $where .= ' AND EXISTS (SELECT 1 FROM expense_entries ef WHERE ef.company_id = b.company_id AND ef.farm_id = ? AND ef.season_id = b.season_id AND ef.cost_center_id = b.cost_center_id AND ef.status = "POSTED")';
            $params[] = $farmId;
        }
        if ($process !== '') {
            $where .= ' AND EXISTS (SELECT 1 FROM expense_entries ep WHERE ep.company_id = b.company_id AND ep.description = ? AND ep.season_id = b.season_id AND ep.cost_center_id = b.cost_center_id AND ep.status = "POSTED")';
            $params[] = $process;
        }
        $actualWhere = ' AND e.entry_date BETWEEN ? AND ?';
        $actualParams = [$dateFrom, $dateTo];
        if ($farmId > 0) {
            $actualWhere .= ' AND e.farm_id = ?';
            $actualParams[] = $farmId;
        }
        if ($process !== '') {
            $actualWhere .= ' AND e.description = ?';
            $actualParams[] = $process;
        }
        $budget = $this->connection->prepare('SELECT COALESCE(SUM(b.amount), 0) AS planned, COALESCE((SELECT SUM(e.amount) FROM expense_entries e WHERE e.company_id = b.company_id AND e.season_id = b.season_id AND e.cost_center_id = b.cost_center_id AND e.status = "POSTED"' . $actualWhere . '), 0) AS actual FROM budgets b WHERE ' . $where);
        $budget->execute(array_merge($actualParams, $params));
        $row = $budget->fetch() ?: ['planned' => 0, 'actual' => 0];
        $planned = (float) $row['planned'];
        $actual = (float) $row['actual'];
        return ['planned' => $planned, 'actual' => $actual, 'variance' => $planned - $actual, 'execution' => $planned > 0 ? ($actual / $planned) * 100 : 0];
    }

    private function costByFarm(array $expense, array $labor): array
    {
        $sql = 'SELECT name, SUM(total) AS total FROM ((SELECT COALESCE(f.name, "Sin fundo") AS name, SUM(e.amount) AS total FROM expense_entries e LEFT JOIN farms f ON f.id = e.farm_id WHERE ' . $expense['where'] . ' GROUP BY e.farm_id, f.name) UNION ALL (SELECT COALESCE(f.name, "Sin fundo") AS name, SUM(l.total_amount) AS total FROM labor_entries l LEFT JOIN farms f ON f.id = l.farm_id WHERE ' . $labor['where'] . ' GROUP BY l.farm_id, f.name)) scoped_costs GROUP BY name ORDER BY total DESC';
        return $this->fetchRows($sql, array_merge($expense['params'], $labor['params']));
    }

    private function costByProcess(array $expense, array $labor): array
    {
        $sql = 'SELECT process, SUM(total) AS total FROM ((SELECT e.description AS process, SUM(e.amount) AS total FROM expense_entries e WHERE ' . $expense['where'] . ' GROUP BY e.description) UNION ALL (SELECT l.labor_type AS process, SUM(l.total_amount) AS total FROM labor_entries l WHERE ' . $labor['where'] . ' GROUP BY l.labor_type)) scoped_costs GROUP BY process ORDER BY total DESC LIMIT 12';
        return $this->fetchRows($sql, array_merge($expense['params'], $labor['params']));
    }

    private function filterOptions(): array
    {
        $farms = $this->connection->prepare('SELECT id, name FROM farms WHERE company_id = ? AND active = 1 ORDER BY name');
        $farms->execute([$this->companyId]);
        $blocks = $this->connection->prepare('SELECT id, farm_id, code, name FROM blocks WHERE company_id = ? AND active = 1 ORDER BY name, code');
        $blocks->execute([$this->companyId]);
        $processes = $this->connection->prepare("SELECT DISTINCT process FROM (SELECT description AS process FROM expense_entries WHERE company_id = ? UNION SELECT labor_type AS process FROM labor_entries WHERE company_id = ? UNION SELECT activity AS process FROM production_entries WHERE company_id = ?) process_options WHERE process <> '' ORDER BY process");
        $processes->execute([$this->companyId, $this->companyId, $this->companyId]);
        $seasons = $this->connection->prepare('SELECT id, name FROM seasons WHERE company_id = ? ORDER BY starts_on DESC');
        $seasons->execute([$this->companyId]);
        $centers = $this->connection->prepare('SELECT id, name, category FROM cost_centers WHERE company_id = ? AND active = 1 ORDER BY category, name');
        $centers->execute([$this->companyId]);
        $workers = $this->connection->prepare('SELECT id, full_name FROM workers WHERE company_id = ? AND active = 1 ORDER BY full_name');
        $workers->execute([$this->companyId]);
        $supervisors = $this->connection->prepare('SELECT DISTINCT w.id, w.full_name FROM crews cr INNER JOIN workers w ON w.id = cr.supervisor_id WHERE cr.company_id = ? AND cr.active = 1 AND cr.supervisor_id IS NOT NULL ORDER BY w.full_name');
        $supervisors->execute([$this->companyId]);
        $suppliers = $this->connection->prepare('SELECT id, business_name FROM suppliers WHERE company_id = ? AND active = 1 ORDER BY business_name');
        $suppliers->execute([$this->companyId]);
        $warehouses = $this->connection->prepare('SELECT id, name FROM warehouses WHERE company_id = ? ORDER BY name');
        $warehouses->execute([$this->companyId]);
        $products = $this->connection->prepare('SELECT id, name, sku FROM inventory_items WHERE company_id = ? AND active = 1 ORDER BY name');
        $products->execute([$this->companyId]);
        $inventoryCategories = $this->connection->prepare('SELECT DISTINCT category FROM inventory_items WHERE company_id = ? AND category <> "" ORDER BY category');
        $inventoryCategories->execute([$this->companyId]);
        $families = $this->connection->prepare('SELECT DISTINCT category AS family FROM inventory_items WHERE company_id = ? AND category <> "" ORDER BY category');
        $families->execute([$this->companyId]);
        $crops = $this->connection->prepare('SELECT DISTINCT name FROM species WHERE company_id = ? ORDER BY name');
        $crops->execute([$this->companyId]);
        $varieties = $this->connection->prepare('SELECT DISTINCT variety FROM species WHERE company_id = ? AND variety <> "" ORDER BY variety');
        $varieties->execute([$this->companyId]);
        $machineTypes = $this->connection->prepare('SELECT DISTINCT machinery_type FROM machinery WHERE company_id = ? AND machinery_type <> "" ORDER BY machinery_type');
        $machineTypes->execute([$this->companyId]);
        $crews = $this->connection->prepare('SELECT id, name FROM crews WHERE company_id = ? AND active = 1 ORDER BY name');
        $crews->execute([$this->companyId]);

        return [
            'processes' => $processes->fetchAll(),
            'farms' => $farms->fetchAll(),
            'blocks' => $blocks->fetchAll(),
            'seasons' => $seasons->fetchAll(),
            'centers' => $centers->fetchAll(),
            'workers' => $workers->fetchAll(),
            'supervisors' => $supervisors->fetchAll(),
            'suppliers' => $suppliers->fetchAll(),
            'warehouses' => $warehouses->fetchAll(),
            'products' => $products->fetchAll(),
            'inventory_categories' => $inventoryCategories->fetchAll(PDO::FETCH_COLUMN),
            'families' => $families->fetchAll(PDO::FETCH_COLUMN),
            'crops' => $crops->fetchAll(PDO::FETCH_COLUMN),
            'varieties' => $varieties->fetchAll(PDO::FETCH_COLUMN),
            'machine_types' => $machineTypes->fetchAll(PDO::FETCH_COLUMN),
            'crews' => $crews->fetchAll(),
            'stock_status' => [
                ['value' => 'low', 'label' => 'Bajo mínimo'],
                ['value' => 'out', 'label' => 'Agotado'],
                ['value' => 'available', 'label' => 'Disponible'],
            ],
        ];
    }

    public function filterSchema(): array
    {
        return [
            'farm' => ['label' => 'Predio', 'name' => 'farm_id', 'type' => 'select', 'options' => 'farms', 'empty_label' => 'Todos'],
            'block' => ['label' => 'Cuartel', 'name' => 'block_id', 'type' => 'select', 'options' => 'blocks', 'empty_label' => 'Todos'],
            'season' => ['label' => 'Temporada', 'name' => 'season_id', 'type' => 'select', 'options' => 'seasons', 'empty_label' => 'Todas'],
            'cost_center' => ['label' => 'Centro de costo', 'name' => 'cost_center_id', 'type' => 'select', 'options' => 'centers', 'empty_label' => 'Todos'],
            'process' => ['label' => 'Proceso', 'name' => 'process', 'type' => 'select', 'options' => 'processes', 'empty_label' => 'Todos'],
            'worker' => ['label' => 'Trabajador', 'name' => 'worker_id', 'type' => 'select', 'options' => 'workers', 'empty_label' => 'Todos'],
            'supervisor' => ['label' => 'Supervisor', 'name' => 'supervisor_id', 'type' => 'select', 'options' => 'supervisors', 'empty_label' => 'Todos'],
            'supplier' => ['label' => 'Proveedor', 'name' => 'supplier_id', 'type' => 'select', 'options' => 'suppliers', 'empty_label' => 'Todos'],
            'warehouse' => ['label' => 'Bodega', 'name' => 'warehouse_id', 'type' => 'select', 'options' => 'warehouses', 'empty_label' => 'Todas'],
            'product' => ['label' => 'Producto', 'name' => 'product_id', 'type' => 'select', 'options' => 'products', 'empty_label' => 'Todos'],
            'category' => ['label' => 'Categoría', 'name' => 'category', 'type' => 'select', 'options' => 'inventory_categories', 'empty_label' => 'Todas'],
            'family' => ['label' => 'Familia', 'name' => 'family', 'type' => 'select', 'options' => 'families', 'empty_label' => 'Todas'],
            'crop' => ['label' => 'Cultivo', 'name' => 'crop', 'type' => 'select', 'options' => 'crops', 'empty_label' => 'Todos'],
            'variety' => ['label' => 'Variedad', 'name' => 'variety', 'type' => 'select', 'options' => 'varieties', 'empty_label' => 'Todas'],
            'machine_type' => ['label' => 'Tipo de máquina', 'name' => 'machine_type', 'type' => 'select', 'options' => 'machine_types', 'empty_label' => 'Todos'],
            'crew' => ['label' => 'Cuadrilla', 'name' => 'crew_id', 'type' => 'select', 'options' => 'crews', 'empty_label' => 'Todas'],
            'stock_status' => ['label' => 'Estado del stock', 'name' => 'stock_status', 'type' => 'select', 'options' => 'stock_status', 'empty_label' => 'Todos'],
        ];
    }

    private function comparisons(string $dateFrom, string $dateTo, string $process, array $filters): array
    {
        $currentStart = new DateTimeImmutable($dateFrom);
        $currentEnd = new DateTimeImmutable($dateTo);
        $previousEnd = $currentStart->modify('-1 day');
        $previousStart = $previousEnd->modify('-' . max(0, $currentStart->diff($currentEnd)->days) . ' days');
        return ['periods' => [
            ['label' => 'Periodo seleccionado', 'metrics' => $this->periodMetrics($dateFrom, $dateTo, $process, $filters)],
            ['label' => 'Periodo anterior', 'metrics' => $this->periodMetrics($previousStart->format('Y-m-d'), $previousEnd->format('Y-m-d'), $process, $filters)],
        ], 'seasons' => $this->seasonComparison($process, $filters)];
    }

    private function periodMetrics(string $dateFrom, string $dateTo, string $process, array $filters): array
    {
        $expense = $this->scope('e', 'e.entry_date', 'e.description', $dateFrom, $dateTo, $process, $filters);
        $labor = $this->scope('l', 'l.labor_date', 'l.labor_type', $dateFrom, $dateTo, $process, $filters);
        $production = $this->scope('p', 'p.production_date', 'p.activity', $dateFrom, $dateTo, $process, $filters);
        $query = $this->connection->prepare('SELECT COALESCE((SELECT SUM(e.amount) FROM expense_entries e WHERE ' . $expense['where'] . '), 0) + COALESCE((SELECT SUM(l.total_amount) FROM labor_entries l WHERE ' . $labor['where'] . '), 0) AS cost, COALESCE((SELECT SUM(p.quantity) FROM production_entries p WHERE ' . $production['where'] . '), 0) AS production, COALESCE((SELECT SUM(l.quantity) FROM labor_entries l WHERE ' . $labor['where'] . '), 0) AS labor');
        $query->execute(array_merge($expense['params'], $labor['params'], $production['params'], $labor['params']));
        return $query->fetch() ?: ['cost' => 0, 'production' => 0, 'labor' => 0];
    }

    private function seasonComparison(string $process, array $filters): array
    {
        $seasonQuery = $this->connection->prepare('SELECT id, name, starts_on, ends_on FROM seasons WHERE company_id = ? ORDER BY starts_on DESC LIMIT 2');
        $seasonQuery->execute([$this->companyId]);
        $rows = [];
        foreach ($seasonQuery->fetchAll() as $season) {
            $seasonFilters = $filters;
            $seasonFilters['seasonId'] = (int) $season['id'];
            $rows[] = ['label' => (string) $season['name'], 'metrics' => $this->periodMetrics((string) $season['starts_on'], (string) $season['ends_on'], $process, $seasonFilters)];
        }
        return $rows;
    }

    private function trends(string $dateFrom, string $dateTo, string $process, array $filters): array
    {
        $expense = $this->scope('e', 'e.entry_date', 'e.description', $dateFrom, $dateTo, $process, $filters);
        $labor = $this->scope('l', 'l.labor_date', 'l.labor_type', $dateFrom, $dateTo, $process, $filters);
        $production = $this->scope('p', 'p.production_date', 'p.activity', $dateFrom, $dateTo, $process, $filters);
        return [
            'costs' => $this->fetchRows('SELECT DATE_FORMAT(e.entry_date, "%Y-%m") AS period, SUM(e.amount) AS value FROM expense_entries e WHERE ' . $expense['where'] . ' GROUP BY period ORDER BY period', $expense['params']),
            'labor' => $this->fetchRows('SELECT DATE_FORMAT(l.labor_date, "%Y-%m") AS period, SUM(l.total_amount) AS value FROM labor_entries l WHERE ' . $labor['where'] . ' GROUP BY period ORDER BY period', $labor['params']),
            'production' => $this->fetchRows('SELECT DATE_FORMAT(p.production_date, "%Y-%m") AS period, SUM(p.quantity) AS value, MIN(p.unit) AS unit FROM production_entries p WHERE ' . $production['where'] . ' GROUP BY period ORDER BY period', $production['params']),
            'budget' => $this->fetchRows('SELECT DATE_FORMAT(e.entry_date, "%Y-%m") AS period, SUM(e.amount) AS value FROM expense_entries e WHERE ' . $expense['where'] . ' AND EXISTS (SELECT 1 FROM budgets b WHERE b.company_id = e.company_id AND b.season_id = e.season_id AND b.cost_center_id = e.cost_center_id AND e.entry_date BETWEEN b.period_start AND b.period_end) GROUP BY period ORDER BY period', $expense['params']),
        ];
    }

    private function inventoryAlerts(int $farmId, int $blockId, string $dateFrom, string $dateTo): array
    {
        $params = [$dateFrom, $dateTo, $this->companyId];
        $where = 'i.company_id = ? AND i.active = 1';
        if ($farmId > 0) {
            $where .= ' AND EXISTS (SELECT 1 FROM inventory_movements m LEFT JOIN blocks b ON b.id = m.block_id WHERE m.company_id = i.company_id AND m.item_id = i.id AND b.farm_id = ?)';
            $params[] = $farmId;
        }
        if ($blockId > 0) {
            $where .= ' AND EXISTS (SELECT 1 FROM inventory_movements m WHERE m.company_id = i.company_id AND m.item_id = i.id AND m.block_id = ?)';
            $params[] = $blockId;
        }

        $query = $this->connection->prepare('SELECT i.id, i.name, i.unit, i.minimum_stock, COALESCE(SUM(CASE WHEN m.movement_type = "IN" THEN m.quantity WHEN m.movement_type = "OUT" THEN -m.quantity ELSE m.quantity END), 0) AS stock FROM inventory_items i LEFT JOIN inventory_movements m ON m.item_id = i.id AND m.company_id = i.company_id AND m.movement_date BETWEEN ? AND ? WHERE ' . $where . ' GROUP BY i.id, i.name, i.unit, i.minimum_stock HAVING stock <= i.minimum_stock ORDER BY stock ASC, i.name LIMIT 5');
        $query->execute($params);
        return $query->fetchAll();
    }

    private function tryFetchRows(string $sql, array $params = []): array
    {
        try {
            $query = $this->connection->prepare($sql);
            $query->execute($params);
            return $query->fetchAll();
        } catch (\Throwable) {
            return [];
        }
    }

    protected function fetchRows(string $sql, array $params = []): array
    {
        $query = $this->connection->prepare($sql);
        $query->execute($params);
        return $query->fetchAll();
    }
}

