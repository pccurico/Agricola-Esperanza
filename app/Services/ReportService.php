<?php

declare(strict_types=1);

namespace CampoSur\Services;

use PDO;

final class ReportService extends BaseService
{
    public function __construct(private readonly PDO $connection, private readonly int $companyId)
    {
    }

    public function summary(): array
    {
        $base = $this->connection->prepare('SELECT COALESCE(SUM(amount), 0) AS total, COUNT(*) AS entries FROM expense_entries WHERE company_id = ? AND status = "POSTED"');
        $base->execute([$this->companyId]);
        $summary = $base->fetch();
        $byCategory = $this->connection->prepare('SELECT c.category, COALESCE(SUM(e.amount), 0) AS total FROM expense_entries e INNER JOIN cost_centers c ON c.id = e.cost_center_id WHERE e.company_id = ? AND e.status = "POSTED" GROUP BY c.category ORDER BY total DESC');
        $byCategory->execute([$this->companyId]);
        $bySeason = $this->connection->prepare('SELECT s.name, COALESCE(SUM(e.amount), 0) AS total FROM expense_entries e INNER JOIN seasons s ON s.id = e.season_id WHERE e.company_id = ? AND e.status = "POSTED" GROUP BY s.id ORDER BY s.starts_on DESC');
        $bySeason->execute([$this->companyId]);
        $byFarm = $this->connection->prepare('SELECT COALESCE(f.name, "Sin fundo") AS name, COALESCE(SUM(e.amount), 0) AS total FROM expense_entries e LEFT JOIN farms f ON f.id = e.farm_id WHERE e.company_id = ? AND e.status = "POSTED" GROUP BY e.farm_id ORDER BY total DESC');
        $byFarm->execute([$this->companyId]);
        return ['summary' => $summary, 'categories' => $byCategory->fetchAll(), 'seasons' => $bySeason->fetchAll(), 'farms' => $byFarm->fetchAll()];
    }
}
