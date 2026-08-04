<?php

declare(strict_types=1);

namespace CampoSur\Services;

use PDO;
use RuntimeException;

final class DemoDataManager extends BaseService
{
    private const PROTECTED_TABLES = [
        'companies', 'roles', 'permissions', 'role_permissions', 'users', 'company_settings',
        'demo_batches', 'demo_records', 'schema_migrations', 'system_catalogs', 'system_catalog_values',
        'audit_logs', 'system_logs', 'backup_records', 'restore_records', 'api_tokens', 'attachments'
    ];

    private array $references = [];

    public function __construct(protected readonly PDO $connection, private readonly string $rootPath, protected readonly int $companyId)
    {
    }

    public function status(): array
    {
        $query = $this->connection->prepare('SELECT id, installation_id, version, status, installed_at, removed_at FROM demo_batches WHERE company_id = ? ORDER BY id DESC');
        $query->execute([$this->companyId]);
        $batches = $query->fetchAll();
        $active = null;
        foreach ($batches as $batch) {
            if (in_array($batch['status'], ['INSTALLED', 'PARTIAL'], true)) {
                $active = $batch;
                break;
            }
        }
        if ($active) {
            $count = $this->connection->prepare('SELECT COUNT(*) FROM demo_records WHERE batch_id = ?');
            $count->execute([(int) $active['id']]);
            $active['records_count'] = (int) $count->fetchColumn();
        }
        return ['active' => $active, 'history' => $batches];
    }

    public function install(int $userId): array
    {
        if ($this->status()['active']) {
            throw new RuntimeException('Ya hay un conjunto de datos demo instalado. ElimÃ­nalo antes de instalarlo nuevamente.');
        }
        $data = $this->loadData();
        $this->references = [];
        $installationId = bin2hex(random_bytes(16));
        $ownsTransaction = !$this->connection->inTransaction();
        if ($ownsTransaction) {
            $this->connection->beginTransaction();
        }
        try {
            $batch = $this->connection->prepare('INSERT INTO demo_batches (company_id, installation_id, version, status, created_by) VALUES (?, ?, ?, \'INSTALLED\', ?)');
            $batch->execute([$this->companyId, $installationId, (string) ($data['version'] ?? '1.0'), $userId]);
            $batchId = (int) $this->connection->lastInsertId();
            $pending = $data['tables'];
            while ($pending !== []) {
                $progress = false;
                foreach ($pending as $table => $rows) {
                    if (!$this->rowsResolvable($rows)) {
                        continue;
                    }
                    $this->insertTable($table, $rows, $batchId);
                    unset($pending[$table]);
                    $progress = true;
                }
                if (!$progress) {
                    throw new RuntimeException('No se pudieron resolver las dependencias de los datos demo.');
                }
            }
            if ($ownsTransaction) {
                $this->connection->commit();
            }
            return ['id' => $batchId, 'installation_id' => $installationId, 'records_count' => $this->countBatchRecords($batchId)];
        } catch (\Throwable $exception) {
            if ($ownsTransaction && $this->connection->inTransaction()) {
                $this->connection->rollBack();
            }
            throw $exception;
        }
    }

    public function remove(): array
    {
        $active = $this->status()['active'];
        if (!$active) {
            throw new RuntimeException('No hay datos demo instalados para eliminar.');
        }
        $batchId = (int) $active['id'];
        $records = $this->connection->prepare('SELECT table_name, record_id FROM demo_records WHERE batch_id = ? ORDER BY id DESC');
        $records->execute([$batchId]);
        $this->connection->beginTransaction();
        try {
            foreach ($records->fetchAll() as $record) {
                $table = (string) $record['table_name'];
                if (!$this->isSafeTable($table)) {
                    continue;
                }
                $query = $this->connection->prepare('DELETE FROM `' . $table . '` WHERE id = ?');
                $query->execute([(int) $record['record_id']]);
                if ($query->rowCount() === 0) {
                    throw new RuntimeException('No se pudo eliminar el registro demo de ' . $table . ' #' . $record['record_id'] . '. Puede estar relacionado con informaciÃ³n creada posteriormente.');
                }
            }
            $this->connection->prepare('UPDATE demo_batches SET status = \'REMOVED\', removed_at = NOW() WHERE id = ? AND company_id = ?')->execute([$batchId, $this->companyId]);
            $this->connection->commit();
            return ['removed_count' => $this->countBatchRecords($batchId)];
        } catch (\Throwable $exception) {
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }
            throw $exception;
        }
    }

    public function reinstall(int $userId): array
    {
        $this->remove();
        return $this->install($userId);
    }

    private function loadData(): array
    {
        $path = $this->rootPath . '/database/demo/demo_data.json';
        if (!is_file($path)) {
            throw new RuntimeException('No se encontrÃ³ el archivo oficial de datos demo.');
        }
        $data = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($data) || !is_array($data['tables'] ?? null)) {
            throw new RuntimeException('El archivo de datos demo no tiene un formato vÃ¡lido.');
        }
        return $data;
    }

    private function insertTable(string $table, mixed $rows, int $batchId): void
    {
        if (!$this->isSafeTable($table) || !is_array($rows)) {
            throw new RuntimeException('La entidad demo no estÃ¡ permitida: ' . $table);
        }
        foreach ($rows as $row) {
            if (!is_array($row) || empty($row['key'])) {
                throw new RuntimeException('Cada registro demo debe tener una clave Ãºnica en ' . $table . '.');
            }
            $key = (string) $row['key'];
            unset($row['key']);
            foreach ($row as $column => $value) {
                $row[$column] = $this->resolve($value);
            }
            $columns = array_keys($row);
            foreach ($columns as $column) {
                if (!preg_match('/^[a-z][a-z0-9_]*$/', $column)) {
                    throw new RuntimeException('Columna demo no vÃ¡lida: ' . $column);
                }
            }
            $quotedColumns = implode(', ', array_map(static fn (string $column): string => '`' . $column . '`', $columns));
            $placeholders = implode(', ', array_fill(0, count($columns), '?'));
            $query = $this->connection->prepare('INSERT INTO `' . $table . '` (' . $quotedColumns . ') VALUES (' . $placeholders . ')');
            $query->execute(array_values($row));
            $recordId = (int) $this->connection->lastInsertId();
            if ($recordId <= 0) {
                throw new RuntimeException('La entidad demo debe tener un identificador autoincremental: ' . $table);
            }
            $this->references[$table][$key] = $recordId;
            $record = $this->connection->prepare('INSERT INTO demo_records (batch_id, table_name, record_id, record_key) VALUES (?, ?, ?, ?)');
            $record->execute([$batchId, $table, $recordId, $key]);
        }
    }

    private function rowsResolvable(mixed $rows): bool
    {
        if (!is_array($rows)) {
            return false;
        }
        foreach ($rows as $row) {
            if (!is_array($row)) {
                return false;
            }
            foreach ($row as $value) {
                if (is_string($value) && str_starts_with($value, '@') && !$this->canResolve($value)) {
                    return false;
                }
            }
        }
        return true;
    }

    private function canResolve(string $value): bool
    {
        [$type, $key] = array_pad(explode(':', substr($value, 1), 2), 2, null);
        if ($type === 'company' && $key === 'current') {
            return true;
        }
        if ($type === 'user' && $key === 'admin') {
            return true;
        }
        return isset($this->references[$type][$key]);
    }

    private function resolve(mixed $value): mixed
    {
        if (!is_string($value) || !str_starts_with($value, '@')) {
            return $value;
        }
        [$type, $key] = array_pad(explode(':', substr($value, 1), 2), 2, null);
        if ($type === 'company' && $key === 'current') {
            return $this->companyId;
        }
        if ($type === 'user' && $key === 'admin') {
            $query = $this->connection->prepare('SELECT id FROM users WHERE company_id = ? ORDER BY id LIMIT 1');
            $query->execute([$this->companyId]);
            $userId = (int) $query->fetchColumn();
            if ($userId <= 0) {
                throw new RuntimeException('No se encontrÃ³ el administrador de la empresa.');
            }
            return $userId;
        }
        if (isset($this->references[$type][$key])) {
            return $this->references[$type][$key];
        }
        throw new RuntimeException('Referencia demo no encontrada: ' . $value);
    }

    private function isSafeTable(string $table): bool
    {
        if (!preg_match('/^[a-z][a-z0-9_]*$/', $table) || in_array($table, self::PROTECTED_TABLES, true)) {
            return false;
        }
        $query = $this->connection->prepare('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?');
        $query->execute([$table]);
        return (int) $query->fetchColumn() === 1;
    }

    private function countBatchRecords(int $batchId): int
    {
        $query = $this->connection->prepare('SELECT COUNT(*) FROM demo_records WHERE batch_id = ?');
        $query->execute([$batchId]);
        return (int) $query->fetchColumn();
    }
}

