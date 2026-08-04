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

    public function summary(array $filters = []): array
    {
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
            'filters' => ['date_from' => $dateFrom, 'date_to' => $dateTo, 'farm_id' => $farmId, 'block_id' => $blockId, 'season_id' => $seasonId, 'cost_center_id' => $centerId, 'worker_id' => $workerId, 'supervisor_id' => $supervisorId, 'process' => $process],
            'filter_options' => $this->filterOptions(),
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
        return ['processes' => $processes->fetchAll(), 'farms' => $farms->fetchAll(), 'blocks' => $blocks->fetchAll(), 'seasons' => $seasons->fetchAll(), 'centers' => $centers->fetchAll(), 'workers' => $workers->fetchAll(), 'supervisors' => $supervisors->fetchAll()];
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

    protected function fetchRows(string $sql, array $params = []): array
    {
        $query = $this->connection->prepare($sql);
        $query->execute($params);
        return $query->fetchAll();
    }
}

