<?php

declare(strict_types=1);

namespace AgroPCC\Services;

use PDO;
use RuntimeException;

final class Installer extends BaseService
{
    public function __construct(protected readonly PDO $connection, private readonly string $rootPath)
    {
    }

    public function install(array $input, array $logo, array $databaseConfig): void
    {
        $this->validate($input, $logo);
        $this->ensureConfigCanBeWritten();
        $logoPath = $this->storeLogo($logo);

        try {
            $this->runSqlFile($this->rootPath . '/database/schema.sql');
            $this->registerBaselineSchema();
            $this->runSeed('001_permissions', $this->rootPath . '/database/seeds/001_permissions.sql');
            $this->runSeed('002_system_catalogs', $this->rootPath . '/database/seeds/002_system_catalogs.sql');
            $this->runSeed('003_catalog_values', $this->rootPath . '/database/seeds/003_catalog_values.sql');
            // The canonical schema is the baseline; migrations add their data fixes and latest permissions.
            (new MigrationRunner($this->connection, $this->rootPath))->run();

            $this->connection->beginTransaction();
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

            $superAdminRoleId = $this->createRole($companyId, 'Super Administrador', 'Acceso completo al sistema', 1);
            $this->grantAllPermissions($superAdminRoleId);

            $roles = [
                ['name' => 'Gerencia', 'description' => 'Acceso ejecutivo para gestión consolidada.', 'permissions' => ['dashboard.view', 'reports.view', 'reports.export', 'notifications.view']],
                ['name' => 'Administración', 'description' => 'Gestión operativa de compras, producción y documentos.', 'permissions' => ['masters.view', 'procurement.view', 'production.view', 'machinery.view', 'documents.view', 'notifications.view', 'tasks.view', 'tasks.create', 'tasks.update', 'calendar.view', 'calendar.create']],
                ['name' => 'Contabilidad y Finanzas', 'description' => 'Control de presupuestos y análisis financiero.', 'permissions' => ['costs.view', 'costs.manage', 'budgets.view', 'budgets.create', 'reports.view', 'reports.export']],
                ['name' => 'RR.HH', 'description' => 'Gestión de personal, cuadrillas y mano de obra.', 'permissions' => ['labor.view', 'labor.create', 'reports.view']],
                ['name' => 'Bodega', 'description' => 'Inventario, bodegas, solicitudes internas y recepciones.', 'permissions' => ['inventory.view', 'inventory.manage', 'warehouse.view', 'warehouse.create', 'warehouse.update', 'requests.view', 'requests.create', 'requests.approve', 'requests.fulfill', 'procurement.receive', 'notifications.view']],
            ];

            foreach ($roles as $roleDefinition) {
                $roleId = $this->createRole(
                    $companyId,
                    $roleDefinition['name'],
                    $roleDefinition['description'],
                    0,
                );
                $this->assignPermissions($roleId, $roleDefinition['permissions']);
            }

            $user = $this->connection->prepare(
                'INSERT INTO users (company_id, role_id, full_name, email, password_hash, phone) VALUES (?, ?, ?, ?, ?, ?)'
            );
            $user->execute([
                $companyId,
                $superAdminRoleId,
                $input['admin_name'],
                strtolower($input['admin_email']),
                password_hash($input['admin_password'], PASSWORD_DEFAULT),
                $input['admin_phone'] ?: null,
            ]);
            $userId = (int) $this->connection->lastInsertId();

            if (!empty($input['install_demo'])) {
                (new DemoDataManager($this->connection, $this->rootPath, $companyId))->install((int) $userId);
            }
            if ($this->connection->inTransaction()) {
                $this->connection->commit();
            }
            $this->writeConfig($databaseConfig);
        } catch (\Throwable $exception) {
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }
            throw $exception;
        }
    }

    private function validate(array $input, array $logo): void
    {
        $required = ['legal_name', 'trade_name', 'admin_name', 'admin_email', 'admin_password'];
        foreach ($required as $field) {
            if (trim((string) ($input[$field] ?? '')) === '') {
                throw new RuntimeException('Por favor, completa todos los campos obligatorios.');
            }
        }
        if (!filter_var($input['admin_email'], FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('El correo del administrador no es vÃ¡lido.');
        }
        if (strlen($input['admin_password']) < 10) {
            throw new RuntimeException('La contraseÃ±a debe tener al menos 10 caracteres.');
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

    private function createRole(int $companyId, string $name, string $description, int $isSystem): int
    {
        $statement = $this->connection->prepare(
            'INSERT INTO roles (company_id, name, description, is_system) VALUES (?, ?, ?, ?)' 
        );
        $statement->execute([$companyId, $name, $description, $isSystem]);
        return (int) $this->connection->lastInsertId();
    }

    private function grantAllPermissions(int $roleId): void
    {
        $this->connection->prepare('INSERT INTO role_permissions (role_id, permission_id) SELECT ?, id FROM permissions')->execute([$roleId]);
    }

    private function assignPermissions(int $roleId, array $permissions): void
    {
        $statement = $this->connection->prepare(
            'INSERT IGNORE INTO role_permissions (role_id, permission_id) SELECT ?, id FROM permissions WHERE code IN (' . implode(',', array_fill(0, count($permissions), '?')) . ')'
        );
        $statement->execute(array_merge([$roleId], $permissions));
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
            '022_complete_module_permissions',
            '023_demo_data_manager',
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
            throw new RuntimeException('No fue posible preparar el directorio de configuraciÃ³n.');
        }
        if (!is_writable($directory)) {
            throw new RuntimeException('El directorio de configuraciÃ³n no tiene permisos de escritura.');
        }
    }

    private function writeConfig(array $databaseConfig): void
    {
        $source = require $this->rootPath . '/config/config.example.php';
        $source['database'] = array_merge($source['database'], $databaseConfig);
        $source['security']['csrf_key'] = bin2hex(random_bytes(32));
        $config = var_export($source, true);
        $path = $this->rootPath . '/config/config.php';
        $temporary = $path . '.tmp';
        if (file_put_contents($temporary, "<?php\n\ndeclare(strict_types=1);\n\nreturn " . $config . ";\n", LOCK_EX) === false || !rename($temporary, $path)) {
            if (is_file($temporary)) {
                unlink($temporary);
            }
            throw new RuntimeException('No fue posible guardar la configuraciÃ³n de la aplicaciÃ³n.');
        }
    }
}

