<?php

declare(strict_types=1);

namespace CampoSur\Controllers;

final class UsersController extends BaseController
{
    public function handle(): array
    {
        $manager = new \CampoSur\Services\UserManagement(database()->connection(), (int) $_SESSION['company_id'], (int) $_SESSION['role_id'], (int) $_SESSION['user_id']);
        $error = null;
        $success = null;
        $toggleSuccess = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            $result = null;

            if ($action === 'create_user') {
                $result = $this->handleAction(function () use ($manager): void {
                    $manager->createUser($_POST);
                }, 'Usuario creado correctamente.', 'user', ['audit' => true, 'auditAction' => 'CREATE', 'userId' => (int) ($_SESSION['user_id'] ?? 0)]);
            }
            if ($action === 'update_user') {
                $result = $this->handleAction(function () use ($manager): void {
                    $manager->updateUser($_POST);
                }, 'Usuario actualizado correctamente.', 'user', ['audit' => true, 'auditAction' => 'UPDATE', 'userId' => (int) ($_SESSION['user_id'] ?? 0)]);
            }
            if ($action === 'delete_user') {
                $result = $this->handleAction(function () use ($manager): void {
                    $manager->deleteUser((int) ($_POST['user_id'] ?? 0));
                }, 'Usuario eliminado correctamente.', 'user', ['audit' => true, 'auditAction' => 'DELETE', 'userId' => (int) ($_SESSION['user_id'] ?? 0)]);
            }
            if ($action === 'toggle_user') {
                $result = $this->handleAction(function () use ($manager, &$toggleSuccess): void {
                    $active = $manager->toggleUser((int) ($_POST['user_id'] ?? 0));
                    $toggleSuccess = $active ? 'Usuario activado correctamente.' : 'Usuario desactivado correctamente.';
                }, 'Usuario actualizado correctamente.', 'user', ['audit' => true, 'auditAction' => 'ACTIVATE', 'userId' => (int) ($_SESSION['user_id'] ?? 0)]);
            }

            $error = $result['error'] ?? null;
            $success = $result['success'] ?? null;
            if ($toggleSuccess !== null) {
                $success = $toggleSuccess;
            }
        }

        $selectedUser = null;
        if (isset($_GET['edit_user_id'])) {
            $selectedUser = $manager->findUser((int) $_GET['edit_user_id']);
        }

        $showUserForm = isset($_GET['new_user']) || isset($_GET['edit_user_id']);

        return [
            'users' => $manager->users(),
            'roles' => $manager->roles(),
            'permissions' => $manager->permissions(),
            'role_permissions' => $manager->rolePermissionsMap(),
            'can_manage_users' => $manager->canManageUsers(),
            'can_manage_roles' => $manager->canManageRoles(),
            'selected_user' => $selectedUser,
            'show_user_form' => $showUserForm,
            'error' => $error,
            'success' => $success,
        ];
    }
}
