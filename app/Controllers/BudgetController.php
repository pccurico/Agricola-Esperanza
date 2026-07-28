<?php

declare(strict_types=1);

namespace CampoSur\Controllers;

final class BudgetController
{
    public function handle(): array
    {
        $service = new \CampoSur\Services\BudgetManagement(database()->connection(), (int) $_SESSION['company_id']);
        $error = null;
        $success = null;
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $service->create($_POST, (int) $_SESSION['user_id']);
                (new \CampoSur\Services\AuditLog(database()->connection(), (int) $_SESSION['company_id']))->record((int) $_SESSION['user_id'], 'CREATE', 'budget');
                $success = 'Presupuesto creado correctamente.';
            }
        } catch (\Throwable $exception) {
            $error = $exception->getMessage();
        }
        return [...$service->options(), 'budgets' => $service->budgets(), 'error' => $error, 'success' => $success];
    }
}
