<?php

declare(strict_types=1);

namespace CampoSur\Services;

use PDO;
use RuntimeException;

final class CostManagement
{
    public function __construct(private readonly PDO $connection, private readonly int $companyId)
    {
    }

    public function entries(?string $category = null): array
    {
        $sql = 'SELECT e.id, e.entry_date, e.description, e.amount, e.status, s.name AS season_name, f.name AS farm_name, b.name AS block_name, c.name AS center_name FROM expense_entries e INNER JOIN seasons s ON s.id = e.season_id LEFT JOIN farms f ON f.id = e.farm_id LEFT JOIN blocks b ON b.id = e.block_id INNER JOIN cost_centers c ON c.id = e.cost_center_id WHERE e.company_id = ?';
        $parameters = [$this->companyId];
        if ($category) {
            $sql .= ' AND c.category = ?';
            $parameters[] = $category;
        }
        $sql .= ' ORDER BY e.entry_date DESC, e.id DESC';
        $query = $this->connection->prepare($sql);
        $query->execute($parameters);
        return $query->fetchAll();
    }

    public function options(?string $category = null): array
    {
        return [
            'seasons' => $this->fetch('SELECT id, name FROM seasons WHERE company_id = ? AND active = 1 ORDER BY starts_on DESC'),
            'farms' => $this->fetch('SELECT id, name FROM farms WHERE company_id = ? AND active = 1 ORDER BY name'),
            'blocks' => $this->fetch('SELECT id, farm_id, code, name FROM blocks WHERE company_id = ? AND active = 1 ORDER BY code'),
            'centers' => $this->fetch('SELECT id, name, category FROM cost_centers WHERE company_id = ? AND active = 1 ORDER BY category, name'),
        ];
    }

    public function create(array $input, int $userId): void
    {
        foreach (['season_id', 'cost_center_id', 'entry_date', 'description', 'amount'] as $field) {
            if (trim((string) ($input[$field] ?? '')) === '') {
                throw new RuntimeException('Completa los campos obligatorios del costo.');
            }
        }
        if (!is_numeric($input['amount']) || (float) $input['amount'] <= 0) {
            throw new RuntimeException('El monto debe ser mayor que cero.');
        }
        $this->belongs('seasons', $input['season_id']);
        $this->belongs('cost_centers', $input['cost_center_id']);
        if (!empty($input['farm_id'])) {
            $this->belongs('farms', $input['farm_id']);
        }
        if (!empty($input['block_id'])) {
            $this->belongs('blocks', $input['block_id']);
        }
        $query = $this->connection->prepare('INSERT INTO expense_entries (company_id, season_id, farm_id, block_id, cost_center_id, entry_date, description, document_number, amount, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $query->execute([$this->companyId, (int) $input['season_id'], $input['farm_id'] ?: null, $input['block_id'] ?: null, (int) $input['cost_center_id'], $input['entry_date'], trim($input['description']), trim($input['document_number']) ?: null, $input['amount'], $userId]);
    }

    private function fetch(string $sql): array
    {
        $query = $this->connection->prepare($sql);
        $query->execute([$this->companyId]);
        return $query->fetchAll();
    }

    private function belongs(string $table, mixed $id): void
    {
        if (!in_array($table, ['seasons', 'cost_centers', 'farms', 'blocks'], true)) {
            throw new RuntimeException('Referencia no válida.');
        }
        $query = $this->connection->prepare('SELECT id FROM ' . $table . ' WHERE id = ? AND company_id = ?');
        $query->execute([(int) $id, $this->companyId]);
        if (!$query->fetchColumn()) {
            throw new RuntimeException('El registro seleccionado no pertenece a esta agrícola.');
        }
    }
}
