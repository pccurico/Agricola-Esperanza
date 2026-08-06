<?php

declare(strict_types=1);

namespace AgroPCC\Services;

use PDO;
use RuntimeException;

final class LaborManagement extends BaseService
{
    public function __construct(protected readonly PDO $connection, protected readonly int $companyId)
    {
    }

    public function workers(): array
    {
        $query = $this->connection->prepare('SELECT w.id, w.full_name, w.tax_id, w.worker_type, w.default_rate, w.active, p.department, p.position FROM workers w LEFT JOIN worker_profiles p ON p.worker_id = w.id WHERE w.company_id = ? ORDER BY w.full_name');
        $query->execute([$this->companyId]);
        return $query->fetchAll();
    }

    public function entries(): array
    {
        $query = $this->connection->prepare('SELECT l.id, l.labor_date, l.labor_type, l.quantity, l.unit_rate, l.total_amount, w.full_name, f.name AS farm_name, b.name AS block_name FROM labor_entries l INNER JOIN workers w ON w.id = l.worker_id LEFT JOIN farms f ON f.id = l.farm_id LEFT JOIN blocks b ON b.id = l.block_id WHERE l.company_id = ? ORDER BY l.labor_date DESC, l.id DESC');
        $query->execute([$this->companyId]);
        return $query->fetchAll();
    }

    public function assignments(): array
    {
        $query = $this->connection->prepare('SELECT a.id, a.start_date, a.end_date, a.department, a.position, w.full_name, f.name AS farm_name, b.name AS block_name FROM worker_assignments a INNER JOIN workers w ON w.id = a.worker_id LEFT JOIN farms f ON f.id = a.farm_id LEFT JOIN blocks b ON b.id = a.block_id WHERE w.company_id = ? ORDER BY a.start_date DESC, a.id DESC');
        $query->execute([$this->companyId]);
        return $query->fetchAll();
    }

    public function leaveRequests(): array
    {
        $query = $this->connection->prepare('SELECT l.id, l.leave_type, l.start_date, l.end_date, l.days_count, l.status, l.notes, w.full_name FROM worker_leave_requests l INNER JOIN workers w ON w.id = l.worker_id WHERE w.company_id = ? ORDER BY l.start_date DESC, l.id DESC');
        $query->execute([$this->companyId]);
        return $query->fetchAll();
    }

    public function createAssignment(array $input): void
    {
        foreach (['worker_id', 'start_date'] as $field) {
            if (trim((string) ($input[$field] ?? '')) === '') {
                throw new RuntimeException('Trabajador y fecha de inicio son obligatorios.');
            }
        }
        $this->belongs('workers', $input['worker_id']);
        if (!empty($input['farm_id'])) {
            $this->belongs('farms', $input['farm_id']);
        }
        if (!empty($input['block_id'])) {
            $this->belongs('blocks', $input['block_id']);
        }
        if (!empty($input['end_date']) && $input['end_date'] < $input['start_date']) {
            throw new RuntimeException('La fecha de término no puede ser anterior al inicio.');
        }
        $this->execute('INSERT INTO worker_assignments (worker_id, farm_id, block_id, department, position, start_date, end_date, is_primary) VALUES (?, ?, ?, ?, ?, ?, ?, ?)', [(int) $input['worker_id'], $input['farm_id'] ?: null, $input['block_id'] ?: null, trim((string) ($input['department'] ?? '')) ?: null, trim((string) ($input['position'] ?? '')) ?: null, $input['start_date'], $input['end_date'] ?: null, 1]);
    }

    public function createLeaveRequest(array $input): void
    {
        foreach (['worker_id', 'leave_type', 'start_date', 'end_date'] as $field) {
            if (trim((string) ($input[$field] ?? '')) === '') {
                throw new RuntimeException('Trabajador, tipo y fechas son obligatorios.');
            }
        }
        if ($input['end_date'] < $input['start_date']) {
            throw new RuntimeException('La fecha de término no puede ser anterior al inicio.');
        }
        $this->belongs('workers', $input['worker_id']);
        $start = new \DateTimeImmutable($input['start_date']);
        $end = new \DateTimeImmutable($input['end_date']);
        $days = $start->diff($end)->days + 1;
        $this->execute('INSERT INTO worker_leave_requests (worker_id, leave_type, start_date, end_date, days_count, status, notes) VALUES (?, ?, ?, ?, ?, \'PENDING\', ?)', [(int) $input['worker_id'], trim((string) $input['leave_type']), $input['start_date'], $input['end_date'], $days, trim((string) ($input['notes'] ?? '')) ?: null]);
    }

    public function workerProfile(int $workerId): array
    {
        $worker = $this->connection->prepare('SELECT id, company_id, full_name, tax_id, worker_type, default_rate, active FROM workers WHERE id = ? AND company_id = ? LIMIT 1');
        $worker->execute([$workerId, $this->companyId]);
        $workerData = $worker->fetch();
        if (!$workerData) {
            return ['worker' => null, 'profile' => null, 'contract' => null, 'benefits' => null, 'bank' => null];
        }

        $profile = $this->connection->prepare('SELECT * FROM worker_profiles WHERE worker_id = ? LIMIT 1');
        $profile->execute([$workerId]);

        $contract = $this->connection->prepare('SELECT * FROM worker_contracts WHERE worker_id = ? ORDER BY start_date DESC LIMIT 1');
        $contract->execute([$workerId]);

        $benefits = $this->connection->prepare('SELECT * FROM worker_benefits WHERE worker_id = ? LIMIT 1');
        $benefits->execute([$workerId]);

        $bank = $this->connection->prepare('SELECT * FROM worker_bank_accounts WHERE worker_id = ? ORDER BY is_primary DESC, id DESC LIMIT 1');
        $bank->execute([$workerId]);

        return ['worker' => $workerData, 'profile' => $profile->fetch(), 'contract' => $contract->fetch(), 'benefits' => $benefits->fetch(), 'bank' => $bank->fetch()];
    }

    public function workerFormData(int $workerId): array
    {
        if ($workerId <= 0) {
            return ['worker' => [], 'profile' => [], 'contract' => [], 'benefits' => [], 'bank' => []];
        }

        return $this->workerProfile($workerId);
    }

    public function updateWorker(int $workerId, array $input): void
    {
        $this->belongs('workers', $workerId);
        $fullName = trim((string) ($input['full_name'] ?? ''));
        if ($fullName === '') {
            throw new RuntimeException('El nombre del trabajador es obligatorio.');
        }

        $this->execute(
            'UPDATE workers SET full_name = ?, tax_id = ?, worker_type = ?, default_rate = ?, active = ? WHERE id = ? AND company_id = ?',
            [
                $fullName,
                trim((string) ($input['tax_id'] ?? '')) ?: null,
                strtoupper(trim((string) ($input['worker_type'] ?? 'TEMPORAL'))),
                (float) ($input['default_rate'] ?? 0),
                (int) ($input['active'] ?? 1),
                $workerId,
                $this->companyId,
            ]
        );
    }

    public function toggleWorker(int $workerId, int $active): void
    {
        $this->belongs('workers', $workerId);
        $this->execute('UPDATE workers SET active = ? WHERE id = ? AND company_id = ?', [$active ? 1 : 0, $workerId, $this->companyId]);
    }

    public function upsertWorkerProfile(array $input): void
    {
        $workerId = (int) ($input['worker_id'] ?? 0);
        $this->belongs('workers', $workerId);

        $this->execute(
            'INSERT INTO worker_profiles (worker_id, birth_date, gender, marital_status, nationality, address, commune, region, email, phone, emergency_contact_name, emergency_contact_phone, employee_number, department, position, hire_date, contract_type, base_salary, currency, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE birth_date = VALUES(birth_date), gender = VALUES(gender), marital_status = VALUES(marital_status), nationality = VALUES(nationality), address = VALUES(address), commune = VALUES(commune), region = VALUES(region), email = VALUES(email), phone = VALUES(phone), emergency_contact_name = VALUES(emergency_contact_name), emergency_contact_phone = VALUES(emergency_contact_phone), employee_number = VALUES(employee_number), department = VALUES(department), position = VALUES(position), hire_date = VALUES(hire_date), contract_type = VALUES(contract_type), base_salary = VALUES(base_salary), currency = VALUES(currency), notes = VALUES(notes), updated_at = CURRENT_TIMESTAMP',
            [
                $workerId,
                $input['birth_date'] ?? null,
                trim((string) ($input['gender'] ?? '')) ?: null,
                trim((string) ($input['marital_status'] ?? '')) ?: null,
                trim((string) ($input['nationality'] ?? '')) ?: null,
                trim((string) ($input['address'] ?? '')) ?: null,
                trim((string) ($input['commune'] ?? '')) ?: null,
                trim((string) ($input['region'] ?? '')) ?: null,
                trim((string) ($input['email'] ?? '')) ?: null,
                trim((string) ($input['phone'] ?? '')) ?: null,
                trim((string) ($input['emergency_contact_name'] ?? '')) ?: null,
                trim((string) ($input['emergency_contact_phone'] ?? '')) ?: null,
                trim((string) ($input['employee_number'] ?? '')) ?: null,
                trim((string) ($input['department'] ?? '')) ?: null,
                trim((string) ($input['position'] ?? '')) ?: null,
                $input['hire_date'] ?? null,
                trim((string) ($input['contract_type'] ?? '')) ?: null,
                (float) ($input['base_salary'] ?? 0),
                trim((string) ($input['currency'] ?? 'CLP')) ?: 'CLP',
                trim((string) ($input['notes'] ?? '')) ?: null,
            ]
        );

        if (!empty($input['contract_type']) || !empty($input['hire_date']) || !empty($input['base_salary'])) {
            $this->execute(
                'INSERT INTO worker_contracts (worker_id, contract_type, status, start_date, weekly_hours, base_salary, currency, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE contract_type = VALUES(contract_type), status = VALUES(status), start_date = VALUES(start_date), weekly_hours = VALUES(weekly_hours), base_salary = VALUES(base_salary), currency = VALUES(currency), notes = VALUES(notes), updated_at = CURRENT_TIMESTAMP',
                [
                    $workerId,
                    trim((string) ($input['contract_type'] ?? '')) ?: 'PERMANENTE',
                    trim((string) ($input['contract_status'] ?? 'ACTIVE')) ?: 'ACTIVE',
                    $input['hire_date'] ?? date('Y-m-d'),
                    (float) ($input['weekly_hours'] ?? 45),
                    (float) ($input['base_salary'] ?? 0),
                    trim((string) ($input['currency'] ?? 'CLP')) ?: 'CLP',
                    trim((string) ($input['contract_notes'] ?? '')) ?: null,
                ]
            );
        }

        if (!empty($input['health_system']) || !empty($input['afp_name'])) {
            $this->execute(
                'INSERT INTO worker_benefits (worker_id, health_system, afp_name, pension_type, extra_benefit, health_plan) VALUES (?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE health_system = VALUES(health_system), afp_name = VALUES(afp_name), pension_type = VALUES(pension_type), extra_benefit = VALUES(extra_benefit), health_plan = VALUES(health_plan)',
                [
                    $workerId,
                    trim((string) ($input['health_system'] ?? '')) ?: null,
                    trim((string) ($input['afp_name'] ?? '')) ?: null,
                    trim((string) ($input['pension_type'] ?? '')) ?: null,
                    trim((string) ($input['extra_benefit'] ?? '')) ?: null,
                    trim((string) ($input['health_plan'] ?? '')) ?: null,
                ]
            );
        }

        if (!empty($input['bank_name']) || !empty($input['account_number'])) {
            $this->execute(
                'INSERT INTO worker_bank_accounts (worker_id, bank_name, account_type, account_number, swift_code, is_primary) VALUES (?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE bank_name = VALUES(bank_name), account_type = VALUES(account_type), account_number = VALUES(account_number), swift_code = VALUES(swift_code), is_primary = VALUES(is_primary)',
                [
                    $workerId,
                    trim((string) ($input['bank_name'] ?? '')) ?: null,
                    trim((string) ($input['account_type'] ?? '')) ?: null,
                    trim((string) ($input['account_number'] ?? '')) ?: null,
                    trim((string) ($input['swift_code'] ?? '')) ?: null,
                    1,
                ]
            );
        }
    }

    public function options(): array
    {
        return [
            'seasons' => $this->fetch('SELECT id, name FROM seasons WHERE company_id = ? AND active = 1 ORDER BY starts_on DESC'),
            'farms' => $this->fetch('SELECT id, name FROM farms WHERE company_id = ? AND active = 1 ORDER BY name'),
            'blocks' => $this->fetch('SELECT id, code, name FROM blocks WHERE company_id = ? AND active = 1 ORDER BY code'),
            'labor_types' => $this->catalogValues('LABOR_TYPE'),
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

    public function createWorker(array $input): int
    {
        if (trim((string) ($input['full_name'] ?? '')) === '') {
            throw new RuntimeException('El nombre del trabajador es obligatorio.');
        }
        if (!(new CatalogLookup($this->connection, $this->companyId))->exists('WORKER_TYPE', (string) $input['worker_type'])) {
            throw new RuntimeException('El tipo de trabajador no está habilitado.');
        }
        $this->execute('INSERT INTO workers (company_id, full_name, tax_id, worker_type, default_rate, active) VALUES (?, ?, ?, ?, ?, ?)', [$this->companyId, trim((string) $input['full_name']), trim((string) ($input['tax_id'] ?? '')) ?: null, strtoupper(trim((string) ($input['worker_type'] ?? 'TEMPORAL'))), (float) ($input['default_rate'] ?? 0), isset($input['active']) ? (int) $input['active'] : 1]);

        return (int) $this->connection->lastInsertId();
    }

    public function createEntry(array $input, int $userId): void
    {
        foreach (['worker_id', 'season_id', 'labor_date', 'labor_type', 'quantity', 'unit_rate'] as $field) {
            if (trim((string) ($input[$field] ?? '')) === '') {
                throw new RuntimeException('Por favor, completa los datos de la labor.');
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

}
