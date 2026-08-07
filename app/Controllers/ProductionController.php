<?php

declare(strict_types=1);

namespace AgroPCC\Controllers;

final class ProductionController extends BaseController
{
    public function handle(): array
    {
        $service = new \AgroPCC\Services\ProductionManagement(database()->connection(), (int) $_SESSION['company_id']);
        $error = null;
        $success = null;
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $service->create($_POST, (int) $_SESSION['user_id']);
                (new \AgroPCC\Services\AuditLog(database()->connection(), (int) $_SESSION['company_id']))->record((int) $_SESSION['user_id'], 'CREATE', 'production_entry');
                $success = 'ProducciÃ³n registrada correctamente.';
            }
        } catch (\Throwable $exception) {
            $error = $exception->getMessage();
        }
        return [...$service->options(), 'entries' => $service->entries(), 'summary' => $service->summary(), 'error' => $error, 'success' => $success];
    }
}
