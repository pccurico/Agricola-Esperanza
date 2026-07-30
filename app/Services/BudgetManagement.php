<?php

declare(strict_types=1);

namespace CampoSur\Services;

use PDO;
use RuntimeException;

final class BudgetManagement extends BaseService
{
    public function __construct(private readonly PDO $connection, private readonly int $companyId)
    {
    }

    public function budgets(): array
    {
        $query = $this->connection->prepare('SELECT b.id, b.period_start, b.period_end, b.amount, b.status, s.name AS season_name, c.name AS center_name, c.category, COALESCE((SELECT SUM(e.amount) FROM expense_entries e WHERE e.company_id = b.company_id AND e.season_id = b.season_id AND e.cost_center_id = b.cost_center_id AND e.entry_date BETWEEN b.period_start AND b.period_end AND e.status = \'POSTED\'), 0) AS actual_amount FROM budgets b INNER JOIN seasons s ON s.id = b.season_id INNER JOIN cost_centers c ON c.id = b.cost_center_id WHERE b.company_id = ? ORDER BY b.period_start DESC, b.id DESC');
        $query->execute([$this->companyId]);
        return $query->fetchAll();
    }

    public function options(): array
    {
        $seasons = $this->connection->prepare('SELECT id, name FROM seasons WHERE company_id = ? AND active = 1 ORDER BY starts_on DESC');
        $seasons->execute([$this->companyId]);
        $centers = $this->connection->prepare('SELECT id, name, category FROM cost_centers WHERE company_id = ? AND active = 1 ORDER BY category, name');
        $centers->execute([$this->companyId]);
        return ['seasons' => $seasons->fetchAll(), 'centers' => $centers->fetchAll()];
    }

    public function create(array $input, int $userId): void
    {
        foreach (['season_id', 'cost_center_id', 'period_start', 'period_end', 'amount'] as $field) {
            if (trim((string) ($input[$field] ?? '')) === '') {
                throw new RuntimeException('Por favor, completa los datos del presupuesto.');
            }
        }
        if ($input['period_end'] <= $input['period_start'] || !is_numeric($input['amount']) || (float) $input['amount'] <= 0) {
            throw new RuntimeException('Revisa el perÃ­odo y el monto del presupuesto.');
        }
        foreach (['seasons', 'cost_centers'] as $table) {
            $id = $input[$table === 'seasons' ? 'season_id' : 'cost_center_id'];
            $query = $this->connection->prepare('SELECT id FROM ' . $table . ' WHERE id = ? AND company_id = ?');
            $query->execute([(int) $id, $this->companyId]);
            if (!$query->fetchColumn()) {
                throw new RuntimeException('La referencia seleccionada no pertenece a esta empresa.');
            }
        }
        $query = $this->connection->prepare('INSERT INTO budgets (company_id, season_id, cost_center_id, period_start, period_end, amount, notes, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $query->execute([$this->companyId, (int) $input['season_id'], (int) $input['cost_center_id'], $input['period_start'], $input['period_end'], $input['amount'], trim($input['notes']) ?: null, $userId]);
    }
}
