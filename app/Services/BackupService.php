<?php

declare(strict_types=1);

namespace AgroPCC\Services;

use DateTimeImmutable;
use PDO;
use RuntimeException;

final class BackupService extends BaseService
{
    private readonly string $backupDirectory;
    private readonly string $databaseName;

    public function __construct(
        protected readonly PDO $connection,
        private readonly string $rootPath,
        protected readonly int $companyId,
        private readonly int $userId
    ) {
        $this->backupDirectory = $this->rootPath . '/storage/backups';
        $this->databaseName = (string) app_config('database.name', '');
        $this->ensureBackupDirectory();
    }

    public function listBackups(): array
    {
        $query = $this->connection->prepare(
            'SELECT br.id, br.file_path, br.file_size, br.status, br.created_at, u.full_name AS created_by FROM backup_records br LEFT JOIN users u ON u.id = br.created_by WHERE br.company_id = ? AND br.file_path <> ? AND br.status <> ? ORDER BY br.id DESC LIMIT 100'
        );
        $query->execute([$this->companyId, '', '']);

        $backups = $query->fetchAll(PDO::FETCH_ASSOC);
        $validBackups = [];
        foreach ($backups as $backup) {
            $fullPath = $this->resolveBackupFilePath((string) ($backup['file_path'] ?? ''));
            if (!is_file($fullPath)) {
                continue;
            }
            $validBackups[] = $backup;
        }

        return $validBackups;
    }

    public function createBackup(): array
    {
        $this->ensureBackupDirectory();
        $this->connection->prepare('INSERT INTO backup_records (company_id, file_path, file_size, checksum, status, created_by) VALUES (?, ?, 0, NULL, ?, ?)')
            ->execute([$this->companyId, '', 'STARTED', $this->userId]);

        $backupId = (int) $this->connection->lastInsertId();
        $stamp = (new DateTimeImmutable('now'))->format('Ymd_His');
        $filenameBase = 'backup_' . $stamp;
        $sqlFile = $this->backupDirectory . '/' . $filenameBase . '.sql';
        $archiveFile = $sqlFile;

        try {
            $exporter = new SqlExporter($this->connection, $this->backupDirectory);
            $exporter->setProgressCallback([$this, 'progressCallback']);
            $archiveFile = $exporter->export($sqlFile, $filenameBase, $this->userId, $this->databaseName);

            $fileSize = (int) filesize($archiveFile);
            $checksum = hash_file('sha256', $archiveFile);

            $this->connection->prepare('UPDATE backup_records SET file_path = ?, file_size = ?, checksum = ?, status = ? WHERE id = ?')
                ->execute([str_replace($this->backupDirectory . '/', '', $archiveFile), $fileSize, $checksum, 'COMPLETED', $backupId]);

            (new AuditLog($this->connection, $this->companyId))->record($this->userId, 'BACKUP_CREATE', 'backup', $backupId, [
                'file' => str_replace($this->backupDirectory . '/', '', $archiveFile),
                'size' => $fileSize,
            ]);

            return ['id' => $backupId, 'path' => str_replace($this->backupDirectory . '/', '', $archiveFile), 'file_size' => $fileSize];
        } catch (\Throwable $exception) {
            $this->connection->prepare('UPDATE backup_records SET status = ? WHERE id = ?')
                ->execute(['FAILED', $backupId]);

            throw $exception;
        }
    }

    public function restoreBackup(int $backupId): void
    {
        $backup = $this->findBackup($backupId);
        if (!$backup) {
            throw new RuntimeException('El respaldo solicitado no existe.');
        }

        $this->connection->prepare('INSERT INTO restore_records (company_id, backup_id, status, created_by) VALUES (?, ?, ?, ?)')
            ->execute([$this->companyId, $backupId, 'STARTED', $this->userId]);
        $restoreId = (int) $this->connection->lastInsertId();

        $fullPath = $this->resolveBackupFilePath((string) ($backup['file_path'] ?? ''));
        if (!is_file($fullPath)) {
            $this->updateRestoreStatus($restoreId, 'FAILED', 'El archivo de respaldo no existe.');
            throw new RuntimeException('El archivo de respaldo no existe.');
        }

        try {
            $restorer = new SqlExporter($this->connection, $this->backupDirectory);
            $restorer->setProgressCallback([$this, 'progressCallback']);
            $restorer->restore($fullPath);

            $this->updateRestoreStatus($restoreId, 'COMPLETED', null);
            (new AuditLog($this->connection, $this->companyId))->record($this->userId, 'BACKUP_RESTORE', 'backup', $backupId, ['file' => $backup['file_path']]);
        } catch (\Throwable $exception) {
            $this->updateRestoreStatus($restoreId, 'FAILED', $exception->getMessage());
            throw $exception;
        }
    }

    public function deleteBackup(int $backupId): void
    {
        $backup = $this->findBackup($backupId);
        if (!$backup) {
            throw new RuntimeException('El respaldo solicitado no existe.');
        }

        $fullPath = $this->resolveBackupFilePath((string) ($backup['file_path'] ?? ''));
        if (is_file($fullPath)) {
            @unlink($fullPath);
        }

        $this->connection->prepare('DELETE FROM backup_records WHERE id = ? AND company_id = ?')
            ->execute([$backupId, $this->companyId]);

        (new AuditLog($this->connection, $this->companyId))->record($this->userId, 'BACKUP_DELETE', 'backup', $backupId, ['file' => $backup['file_path']]);
    }

    public function downloadBackup(int $backupId): void
    {
        $backup = $this->findBackup($backupId);
        if (!$backup) {
            throw new RuntimeException('El respaldo solicitado no existe.');
        }

        $fullPath = $this->resolveBackupFilePath((string) ($backup['file_path'] ?? ''));
        if (!is_file($fullPath)) {
            throw new RuntimeException('El archivo de respaldo no está disponible.');
        }

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($fullPath) . '"');
        header('Content-Length: ' . (int) filesize($fullPath));
        readfile($fullPath);
        exit;
    }

    public function progressStatus(): array
    {
        return [
            'current' => $_SESSION['backup_progress_current'] ?? 0,
            'total' => $_SESSION['backup_progress_total'] ?? 0,
            'status' => $_SESSION['backup_progress_status'] ?? 'idle',
        ];
    }

    public function progressCallback(int $current, int $total, string $status): void
    {
        $_SESSION['backup_progress_current'] = $current;
        $_SESSION['backup_progress_total'] = $total;
        $_SESSION['backup_progress_status'] = $status;
    }

    private function ensureBackupDirectory(): void
    {
        if (!is_dir($this->backupDirectory) && !mkdir($this->backupDirectory, 0755, true) && !is_dir($this->backupDirectory)) {
            throw new RuntimeException('No fue posible crear la carpeta de respaldos.');
        }
    }

    private function findBackup(int $backupId): ?array
    {
        $query = $this->connection->prepare('SELECT id, file_path, file_size, status FROM backup_records WHERE id = ? AND company_id = ? LIMIT 1');
        $query->execute([$backupId, $this->companyId]);
        $backup = $query->fetch(PDO::FETCH_ASSOC);

        return $backup ?: null;
    }

    private function updateRestoreStatus(int $restoreId, string $status, ?string $errorMessage): void
    {
        $query = $this->connection->prepare('UPDATE restore_records SET status = ?, error_message = ? WHERE id = ?');
        $query->execute([$status, $errorMessage, $restoreId]);
    }

    private function resolveBackupFilePath(string $filePath): string
    {
        $filePath = str_replace('\\', '/', trim($filePath));
        if ($filePath === '') {
            return $this->backupDirectory;
        }

        if (str_starts_with($filePath, '/')) {
            return $filePath;
        }

        if (str_starts_with($filePath, 'storage/backups/')) {
            return $this->rootPath . '/' . $filePath;
        }

        if (preg_match('#^[A-Za-z]:/#', $filePath) === 1) {
            return $filePath;
        }

        return $this->backupDirectory . '/' . ltrim($filePath, '/');
    }
}
