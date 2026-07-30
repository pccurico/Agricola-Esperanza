<?php

declare(strict_types=1);

namespace CampoSur\Controllers;

use CampoSur\Services\MasterData;

final class MastersController
{
    public function handle(): array
    {
        $manager = new \CampoSur\Services\MasterData(database()->connection(), (int) $_SESSION['company_id']);
        $error = null;
        $success = null;
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create_master') {
                $manager->create((string) ($_POST['type'] ?? ''), $_POST);
                (new \CampoSur\Services\AuditLog(database()->connection(), (int) $_SESSION['company_id']))->record((int) $_SESSION['user_id'], 'CREATE', 'master', null, ['type' => $_POST['type'] ?? null]);
                $success = 'Maestro creado correctamente.';
            }
        } catch (\Throwable $exception) {
            $error = $exception->getMessage();
        }
        return [...$manager->all(), 'error' => $error, 'success' => $success];
    }
}
