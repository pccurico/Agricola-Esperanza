<?php

declare(strict_types=1);

namespace CampoSur\Controllers;

use CampoSur\Services\Installer;
use RuntimeException;

final class SetupController
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
            'legal_name' => trim((string) ($_POST['legal_name'] ?? '')),
            'trade_name' => trim((string) ($_POST['trade_name'] ?? '')),
            'tax_id' => trim((string) ($_POST['tax_id'] ?? '')),
            'company_email' => trim((string) ($_POST['company_email'] ?? '')),
            'company_phone' => trim((string) ($_POST['company_phone'] ?? '')),
            'commune' => trim((string) ($_POST['commune'] ?? '')),
            'region' => trim((string) ($_POST['region'] ?? '')),
            'admin_name' => trim((string) ($_POST['admin_name'] ?? '')),
            'admin_email' => trim((string) ($_POST['admin_email'] ?? '')),
            'admin_phone' => trim((string) ($_POST['admin_phone'] ?? '')),
            'admin_password' => (string) ($_POST['admin_password'] ?? ''),
            'farm_name' => trim((string) ($_POST['farm_name'] ?? '')),
            'farm_code' => trim((string) ($_POST['farm_code'] ?? '')),
            'farm_location' => trim((string) ($_POST['farm_location'] ?? '')),
            'farm_hectares' => trim((string) ($_POST['farm_hectares'] ?? '')),
            'season_name' => trim((string) ($_POST['season_name'] ?? '')),
            'season_start' => trim((string) ($_POST['season_start'] ?? '')),
            'season_end' => trim((string) ($_POST['season_end'] ?? '')),
        ];

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['data' => $data, 'csrf' => $_SESSION['setup_csrf'], 'error' => null];
        }

        try {
            if (!hash_equals($_SESSION['setup_csrf'], (string) ($_POST['csrf'] ?? ''))) {
                throw new RuntimeException('La sesión de configuración expiró. Recarga la página.');
            }
            (new Installer(database()->connection(), dirname(__DIR__, 2)))->install($data, $_FILES['logo'] ?? []);
            session_destroy();
            header('Location: ' . app_config('app.url') . '/');
            exit;
        } catch (\Throwable $exception) {
            return ['data' => $data, 'csrf' => $_SESSION['setup_csrf'], 'error' => $exception->getMessage()];
        }
    }
}
