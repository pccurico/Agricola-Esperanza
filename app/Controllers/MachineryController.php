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

        if (isset($_GET['export']) && $_GET['export'] === 'csv') {
            $this->sendCsv($service->machinery());
        }

        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create_machinery') {
                $service->createMachinery($_POST);
                (new \CampoSur\Services\AuditLog(database()->connection(), (int) $_SESSION['company_id']))->record((int) $_SESSION['user_id'], 'CREATE', 'machinery');
                $success = 'Maquinaria creada correctamente.';
            }
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create_maintenance') {
                $service->createMaintenance($_POST, (int) $_SESSION['user_id']);
                (new \CampoSur\Services\AuditLog(database()->connection(), (int) $_SESSION['company_id']))->record((int) $_SESSION['user_id'], 'CREATE', 'machinery_maintenance');
                $success = 'Mantención registrada correctamente.';
            }
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create_fuel') {
                $service->createFuel($_POST, (int) $_SESSION['user_id']);
                (new \CampoSur\Services\AuditLog(database()->connection(), (int) $_SESSION['company_id']))->record((int) $_SESSION['user_id'], 'CREATE', 'fuel_movement');
                $success = 'Combustible registrado correctamente.';
            }
        } catch (\Throwable $exception) {
            $error = $exception->getMessage();
        }

        $permissions = $this->permissionsSnapshot((int) ($_SESSION['role_id'] ?? 0));

        return ['machinery' => $service->machinery(), 'maintenance' => $service->maintenance(), 'fuel' => $service->fuel(), 'farms' => $service->farms(), 'dashboard' => $service->dashboard(), 'permissions' => $permissions, 'error' => $error, 'success' => $success];
    }

    private function permissionsSnapshot(int $roleId): array
    {
        $auth = new \CampoSur\Services\Auth(database()->connection());
        $roleName = strtolower((string) ($_SESSION['role_name'] ?? ''));
        $department = strtolower((string) ($_SESSION['role_department'] ?? ''));
        $canManage = $auth->can($roleId, 'machinery.manage') || $auth->can($roleId, 'machinery.create') || $department === 'administracion' || str_contains($roleName, 'administrador');
        $canViewReports = $auth->can($roleId, 'reports.view') || $department === 'gerencia' || $canManage;
        $canRegisterUse = $auth->can($roleId, 'machinery.use') || $department === 'produccion' || $canManage;

        return [
            'can_manage' => $canManage,
            'can_view_reports' => $canViewReports,
            'can_register_use' => $canRegisterUse,
            'is_read_only' => !$canManage && !$canRegisterUse && !$canViewReports,
        ];
    }

    private function sendCsv(array $rows): void
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="maquinaria.csv"');
        $handle = fopen('php://output', 'w');
        fputcsv($handle, ['codigo', 'nombre', 'tipo', 'marca', 'estado', 'fundo', 'horometro']);
        foreach ($rows as $row) {
            fputcsv($handle, [
                (string) ($row['code'] ?? ''),
                (string) ($row['name'] ?? ''),
                (string) ($row['machinery_type'] ?? ''),
                (string) ($row['brand'] ?? ''),
                (string) ($row['status'] ?? ''),
                (string) ($row['farm_name'] ?? ''),
                (string) ($row['meter'] ?? '0'),
            ]);
        }
        fclose($handle);
        exit;
    }
}
