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
        $logoPath = $this->storeLogo($logo);

        $this->connection->beginTransaction();

        try {
            $this->runSqlFile($this->rootPath . '/database/migrations/001_initial_schema.sql');
            $this->runSqlFile($this->rootPath . '/database/migrations/002_labor_schema.sql');
            $this->runSqlFile($this->rootPath . '/database/seeds/001_permissions.sql');

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
        if ($input['season_end'] <= $input['season_start']) {
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

    private function runSqlFile(string $path): void
    {
        $statements = preg_split('/;\s*(?:\r?\n|$)/', file_get_contents($path));
        foreach ($statements as $statement) {
            if (trim($statement) !== '') {
                $this->connection->exec($statement);
            }
        }
    }

    private function writeConfig(): void
    {
        $config = var_export(require $this->rootPath . '/config/config.example.php', true);
        $path = $this->rootPath . '/config/config.php';
        $temporary = $path . '.tmp';
        file_put_contents($temporary, "<?php\n\ndeclare(strict_types=1);\n\nreturn " . $config . ";\n", LOCK_EX);
        rename($temporary, $path);
    }
}
