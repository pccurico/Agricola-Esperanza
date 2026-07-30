<?php

declare(strict_types=1);

namespace CampoSur\Controllers;

use CampoSur\Core\Database;
use CampoSur\Services\Installer;
use RuntimeException;

final class SetupController extends BaseController
{
    public function handle(): array
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        if (empty($_SESSION['setup_csrf'])) {
            $_SESSION['setup_csrf'] = bin2hex(random_bytes(32));
        }

        $data = [
            'legal_name' => \CampoSur\Services\InputNormalizer::text((string) ($_POST['legal_name'] ?? '')),
            'trade_name' => \CampoSur\Services\InputNormalizer::text((string) ($_POST['trade_name'] ?? '')),
            'tax_id' => \CampoSur\Services\InputNormalizer::rut((string) ($_POST['tax_id'] ?? '')),
            'company_email' => \CampoSur\Services\InputNormalizer::email((string) ($_POST['company_email'] ?? '')),
            'company_phone' => \CampoSur\Services\InputNormalizer::phone((string) ($_POST['company_phone'] ?? '')),
            'commune' => \CampoSur\Services\InputNormalizer::text((string) ($_POST['commune'] ?? '')),
            'region' => \CampoSur\Services\InputNormalizer::text((string) ($_POST['region'] ?? '')),
            'admin_name' => \CampoSur\Services\InputNormalizer::text((string) ($_POST['admin_name'] ?? '')),
            'admin_email' => \CampoSur\Services\InputNormalizer::email((string) ($_POST['admin_email'] ?? '')),
            'admin_phone' => \CampoSur\Services\InputNormalizer::phone((string) ($_POST['admin_phone'] ?? '')),
            'admin_password' => (string) ($_POST['admin_password'] ?? ''),
            'install_demo' => ($_POST['install_demo'] ?? '') === '1',
            'db_host' => trim((string) ($_POST['db_host'] ?? 'localhost')),
            'db_port' => (int) ($_POST['db_port'] ?? 3306),
            'db_name' => trim((string) ($_POST['db_name'] ?? '')),
            'db_user' => trim((string) ($_POST['db_user'] ?? '')),
            'db_password' => (string) ($_POST['db_password'] ?? ''),
        ];

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['data' => $data, 'csrf' => $_SESSION['setup_csrf'], 'error' => null];
        }

        try {
            if (!hash_equals($_SESSION['setup_csrf'], (string) ($_POST['csrf'] ?? ''))) {
                throw new RuntimeException('La sesiÃ³n de configuraciÃ³n expirÃ³. Recarga la pÃ¡gina.');
            }
            if ($data['db_host'] === '' || $data['db_name'] === '' || $data['db_user'] === '' || $data['db_port'] < 1 || $data['db_port'] > 65535) {
                throw new RuntimeException('Completa correctamente los datos de conexiÃ³n a la base de datos.');
            }
            $databaseConfig = [
                'host' => $data['db_host'],
                'port' => $data['db_port'],
                'name' => $data['db_name'],
                'user' => $data['db_user'],
                'password' => $data['db_password'],
                'charset' => 'utf8mb4',
            ];
            (new Installer((new Database($databaseConfig))->connection(), dirname(__DIR__, 2)))->install($data, $_FILES['logo'] ?? [], $databaseConfig);
            session_destroy();
            $this->redirect('./');
        } catch (\Throwable $exception) {
            return ['data' => $data, 'csrf' => $_SESSION['setup_csrf'], 'error' => $exception->getMessage()];
        }
    }
}
