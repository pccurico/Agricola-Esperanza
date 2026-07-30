<?php

declare(strict_types=1);

namespace CampoSur\Controllers;

final class ProcurementController extends BaseController
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
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create_invoice') {
                $invoiceId = $service->createInvoice($_POST, (int) $_SESSION['user_id']);
                (new \CampoSur\Services\AuditLog(database()->connection(), (int) $_SESSION['company_id']))->record((int) $_SESSION['user_id'], 'CREATE', 'purchase_invoice', $invoiceId);
                $success = 'Factura de compra registrada correctamente.';
            }
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'receive_order') {
                $service->receiveOrder($_POST, (int) $_SESSION['user_id']);
                $success = 'RecepciÃ³n registrada y existencias actualizadas.';
            }
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_reception') {
                $service->updateReception($_POST, (int) $_SESSION['user_id']);
                $success = 'RecepciÃ³n actualizada correctamente.';
            }
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_reception') {
                $service->deleteReception((int) ($_POST['reception_id'] ?? 0), (int) $_SESSION['user_id']);
                $success = 'RecepciÃ³n eliminada y existencias revertidas.';
            }
        } catch (\Throwable $exception) {
            $error = $exception->getMessage();
        }
        $selectedReception = isset($_GET['reception_id']) ? $service->reception((int) $_GET['reception_id']) : null;
        return [...$service->options(), 'suppliers' => $service->suppliers(), 'orders' => $service->orders(), 'purchase_invoices' => $service->invoices(), 'reception_lines' => $service->receptionOptions(), 'reception_history' => $service->receptionHistory(), 'selected_reception' => $selectedReception, 'error' => $error, 'success' => $success];
    }
}
