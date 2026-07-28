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
        $recent = $this->connection->prepare('SELECT description AS label, amount AS value, entry_date AS date FROM expense_entries WHERE company_id = ? ORDER BY entry_date DESC, id DESC LIMIT 5');
        $recent->execute([$this->companyId]);
        return ['company' => $company->fetch() ?: [], 'totals' => $totals->fetch() ?: ['total_cost' => 0, 'hectares' => 0, 'movements' => 0], 'recent' => $recent->fetchAll()];
    }
}
