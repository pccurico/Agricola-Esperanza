<?php

declare(strict_types=1);

namespace AgroPCC\Controllers;

use AgroPCC\Services\ToolsService;

final class ToolsController extends BaseController
{
    public function handle(): array
    {
        $service = new ToolsService(database()->connection(), dirname(__DIR__, 2), (int) ($_SESSION['company_id'] ?? 0), (int) ($_SESSION['user_id'] ?? 0));
        $error = null;
        $success = null;
        $isAjaxUpdate = $_SERVER['REQUEST_METHOD'] === 'POST' && (string) ($_POST['action'] ?? '') === 'remote_update' && (isset($_POST['ajax']) || str_contains((string) ($_SERVER['HTTP_ACCEPT'] ?? ''), 'application/json'));

        if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['remote_progress'])) {
            $this->json($service->remoteProgressStatus());
            exit;
        }

        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $action = (string) ($_POST['action'] ?? '');
                $supportedAction = match ($action) {
                    'backup', 'sync_schema', 'repair', 'restore', 'update', 'remote_update' => true,
                    default => false,
                };

                if (!$supportedAction) {
                    $error = 'Acción no soportada.';
                } else {
                    match ($action) {
                        'backup' => $service->createBackup(),
                        'sync_schema' => $service->syncSchema(),
                        'repair' => $service->repairApplication(),
                        'restore' => $service->restoreBackup((int) ($_POST['backup_id'] ?? 0)),
                        'update' => $service->runUpdate(),
                        'remote_update' => $service->downloadAndInstallRemoteUpdate(),
                        default => null,
                    };

                    $success = match ($action) {
                        'backup' => 'Respaldo generado correctamente.',
                        'sync_schema' => 'Sincronización de esquema aplicada correctamente.',
                        'repair' => 'Reparación ejecutada correctamente.',
                        'restore' => 'Restauración ejecutada correctamente.',
                        'update' => 'Actualización ejecutada correctamente.',
                        'remote_update' => 'Actualización remota descargada e instalada correctamente.',
                        default => null,
                    };
                }
            }
        } catch (\Throwable $exception) {
            $error = $exception->getMessage();
            try {
                (new \AgroPCC\Services\AuditLog(database()->connection(), (int) ($_SESSION['company_id'] ?? 0)))->record((int) ($_SESSION['user_id'] ?? 0), 'ERROR', 'tools', null, ['error' => $exception->getMessage()]);
            } catch (\Throwable $auditException) {
                $error .= ' | Auditoría no disponible: ' . $auditException->getMessage();
            }
        }

        if ($isAjaxUpdate) {
            $this->json([
                'success' => $success,
                'error' => $error,
                'progress' => $service->remoteProgressStatus(),
            ]);
            exit;
        }

        $status = $service->status();

        return [
            'status' => $status,
            'backups' => $service->backups(),
            'logs' => $service->recentLogs(),
            'remote_progress' => $service->remoteProgressStatus(),
            'error' => $error,
            'success' => $success,
        ];
    }
}
