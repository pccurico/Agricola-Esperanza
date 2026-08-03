<?php

declare(strict_types=1);

namespace CampoSur\Services;

use DateTimeImmutable;
use PDO;

final class DashboardService extends BaseService
{
    public function __construct(private readonly PDO $connection, private readonly int $companyId)
    {
    }

    public function summary(string $period = 'month', ?string $referenceDate = null, array $filters = []): array
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
        $metrics = $this->connection->prepare("SELECT (SELECT COUNT(*) FROM farms WHERE company_id = ? AND active = 1) AS farms, (SELECT COUNT(*) FROM blocks WHERE company_id = ? AND active = 1) AS blocks, (SELECT COUNT(*) FROM workers WHERE company_id = ? AND active = 1) AS workers, (SELECT COUNT(*) FROM inventory_items WHERE company_id = ? AND active = 1) AS items, (SELECT COUNT(*) FROM machinery WHERE company_id = ? AND status = 'ACTIVE') AS machinery, (SELECT COALESCE(SUM(quantity), 0) FROM production_entries WHERE company_id = ? AND production_date BETWEEN '{$periodStart}' AND '{$periodEnd}'{$productionFilters}) AS production");
        $metrics->execute([$this->companyId, $this->companyId, $this->companyId, $this->companyId, $this->companyId, $this->companyId]);
        $operational = $this->connection->prepare('SELECT (SELECT COUNT(*) FROM tasks WHERE company_id = ? AND status NOT IN ("DONE", "CANCELLED")) AS pending_tasks, (SELECT COUNT(*) FROM internal_requests WHERE company_id = ? AND status IN ("REQUESTED", "APPROVED")) AS open_requests, (SELECT COUNT(*) FROM purchase_orders WHERE company_id = ? AND status IN ("SENT", "PARTIAL")) AS pending_orders');
        $operational->execute([$this->companyId, $this->companyId, $this->companyId]);
        $costLimit = $period === 'day' ? 14 : ($period === 'week' ? 12 : ($period === 'month' ? 12 : 5));
        $costFormat = $config['cost_format'];
        $productionFormat = $config['production_format'];
        $costSeries = $this->series("SELECT DATE_FORMAT(entry_date, '{$costFormat}') AS period, SUM(amount) AS value FROM expense_entries WHERE company_id = ? AND status = 'POSTED' AND entry_date BETWEEN '{$periodStart}' AND '{$periodEnd}'{$expenseFilters} GROUP BY period ORDER BY period DESC LIMIT {$costLimit}");
        $productionSeries = $this->series("SELECT DATE_FORMAT(production_date, '{$productionFormat}') AS period, SUM(quantity) AS value FROM production_entries WHERE company_id = ? AND production_date BETWEEN '{$periodStart}' AND '{$periodEnd}'{$productionFilters} GROUP BY period ORDER BY period DESC LIMIT {$costLimit}");
        $inventoryAlerts = $this->connection->prepare('SELECT i.name, i.unit, i.minimum_stock, COALESCE(SUM(CASE WHEN m.movement_type = "IN" THEN m.quantity WHEN m.movement_type = "OUT" THEN -m.quantity ELSE m.quantity END), 0) AS stock FROM inventory_items i LEFT JOIN inventory_movements m ON m.item_id = i.id AND m.company_id = i.company_id WHERE i.company_id = ? AND i.active = 1 GROUP BY i.id, i.name, i.unit, i.minimum_stock HAVING stock <= i.minimum_stock ORDER BY stock ASC, i.name LIMIT 6');
        $inventoryAlerts->execute([$this->companyId]);
        $metricsData = $metrics->fetch() ?: [];
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
        return [
            'company' => $company->fetch() ?: [],
            'totals' => $totals->fetch() ?: ['total_cost' => 0, 'hectares' => 0, 'movements' => 0],
            'recent' => $recent->fetchAll(),
            'metrics' => $metricsData,
            'operational' => $operational->fetch() ?: [],
            'cost_series' => array_reverse($costSeries),
            'production_series' => array_reverse($productionSeries),
            'inventory_alerts' => $inventoryAlerts->fetchAll(),
            'period' => $period,
            'reference_date' => $fromDate->format('Y-m-d'),
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'filters' => ['process' => $process, 'farm_id' => $farmId, 'block_id' => $blockId, 'date_from' => $periodStart, 'date_to' => $periodEnd],
            'filter_options' => $this->filterOptions(),
            'activity_dates' => $this->activityDates($periodStart, $periodEnd, $expenseFilters, $laborFilters, $productionFilters),
        ];
    }

    private function activityDates(string $periodStart, string $periodEnd, string $expenseFilters, string $laborFilters, string $productionFilters): array
    {
        $query = $this->connection->prepare("SELECT activity_date FROM (SELECT entry_date AS activity_date FROM expense_entries WHERE company_id = ?{$expenseFilters} UNION SELECT labor_date AS activity_date FROM labor_entries WHERE company_id = ?{$laborFilters} UNION SELECT production_date AS activity_date FROM production_entries WHERE company_id = ?{$productionFilters} UNION SELECT movement_date AS activity_date FROM inventory_movements WHERE company_id = ?) activity_dates ORDER BY activity_date");
        $query->execute([$this->companyId, $this->companyId, $this->companyId, $this->companyId]);
        return array_values(array_map(static fn (array $row): string => (string) $row['activity_date'], $query->fetchAll()));
    }

    private function filterOptions(): array
    {
        $farms = $this->connection->prepare('SELECT id, name FROM farms WHERE company_id = ? AND active = 1 ORDER BY name');
        $farms->execute([$this->companyId]);
        $blocks = $this->connection->prepare('SELECT id, code, name FROM blocks WHERE company_id = ? AND active = 1 ORDER BY name, code');
        $blocks->execute([$this->companyId]);
        $processes = $this->connection->prepare("SELECT DISTINCT process FROM (SELECT description AS process FROM expense_entries WHERE company_id = ? UNION SELECT labor_type AS process FROM labor_entries WHERE company_id = ? UNION SELECT activity AS process FROM production_entries WHERE company_id = ?) process_options WHERE process <> '' ORDER BY process");
        $processes->execute([$this->companyId, $this->companyId, $this->companyId]);
        return ['processes' => $processes->fetchAll(), 'farms' => $farms->fetchAll(), 'blocks' => $blocks->fetchAll()];
    }

    private function series(string $sql): array
    {
        $query = $this->connection->prepare($sql);
        $query->execute([$this->companyId]);
        return $query->fetchAll();
    }
}
