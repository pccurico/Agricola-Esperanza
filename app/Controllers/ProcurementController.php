<?php

declare(strict_types=1);

namespace CampoSur\Controllers;

final class ProcurementController
{
    public function handle(): array
    {
        $service = new \CampoSur\Services\ProcurementManagement(database()->connection(), (int) $_SESSION['company_id']);
        $error = null;
        $success = null;
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create_supplier') {
                $service->createSupplier($_POST);
                (new \CampoSur\Services\AuditLog(database()->connection(), (int) $_SESSION['company_id']))->record((int) $_SESSION['user_id'], 'CREATE', 'supplier');
                $success = 'Proveedor creado correctamente.';
            }
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create_order') {
                $service->createOrder($_POST, (int) $_SESSION['user_id']);
                (new \CampoSur\Services\AuditLog(database()->connection(), (int) $_SESSION['company_id']))->record((int) $_SESSION['user_id'], 'CREATE', 'purchase_order');
                $success = 'Orden de compra creada correctamente.';
            }
        } catch (\Throwable $exception) {
            $error = $exception->getMessage();
        }
        return [...$service->options(), 'suppliers' => $service->suppliers(), 'orders' => $service->orders(), 'error' => $error, 'success' => $success];
    }
}
