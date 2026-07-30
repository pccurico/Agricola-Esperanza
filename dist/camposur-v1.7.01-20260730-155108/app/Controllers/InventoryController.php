<?php

declare(strict_types=1);

namespace CampoSur\Controllers;

use CampoSur\Services\InventoryManagement;

final class InventoryController
{
    public function handle(): array
    {
        $service = new \CampoSur\Services\InventoryManagement(database()->connection(), (int) $_SESSION['company_id']);
        $error = null;
        $success = null;
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create_item') {
                $service->createItem($_POST);
                (new \CampoSur\Services\AuditLog(database()->connection(), (int) $_SESSION['company_id']))->record((int) $_SESSION['user_id'], 'CREATE', 'inventory_item');
                $success = 'Artículo creado correctamente.';
            }
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create_movement') {
                $service->createMovement($_POST, (int) $_SESSION['user_id']);
                (new \CampoSur\Services\AuditLog(database()->connection(), (int) $_SESSION['company_id']))->record((int) $_SESSION['user_id'], 'CREATE', 'inventory_movement');
                $success = 'Movimiento registrado correctamente.';
            }
        } catch (\Throwable $exception) {
            $error = $exception->getMessage();
        }
        return ['items' => $service->items(), 'movements' => $service->movements(), 'item_options' => $service->itemOptions(), 'error' => $error, 'success' => $success];
    }
}
