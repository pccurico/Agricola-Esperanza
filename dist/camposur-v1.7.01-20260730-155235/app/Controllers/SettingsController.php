<?php

declare(strict_types=1);

namespace CampoSur\Controllers;

final class SettingsController
{
    public function handle(): array
    {
        $service = new \CampoSur\Services\CompanySettings(database()->connection(), (int) $_SESSION['company_id'], dirname(__DIR__, 2));
        $error = null;
        $success = null;
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $service->update($_POST, $_FILES['logo'] ?? []);
                (new \CampoSur\Services\AuditLog(database()->connection(), (int) $_SESSION['company_id']))->record((int) $_SESSION['user_id'], 'UPDATE', 'company');
                $success = 'Configuración actualizada correctamente.';
            }
        } catch (\Throwable $exception) {
            $error = $exception->getMessage();
        }
        return ['company' => $service->company(), 'error' => $error, 'success' => $success];
    }
}
