<?php

declare(strict_types=1);

namespace CampoSur\Controllers;

final class WarehouseController
{
    public function handle(): array
    {
        $service = new \CampoSur\Services\WarehouseManagement(database()->connection(), (int) $_SESSION['company_id']);
        $error = null;
        $success = null;
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $action = (string) ($_POST['action'] ?? '');
                if ($action === 'create_warehouse') {
                    $service->createWarehouse($_POST, (int) $_SESSION['user_id']);
                    $success = 'Bodega creada correctamente.';
                } elseif ($action === 'create_location') {
                    $service->createLocation($_POST, (int) $_SESSION['user_id']);
                    $success = 'Ubicación creada correctamente.';
                } elseif ($action === 'create_lot') {
                    $service->createLot($_POST, (int) $_SESSION['user_id']);
                    $success = 'Lote creado correctamente.';
                } elseif ($action === 'create_transfer') {
                    $service->createTransfer($_POST, (int) $_SESSION['user_id']);
                    $success = 'Transferencia creada correctamente.';
                } elseif ($action === 'approve_transfer') {
                    $service->approveTransfer((int) $_POST['transfer_id'], (int) $_SESSION['user_id']);
                    $success = 'Transferencia aprobada y movimientos generados.';
                }
            }
        } catch (\Throwable $exception) {
            $error = $exception->getMessage();
        }
        return [...$service->options(), 'locations' => $service->locations(), 'lots' => $service->lots(), 'transfers' => $service->transfers(), 'error' => $error, 'success' => $success];
    }
}
