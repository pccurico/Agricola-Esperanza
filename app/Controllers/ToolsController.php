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

        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $action = (string) ($_POST['action'] ?? '');
                $supportedAction = match ($action) {
                    'backup', 'sync_schema', 'repair', 'restore', 'update' => true,
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
                        default => null,
                    };

                    $success = match ($action) {
                        'backup' => 'Respaldo generado correctamente.',
                        'sync_schema' => 'Sincronización de esquema aplicada correctamente.',
                        'repair' => 'Reparación ejecutada correctamente.',
                        'restore' => 'Restauración ejecutada correctamente.',
                        'update' => 'Actualización ejecutada correctamente.',
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

        $status = $service->status();

        return [
            'status' => $status,
            'backups' => $service->backups(),
            'logs' => $service->recentLogs(),
            'error' => $error,
            'success' => $success,
        ];
    }
}
