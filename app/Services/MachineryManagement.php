<?php

declare(strict_types=1);

namespace AgroPCC\Services;

use PDO;
use RuntimeException;

final class MachineryManagement extends BaseService
{
    public function __construct(protected readonly PDO $connection, protected readonly int $companyId)
    {
    }

    public function overview(): array
    {
        $machinery = $this->fetchMachineryData();
        $maintenance = $this->fetchMaintenanceData();
        $fuel = $this->fetchFuelData();

        return [
            'machinery' => $machinery,
            'maintenance' => $maintenance,
            'fuel' => $fuel,
            'dashboard' => $this->buildDashboard($machinery, $maintenance, $fuel),
        ];
    }

    public function machinery(): array
    {
        return $this->fetchMachineryData();
    }

    public function maintenance(): array
    {
        return $this->fetchMaintenanceData();
    }

    public function fuel(): array
    {
        return $this->fetchFuelData();
    }

    public function dashboard(): array
    {
        $overview = $this->overview();
        return $overview['dashboard'];
    }

    private function fetchMachineryData(): array
    {
        $query = $this->connection->prepare('SELECT m.id, m.code, m.name, m.machinery_type, m.brand, m.model, m.plate, m.meter, m.status, f.name AS farm_name FROM machinery m LEFT JOIN farms f ON f.id = m.farm_id WHERE m.company_id = ? ORDER BY m.name');
        $query->execute([$this->companyId]);
        return $query->fetchAll();
    }

    private function fetchMaintenanceData(): array
    {
        $query = $this->connection->prepare('SELECT mm.maintenance_date, mm.maintenance_type, mm.description, mm.cost, mm.next_date, mm.machinery_id, m.code AS machinery_code, m.name AS machinery_name FROM machinery_maintenance mm INNER JOIN machinery m ON m.id = mm.machinery_id WHERE mm.company_id = ? ORDER BY mm.maintenance_date DESC, mm.id DESC');
        $query->execute([$this->companyId]);
        return $query->fetchAll();
    }

    private function fetchFuelData(): array
    {
        $query = $this->connection->prepare('SELECT fm.fuel_date, fm.liters, fm.unit_cost, fm.meter, fm.reference, fm.machinery_id, m.code AS machinery_code, m.name AS machinery_name FROM fuel_movements fm INNER JOIN machinery m ON m.id = fm.machinery_id WHERE fm.company_id = ? ORDER BY fm.fuel_date DESC, fm.id DESC');
        $query->execute([$this->companyId]);
        return $query->fetchAll();
    }

    private function buildDashboard(array $machinery, array $maintenance, array $fuel): array
    {
        $today = new \DateTimeImmutable('today');
        $monthStart = new \DateTimeImmutable('first day of this month');

        $operational = 0;
        $maintenanceState = 0;
        $outOfService = 0;
        $overdue = 0;
        $soon = 0;
        $fuelTotal = 0.0;
        $fuelMonthCost = 0.0;
        $maintenanceMonthCost = 0.0;
        $accumulatedHours = 0.0;
        $activityCount = 0;
        $alerts = [];

        foreach ($machinery as $item) {
            $status = strtoupper((string) ($item['status'] ?? 'ACTIVO'));
            $meter = (float) ($item['meter'] ?? 0);
            $accumulatedHours += $meter;

            if ($status === 'OPERATIVE' || $status === 'ACTIVO' || $status === 'OPERATIVO') {
                $operational++;
            } elseif ($status === 'MAINTENANCE' || $status === 'MANTENCIÓN' || $status === 'MANTENCIÓN') {
                $maintenanceState++;
            } elseif ($status === 'OUT_OF_SERVICE' || $status === 'INACTIVE' || $status === 'FUERA_DE_SERVICIO') {
                $outOfService++;
            }

            $type = strtoupper((string) ($item['machinery_type'] ?? ''));
            $threshold = match ($type) {
                'TRACTOR' => 1000,
                'PULVERIZADOR' => 800,
                'CAMIÓN', 'CAMION' => 1500,
                'MOTOCULTOR' => 400,
                default => 500,
            };
            if ($meter >= $threshold) {
                $alerts[] = ['type' => 'limit', 'title' => 'Horómetro alto', 'message' => ((string) ($item['name'] ?? 'Equipo') . ' supera el límite estimado de ' . $threshold . ' h.')];
            }

            $lastMaintenanceDate = null;
            $lastFuelDate = null;
            foreach ($maintenance as $entry) {
                if ((int) ($entry['machinery_id'] ?? 0) === (int) ($item['id'] ?? 0)) {
                    $lastMaintenanceDate = $entry['maintenance_date'] ?? null;
                    break;
                }
            }
            foreach ($fuel as $entry) {
                if ((int) ($entry['machinery_id'] ?? 0) === (int) ($item['id'] ?? 0)) {
                    $lastFuelDate = $entry['fuel_date'] ?? null;
                    break;
                }
            }
            $activityDate = $lastMaintenanceDate ?? $lastFuelDate;
            if ($activityDate !== null) {
                $activityCount++;
                $activityDateTime = new \DateTimeImmutable((string) $activityDate);
                if ($activityDateTime < $today->modify('-90 days')) {
                    $alerts[] = ['type' => 'inactive', 'title' => 'Sin actividad', 'message' => ((string) ($item['name'] ?? 'Equipo') . ' no registra actividad desde ' . $activityDateTime->format('Y-m-d') . '.')];
                }
            }
        }

        foreach ($maintenance as $entry) {
            $maintenanceCost = (float) ($entry['cost'] ?? 0);
            $maintenanceMonthCost += $maintenanceCost;
            $entryDate = $entry['maintenance_date'] ?? null;
            $entryDateTime = $entryDate ? new \DateTimeImmutable((string) $entryDate) : null;
            if ($entryDateTime && $entryDateTime >= $monthStart) {
                $maintenanceMonthCost += $maintenanceCost;
            }
            if ($entryDateTime && $entryDateTime < $today) {
                $overdue++;
            }
            if ($entryDateTime && $entryDateTime >= $today && $entryDateTime <= $today->modify('+15 days')) {
                $soon++;
            }
        }

        foreach ($fuel as $entry) {
            $fuelTotal += (float) ($entry['liters'] ?? 0);
            $fuelMonthCost += (float) ($entry['liters'] ?? 0) * (float) ($entry['unit_cost'] ?? 0);
            $entryDate = $entry['fuel_date'] ?? null;
            $entryDateTime = $entryDate ? new \DateTimeImmutable((string) $entryDate) : null;
            if ($entryDateTime && $entryDateTime >= $monthStart) {
                $fuelMonthCost += (float) ($entry['liters'] ?? 0) * (float) ($entry['unit_cost'] ?? 0);
            }
        }

        $monthlyFuel = [];
        $monthlyCosts = [];
        $monthlyMaintenance = [];
        $utilization = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthDate = (new \DateTimeImmutable('first day of this month'))->modify('-' . $i . ' months');
            $monthKey = $monthDate->format('Y-m');
            $monthLabel = $monthDate->format('M Y');
            $monthFuel = 0.0;
            $monthCost = 0.0;
            $monthMaint = 0.0;
            foreach ($fuel as $entry) {
                $entryDate = $entry['fuel_date'] ?? null;
                if ($entryDate && substr((string) $entryDate, 0, 7) === $monthKey) {
                    $monthFuel += (float) ($entry['liters'] ?? 0);
                    $monthCost += (float) ($entry['liters'] ?? 0) * (float) ($entry['unit_cost'] ?? 0);
                }
            }
            foreach ($maintenance as $entry) {
                $entryDate = $entry['maintenance_date'] ?? null;
                if ($entryDate && substr((string) $entryDate, 0, 7) === $monthKey) {
                    $monthMaint += (float) ($entry['cost'] ?? 0);
                }
            }
            $monthlyFuel[] = ['label' => $monthLabel, 'value' => round($monthFuel, 2)];
            $monthlyCosts[] = ['label' => $monthLabel, 'value' => round($monthCost, 2)];
            $monthlyMaintenance[] = ['label' => $monthLabel, 'value' => round($monthMaint, 2)];
            $utilization[] = ['label' => $monthLabel, 'value' => round(min(100, 20 + ($i * 10)), 1)];
        }

        $alerts = array_values(array_unique($alerts, SORT_REGULAR));
        return [
            'kpis' => [
                'total_equipment' => count($machinery),
                'operational' => $operational,
                'maintenance' => $maintenanceState,
                'out_of_service' => $outOfService,
                'maintenance_due_soon' => $soon,
                'maintenance_overdue' => $overdue,
                'fuel_total_liters' => round($fuelTotal, 2),
                'fuel_month_cost' => round($fuelMonthCost, 2),
                'maintenance_month_cost' => round($maintenanceMonthCost, 2),
                'accumulated_hours' => round($accumulatedHours, 2),
                'average_utilization' => count($machinery) > 0 ? round(($operational / count($machinery)) * 100, 1) : 0,
            ],
            'alerts' => $alerts,
            'fuel' => $monthlyFuel,
            'costs' => $monthlyCosts,
            'maintenance' => $monthlyMaintenance,
            'utilization' => $utilization,
            'filters' => [
                'farms' => array_values(array_unique(array_filter(array_map(static function ($item) { return (string) ($item['farm_name'] ?? ''); }, $machinery)))),
                'types' => array_values(array_unique(array_filter(array_map(static function ($item) { return (string) ($item['machinery_type'] ?? ''); }, $machinery)))),
                'statuses' => array_values(array_unique(array_filter(array_map(static function ($item) { return (string) ($item['status'] ?? ''); }, $machinery)))),
                'brands' => array_values(array_unique(array_filter(array_map(static function ($item) { return (string) ($item['brand'] ?? ''); }, $machinery)))),
            ],
        ];
    }

    public function farms(): array
    {
        $query = $this->connection->prepare('SELECT id, name FROM farms WHERE company_id = ? AND active = 1 ORDER BY name');
        $query->execute([$this->companyId]);
        return $query->fetchAll();
    }

    public function options(): array
    {
        return [
            'machinery_types' => $this->catalogValues('MACHINERY_TYPE'),
            'maintenance_types' => $this->catalogValues('MAINTENANCE_TYPE'),
        ];
    }

    private function catalogValues(string $catalogCode): array
    {
        return $this->fetchRows(
            'SELECT v.code, v.label
             FROM system_catalog_values v
             INNER JOIN system_catalogs c ON c.id = v.catalog_id
             WHERE c.code = ? AND c.active = 1 AND v.active = 1
               AND (v.company_id IS NULL OR v.company_id = ?)
             ORDER BY v.sort_order, v.label',
            [$catalogCode, $this->companyId],
        );
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

}
