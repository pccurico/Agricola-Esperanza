<?php

declare(strict_types=1);

namespace AgroPCC\Controllers;

use AgroPCC\Services\BackupService;

final class BackupController extends BaseController
{
    public function handle(): array
    {
        $service = new BackupService(database()->connection(), dirname(__DIR__, 2), (int) ($_SESSION['company_id'] ?? 0), (int) ($_SESSION['user_id'] ?? 0));
        $error = null;
        $success = null;

        if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['download_backup_id'])) {
            $service->downloadBackup((int) $_GET['download_backup_id']);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['progress'])) {
            $this->json($service->progressStatus());
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $action = (string) ($_POST['action'] ?? '');
                if ($action === 'create_backup') {
                    $backup = $service->createBackup();
                    $success = 'Respaldo generado correctamente: ' . basename($backup['path']);
                } elseif ($action === 'restore_backup') {
                    $service->restoreBackup((int) ($_POST['backup_id'] ?? 0));
                    $success = 'Restauración ejecutada correctamente.';
                } elseif ($action === 'delete_backup') {
                    $service->deleteBackup((int) ($_POST['backup_id'] ?? 0));
                    $success = 'Copia eliminada correctamente.';
                }
            } catch (\Throwable $exception) {
                $error = $exception->getMessage();
            }
        }

        return [
            'backups' => $service->listBackups(),
            'progress' => $service->progressStatus(),
            'error' => $error,
            'success' => $success,
        ];
    }
}
