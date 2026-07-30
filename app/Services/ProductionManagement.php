<?php

declare(strict_types=1);

namespace CampoSur\Services;

use PDO;
use RuntimeException;

final class ProductionManagement extends BaseService
{
    public function __construct(protected readonly PDO $connection, protected readonly int $companyId)
    {
    }

    public function entries(): array
    {
        $query = $this->connection->prepare('SELECT p.id, p.production_date, p.activity, p.quantity, p.unit, p.quality, s.name AS season_name, f.name AS farm_name, b.name AS block_name, sp.name AS species_name FROM production_entries p INNER JOIN seasons s ON s.id = p.season_id LEFT JOIN farms f ON f.id = p.farm_id LEFT JOIN blocks b ON b.id = p.block_id LEFT JOIN species sp ON sp.id = p.species_id WHERE p.company_id = ? ORDER BY p.production_date DESC, p.id DESC');
        $query->execute([$this->companyId]);
        return $query->fetchAll();
    }

    public function summary(): array
    {
        $query = $this->connection->prepare('SELECT COUNT(*) AS entries, COALESCE(SUM(quantity), 0) AS quantity FROM production_entries WHERE company_id = ?');
        $query->execute([$this->companyId]);
        return $query->fetch() ?: ['entries' => 0, 'quantity' => 0];
    }

    public function options(): array
    {
        return ['seasons' => $this->fetch('SELECT id, name FROM seasons WHERE company_id = ? AND active = 1 ORDER BY starts_on DESC'), 'farms' => $this->fetch('SELECT id, name FROM farms WHERE company_id = ? AND active = 1 ORDER BY name'), 'blocks' => $this->fetch('SELECT id, code, name FROM blocks WHERE company_id = ? AND active = 1 ORDER BY code'), 'species' => $this->fetch('SELECT id, name, variety FROM species WHERE company_id = ? AND active = 1 ORDER BY name')];
    }

    public function create(array $input, int $userId): void
    {
        foreach (['season_id', 'production_date', 'activity', 'quantity', 'unit'] as $field) {
            if (trim((string) ($input[$field] ?? '')) === '') {
                throw new RuntimeException('Por favor, completa los datos de producción.');
            }
        }
        if (!is_numeric($input['quantity']) || (float) $input['quantity'] <= 0) {
            throw new RuntimeException('La cantidad producida debe ser mayor que cero.');
        }
        $catalogs = new CatalogLookup($this->connection, $this->companyId);
        if (!$catalogs->exists('MEASUREMENT_UNIT', (string) $input['unit'])) {
            throw new RuntimeException('La unidad de producción no está habilitada.');
        }
        if (!empty($input['quality']) && !$catalogs->exists('PRODUCTION_QUALITY', (string) $input['quality'])) {
            throw new RuntimeException('La calidad de producción no está habilitada.');
        }
        foreach ([['seasons', $input['season_id']], ['farms', $input['farm_id'] ?? null], ['blocks', $input['block_id'] ?? null], ['species', $input['species_id'] ?? null]] as [$table, $id]) {
            if ($id) {
                $this->belongs($table, $id);
            }
        }
        $query = $this->connection->prepare('INSERT INTO production_entries (company_id, season_id, farm_id, block_id, species_id, production_date, activity, quantity, unit, quality, notes, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $query->execute([$this->companyId, (int) $input['season_id'], $input['farm_id'] ?: null, $input['block_id'] ?: null, $input['species_id'] ?: null, $input['production_date'], trim($input['activity']), $input['quantity'], strtoupper(trim($input['unit'])), strtoupper(trim($input['quality'])) ?: null, trim($input['notes']) ?: null, $userId]);
    }

    private function belongs(string $table, mixed $id): void
    {
        if (!in_array($table, ['seasons', 'farms', 'blocks', 'species'], true)) {
            throw new RuntimeException('Referencia no válida.');
        }
        $query = $this->connection->prepare('SELECT id FROM ' . $table . ' WHERE id = ? AND company_id = ?');
        $query->execute([(int) $id, $this->companyId]);
        if (!$query->fetchColumn()) {
            throw new RuntimeException('El registro seleccionado no pertenece a esta agrícola.');
        }
    }
}
