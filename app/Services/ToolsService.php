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

        $remoteRelease = $this->remoteReleaseStatus();
        $remoteVersion = (string) ($remoteRelease['tag_name'] ?? '');
        $remoteUrl = (string) ($remoteRelease['html_url'] ?? '');
        $remoteError = (string) ($remoteRelease['error'] ?? '');
        $localVersion = (string) app_config('app.version', '');
        $remoteUpdateAvailable = $remoteVersion !== '' && $localVersion !== '' && version_compare(ltrim($remoteVersion, 'v'), ltrim($localVersion, 'v'), '>');

        return [
            'installed_version' => $latestInstalled ?: 'sin-migraciones',
            'available_version' => $latestAvailable ?: 'sin-migraciones',
            'can_update' => $updateAvailable,
            'missing_tables' => $missingTables,
            'missing_columns' => $missingColumns,
            'backup_count' => count($this->backups()),
            'recent_logs' => $this->recentLogs(),
            'remote_version' => $remoteVersion ?: 'no disponible',
            'remote_url' => $remoteUrl,
            'remote_error' => $remoteError,
            'remote_update_available' => $remoteUpdateAvailable,
            'local_app_version' => $localVersion ?: 'no definido',
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
        $filenameBase = 'backup_' . $stamp;
        $sqlFile = $backupDirectory . '/' . $filenameBase . '.sql';
        $configCopy = $backupDirectory . '/config_' . $stamp . '.php';

        $this->logSystemEvent('tools.backup', 'INFO', 'Generando respaldo con exportador PHP interno', []);

        try {
            $exporter = new SqlExporter($this->connection, $backupDirectory);
            $exporter->setProgressCallback([$this, 'progressCallback']);
            $archiveFile = $exporter->export($sqlFile, $filenameBase, $this->userId, (string) app_config('database.name', ''));
        } catch (\Throwable $exception) {
            $this->markBackupStatus($backupId, 'FAILED', 'El respaldo de base de datos no pudo generarse.');
            throw new RuntimeException('El respaldo de base de datos no pudo generarse: ' . $exception->getMessage());
        }

        if (!is_file($archiveFile) || filesize($archiveFile) < 100) {
            $this->markBackupStatus($backupId, 'FAILED', 'El respaldo de base de datos no pudo generarse.');
            throw new RuntimeException('El respaldo de base de datos no pudo generarse.');
        }

        $configSource = $this->rootPath . '/config/config.php';
        if (is_file($configSource) && !copy($configSource, $configCopy)) {
            $this->markBackupStatus($backupId, 'FAILED', 'El respaldo de configuración no pudo copiarse.');
            throw new RuntimeException('El respaldo de configuración no pudo copiarse.');
        }

        $fileSize = (int) filesize($archiveFile);
        $checksum = hash_file('sha256', $archiveFile);
        $relativePath = str_replace($this->rootPath . '/', '', $archiveFile);
        $this->connection->prepare(
            'UPDATE backup_records SET file_path = ?, file_size = ?, checksum = ?, status = ? WHERE id = ?'
        )->execute([
            $relativePath,
            $fileSize,
            $checksum,
            'COMPLETED',
            $backupId,
        ]);

        $this->logSystemEvent('tools.backup', 'INFO', 'Respaldo creado', [
            'backup_id' => $backupId,
            'file_path' => $relativePath,
            'file_size' => $fileSize,
        ]);

        return [
            'id' => $backupId,
            'path' => $relativePath,
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

        $backupFile = $this->resolveBackupFilePath((string) $backup['file_path']);
        if (!is_file($backupFile)) {
            throw new RuntimeException('El archivo de respaldo ya no está disponible en el servidor.');
        }

        $backupDirectory = $this->rootPath . '/storage/backups';
        $restoreId = $this->connection->prepare(
            'INSERT INTO restore_records (company_id, backup_id, status, created_by) VALUES (?, ?, ?, ?)'
        )->execute([
            $this->companyId,
            $backupId,
            'STARTED',
            $this->userId,
        ]);
        $restoreId = (int) $this->connection->lastInsertId();

        try {
            $restorer = new SqlExporter($this->connection, $backupDirectory);
            $restorer->setProgressCallback([$this, 'progressCallback']);
            $restorer->restore($backupFile);
        } catch (\Throwable $exception) {
            $this->connection->prepare('UPDATE restore_records SET status = ?, error_message = ? WHERE id = ?')->execute([
                'FAILED',
                $exception->getMessage(),
                $restoreId,
            ]);
            throw new RuntimeException('La restauración del respaldo falló en el entorno: ' . $exception->getMessage());
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

    public function downloadAndInstallRemoteUpdate(): void
    {
        $this->setRemoteUpdateProgress(1, 6, 'Iniciando actualización remota');

        $backup = $this->createBackup();
        $this->logSystemEvent('tools.remote_update', 'INFO', 'Actualización remota iniciada', ['backup_id' => $backup['id']]);
        $this->setRemoteUpdateProgress(2, 6, 'Backup inicial creado');

        $release = $this->remoteReleaseStatus();
        if (!empty($release['error'])) {
            throw new RuntimeException('No fue posible obtener la actualización remota: ' . $release['error']);
        }

        $zipUrl = (string) ($release['zipball_url'] ?? '');
        if ($zipUrl === '') {
            throw new RuntimeException('La release remota no contiene URL de descarga de archivo ZIP.');
        }

        $tempDir = $this->createTemporaryDirectory();
        $zipFile = $tempDir . '/release.zip';
        $this->setRemoteUpdateProgress(3, 6, 'Descargando release remota');
        $this->downloadToFile($zipUrl, $zipFile);

        $this->setRemoteUpdateProgress(4, 6, 'Extrayendo release');
        $extractDir = $tempDir . '/extract';
        $this->extractZipArchive($zipFile, $extractDir);

        $sourceRoot = $this->findFirstSubdirectory($extractDir);
        if ($sourceRoot === null) {
            throw new RuntimeException('No se encontró el contenido extraído del release.');
        }

        $this->setRemoteUpdateProgress(5, 6, 'Aplicando archivos de actualización');
        $this->mergeReleaseFiles($sourceRoot, $this->rootPath, [
            '.git',
            'storage',
            'config/config.php',
            '.env',
            'vendor',
            'node_modules',
        ]);

        $this->setRemoteUpdateProgress(6, 6, 'Instalando dependencias y aplicando migraciones');
        $this->installComposerDependencies();
        $this->syncSchema();

        $this->setRemoteUpdateProgress(6, 6, 'Actualización remota completada', true);
        $this->logSystemEvent('tools.remote_update', 'INFO', 'Actualización remota completada', ['backup_id' => $backup['id'], 'release' => $release['tag_name'] ?? '']);
    }

    public function backups(): array
    {
        $query = $this->connection->prepare(
            'SELECT br.id, br.file_path, br.file_size, br.checksum, br.status, br.created_at, u.full_name AS created_by FROM backup_records br LEFT JOIN users u ON u.id = br.created_by WHERE br.company_id = ? AND br.file_path <> ? AND br.status <> ? ORDER BY br.id DESC LIMIT 20'
        );
        $query->execute([$this->companyId, '', '']);
        $backups = $query->fetchAll();
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

    private function resolveBackupFilePath(string $filePath): string
    {
        $filePath = str_replace('\\', '/', trim($filePath));
        if ($filePath === '') {
            return $this->rootPath . '/storage/backups';
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

        return $this->rootPath . '/storage/backups/' . ltrim($filePath, '/');
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

    private function remoteReleaseStatus(): array
    {
        $repo = (string) app_config('updates.github_repo', 'pccurico/Agricola-Esperanza');
        $defaultApi = 'https://api.github.com/repos/' . trim($repo, '/') . '/releases/latest';
        $apiUrl = (string) app_config('updates.github_api', $defaultApi);
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => "User-Agent: PCCURICO-Update-Checker\r\nAccept: application/vnd.github.v3+json\r\n",
                'timeout' => 10,
                'follow_location' => 1,
                'max_redirects' => 5,
            ],
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
            ],
        ]);

        $response = @file_get_contents($apiUrl, false, $context);
        if ($response === false) {
            $lastError = error_get_last();
            $errorMessage = isset($lastError['message']) ? ' (' . $lastError['message'] . ')' : '';
            return ['error' => 'No fue posible acceder a GitHub: ' . $apiUrl . $errorMessage];
        }

        $payload = json_decode($response, true);
        if (!is_array($payload)) {
            return ['error' => 'Respuesta inválida de GitHub.'];
        }

        if (isset($payload['message']) && is_string($payload['message']) && $payload['message'] !== '') {
            return ['error' => 'GitHub API error: ' . $payload['message']];
        }

        return [
            'tag_name' => (string) ($payload['tag_name'] ?? ''),
            'html_url' => (string) ($payload['html_url'] ?? ''),
            'zipball_url' => (string) ($payload['zipball_url'] ?? ''),
            'name' => (string) ($payload['name'] ?? ''),
            'body' => (string) ($payload['body'] ?? ''),
        ];
    }

    private function createTemporaryDirectory(): string
    {
        $tempRoot = sys_get_temp_dir();
        $tempDir = $tempRoot . DIRECTORY_SEPARATOR . 'pccurico_update_' . bin2hex(random_bytes(8));
        if (!mkdir($tempDir, 0755, true) && !is_dir($tempDir)) {
            throw new RuntimeException('No se pudo crear el directorio temporal para la actualización.');
        }

        return $tempDir;
    }

    private function downloadToFile(string $url, string $destination): void
    {
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => "User-Agent: PCCURICO-Update-Checker\r\n",
                'timeout' => 60,
                'follow_location' => 1,
                'max_redirects' => 5,
            ],
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
            ],
        ]);

        $data = @file_get_contents($url, false, $context);
        if ($data !== false && file_put_contents($destination, $data) !== false) {
            return;
        }

        $lastError = error_get_last();
        $errorMessage = isset($lastError['message']) ? trim($lastError['message']) : '';
        $statusMessage = $this->parseHttpStatus($http_response_header ?? []);
        if ($statusMessage !== '') {
            $errorMessage = trim(($statusMessage !== '' ? $statusMessage . '. ' : '') . $errorMessage);
        }

        if (extension_loaded('curl')) {
            $fp = @fopen($destination, 'wb');
            if ($fp !== false) {
                $curl = curl_init($url);
                $curlOptions = [
                    CURLOPT_FILE => $fp,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_MAXREDIRS => 5,
                    CURLOPT_TIMEOUT => 120,
                    CURLOPT_USERAGENT => 'PCCURICO-Update-Checker',
                    CURLOPT_SSL_VERIFYHOST => 2,
                    CURLOPT_HTTPHEADER => ['User-Agent: PCCURICO-Update-Checker'],
                ];

                $curlCaInfo = $this->getSslCaInfo();
                if ($curlCaInfo !== null) {
                    $curlOptions[CURLOPT_CAINFO] = $curlCaInfo;
                    $curlOptions[CURLOPT_SSL_VERIFYPEER] = true;
                } else {
                    $curlOptions[CURLOPT_SSL_VERIFYPEER] = false;
                    $errorMessage = trim($errorMessage . ' cURL SSL certificate verification disabled because no CA bundle was found.');
                }

                curl_setopt_array($curl, $curlOptions);
                $success = curl_exec($curl);
                $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                $curlError = curl_error($curl);
                curl_close($curl);
                fclose($fp);

                if ($success !== false && $statusCode >= 200 && $statusCode < 300 && is_file($destination)) {
                    return;
                }

                @unlink($destination);
                $errorMessage = trim(($curlError !== '' ? 'cURL error: ' . $curlError . '. ' : '') . 'HTTP status: ' . $statusCode . '. ' . $errorMessage);
            } else {
                $errorMessage = trim('No se pudo crear el archivo de destino. ' . $errorMessage);
            }
        }

        throw new RuntimeException('No fue posible descargar la actualización remota.' . ($errorMessage !== '' ? ' ' . $errorMessage : ''));
    }

    private function parseHttpStatus(array $headers): string
    {
        foreach ($headers as $header) {
            if (preg_match('#^HTTP/\d+\.\d+\s+(\d+)(?:\s+(.*))?#i', $header, $matches)) {
                return 'HTTP status: ' . $matches[1] . ($matches[2] ? ' ' . $matches[2] : '');
            }
        }
        return '';
    }

    private function getSslCaInfo(): ?string
    {
        $paths = [ini_get('curl.cainfo'), ini_get('openssl.cafile')];
        foreach ($paths as $path) {
            if (!is_string($path) || $path === '') {
                continue;
            }
            if (is_file($path)) {
                return $path;
            }
        }
        return null;
    }

    private function extractZipArchive(string $zipFile, string $destination): void
    {
        if (!class_exists('\ZipArchive')) {
            throw new RuntimeException('ZipArchive no está disponible en el entorno para extraer la actualización.');
        }

        $zip = new \ZipArchive();
        if ($zip->open($zipFile) !== true) {
            throw new RuntimeException('No se pudo abrir el archivo ZIP de la actualización.');
        }

        if (!is_dir($destination) && !mkdir($destination, 0755, true) && !is_dir($destination)) {
            throw new RuntimeException('No se pudo crear el directorio de extracción.');
        }

        if (!$zip->extractTo($destination)) {
            $zip->close();
            throw new RuntimeException('No fue posible extraer la actualización remota.');
        }

        $zip->close();
    }

    private function findFirstSubdirectory(string $directory): ?string
    {
        $items = scandir($directory);
        if ($items === false) {
            return null;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $path = $directory . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                return $path;
            }
        }

        return null;
    }

    private function mergeReleaseFiles(string $source, string $destination, array $excludes): void
    {
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST);
        foreach ($iterator as $item) {
            $relativePath = ltrim(str_replace($source, '', $item->getPathname()), DIRECTORY_SEPARATOR);
            foreach ($excludes as $exclude) {
                if ($relativePath === $exclude || str_starts_with($relativePath, rtrim($exclude, '/')) || str_starts_with($relativePath, trim($exclude, '/') . DIRECTORY_SEPARATOR)) {
                    continue 2;
                }
            }

            $target = $destination . DIRECTORY_SEPARATOR . $relativePath;
            if ($item->isDir()) {
                if (!is_dir($target) && !mkdir($target, 0755, true) && !is_dir($target)) {
                    throw new RuntimeException('No fue posible crear el directorio de actualización: ' . $target);
                }
                continue;
            }

            $targetDirectory = dirname($target);
            if (!is_dir($targetDirectory) && !mkdir($targetDirectory, 0755, true) && !is_dir($targetDirectory)) {
                throw new RuntimeException('No fue posible crear el directorio de destino: ' . $targetDirectory);
            }

            if (!copy($item->getPathname(), $target)) {
                throw new RuntimeException('No fue posible copiar el archivo de actualización: ' . $relativePath);
            }
        }
    }

    private function installComposerDependencies(): void
    {
        $composerCommand = $this->resolveExecutablePath('composer');
        if ($composerCommand === null) {
            return;
        }

        $command = sprintf(
            '%s install --no-dev --prefer-dist --optimize-autoloader --no-interaction',
            escapeshellarg($composerCommand)
        );

        $result = $this->runExternalCommand($command);
        if ($result === null) {
            throw new RuntimeException('No fue posible ejecutar Composer para instalar dependencias después de la actualización.');
        }
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

    public function progressCallback(int $current, int $total, string $status): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $_SESSION['backup_progress_current'] = $current;
        $_SESSION['backup_progress_total'] = $total;
        if ($total > 0 && $current >= $total) {
            $_SESSION['backup_progress_status'] = 'COMPLETED';
        } else {
            $_SESSION['backup_progress_status'] = $status;
        }

        session_write_close();
    }

    public function remoteProgressStatus(): array
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        return [
            'current' => $_SESSION['remote_update_progress_current'] ?? 0,
            'total' => $_SESSION['remote_update_progress_total'] ?? 0,
            'status' => $_SESSION['remote_update_progress_status'] ?? 'idle',
            'message' => $_SESSION['remote_update_progress_message'] ?? '',
        ];
    }

    private function setRemoteUpdateProgress(int $current, int $total, string $status, bool $completed = false): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $_SESSION['remote_update_progress_current'] = $current;
        $_SESSION['remote_update_progress_total'] = $total;
        $_SESSION['remote_update_progress_status'] = $completed ? 'COMPLETED' : $status;
        $_SESSION['remote_update_progress_message'] = $status;

        session_write_close();
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

}

