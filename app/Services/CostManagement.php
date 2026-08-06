<?php

declare(strict_types=1);

namespace AgroPCC\Services;

use PDO;
use RuntimeException;

final class CostManagement extends BaseService
{
    public function __construct(protected readonly PDO $connection, protected readonly int $companyId)
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

    public function cashTransactions(): array
    {
        $query = $this->connection->prepare('SELECT id, transaction_date, transaction_type, category, description, amount, reference, status FROM cash_transactions WHERE company_id = ? ORDER BY transaction_date DESC, id DESC');
        $query->execute([$this->companyId]);
        return $query->fetchAll();
    }

    public function cashSummary(): array
    {
        $query = $this->connection->prepare('SELECT COALESCE(SUM(CASE WHEN transaction_type = \'INCOME\' AND status = \'POSTED\' THEN amount ELSE 0 END), 0) AS income, COALESCE(SUM(CASE WHEN transaction_type = \'EXPENSE\' AND status = \'POSTED\' THEN amount ELSE 0 END), 0) AS expense FROM cash_transactions WHERE company_id = ?');
        $query->execute([$this->companyId]);
        $summary = $query->fetch() ?: [];
        $summary['balance'] = (float) ($summary['income'] ?? 0) - (float) ($summary['expense'] ?? 0);
        return $summary;
    }

    public function createCashTransaction(array $input, int $userId): void
    {
        foreach (['transaction_date', 'transaction_type', 'category', 'description', 'amount'] as $field) {
            if (trim((string) ($input[$field] ?? '')) === '') {
                throw new RuntimeException('Completa todos los datos del movimiento de caja.');
            }
        }
        if (!in_array($input['transaction_type'], ['INCOME', 'EXPENSE'], true) || !is_numeric($input['amount']) || (float) $input['amount'] <= 0) {
            throw new RuntimeException('El tipo o monto del movimiento de caja no es válido.');
        }
        $this->execute('INSERT INTO cash_transactions (company_id, transaction_date, transaction_type, category, description, amount, reference, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)', [$this->companyId, $input['transaction_date'], $input['transaction_type'], trim($input['category']), trim($input['description']), $input['amount'], trim((string) ($input['reference'] ?? '')) ?: null, $userId]);
    }

    public function create(array $input, int $userId): void
    {
        foreach (['season_id', 'cost_center_id', 'entry_date', 'description', 'amount'] as $field) {
            if (trim((string) ($input[$field] ?? '')) === '') {
                throw new RuntimeException('Por favor, completa los datos del costo.');
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
