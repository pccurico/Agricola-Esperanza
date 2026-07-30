<?php

declare(strict_types=1);

namespace CampoSur\Controllers;

final class ProfileController extends BaseController
{
    public function handle(): array
    {
        $service = new \CampoSur\Services\ProfileService(database()->connection(), (int) $_SESSION['user_id'], (int) $_SESSION['company_id']);
        $error = null;
        $success = null;
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $service->update($_POST);
                (new \CampoSur\Services\AuditLog(database()->connection(), (int) $_SESSION['company_id']))->record((int) $_SESSION['user_id'], 'UPDATE', 'user_profile');
                $_SESSION['user_name'] = trim($_POST['full_name']);
                $success = 'Perfil actualizado correctamente.';
            }
        } catch (\Throwable $exception) {
            $error = $exception->getMessage();
        }
        return ['user' => $service->user(), 'error' => $error, 'success' => $success];
    }
}
