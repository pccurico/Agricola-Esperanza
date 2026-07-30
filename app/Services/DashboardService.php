<?php

declare(strict_types=1);

namespace CampoSur\Services;

use PDO;

final class DashboardService
{
    public function __construct(private readonly PDO $connection, private readonly int $companyId)
    {
    }

    public function summary(): array
    {
        $company = $this->connection->prepare('SELECT trade_name, logo_path FROM companies WHERE id = ?');
        $company->execute([$this->companyId]);
        $totals = $this->connection->prepare('SELECT COALESCE((SELECT SUM(amount) FROM expense_entries WHERE company_id = ? AND status = "POSTED"), 0) + COALESCE((SELECT SUM(total_amount) FROM labor_entries WHERE company_id = ? AND status = "POSTED"), 0) AS total_cost, COALESCE((SELECT SUM(hectares) FROM farms WHERE company_id = ? AND active = 1), 0) AS hectares, COALESCE((SELECT COUNT(*) FROM expense_entries WHERE company_id = ?), 0) + COALESCE((SELECT COUNT(*) FROM labor_entries WHERE company_id = ?), 0) + COALESCE((SELECT COUNT(*) FROM inventory_movements WHERE company_id = ?), 0) AS movements');
        $totals->execute([$this->companyId, $this->companyId, $this->companyId, $this->companyId, $this->companyId, $this->companyId]);
        $recent = $this->connection->prepare('(SELECT description AS label, amount AS value, entry_date AS date, "Costo" AS type FROM expense_entries WHERE company_id = ? ORDER BY entry_date DESC, id DESC LIMIT 5) UNION ALL (SELECT labor_type AS label, total_amount AS value, labor_date AS date, "Labor" AS type FROM labor_entries WHERE company_id = ? ORDER BY labor_date DESC, id DESC LIMIT 5) ORDER BY date DESC LIMIT 8');
        $recent->execute([$this->companyId, $this->companyId]);
        $metrics = $this->connection->prepare('SELECT (SELECT COUNT(*) FROM farms WHERE company_id = ? AND active = 1) AS farms, (SELECT COUNT(*) FROM blocks WHERE company_id = ? AND active = 1) AS blocks, (SELECT COUNT(*) FROM workers WHERE company_id = ? AND active = 1) AS workers, (SELECT COUNT(*) FROM inventory_items WHERE company_id = ? AND active = 1) AS items, (SELECT COUNT(*) FROM machinery WHERE company_id = ? AND status = "ACTIVE") AS machinery, (SELECT COALESCE(SUM(quantity), 0) FROM production_entries WHERE company_id = ?) AS production');
        $metrics->execute([$this->companyId, $this->companyId, $this->companyId, $this->companyId, $this->companyId, $this->companyId]);
        $operational = $this->connection->prepare('SELECT (SELECT COUNT(*) FROM tasks WHERE company_id = ? AND status NOT IN ("DONE", "CANCELLED")) AS pending_tasks, (SELECT COUNT(*) FROM internal_requests WHERE company_id = ? AND status IN ("REQUESTED", "APPROVED")) AS open_requests, (SELECT COUNT(*) FROM purchase_orders WHERE company_id = ? AND status IN ("SENT", "PARTIAL")) AS pending_orders');
        $operational->execute([$this->companyId, $this->companyId, $this->companyId]);
        $costSeries = $this->series('SELECT DATE_FORMAT(entry_date, \'%Y-%m\') AS period, SUM(amount) AS value FROM expense_entries WHERE company_id = ? AND status = \'POSTED\' GROUP BY period ORDER BY period DESC LIMIT 6');
        $productionSeries = $this->series('SELECT DATE_FORMAT(production_date, \'%Y-%m\') AS period, SUM(quantity) AS value FROM production_entries WHERE company_id = ? GROUP BY period ORDER BY period DESC LIMIT 6');
        $inventoryAlerts = $this->connection->prepare('SELECT i.name, i.unit, i.minimum_stock, COALESCE(SUM(CASE WHEN m.movement_type = "IN" THEN m.quantity WHEN m.movement_type = "OUT" THEN -m.quantity ELSE m.quantity END), 0) AS stock FROM inventory_items i LEFT JOIN inventory_movements m ON m.item_id = i.id AND m.company_id = i.company_id WHERE i.company_id = ? AND i.active = 1 GROUP BY i.id, i.name, i.unit, i.minimum_stock HAVING stock <= i.minimum_stock ORDER BY stock ASC, i.name LIMIT 6');
        $inventoryAlerts->execute([$this->companyId]);
        return [
            'company' => $company->fetch() ?: [],
            'totals' => $totals->fetch() ?: ['total_cost' => 0, 'hectares' => 0, 'movements' => 0],
            'recent' => $recent->fetchAll(),
            'metrics' => $metrics->fetch() ?: [],
            'operational' => $operational->fetch() ?: [],
            'cost_series' => array_reverse($costSeries),
            'production_series' => array_reverse($productionSeries),
            'inventory_alerts' => $inventoryAlerts->fetchAll(),
        ];
    }

    private function series(string $sql): array
    {
        $query = $this->connection->prepare($sql);
        $query->execute([$this->companyId]);
        return $query->fetchAll();
    }
}
