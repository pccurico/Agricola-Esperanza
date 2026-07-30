<?php

declare(strict_types=1);

namespace CampoSur\Controllers;

use CampoSur\Services\Auth;

final class AuthController
{
    public function handle(): array
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (isset($_GET['logout'])) {
            (new Auth(database()->connection()))->logout();
            header('Location: ./');
            exit;
        }

        $csrf = csrf_token();
        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'login') {
            if (!hash_equals($csrf, (string) ($_POST['csrf'] ?? ''))) {
                return ['error' => 'La sesión expiró. Recarga la página.', 'company' => $this->company()];
            }
            if ((new Auth(database()->connection()))->login((string) ($_POST['email'] ?? ''), (string) ($_POST['password'] ?? ''))) {
                header('Location: ./');
                exit;
            }
            $error = 'El correo o la contraseña no son correctos.';
        }

        $company = $this->company();

        return ['error' => $error, 'company' => $company];
    }

    private function company(): array
    {
        $query = database()->connection()->query('SELECT trade_name, logo_path FROM companies WHERE active = 1 ORDER BY id LIMIT 1');
        return $query->fetch() ?: [];
    }
}
