<?php

declare(strict_types=1);

namespace CampoSur\Services;

use PDO;
use RuntimeException;

final class MachineryManagement
{
    public function __construct(private readonly PDO $connection, private readonly int $companyId)
    {
    }

    public function machinery(): array
    {
        $query = $this->connection->prepare('SELECT m.id, m.code, m.name, m.machinery_type, m.brand, m.model, m.plate, m.meter, m.status, f.name AS farm_name FROM machinery m LEFT JOIN farms f ON f.id = m.farm_id WHERE m.company_id = ? ORDER BY m.name');
        $query->execute([$this->companyId]);
        return $query->fetchAll();
    }

    public function maintenance(): array
    {
        $query = $this->connection->prepare('SELECT mm.maintenance_date, mm.maintenance_type, mm.description, mm.cost, mm.next_date, m.name AS machinery_name FROM machinery_maintenance mm INNER JOIN machinery m ON m.id = mm.machinery_id WHERE mm.company_id = ? ORDER BY mm.maintenance_date DESC, mm.id DESC');
        $query->execute([$this->companyId]);
        return $query->fetchAll();
    }

    public function fuel(): array
    {
        $query = $this->connection->prepare('SELECT fm.fuel_date, fm.liters, fm.unit_cost, fm.meter, fm.reference, m.name AS machinery_name FROM fuel_movements fm INNER JOIN machinery m ON m.id = fm.machinery_id WHERE fm.company_id = ? ORDER BY fm.fuel_date DESC, fm.id DESC');
        $query->execute([$this->companyId]);
        return $query->fetchAll();
    }

    public function farms(): array
    {
        $query = $this->connection->prepare('SELECT id, name FROM farms WHERE company_id = ? AND active = 1 ORDER BY name');
        $query->execute([$this->companyId]);
        return $query->fetchAll();
    }

    public function createMachinery(array $input): void
    {
        foreach (['code', 'name', 'machinery_type'] as $field) {
            if (trim((string) ($input[$field] ?? '')) === '') {
                throw new RuntimeException('Por favor, completa los datos de la maquinaria.');
            }
        }
        if (!(new CatalogLookup($this->connection, $this->companyId))->exists('MACHINERY_TYPE', (string) $input['machinery_type'])) {
            throw new RuntimeException('El tipo de maquinaria no está habilitado.');
        }
        $this->execute('INSERT INTO machinery (company_id, farm_id, code, name, machinery_type, brand, model, plate, meter) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)', [$this->companyId, $input['farm_id'] ?: null, strtoupper(trim($input['code'])), trim($input['name']), strtoupper(trim($input['machinery_type'])), trim($input['brand']) ?: null, trim($input['model']) ?: null, trim($input['plate']) ?: null, $input['meter'] ?: 0]);
    }

    public function createMaintenance(array $input, int $userId): void
    {
        foreach (['machinery_id', 'maintenance_date', 'maintenance_type', 'description'] as $field) {
            if (trim((string) ($input[$field] ?? '')) === '') {
                throw new RuntimeException('Completa los datos de la mantención.');
            }
        }
        $this->belongs($input['machinery_id']);
        if (!(new CatalogLookup($this->connection, $this->companyId))->exists('MAINTENANCE_TYPE', (string) $input['maintenance_type'])) {
            throw new RuntimeException('El tipo de mantención no está habilitado.');
        }
        $this->execute('INSERT INTO machinery_maintenance (company_id, machinery_id, maintenance_date, maintenance_type, description, cost, next_date, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)', [$this->companyId, (int) $input['machinery_id'], $input['maintenance_date'], $input['maintenance_type'], trim($input['description']), $input['cost'] ?: 0, $input['next_date'] ?: null, $userId]);
    }

    public function createFuel(array $input, int $userId): void
    {
        foreach (['machinery_id', 'fuel_date', 'liters', 'unit_cost'] as $field) {
            if (trim((string) ($input[$field] ?? '')) === '') {
                throw new RuntimeException('Completa los datos del combustible.');
            }
        }
        if ((float) $input['liters'] <= 0 || (float) $input['unit_cost'] < 0) {
            throw new RuntimeException('Los litros deben ser mayores que cero y el costo no puede ser negativo.');
        }
        $this->belongs($input['machinery_id']);
        $this->execute('INSERT INTO fuel_movements (company_id, machinery_id, farm_id, fuel_date, liters, unit_cost, meter, reference, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)', [$this->companyId, (int) $input['machinery_id'], $input['farm_id'] ?: null, $input['fuel_date'], $input['liters'], $input['unit_cost'], $input['meter'] ?: null, trim($input['reference']) ?: null, $userId]);
    }

    private function belongs(mixed $id): void
    {
        $query = $this->connection->prepare('SELECT id FROM machinery WHERE id = ? AND company_id = ? AND status <> "INACTIVE"');
        $query->execute([(int) $id, $this->companyId]);
        if (!$query->fetchColumn()) {
            throw new RuntimeException('La maquinaria no pertenece a esta empresa o está inactiva.');
        }
    }

    private function execute(string $sql, array $params): void
    {
        $this->connection->prepare($sql)->execute($params);
    }
}
