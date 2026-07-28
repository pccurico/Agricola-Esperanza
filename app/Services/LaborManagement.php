<?php

declare(strict_types=1);

namespace CampoSur\Services;

use PDO;
use RuntimeException;

final class LaborManagement
{
    public function __construct(private readonly PDO $connection, private readonly int $companyId)
    {
    }

    public function workers(): array
    {
        $query = $this->connection->prepare('SELECT id, full_name, tax_id, worker_type, default_rate, active FROM workers WHERE company_id = ? ORDER BY full_name');
        $query->execute([$this->companyId]);
        return $query->fetchAll();
    }

    public function entries(): array
    {
        $query = $this->connection->prepare('SELECT l.id, l.labor_date, l.labor_type, l.quantity, l.unit_rate, l.total_amount, w.full_name, f.name AS farm_name, b.name AS block_name FROM labor_entries l INNER JOIN workers w ON w.id = l.worker_id LEFT JOIN farms f ON f.id = l.farm_id LEFT JOIN blocks b ON b.id = l.block_id WHERE l.company_id = ? ORDER BY l.labor_date DESC, l.id DESC');
        $query->execute([$this->companyId]);
        return $query->fetchAll();
    }

    public function options(): array
    {
        return ['seasons' => $this->fetch('SELECT id, name FROM seasons WHERE company_id = ? AND active = 1 ORDER BY starts_on DESC'), 'farms' => $this->fetch('SELECT id, name FROM farms WHERE company_id = ? AND active = 1 ORDER BY name'), 'blocks' => $this->fetch('SELECT id, code, name FROM blocks WHERE company_id = ? AND active = 1 ORDER BY code')];
    }

    public function createWorker(array $input): void
    {
        if (trim((string) ($input['full_name'] ?? '')) === '') {
            throw new RuntimeException('El nombre del trabajador es obligatorio.');
        }
        if (!(new CatalogLookup($this->connection, $this->companyId))->exists('WORKER_TYPE', (string) $input['worker_type'])) {
            throw new RuntimeException('El tipo de trabajador no está habilitado.');
        }
        $this->execute('INSERT INTO workers (company_id, full_name, tax_id, worker_type, default_rate) VALUES (?, ?, ?, ?, ?)', [$this->companyId, trim($input['full_name']), trim($input['tax_id']) ?: null, strtoupper(trim($input['worker_type'])), $input['default_rate'] ?: 0]);
    }

    public function createEntry(array $input, int $userId): void
    {
        foreach (['worker_id', 'season_id', 'labor_date', 'labor_type', 'quantity', 'unit_rate'] as $field) {
            if (trim((string) ($input[$field] ?? '')) === '') {
                throw new RuntimeException('Completa todos los datos de la labor.');
            }
        }
        if ((float) $input['quantity'] <= 0 || (float) $input['unit_rate'] < 0) {
            throw new RuntimeException('La cantidad debe ser mayor que cero y la tarifa no puede ser negativa.');
        }
        $this->belongs('workers', $input['worker_id']);
        $this->belongs('seasons', $input['season_id']);
        if (!(new CatalogLookup($this->connection, $this->companyId))->exists('LABOR_TYPE', (string) $input['labor_type'])) {
            throw new RuntimeException('El tipo de labor no está habilitado.');
        }
        if (!empty($input['farm_id'])) {
            $this->belongs('farms', $input['farm_id']);
        }
        if (!empty($input['block_id'])) {
            $this->belongs('blocks', $input['block_id']);
        }
        $this->execute('INSERT INTO labor_entries (company_id, worker_id, season_id, farm_id, block_id, labor_date, labor_type, quantity, unit_rate, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [$this->companyId, (int) $input['worker_id'], (int) $input['season_id'], $input['farm_id'] ?: null, $input['block_id'] ?: null, $input['labor_date'], trim($input['labor_type']), $input['quantity'], $input['unit_rate'], $userId]);
    }

    private function fetch(string $sql): array
    {
        $query = $this->connection->prepare($sql);
        $query->execute([$this->companyId]);
        return $query->fetchAll();
    }

    private function belongs(string $table, mixed $id): void
    {
        if (!in_array($table, ['workers', 'seasons', 'farms', 'blocks'], true)) {
            throw new RuntimeException('Referencia no válida.');
        }
        $query = $this->connection->prepare('SELECT id FROM ' . $table . ' WHERE id = ? AND company_id = ?');
        $query->execute([(int) $id, $this->companyId]);
        if (!$query->fetchColumn()) {
            throw new RuntimeException('El registro seleccionado no pertenece a esta agrícola.');
        }
    }

    private function execute(string $sql, array $params): void
    {
        $this->connection->prepare($sql)->execute($params);
    }
}
