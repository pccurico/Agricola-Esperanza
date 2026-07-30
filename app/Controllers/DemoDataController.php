<?php

declare(strict_types=1);

namespace CampoSur\Controllers;

use CampoSur\Services\DemoDataManager;

final class DemoDataController extends BaseController
{
    public function handle(): array
    {
        $manager = new DemoDataManager(database()->connection(), dirname(__DIR__, 2), (int) $_SESSION['company_id']);
        $error = null;
        $success = null;
        try {
            $action = (string) ($_POST['action'] ?? '');
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if ($action === 'install') {
                    $result = $manager->install((int) $_SESSION['user_id']);
                    $success = 'Datos demo instalados. Se cargaron ' . $result['records_count'] . ' registros.';
                } elseif ($action === 'remove') {
                    $manager->remove();
                    $success = 'Los datos demo fueron eliminados sin modificar la empresa ni sus usuarios.';
                } elseif ($action === 'reinstall') {
                    $result = $manager->reinstall((int) $_SESSION['user_id']);
                    $success = 'Datos demo reinstalados. Se cargaron ' . $result['records_count'] . ' registros.';
                }
            }
        } catch (\Throwable $exception) {
            $error = $exception->getMessage();
        }
        return [...$manager->status(), 'error' => $error, 'success' => $success];
    }
}
