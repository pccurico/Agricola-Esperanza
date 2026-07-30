<?php

declare(strict_types=1);

/**
 * Compara el esquema canónico y las migraciones con la base configurada.
 * No ejecuta DDL ni modifica datos.
 */

$rootPath = $argv[1] ?? dirname(__DIR__);
$configPath = $rootPath . '/config/config.php';
$schemaPath = $rootPath . '/database/schema.sql';

if (!is_file($configPath) || !is_file($schemaPath)) {
    fwrite(STDERR, "No se encontró config/config.php o database/schema.sql.\n");
    exit(1);
}

$config = require $configPath;
$database = $config['database'] ?? [];
$dsn = sprintf(
    'mysql:host=%s;port=%s;dbname=%s;charset=%s',
    $database['host'] ?? '',
    $database['port'] ?? '',
    $database['name'] ?? '',
    $database['charset'] ?? 'utf8mb4'
);

try {
    $connection = new PDO($dsn, $database['user'] ?? '', $database['password'] ?? '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_CASE => PDO::CASE_LOWER]);
} catch (Throwable $exception) {
    fwrite(STDERR, "No fue posible conectar a MySQL: {$exception->getMessage()}\n");
    exit(1);
}

/** @return array<string, string> */
function createTables(string $sql): array
{
    preg_match_all('/CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?`?([a-zA-Z0-9_]+)`?\s*\(/i', $sql, $matches, PREG_OFFSET_CAPTURE);
    $tables = [];
    foreach ($matches[0] as $index => $match) {
        $start = $match[1] + strlen($match[0]) - 1;
        $depth = 0;
        $quote = null;
        for ($position = $start, $length = strlen($sql); $position < $length; $position++) {
            $character = $sql[$position];
            if ($quote !== null) {
                if ($character === $quote && ($position === 0 || $sql[$position - 1] !== '\\')) {
                    $quote = null;
                }
                continue;
            }
            if ($character === "'" || $character === '"' || $character === '`') {
                $quote = $character;
            } elseif ($character === '(') {
                $depth++;
            } elseif ($character === ')' && --$depth === 0) {
                $tables[strtolower($matches[1][$index][0])] = substr($sql, $start + 1, $position - $start - 1);
                break;
            }
        }
    }
    return $tables;
}

/** @return list<string> */
function definitions(string $body): array
{
    $definitions = [];
    $start = 0;
    $depth = 0;
    $quote = null;
    for ($position = 0, $length = strlen($body); $position < $length; $position++) {
        $character = $body[$position];
        if ($quote !== null) {
            if ($character === $quote && ($position === 0 || $body[$position - 1] !== '\\')) {
                $quote = null;
            }
            continue;
        }
        if ($character === "'" || $character === '"' || $character === '`') {
            $quote = $character;
        } elseif ($character === '(') {
            $depth++;
        } elseif ($character === ')') {
            $depth--;
        } elseif ($character === ',' && $depth === 0) {
            $definitions[] = trim(substr($body, $start, $position - $start));
            $start = $position + 1;
        }
    }
    $last = trim(substr($body, $start));
    if ($last !== '') {
        $definitions[] = $last;
    }
    return $definitions;
}

/** @return array<string, array{type:string, nullable:bool, default:?string, extra:string}> */
function schemaColumns(string $body): array
{
    $columns = [];
    foreach (definitions($body) as $definition) {
        if (!preg_match('/^`?([a-zA-Z0-9_]+)`?\s+(.+)$/s', $definition, $matches)) {
            continue;
        }
        if (preg_match('/^(CONSTRAINT|PRIMARY|UNIQUE|KEY|INDEX|FOREIGN|CHECK)\b/i', $matches[1])) {
            continue;
        }
        $definition = preg_replace('/\s+/', ' ', trim($matches[2]));
        preg_match('/^([A-Z]+(?:\([^)]*\))?(?:\s+UNSIGNED)?)/i', $definition, $type);
        preg_match('/\bDEFAULT\s+((?:\'[^\']*\')|(?:"[^"]*")|[^\s]+)/i', $definition, $default);
        $columns[strtolower($matches[1])] = [
            'type' => normalizeType($type[1] ?? ''),
            'nullable' => !str_contains(strtoupper($definition), 'NOT NULL') && !str_contains(strtoupper($definition), 'PRIMARY KEY'),
            'default' => isset($default[1]) ? normalizeDefault($default[1]) : null,
            'extra' => trim((str_contains(strtoupper($definition), 'AUTO_INCREMENT') ? 'auto_increment ' : '') . (str_contains(strtoupper($definition), 'ON UPDATE CURRENT_TIMESTAMP') ? 'on update current_timestamp' : '')),
        ];
    }
    return $columns;
}

function normalizeType(string $type): string
{
    $type = strtolower($type);
    return preg_replace('/\b(tinyint|smallint|mediumint|int|integer|bigint)\(\d+\)/', '$1', $type) ?? $type;
}

function normalizeDefault(?string $value): ?string
{
    if ($value === null) {
        return null;
    }
    $value = strtolower(trim($value, " '\""));
    if (is_numeric($value)) {
        return rtrim(rtrim($value, '0'), '.') ?: '0';
    }
    return $value === 'current_timestamp()' ? 'current_timestamp' : $value;
}

/** @return array<string, string> */
function schemaIndexes(string $body): array
{
    $indexes = [];
    foreach (definitions($body) as $definition) {
        if (preg_match('/^`?([a-zA-Z0-9_]+)`?\s+.+\bPRIMARY\s+KEY\b/i', $definition, $matches)) {
            $indexes['primary'] = 'unique:' . strtolower($matches[1]);
        } elseif (preg_match('/^PRIMARY\s+KEY\s*\(([^)]+)\)/i', $definition, $matches)) {
            $indexes['primary'] = 'unique:' . normalizeColumns($matches[1]);
        } elseif (preg_match('/^UNIQUE\s+KEY\s+`?([a-zA-Z0-9_]+)`?\s*\(([^)]+)\)/i', $definition, $matches)) {
            $indexes[strtolower($matches[1])] = 'unique:' . normalizeColumns($matches[2]);
        } elseif (preg_match('/^(?:KEY|INDEX)\s+`?([a-zA-Z0-9_]+)`?\s*\(([^)]+)\)/i', $definition, $matches)) {
            $indexes[strtolower($matches[1])] = 'index:' . normalizeColumns($matches[2]);
        }
    }
    return $indexes;
}

function normalizeColumns(string $columns): string
{
    return strtolower(preg_replace('/[`\s]/', '', $columns) ?? '');
}

/** @return array<string, string> */
function schemaForeignKeys(string $body): array
{
    $foreignKeys = [];
    foreach (definitions($body) as $definition) {
        if (preg_match('/^CONSTRAINT\s+`?([a-zA-Z0-9_]+)`?\s+FOREIGN\s+KEY\s*\(([^)]+)\)\s+REFERENCES\s+`?([a-zA-Z0-9_]+)`?\s*\(([^)]+)\)(.*)$/i', $definition, $matches)) {
            preg_match('/ON\s+DELETE\s+(CASCADE|SET\s+NULL|RESTRICT|NO\s+ACTION)/i', $matches[5], $delete);
            preg_match('/ON\s+UPDATE\s+(CASCADE|SET\s+NULL|RESTRICT|NO\s+ACTION)/i', $matches[5], $update);
            $foreignKeys[strtolower($matches[1])] = normalizeColumns($matches[2]) . '->' . strtolower($matches[3]) . '.' . normalizeColumns($matches[4]) . ':delete=' . strtolower(str_replace(' ', '_', $delete[1] ?? 'restrict')) . ':update=' . strtolower(str_replace(' ', '_', $update[1] ?? 'restrict'));
        }
    }
    return $foreignKeys;
}

$schemaTables = createTables((string) file_get_contents($schemaPath));
$databaseTables = $connection->query("SELECT table_name FROM information_schema.tables WHERE table_schema = DATABASE() AND table_type = 'BASE TABLE'")->fetchAll(PDO::FETCH_COLUMN);
$databaseLookup = array_fill_keys(array_map('strtolower', $databaseTables), true);
$errors = [];

foreach (array_keys($schemaTables) as $table) {
    if (!isset($databaseLookup[$table])) {
        $errors[] = "Tabla ausente en MySQL: {$table}";
    }
}
foreach (array_keys($databaseLookup) as $table) {
    if (!isset($schemaTables[$table])) {
        $errors[] = "Tabla ausente en schema.sql: {$table}";
    }
}

foreach ($schemaTables as $table => $body) {
    if (!isset($databaseLookup[$table])) {
        continue;
    }
    $expectedColumns = schemaColumns($body);
    $statement = $connection->prepare('SELECT column_name, column_type, is_nullable, column_default, extra FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ?');
    $statement->execute([$table]);
    $actualColumns = [];
    foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $column) {
        $actualColumns[strtolower($column['column_name'])] = [
            'type' => normalizeType($column['column_type']),
            'nullable' => $column['is_nullable'] === 'YES',
            'default' => normalizeDefault($column['column_default']),
            'extra' => trim((str_contains(strtolower($column['extra']), 'auto_increment') ? 'auto_increment ' : '') . (str_contains(strtolower($column['extra']), 'on update current_timestamp') ? 'on update current_timestamp' : '')),
        ];
    }
    foreach ($expectedColumns as $name => $expected) {
        if (!isset($actualColumns[$name])) {
            $errors[] = "Columna ausente en MySQL: {$table}.{$name}";
        } elseif ($expected !== $actualColumns[$name]) {
            $errors[] = "Definición distinta: {$table}.{$name}";
        }
    }
    foreach (array_keys($actualColumns) as $name) {
        if (!isset($expectedColumns[$name])) {
            $errors[] = "Columna ausente en schema.sql: {$table}.{$name}";
        }
    }

    $expectedIndexes = schemaIndexes($body);
    $statement = $connection->prepare('SELECT index_name, non_unique, column_name FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = ? ORDER BY index_name, seq_in_index');
    $statement->execute([$table]);
    $actualIndexes = [];
    foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $index) {
        $name = strtolower($index['index_name']);
        $actualIndexes[$name]['kind'] = (int) $index['non_unique'] === 0 ? 'unique' : 'index';
        $actualIndexes[$name]['columns'][] = strtolower($index['column_name']);
    }
    foreach ($actualIndexes as $name => $index) {
        $actualIndexes[$name] = $index['kind'] . ':' . implode(',', $index['columns']);
    }
    foreach ($expectedIndexes as $name => $expected) {
        if (($actualIndexes[$name] ?? null) !== $expected) {
            $errors[] = "Índice distinto o ausente en MySQL: {$table}.{$name}";
        }
    }

    $expectedForeignKeys = schemaForeignKeys($body);
    $statement = $connection->prepare("SELECT k.constraint_name, k.column_name, k.referenced_table_name, k.referenced_column_name, r.delete_rule, r.update_rule FROM information_schema.key_column_usage k INNER JOIN information_schema.referential_constraints r ON r.constraint_schema = k.constraint_schema AND r.constraint_name = k.constraint_name AND r.table_name = k.table_name WHERE k.table_schema = DATABASE() AND k.table_name = ? AND k.referenced_table_name IS NOT NULL ORDER BY k.constraint_name, k.ordinal_position");
    $statement->execute([$table]);
    $actualForeignKeys = [];
    foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $foreignKey) {
        $name = strtolower($foreignKey['constraint_name']);
        $actualForeignKeys[$name]['columns'][] = strtolower($foreignKey['column_name']);
        $actualForeignKeys[$name]['table'] = strtolower($foreignKey['referenced_table_name']);
        $actualForeignKeys[$name]['references'][] = strtolower($foreignKey['referenced_column_name']);
        $actualForeignKeys[$name]['delete'] = strtolower(str_replace(' ', '_', $foreignKey['delete_rule']));
        $actualForeignKeys[$name]['update'] = strtolower(str_replace(' ', '_', $foreignKey['update_rule']));
    }
    foreach ($actualForeignKeys as $name => $foreignKey) {
        $actualForeignKeys[$name] = implode(',', $foreignKey['columns']) . '->' . $foreignKey['table'] . '.' . implode(',', $foreignKey['references']) . ':delete=' . $foreignKey['delete'] . ':update=' . $foreignKey['update'];
    }
    foreach ($expectedForeignKeys as $name => $expected) {
        if (!isset($actualForeignKeys[$name])) {
            $errors[] = "Clave foránea distinta o ausente en MySQL: {$table}.{$name}";
        }
    }
    foreach (array_keys($actualForeignKeys) as $name) {
        if (!isset($expectedForeignKeys[$name])) {
            $errors[] = "Clave foránea ausente en schema.sql: {$table}.{$name}";
        }
    }
}

foreach (glob($rootPath . '/database/migrations/*.sql') ?: [] as $migration) {
    foreach (array_keys(createTables((string) file_get_contents($migration))) as $table) {
        if (!isset($schemaTables[$table])) {
            $errors[] = 'Tabla de migración ausente en schema.sql: ' . $table . ' (' . basename($migration) . ')';
        }
        if (!isset($databaseLookup[$table])) {
            $errors[] = 'Tabla de migración ausente en MySQL: ' . $table . ' (' . basename($migration) . ')';
        }
    }
}

if ($errors !== []) {
    foreach ($errors as $error) {
        fwrite(STDERR, "[FAIL] {$error}\n");
    }
    exit(1);
}

echo '[OK] schema.sql, migraciones y MySQL son consistentes para tablas, columnas, índices y claves foráneas.' . PHP_EOL;
