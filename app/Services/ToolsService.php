<?php

declare(strict_types=1);

namespace AgroPCC\Services;

use DateTimeImmutable;
use PDO;
use RuntimeException;

final class ToolsService extends BaseService
{
    public function __construct(
        protected readonly PDO $connection,
        private readonly string $rootPath,
        protected readonly int $companyId,
        private readonly int $userId
    ) {
    }

    public function status(): array
    {
        $schemaSql = @file_get_contents($this->rootPath . '/database/schema.sql') ?: '';
        $expectedTables = $this->expectedSchemaTables($schemaSql);
        $installedTables = $this->installedTables();
        $missingTables = array_values(array_diff($expectedTables, $installedTables));
        $missingColumns = 0;
        foreach ($expectedTables as $table) {
            if (!in_array($table, $installedTables, true)) {
                continue;
            }
            $expectedColumns = $this->expectedSchemaColumns($schemaSql, $table);
            $actualColumns = $this->installedColumns($table);
            $missingColumns += count(array_diff($expectedColumns, $actualColumns));
        }
        $latestInstalled = $this->latestAppliedMigration();
        $latestAvailable = $this->latestAvailableMigration();
        $updateAvailable = $latestInstalled !== '' && $latestAvailable !== '' && $latestInstalled !== $latestAvailable;

        return [
            'installed_version' => $latestInstalled ?: 'sin-migraciones',
            'available_version' => $latestAvailable ?: 'sin-migraciones',
            'can_update' => $updateAvailable,
            'missing_tables' => $missingTables,
            'missing_columns' => $missingColumns,
            'backup_count' => count($this->backups()),
            'recent_logs' => $this->recentLogs(),
        ];
    }

    public function createBackup(): array
    {
        $backupDirectory = $this->rootPath . '/storage/backups';
        if (!is_dir($backupDirectory) && !mkdir($backupDirectory, 0750, true) && !is_dir($backupDirectory)) {
            throw new RuntimeException('No fue posible preparar la carpeta de respaldos.');
        }

        $this->connection->prepare(
            'INSERT INTO backup_records (company_id, file_path, file_size, checksum, status, created_by) VALUES (?, ?, 0, NULL, ?, ?)'
        )->execute([
            $this->companyId,
            '',
            'STARTED',
            $this->userId,
        ]);
        $backupId = (int) $this->connection->lastInsertId();

        $stamp = (new DateTimeImmutable('now'))->format('Ymd_His');
        $backupFile = $backupDirectory . '/backup_' . $stamp . '.sql';
        $configCopy = $backupDirectory . '/config_' . $stamp . '.php';
        $dbConfig = app_config('database');
        $dumpCommand = $this->resolveDumpCommand();
        if ($dumpCommand === null) {
            $this->logSystemEvent('tools.backup', 'WARNING', 'No se encontró mysqldump; usando respaldo PHP interno', []);
            $this->dumpDatabaseWithPdo($backupFile);
        } else {
            $command = sprintf(
                '%s --defaults-extra-file=/dev/null --host=%s --port=%s --user=%s --password=%s --single-transaction --routines --events --skip-comments %s > %s',
                escapeshellarg($dumpCommand),
                escapeshellarg((string) ($dbConfig['host'] ?? '127.0.0.1')),
                escapeshellarg((string) ($dbConfig['port'] ?? '3306')),
                escapeshellarg((string) ($dbConfig['username'] ?? 'root')),
                escapeshellarg((string) ($dbConfig['password'] ?? '')),
                escapeshellarg((string) ($dbConfig['database'] ?? '')),
                escapeshellarg($backupFile)
            );
            $this->runExternalCommand($command);
        }

        if (!is_file($backupFile) || filesize($backupFile) < 100) {
            $this->markBackupStatus($backupId, 'FAILED', 'El respaldo de base de datos no pudo generarse.');
            throw new RuntimeException('El respaldo de base de datos no pudo generarse.');
        }

        $configSource = $this->rootPath . '/config/config.php';
        if (is_file($configSource) && !copy($configSource, $configCopy)) {
            $this->markBackupStatus($backupId, 'FAILED', 'El respaldo de configuración no pudo copiarse.');
            throw new RuntimeException('El respaldo de configuración no pudo copiarse.');
        }

        $fileSize = (int) filesize($backupFile);
        $checksum = hash_file('sha256', $backupFile);
        $this->connection->prepare(
            'UPDATE backup_records SET file_path = ?, file_size = ?, checksum = ?, status = ? WHERE id = ?'
        )->execute([
            str_replace($this->rootPath . '/', '', $backupFile),
            $fileSize,
            $checksum,
            'COMPLETED',
            $backupId,
        ]);

        $this->logSystemEvent('tools.backup', 'INFO', 'Respaldo creado', [
            'backup_id' => $backupId,
            'file_path' => str_replace($this->rootPath . '/', '', $backupFile),
            'file_size' => $fileSize,
        ]);

        return [
            'id' => $backupId,
            'path' => str_replace($this->rootPath . '/', '', $backupFile),
            'checksum' => $checksum,
            'file_size' => $fileSize,
        ];
    }

    public function restoreBackup(int $backupId): void
    {
        $backupQuery = $this->connection->prepare(
            'SELECT id, file_path, company_id, status FROM backup_records WHERE id = ? AND company_id = ? LIMIT 1'
        );
        $backupQuery->execute([$backupId, $this->companyId]);
        $backup = $backupQuery->fetch();
        $backupQuery->closeCursor();
        if (!$backup) {
            throw new RuntimeException('El respaldo solicitado no existe para esta empresa.');
        }

        $backupFile = $this->rootPath . '/' . ltrim((string) $backup['file_path'], '/');
        if (!is_file($backupFile)) {
            throw new RuntimeException('El archivo de respaldo ya no está disponible en el servidor.');
        }

        $restoreId = $this->connection->prepare(
            'INSERT INTO restore_records (company_id, backup_id, status, created_by) VALUES (?, ?, ?, ?)'
        )->execute([
            $this->companyId,
            $backupId,
            'STARTED',
            $this->userId,
        ]);
        $restoreId = (int) $this->connection->lastInsertId();

        $dbConfig = app_config('database');
        $restoreCommand = $this->resolveRestoreCommand();
        if ($restoreCommand === null) {
            $this->connection->prepare('UPDATE restore_records SET status = ?, error_message = ? WHERE id = ?')->execute([
                'FAILED',
                'No se encontró mysql en el entorno para restaurar el respaldo.',
                $restoreId,
            ]);
            throw new RuntimeException('No se encontró mysql en el entorno para restaurar el respaldo.');
        }

        $command = sprintf(
            '%s --host=%s --port=%s --user=%s --password=%s %s < %s',
            escapeshellarg($restoreCommand),
            escapeshellarg((string) ($dbConfig['host'] ?? '127.0.0.1')),
            escapeshellarg((string) ($dbConfig['port'] ?? '3306')),
            escapeshellarg((string) ($dbConfig['username'] ?? 'root')),
            escapeshellarg((string) ($dbConfig['password'] ?? '')),
            escapeshellarg((string) ($dbConfig['database'] ?? '')),
            escapeshellarg($backupFile)
        );
        $restoreOutput = $this->runExternalCommand($command);
        if ($restoreOutput === null) {
            $this->connection->prepare('UPDATE restore_records SET status = ?, error_message = ? WHERE id = ?')->execute([
                'FAILED',
                'La restauración del respaldo falló en el entorno.',
                $restoreId,
            ]);
            throw new RuntimeException('La restauración del respaldo falló en el entorno.');
        }

        $this->connection->prepare('UPDATE restore_records SET status = ? WHERE id = ?')->execute([
            'COMPLETED',
            $restoreId,
        ]);
        $this->logSystemEvent('tools.restore', 'INFO', 'Restauración completada', ['backup_id' => $backupId]);
    }

    public function syncSchema(): void
    {
        $this->logSystemEvent('tools.schema', 'INFO', 'Sincronización de esquema iniciada', []);
        (new MigrationRunner($this->connection, $this->rootPath))->run();
        $this->logSystemEvent('tools.schema', 'INFO', 'Sincronización de esquema completada', []);
    }

    public function repairApplication(): void
    {
        $this->logSystemEvent('tools.repair', 'INFO', 'Reparación de sistema iniciada', []);
        if (function_exists('opcache_reset')) {
            @opcache_reset();
        }
        (new MigrationRunner($this->connection, $this->rootPath))->run();
        $tables = $this->connection->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
        foreach ($tables as $table) {
            $this->connection->exec('ANALYZE TABLE `' . str_replace('`', '', (string) $table) . '`');
        }
        $this->logSystemEvent('tools.repair', 'INFO', 'Reparación de sistema completada', []);
    }

    public function runUpdate(): void
    {
        $backup = $this->createBackup();
        $this->logSystemEvent('tools.update', 'INFO', 'Actualización iniciada', ['backup_id' => $backup['id']]);
        (new MigrationRunner($this->connection, $this->rootPath))->run();
        $this->logSystemEvent('tools.update', 'INFO', 'Actualización finalizada', ['backup_id' => $backup['id']]);
    }

    public function backups(): array
    {
        $query = $this->connection->prepare(
            'SELECT br.id, br.file_path, br.file_size, br.checksum, br.status, br.created_at, u.full_name AS created_by FROM backup_records br LEFT JOIN users u ON u.id = br.created_by WHERE br.company_id = ? ORDER BY br.id DESC LIMIT 20'
        );
        $query->execute([$this->companyId]);
        return $query->fetchAll();
    }

    public function recentLogs(): array
    {
        $query = $this->connection->prepare(
            'SELECT sl.id, sl.level, sl.channel, sl.message, sl.context_json, sl.created_at, COALESCE(u.full_name, "Sistema") AS user_name FROM system_logs sl LEFT JOIN users u ON u.id = sl.user_id WHERE sl.company_id = ? ORDER BY sl.created_at DESC, sl.id DESC LIMIT 20'
        );
        $query->execute([$this->companyId]);
        return $query->fetchAll();
    }

    private function logSystemEvent(string $channel, string $level, string $message, array $context): void
    {
        $query = $this->connection->prepare(
            'INSERT INTO system_logs (company_id, user_id, level, channel, message, context_json) VALUES (?, ?, ?, ?, ?, ?)'
        );
        $query->execute([
            $this->companyId,
            $this->userId,
            $level,
            $channel,
            $message,
            $context !== [] ? json_encode($context, JSON_UNESCAPED_UNICODE) : null,
        ]);
    }

    private function markBackupStatus(int $backupId, string $status, string $message): void
    {
        $this->connection->prepare('UPDATE backup_records SET status = ?, checksum = ? WHERE id = ?')->execute([
            $status,
            $message,
            $backupId,
        ]);
    }

    private function latestAppliedMigration(): string
    {
        $query = $this->connection->prepare('SELECT version FROM schema_migrations ORDER BY applied_at DESC, version DESC LIMIT 1');
        $query->execute();
        $version = $query->fetchColumn();
        $query->closeCursor();
        return is_string($version) ? $version : '';
    }

    private function latestAvailableMigration(): string
    {
        $files = glob($this->rootPath . '/database/migrations/*.sql') ?: [];
        $versions = [];
        foreach ($files as $file) {
            $versions[] = pathinfo($file, PATHINFO_FILENAME);
        }
        sort($versions, SORT_STRING);
        return $versions === [] ? '' : (string) end($versions);
    }

    private function expectedSchemaTables(string $schemaSql): array
    {
        preg_match_all('/CREATE TABLE IF NOT EXISTS\s+`?([a-z_]+)`?\s*\(/i', $schemaSql, $matches);
        return array_values(array_unique(array_filter($matches[1], 'is_string')));
    }

    private function expectedSchemaColumns(string $schemaSql, string $table): array
    {
        preg_match('/CREATE TABLE IF NOT EXISTS\s+`?' . preg_quote($table, '/') . '`?\s*\((.*?)\)\s*ENGINE=/is', $schemaSql, $match);
        if (!isset($match[1])) {
            return [];
        }

        $columns = [];
        foreach (preg_split('/\r?\n/', $match[1]) as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, 'CONSTRAINT') || str_starts_with($line, 'KEY ') || str_starts_with($line, 'UNIQUE KEY') || str_starts_with($line, 'PRIMARY KEY') || str_starts_with($line, 'FOREIGN KEY')) {
                continue;
            }

            if (preg_match('/^`?([a-z_]+)`?\s+(?:tinyint|bigint|int|decimal|varchar|char|text|date|datetime|json|enum|timestamp|double|float|blob|boolean)/i', $line, $matches)) {
                $columns[] = $matches[1];
            }
        }

        return array_values(array_unique(array_filter($columns, 'is_string')));
    }

    private function installedTables(): array
    {
        $query = $this->connection->prepare('SELECT table_name FROM information_schema.tables WHERE table_schema = DATABASE() ORDER BY table_name');
        $query->execute();
        return array_values(array_map(static fn ($row): string => (string) $row, $query->fetchAll(PDO::FETCH_COLUMN)));
    }

    private function installedColumns(string $table): array
    {
        $query = $this->connection->prepare('SELECT column_name FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? ORDER BY ordinal_position');
        $query->execute([$table]);
        return array_values(array_map(static fn ($row): string => (string) $row, $query->fetchAll(PDO::FETCH_COLUMN)));
    }

    private function dumpDatabaseWithPdo(string $backupFile): void
    {
        $handle = fopen($backupFile, 'wb');
        if ($handle === false) {
            throw new RuntimeException('No se pudo abrir el archivo de respaldo para escritura.');
        }

        fwrite($handle, "-- Respaldo de base de datos generado por PHP\n");
        fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n");
        fwrite($handle, "SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\n");
        fwrite($handle, "SET NAMES utf8mb4;\n\n");

        $tables = $this->connection->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
        foreach ($tables as $table) {
            $table = (string) $table;
            $create = $this->connection->query('SHOW CREATE TABLE `' . str_replace('`', '', $table) . '`')->fetch(PDO::FETCH_ASSOC);
            if (!isset($create['Create Table'])) {
                continue;
            }

            fwrite($handle, "DROP TABLE IF EXISTS `{$table}`;\n");
            fwrite($handle, $create['Create Table'] . ";\n\n");

            $stmt = $this->connection->query('SELECT * FROM `' . str_replace('`', '', $table) . '`');
            $rowCount = 0;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if ($rowCount === 0) {
                    fwrite($handle, "LOCK TABLES `{$table}` WRITE;\n");
                }

                $columns = array_map(static fn ($column): string => '`' . str_replace('`', '``', $column) . '`', array_keys($row));
                $values = array_map([$this, 'quoteValue'], array_values($row));
                fwrite($handle, 'INSERT INTO `' . $table . '` (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $values) . ");\n");
                $rowCount++;
            }

            if ($rowCount > 0) {
                fwrite($handle, "UNLOCK TABLES;\n\n");
            }
        }

        fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
        fclose($handle);
    }

    private function quoteValue(mixed $value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        return "'" . str_replace(["\\", "'", "\n", "\r", "\t", "\0"], ["\\\\", "\\'", "\\n", "\\r", "\\t", "\\0"], (string) $value) . "'";
    }

    private function resolveDumpCommand(): ?string
    {
        return $this->resolveExecutablePath('mysqldump');
    }

    private function resolveRestoreCommand(): ?string
    {
        return $this->resolveExecutablePath('mysql');
    }

    private function resolveExecutablePath(string $binary): ?string
    {
        $command = DIRECTORY_SEPARATOR === '\\'
            ? 'where ' . escapeshellarg($binary)
            : 'command -v ' . escapeshellarg($binary);

        $output = $this->runExternalCommand($command);
        if ($output === null || trim($output) === '') {
            return null;
        }

        $path = trim((string) $output);
        return explode(PHP_EOL, $path)[0] ?: null;
    }

    private function runExternalCommand(string $command): ?string
    {
        $descriptorSpec = [
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = @proc_open($command, $descriptorSpec, $pipes);
        if (!is_resource($process)) {
            return null;
        }

        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $exitCode = proc_close($process);

        if ($exitCode !== 0) {
            return null;
        }

        if (trim((string) $stdout) !== '') {
            return trim((string) $stdout);
        }

        $stderrValue = trim((string) $stderr);
        return $stderrValue !== '' ? $stderrValue : null;
    }
}

