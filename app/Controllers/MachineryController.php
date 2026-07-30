<?php

declare(strict_types=1);

namespace CampoSur\Controllers;

final class MachineryController extends BaseController
{
    public function handle(): array
    {
        $service = new \CampoSur\Services\MachineryManagement(database()->connection(), (int) $_SESSION['company_id']);
        $error = null;
        $success = null;
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create_machinery') {
                $service->createMachinery($_POST);
                (new \CampoSur\Services\AuditLog(database()->connection(), (int) $_SESSION['company_id']))->record((int) $_SESSION['user_id'], 'CREATE', 'machinery');
                $success = 'Maquinaria creada correctamente.';
            }
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create_maintenance') {
                $service->createMaintenance($_POST, (int) $_SESSION['user_id']);
                (new \CampoSur\Services\AuditLog(database()->connection(), (int) $_SESSION['company_id']))->record((int) $_SESSION['user_id'], 'CREATE', 'machinery_maintenance');
                $success = 'MantenciÃ³n registrada correctamente.';
            }
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create_fuel') {
                $service->createFuel($_POST, (int) $_SESSION['user_id']);
                (new \CampoSur\Services\AuditLog(database()->connection(), (int) $_SESSION['company_id']))->record((int) $_SESSION['user_id'], 'CREATE', 'fuel_movement');
                $success = 'Combustible registrado correctamente.';
            }
        } catch (\Throwable $exception) {
            $error = $exception->getMessage();
        }
        return ['machinery' => $service->machinery(), 'maintenance' => $service->maintenance(), 'fuel' => $service->fuel(), 'farms' => $service->farms(), 'error' => $error, 'success' => $success];
    }
}
