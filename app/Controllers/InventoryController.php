<?php

declare(strict_types=1);

namespace AgroPCC\Controllers;

use AgroPCC\Services\InventoryManagement;

final class InventoryController extends BaseController
{
    public function handle(): array
    {
        $service = new \AgroPCC\Services\InventoryManagement(database()->connection(), (int) $_SESSION['company_id']);
        $error = null;
        $success = null;
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create_item') {
                $service->createItem($_POST);
                (new \AgroPCC\Services\AuditLog(database()->connection(), (int) $_SESSION['company_id']))->record((int) $_SESSION['user_id'], 'CREATE', 'inventory_item');
                $success = 'Producto creado correctamente.';
            }
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_item') {
                $service->updateItem($_POST);
                (new \AgroPCC\Services\AuditLog(database()->connection(), (int) $_SESSION['company_id']))->record((int) $_SESSION['user_id'], 'UPDATE', 'inventory_item', (int) ($_POST['item_id'] ?? 0));
                $success = 'Producto actualizado correctamente.';
            }
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_item') {
                $service->deleteItem((int) ($_POST['item_id'] ?? 0));
                (new \AgroPCC\Services\AuditLog(database()->connection(), (int) $_SESSION['company_id']))->record((int) $_SESSION['user_id'], 'DEACTIVATE', 'inventory_item', (int) ($_POST['item_id'] ?? 0));
                $success = 'Producto eliminado correctamente.';
            }
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create_movement') {
                $service->createMovement($_POST, (int) $_SESSION['user_id']);
                (new \AgroPCC\Services\AuditLog(database()->connection(), (int) $_SESSION['company_id']))->record((int) $_SESSION['user_id'], 'CREATE', 'inventory_movement');
                $success = 'Movimiento registrado correctamente.';
            }
        } catch (\Throwable $exception) {
            $error = $exception->getMessage();
        }
        return ['inventory_items' => $service->items(), 'movements' => $service->movements(), 'item_options' => $service->itemOptions(), 'categories' => $service->categories(), 'subcategories' => $service->subcategories(), 'units' => $service->units(), 'warehouses' => $service->warehouses(), ...$service->assignmentOptions(), 'error' => $error, 'success' => $success];
    }
}
