<?php

declare(strict_types=1);

namespace AgroPCC\Controllers;

use AgroPCC\Services\Auth;

final class AuthController extends BaseController
{
    public function handle(): array
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (isset($_GET['logout'])) {
            (new Auth(database()->connection()))->logout();
            $this->redirect('./');
        }

        $csrf = csrf_token();
        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'login') {
            if (!hash_equals($csrf, (string) ($_POST['csrf'] ?? ''))) {
                return ['error' => 'La sesión expiró. Recarga la página.', 'company' => (new Auth(database()->connection()))->company()];
            }
            if ((new Auth(database()->connection()))->login((string) ($_POST['email'] ?? ''), (string) ($_POST['password'] ?? ''))) {
                $this->redirect('./');
            }
            $error = 'El correo o la contraseña no son correctos.';
        }

        $company = (new Auth(database()->connection()))->company();

        return ['error' => $error, 'company' => $company];
    }

}
