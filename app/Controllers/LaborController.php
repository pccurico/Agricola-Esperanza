<?php

declare(strict_types=1);

namespace AgroPCC\Controllers;

final class LaborController extends BaseController
{
    public function handle(): array
    {
        $service = new \AgroPCC\Services\LaborManagement(database()->connection(), (int) $_SESSION['company_id']);
        $error = null;
        $success = null;
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create_worker') {
                $service->createWorker($_POST);
                (new \AgroPCC\Services\AuditLog(database()->connection(), (int) $_SESSION['company_id']))->record((int) $_SESSION['user_id'], 'CREATE', 'worker');
                $success = 'Trabajador creado correctamente.';
            }
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create_labor') {
                $service->createEntry($_POST, (int) $_SESSION['user_id']);
                (new \AgroPCC\Services\AuditLog(database()->connection(), (int) $_SESSION['company_id']))->record((int) $_SESSION['user_id'], 'CREATE', 'labor_entry');
                $success = 'Labor registrada correctamente.';
            }
        } catch (\Throwable $exception) {
            $error = $exception->getMessage();
        }
        return [...$service->options(), 'workers' => $service->workers(), 'entries' => $service->entries(), 'error' => $error, 'success' => $success];
    }
}
