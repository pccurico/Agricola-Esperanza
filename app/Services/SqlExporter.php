<?php

declare(strict_types=1);

namespace AgroPCC\Services;

use PDO;
use RuntimeException;

final class SqlExporter
{
    private readonly PDO $connection;
    private readonly string $backupDirectory;
    private $progressCallback;

    public function __construct(PDO $connection, string $backupDirectory)
    {
        $this->connection = $connection;
        $this->backupDirectory = $backupDirectory;
    }

    public function setProgressCallback($callback): void
    {
        if (!is_callable($callback)) {
            throw new RuntimeException('El callback de progreso proporcionado no es callable.');
        }
        $this->progressCallback = $callback;
    }

    public function export(string $sqlFile, string $filenameBase, int $userId, string $databaseName): string
    {
        $header = $this->buildHeader($userId, $databaseName);
        $handle = fopen($sqlFile, 'wb');
        if ($handle === false) {
            throw new RuntimeException('No se pudo crear el archivo de respaldo.');
        }
        fwrite($handle, $header);

        $tables = $this->fetchTables();
        $total = count($tables);
        $current = 0;

        foreach ($tables as $table) {
            $current++;
            $this->reportProgress($current, $total, 'exporting');
            $this->exportTable($handle, $table);
        }

        fclose($handle);
        $archiveFile = $this->compressArchive($sqlFile, $filenameBase);

        return $archiveFile;
    }

    public function restore(string $path): void
    {
        if (!is_file($path)) {
            throw new RuntimeException('El archivo de respaldo no existe.');
        }

        $temporaryFile = null;
        if (str_ends_with($path, '.zip')) {
            $temporaryFile = $this->extractZipToTemp($path);
            $path = $temporaryFile;
        } elseif (str_ends_with($path, '.gz')) {
            $temporaryFile = $this->decompressGzipToTemp($path);
            $path = $temporaryFile;
        }

        try {
            $this->restoreFromFile($path);
        } finally {
            if ($temporaryFile !== null && is_file($temporaryFile)) {
                @unlink($temporaryFile);
            }
        }
    }

    private function restoreFromFile(string $path): void
    {
        $handle = fopen($path, 'rb');
        if ($handle === false) {
            throw new RuntimeException('No se pudo abrir el archivo de respaldo.');
        }

        $buffer = '';
        while (!feof($handle)) {
            $line = fgets($handle);
            if ($line === false) {
                break;
            }
            $trimmed = trim($line);
            if ($trimmed === '' || str_starts_with($trimmed, '--') || str_starts_with($trimmed, '/*') || str_starts_with($trimmed, '*/')) {
                continue;
            }

            $buffer .= $line;
            if (str_ends_with($trimmed, ';')) {
                $this->connection->exec($buffer);
                $buffer = '';
            }
        }

        fclose($handle);
    }

    private function extractZipToTemp(string $path): string
    {
        if (!class_exists('\ZipArchive')) {
            throw new RuntimeException('ZipArchive no está disponible para restaurar archivos ZIP.');
        }

        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) {
            throw new RuntimeException('No se pudo abrir el archivo ZIP de respaldo.');
        }

        if ($zip->numFiles !== 1) {
            $zip->close();
            throw new RuntimeException('El archivo ZIP de respaldo debe contener un único archivo SQL.');
        }

        $content = $zip->getFromIndex(0);
        $zip->close();
        if ($content === false) {
            throw new RuntimeException('No se pudo extraer el archivo SQL desde el ZIP.');
        }

        $temporaryFile = tempnam(sys_get_temp_dir(), 'restore_');
        if ($temporaryFile === false) {
            throw new RuntimeException('No se pudo crear un archivo temporal para restaurar.');
        }

        file_put_contents($temporaryFile, $content);

        return $temporaryFile;
    }

    private function decompressGzipToTemp(string $path): string
    {
        if (!function_exists('gzopen')) {
            throw new RuntimeException('La extensión zlib no está disponible para restaurar archivos comprimidos.');
        }

        $input = gzopen($path, 'rb');
        if ($input === false) {
            throw new RuntimeException('No se pudo abrir el archivo GZIP de respaldo.');
        }

        $temporaryFile = tempnam(sys_get_temp_dir(), 'restore_');
        if ($temporaryFile === false) {
            gzclose($input);
            throw new RuntimeException('No se pudo crear un archivo temporal para restaurar.');
        }

        $output = fopen($temporaryFile, 'wb');
        if ($output === false) {
            gzclose($input);
            throw new RuntimeException('No se pudo crear un archivo temporal para restaurar.');
        }

        while (!gzeof($input)) {
            $chunk = gzread($input, 8192);
            if ($chunk === false) {
                break;
            }
            fwrite($output, $chunk);
        }

        gzclose($input);
        fclose($output);

        return $temporaryFile;
    }

    private function fetchTables(): array
    {
        $tables = [];
        $query = $this->connection->query('SHOW TABLES');
        while ($row = $query->fetch(PDO::FETCH_NUM)) {
            $tables[] = (string) $row[0];
        }
        return $tables;
    }

    private function exportTable($handle, string $table): void
    {
        fwrite($handle, "\nDROP TABLE IF EXISTS `{$table}`;\n");

        $create = $this->connection->query('SHOW CREATE TABLE `' . str_replace('`', '``', $table) . '`')->fetch(PDO::FETCH_ASSOC);
        if (!isset($create['Create Table'])) {
            throw new RuntimeException('No se pudo obtener la estructura de la tabla: ' . $table);
        }
        fwrite($handle, $create['Create Table'] . ";\n\n");

        fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n");

        $rowsQuery = $this->connection->prepare('SELECT COUNT(*) AS total FROM `' . str_replace('`', '``', $table) . '`');
        $rowsQuery->execute();
        $totalRows = (int) $rowsQuery->fetchColumn();
        $batchSize = 1000;
        $offset = 0;
        while ($offset < $totalRows) {
            $query = $this->connection->prepare('SELECT * FROM `' . str_replace('`', '``', $table) . '` LIMIT ? OFFSET ?');
            $query->bindValue(1, $batchSize, PDO::PARAM_INT);
            $query->bindValue(2, $offset, PDO::PARAM_INT);
            $query->execute();
            $rows = $query->fetchAll(PDO::FETCH_ASSOC);
            if ($rows === []) {
                break;
            }
            $this->writeRows($handle, $table, $rows);
            $offset += $batchSize;
        }

        fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n\n");
    }

    private function writeRows($handle, string $table, array $rows): void
    {
        foreach ($rows as $row) {
            $columns = [];
            $values = [];
            foreach ($row as $column => $value) {
                $columns[] = '`' . str_replace('`', '``', $column) . '`';
                $values[] = $this->quoteValue($value);
            }
            fwrite($handle, 'INSERT INTO `' . str_replace('`', '``', $table) . '` (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $values) . ");\n");
        }
    }

    private function quoteValue(mixed $value): string
    {
        if ($value === null) {
            return 'NULL';
        }
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }
        if (is_string($value)) {
            return "'" . str_replace(["\\", "'", "\n", "\r", "\t", "\0"], ["\\\\", "\\'", "\\n", "\\r", "\\t", "\\0"], $value) . "'";
        }
        return (string) $value;
    }

    private function buildHeader(int $userId, string $databaseName): string
    {
        $userName = (string) ($_SESSION['user_name'] ?? 'Sistema');
        $date = (new \DateTimeImmutable('now'))->format('Y-m-d H:i:s');
        $phpVersion = PHP_VERSION;
        $mysqlVersion = $this->connection->getAttribute(PDO::ATTR_SERVER_VERSION);

        return "-- Sistema Gestión Agrícola PCCURICO\n"
            . "-- Fecha: {$date}\n"
            . "-- Usuario: {$userName}\n"
            . "-- Base de datos: {$databaseName}\n"
            . "-- PHP: {$phpVersion}\n"
            . "-- MySQL/MariaDB: {$mysqlVersion}\n\n"
            . "SET FOREIGN_KEY_CHECKS=0;\n\n";
    }

    private function compressArchive(string $sqlFile, string $filenameBase): string
    {
        if (class_exists('\ZipArchive')) {
            $zipFile = $this->backupDirectory . '/' . $filenameBase . '.zip';
            $zip = new \ZipArchive();
            if ($zip->open($zipFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
                throw new RuntimeException('No se pudo crear el archivo ZIP de respaldo.');
            }
            $zip->addFile($sqlFile, basename($sqlFile));
            $zip->close();
            @unlink($sqlFile);
            return $zipFile;
        }

        if (function_exists('gzencode')) {
            $compressed = gzencode(file_get_contents($sqlFile));
            if ($compressed === false) {
                throw new RuntimeException('No se pudo comprimir el archivo de respaldo.');
            }
            $gzFile = $this->backupDirectory . '/' . $filenameBase . '.sql.gz';
            file_put_contents($gzFile, $compressed);
            @unlink($sqlFile);
            return $gzFile;
        }

        return $sqlFile;
    }

    private function reportProgress(int $current, int $total, string $status): void
    {
        if (is_callable($this->progressCallback)) {
            ($this->progressCallback)($current, $total, $status);
        }
    }
}
