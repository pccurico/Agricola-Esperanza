<?php

declare(strict_types=1);

namespace CampoSur\Services;

use PDO;
use RuntimeException;

final class Installer
{
    public function __construct(private readonly PDO $connection, private readonly string $rootPath)
    {
    }

    public function install(array $input, array $logo): void
    {
        $this->validate($input, $logo);
        $this->ensureConfigCanBeWritten();
        $logoPath = $this->storeLogo($logo);

        $this->connection->beginTransaction();

        try {
            $this->runSqlFile($this->rootPath . '/database/schema.sql');
            $this->registerBaselineSchema();
            $this->runSeed('001_permissions', $this->rootPath . '/database/seeds/001_permissions.sql');
            $this->runSeed('002_system_catalogs', $this->rootPath . '/database/seeds/002_system_catalogs.sql');
            $this->runSeed('003_catalog_values', $this->rootPath . '/database/seeds/003_catalog_values.sql');

            $company = $this->connection->prepare(
                'INSERT INTO companies (legal_name, trade_name, tax_id, logo_path, email, phone, commune, region) VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $company->execute([
                $input['legal_name'],
                $input['trade_name'],
                $input['tax_id'] ?: null,
                $logoPath,
                $input['company_email'] ?: null,
                $input['company_phone'] ?: null,
                $input['commune'] ?: null,
                $input['region'] ?: null,
            ]);
            $companyId = (int) $this->connection->lastInsertId();

            $role = $this->connection->prepare(
                'INSERT INTO roles (company_id, name, description, is_system) VALUES (?, ?, ?, 1)'
            );
            $role->execute([$companyId, 'Administrador', 'Acceso completo a la gestión de la agrícola']);
            $roleId = (int) $this->connection->lastInsertId();

            $this->connection->exec(
                'INSERT INTO role_permissions (role_id, permission_id) SELECT ' . $roleId . ', id FROM permissions'
            );

            $user = $this->connection->prepare(
                'INSERT INTO users (company_id, role_id, full_name, email, password_hash, phone) VALUES (?, ?, ?, ?, ?, ?)'
            );
            $user->execute([
                $companyId,
                $roleId,
                $input['admin_name'],
                strtolower($input['admin_email']),
                password_hash($input['admin_password'], PASSWORD_DEFAULT),
                $input['admin_phone'] ?: null,
            ]);

            $farm = $this->connection->prepare(
                'INSERT INTO farms (company_id, name, code, location, hectares) VALUES (?, ?, ?, ?, ?)'
            );
            $farm->execute([$companyId, $input['farm_name'], strtoupper($input['farm_code']), $input['farm_location'] ?: null, $input['farm_hectares']]);

            $season = $this->connection->prepare(
                'INSERT INTO seasons (company_id, name, starts_on, ends_on) VALUES (?, ?, ?, ?)'
            );
            $season->execute([$companyId, $input['season_name'], $input['season_start'], $input['season_end']]);

            $center = $this->connection->prepare('INSERT INTO cost_centers (company_id, code, name, category) VALUES (?, ?, ?, ?)');
            foreach ([
                ['ADM-001', 'Administración general', 'ADMINISTRACION'],
                ['MO-001', 'Mano de obra agrícola', 'MANO_DE_OBRA'],
                ['INV-001', 'Inversiones y proyectos', 'INVERSION'],
                ['SG-001', 'Servicios y gastos generales', 'SERVICIOS_GASTOS'],
                ['BOD-001', 'Bodega e insumos', 'BODEGA'],
            ] as [$code, $name, $category]) {
                $center->execute([$companyId, $code, $name, $category]);
            }

            $this->connection->commit();
            $this->writeConfig();
        } catch (\Throwable $exception) {
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }
            throw $exception;
        }
    }

    private function validate(array $input, array $logo): void
    {
        $required = ['legal_name', 'trade_name', 'admin_name', 'admin_email', 'admin_password', 'farm_name', 'farm_code', 'farm_hectares', 'season_name', 'season_start', 'season_end'];
        foreach ($required as $field) {
            if (trim((string) ($input[$field] ?? '')) === '') {
                throw new RuntimeException('Completa todos los campos obligatorios.');
            }
        }
        if (!filter_var($input['admin_email'], FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('El correo del administrador no es válido.');
        }
        if (strlen($input['admin_password']) < 10) {
            throw new RuntimeException('La contraseña debe tener al menos 10 caracteres.');
        }
        if (!is_numeric($input['farm_hectares']) || (float) $input['farm_hectares'] < 0) {
            throw new RuntimeException('La superficie del fundo debe ser un número válido.');
        }
        $start = \DateTimeImmutable::createFromFormat('!Y-m-d', (string) $input['season_start']);
        $end = \DateTimeImmutable::createFromFormat('!Y-m-d', (string) $input['season_end']);
        $startErrors = \DateTimeImmutable::getLastErrors();
        $endErrors = \DateTimeImmutable::getLastErrors();
        if (!$start || !$end || ($startErrors !== false && ($startErrors['warning_count'] > 0 || $startErrors['error_count'] > 0)) || ($endErrors !== false && ($endErrors['warning_count'] > 0 || $endErrors['error_count'] > 0))) {
            throw new RuntimeException('Las fechas de la temporada no son válidas.');
        }
        if ($end <= $start) {
            throw new RuntimeException('El término de la temporada debe ser posterior al inicio.');
        }
        if (($logo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            if ($logo['error'] !== UPLOAD_ERR_OK || $logo['size'] > 2 * 1024 * 1024) {
                throw new RuntimeException('El logo debe pesar menos de 2 MB.');
            }
            $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($logo['tmp_name']);
            if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
                throw new RuntimeException('El logo debe ser JPG, PNG o WEBP.');
            }
        }
    }

    private function storeLogo(array $logo): ?string
    {
        if (($logo['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($logo['tmp_name']);
        $extension = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$mime];
        $directory = $this->rootPath . '/storage/uploads';
        if (!is_dir($directory)) {
            mkdir($directory, 0750, true);
        }
        $filename = 'company-logo-' . bin2hex(random_bytes(12)) . '.' . $extension;
        if (!move_uploaded_file($logo['tmp_name'], $directory . '/' . $filename)) {
            throw new RuntimeException('No fue posible guardar el logo.');
        }
        return 'storage/uploads/' . $filename;
    }

    private function registerBaselineSchema(): void
    {
        $versions = [
            '001_initial_schema',
            '002_labor_schema',
            '003_production_schema',
            '004_procurement_schema',
            '005_budget_schema',
            '006_machinery_schema',
            '007_module_permissions',
            '008_platform_entities',
            '009_system_logs',
            '010_system_catalogs',
            '011_catalog_backed_values',
            '012_purchase_receptions',
            '013_procurement_reception_permission',
            '014_inventory_warehouse_scope',
            '015_warehouse_permissions',
            '016_internal_request_items',
            '017_internal_request_permissions',
            '018_notification_permissions',
            '019_tasks_calendar_permissions',
            '020_document_permissions',
            '021_api_token_permissions',
        ];
        $statement = $this->connection->prepare('INSERT IGNORE INTO schema_migrations (version) VALUES (?)');
        foreach ($versions as $version) {
            $statement->execute([$version]);
        }
    }

    private function runSeed(string $version, string $path): void
    {
        $this->runSqlFile($path);
    }

    private function runSqlFile(string $path): void
    {
        $statements = preg_split('/;\s*(?:\r?\n|$)/', file_get_contents($path));
        foreach ($statements as $statement) {
            if (trim($statement) !== '') {
                $this->connection->exec($statement);
            }
        }
    }

    private function ensureConfigCanBeWritten(): void
    {
        $directory = $this->rootPath . '/config';
        if (!is_dir($directory) && !mkdir($directory, 0750, true) && !is_dir($directory)) {
            throw new RuntimeException('No fue posible preparar el directorio de configuración.');
        }
        if (!is_writable($directory)) {
            throw new RuntimeException('El directorio de configuración no tiene permisos de escritura.');
        }
    }

    private function writeConfig(): void
    {
        $source = require $this->rootPath . '/config/config.example.php';
        $source['security']['csrf_key'] = bin2hex(random_bytes(32));
        $config = var_export($source, true);
        $path = $this->rootPath . '/config/config.php';
        $temporary = $path . '.tmp';
        if (file_put_contents($temporary, "<?php\n\ndeclare(strict_types=1);\n\nreturn " . $config . ";\n", LOCK_EX) === false || !rename($temporary, $path)) {
            if (is_file($temporary)) {
                unlink($temporary);
            }
            throw new RuntimeException('No fue posible guardar la configuración de la aplicación.');
        }
    }
}
