<?php

declare(strict_types=1);

namespace AgroPCC\Services;

use PDO;
use RuntimeException;

final class MasterData extends BaseService
{
    public function __construct(protected readonly PDO $connection, protected readonly int $companyId)
    {
    }

    public function all(): array
    {
        return [
            'farms' => $this->fetch('SELECT id, name, code, location, hectares, active FROM farms WHERE company_id = ? ORDER BY name'),
            'species' => $this->fetch('SELECT id, name, variety, active FROM species WHERE company_id = ? ORDER BY name, variety'),
            'seasons' => $this->fetch('SELECT id, name, starts_on, ends_on, active FROM seasons WHERE company_id = ? ORDER BY starts_on DESC'),
            'blocks' => $this->fetch('SELECT b.id, b.farm_id, b.code, b.name, b.hectares, b.active, f.name AS farm_name, s.name AS species_name FROM blocks b INNER JOIN farms f ON f.id = b.farm_id LEFT JOIN species s ON s.id = b.species_id WHERE b.company_id = ? ORDER BY f.name, b.code'),
            'cost_centers' => $this->fetch('SELECT id, code, name, category, active FROM cost_centers WHERE company_id = ? ORDER BY category, name'),
        ];
    }

    public function create(string $type, array $input): void
    {
        match ($type) {
            'farm' => $this->createFarm($input),
            'species' => $this->createSpecies($input),
            'season' => $this->createSeason($input),
            'block' => $this->createBlock($input),
            'cost_center' => $this->createCostCenter($input),
            default => throw new RuntimeException('Tipo de maestro no válido.'),
        };
    }

    private function createFarm(array $input): void
    {
        $this->required($input, ['name', 'code']);
        $this->execute('INSERT INTO farms (company_id, name, code, location, hectares) VALUES (?, ?, ?, ?, ?)', [$this->companyId, trim($input['name']), strtoupper(trim($input['code'])), trim($input['location']) ?: null, $this->decimal($input['hectares'] ?? 0)]);
    }

    private function createSpecies(array $input): void
    {
        $this->required($input, ['name']);
        $this->execute('INSERT INTO species (company_id, name, variety) VALUES (?, ?, ?)', [$this->companyId, trim($input['name']), trim($input['variety']) ?: null]);
    }

    private function createSeason(array $input): void
    {
        $this->required($input, ['name', 'starts_on', 'ends_on']);
        if ($input['ends_on'] <= $input['starts_on']) {
            throw new RuntimeException('La temporada debe terminar después de comenzar.');
        }
        $this->execute('INSERT INTO seasons (company_id, name, starts_on, ends_on) VALUES (?, ?, ?, ?)', [$this->companyId, trim($input['name']), $input['starts_on'], $input['ends_on']]);
    }

    private function createBlock(array $input): void
    {
        $this->required($input, ['farm_id', 'code', 'name']);
        $this->belongsToCompany('farms', $input['farm_id']);
        if (!empty($input['species_id'])) {
            $this->belongsToCompany('species', $input['species_id']);
        }
        $this->execute('INSERT INTO blocks (company_id, farm_id, species_id, code, name, hectares, planting_year) VALUES (?, ?, ?, ?, ?, ?, ?)', [$this->companyId, (int) $input['farm_id'], $input['species_id'] ?: null, strtoupper(trim($input['code'])), trim($input['name']), $this->decimal($input['hectares'] ?? 0), $input['planting_year'] ?: null]);
    }

    private function createCostCenter(array $input): void
    {
        $this->required($input, ['code', 'name', 'category']);
        if (!$this->catalogValueExists('COST_CATEGORY', (string) $input['category'])) {
            throw new RuntimeException('La categoría del centro de costo no es válida.');
        }
        $this->execute('INSERT INTO cost_centers (company_id, code, name, category) VALUES (?, ?, ?, ?)', [$this->companyId, strtoupper(trim($input['code'])), trim($input['name']), strtoupper(trim($input['category']))]);
    }

    private function catalogValueExists(string $catalogCode, string $valueCode): bool
    {
        $query = $this->connection->prepare(
            'SELECT v.id FROM system_catalog_values v
             INNER JOIN system_catalogs c ON c.id = v.catalog_id
             WHERE c.code = ? AND c.active = 1 AND v.active = 1
               AND v.code = ? AND (v.company_id IS NULL OR v.company_id = ?) LIMIT 1'
        );
        $query->execute([$catalogCode, strtoupper(trim($valueCode)), $this->companyId]);
        return (bool) $query->fetchColumn();
    }

    private function belongsToCompany(string $table, mixed $id): void
    {
        if (!in_array($table, ['farms', 'species'], true)) {
            throw new RuntimeException('Referencia no válida.');
        }
        $query = $this->connection->prepare('SELECT id FROM ' . $table . ' WHERE id = ? AND company_id = ?');
        $query->execute([(int) $id, $this->companyId]);
        if (!$query->fetchColumn()) {
            throw new RuntimeException('El registro seleccionado no pertenece a esta agrícola.');
        }
    }

    private function required(array $input, array $fields): void
    {
        foreach ($fields as $field) {
            if (trim((string) ($input[$field] ?? '')) === '') {
                throw new RuntimeException('Completa los campos obligatorios.');
            }
        }
    }

    private function decimal(mixed $value): float
    {
        if (!is_numeric($value) || (float) $value < 0) {
            throw new RuntimeException('La superficie debe ser un número válido.');
        }
        return (float) $value;
    }
}
