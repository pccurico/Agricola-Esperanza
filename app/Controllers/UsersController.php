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
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_user') {
                $manager->updateUser($_POST);
                (new \CampoSur\Services\AuditLog(database()->connection(), (int) $_SESSION['company_id']))->record((int) $_SESSION['user_id'], 'UPDATE', 'user');
                $success = 'Usuario actualizado correctamente.';
            }
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_user') {
                $manager->deleteUser((int) ($_POST['user_id'] ?? 0));
                (new \CampoSur\Services\AuditLog(database()->connection(), (int) $_SESSION['company_id']))->record((int) $_SESSION['user_id'], 'DELETE', 'user');
                $success = 'Usuario eliminado correctamente.';
            }
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create_role') {
                $manager->createRole(['name' => (string) ($_POST['name'] ?? ''), 'description' => (string) ($_POST['description'] ?? ''), 'permissions' => (array) ($_POST['permissions'] ?? [])]);
                (new \CampoSur\Services\AuditLog(database()->connection(), (int) $_SESSION['company_id']))->record((int) $_SESSION['user_id'], 'CREATE', 'role');
                $success = 'Rol creado correctamente.';
            }
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_role') {
                $manager->updateRole(['role_id' => (int) ($_POST['role_id'] ?? 0), 'name' => (string) ($_POST['name'] ?? ''), 'description' => (string) ($_POST['description'] ?? ''), 'permissions' => (array) ($_POST['permissions'] ?? [])]);
                (new \CampoSur\Services\AuditLog(database()->connection(), (int) $_SESSION['company_id']))->record((int) $_SESSION['user_id'], 'UPDATE', 'role');
                $success = 'Rol actualizado correctamente.';
            }
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_role') {
                $manager->deleteRole((int) ($_POST['role_id'] ?? 0));
                (new \CampoSur\Services\AuditLog(database()->connection(), (int) $_SESSION['company_id']))->record((int) $_SESSION['user_id'], 'DELETE', 'role');
                $success = 'Rol eliminado correctamente.';
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

        $selectedUser = null;
        $selectedRole = null;
        if (isset($_GET['edit_user_id'])) {
            $selectedUser = $manager->findUser((int) $_GET['edit_user_id']);
        }
        if (isset($_GET['edit_role_id'])) {
            $selectedRole = $manager->findRole((int) $_GET['edit_role_id']);
        }

        return [
            'users' => $manager->users(),
            'roles' => $manager->roles(),
            'permissions' => $manager->permissions(),
            'role_permissions' => $manager->rolePermissionsMap(),
            'can_manage_users' => $manager->canManageUsers(),
            'can_manage_roles' => $manager->canManageRoles(),
            'selected_user' => $selectedUser,
            'selected_role' => $selectedRole,
            'error' => $error,
            'success' => $success,
        ];
    }
}
