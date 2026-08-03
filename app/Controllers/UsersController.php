<?php

declare(strict_types=1);

namespace CampoSur\Controllers;

use CampoSur\Services\UserManagement;

final class UsersController extends BaseController
{
    public function handle(): array
    {
        $manager = new \CampoSur\Services\UserManagement(database()->connection(), (int) $_SESSION['company_id'], (int) $_SESSION['role_id'], (int) $_SESSION['user_id']);
        $error = null;
        $success = null;
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create_user') {
                $manager->createUser($_POST);
                (new \CampoSur\Services\AuditLog(database()->connection(), (int) $_SESSION['company_id']))->record((int) $_SESSION['user_id'], 'CREATE', 'user');
                $success = 'Usuario creado correctamente.';
            }
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create_role') {
                $manager->createRole(['name' => (string) ($_POST['name'] ?? ''), 'description' => (string) ($_POST['description'] ?? ''), 'permissions' => (array) ($_POST['permissions'] ?? [])]);
                (new \CampoSur\Services\AuditLog(database()->connection(), (int) $_SESSION['company_id']))->record((int) $_SESSION['user_id'], 'CREATE', 'role');
                $success = 'Rol creado correctamente.';
            }
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'toggle_user') {
                $active = $manager->toggleUser((int) ($_POST['user_id'] ?? 0));
                (new \CampoSur\Services\AuditLog(database()->connection(), (int) $_SESSION['company_id']))->record((int) $_SESSION['user_id'], $active ? 'ACTIVATE' : 'DEACTIVATE', 'user');
                $success = $active ? 'Usuario activado correctamente.' : 'Usuario desactivado correctamente.';
            }
        } catch (\Throwable $exception) {
            $error = $exception instanceof \PDOException
                ? 'No fue posible completar la operación. Verifica los datos e inténtalo nuevamente.'
                : $exception->getMessage();
        }
        return ['users' => $manager->users(), 'roles' => $manager->roles(), 'permissions' => $manager->permissions(), 'can_manage_users' => $manager->canManageUsers(), 'can_manage_roles' => $manager->canManageRoles(), 'error' => $error, 'success' => $success];
    }
}
