<?php

declare(strict_types=1);

namespace AgroPCC\Controllers;

final class SettingsController extends BaseController
{
    public function handle(): array
    {
        $service = new \AgroPCC\Services\CompanySettings(database()->connection(), (int) $_SESSION['company_id'], dirname(__DIR__, 2));
        $error = null;
        $success = null;
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $service->update($_POST, $_FILES['logo'] ?? []);
                (new \AgroPCC\Services\AuditLog(database()->connection(), (int) $_SESSION['company_id']))->record((int) $_SESSION['user_id'], 'UPDATE', 'company');
                $success = 'ConfiguraciÃ³n actualizada correctamente.';
            }
        } catch (\Throwable $exception) {
            $error = $exception->getMessage();
        }
        return ['company' => $service->company(), 'error' => $error, 'success' => $success];
    }
}
