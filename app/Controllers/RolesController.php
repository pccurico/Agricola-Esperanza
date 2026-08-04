<?php

declare(strict_types=1);

namespace AgroPCC\Controllers;

final class RolesController extends BaseController
{
    public function handle(): array
    {
        $manager = new \AgroPCC\Services\UserManagement(database()->connection(), (int) $_SESSION['company_id'], (int) $_SESSION['role_id'], (int) $_SESSION['user_id']);
        $error = null;
        $success = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            $result = null;

            if ($action === 'create_role') {
                $result = $this->handleAction(function () use ($manager): void {
                    $manager->createRole(['name' => (string) ($_POST['name'] ?? ''), 'description' => (string) ($_POST['description'] ?? ''), 'permissions' => (array) ($_POST['permissions'] ?? [])]);
                }, 'Rol creado correctamente.', 'role', ['audit' => true, 'auditAction' => 'CREATE', 'userId' => (int) ($_SESSION['user_id'] ?? 0)]);
            }
            if ($action === 'update_role') {
                $result = $this->handleAction(function () use ($manager): void {
                    $manager->updateRole(['role_id' => (int) ($_POST['role_id'] ?? 0), 'name' => (string) ($_POST['name'] ?? ''), 'description' => (string) ($_POST['description'] ?? ''), 'permissions' => (array) ($_POST['permissions'] ?? [])]);
                }, 'Rol actualizado correctamente.', 'role', ['audit' => true, 'auditAction' => 'UPDATE', 'userId' => (int) ($_SESSION['user_id'] ?? 0)]);
            }
            if ($action === 'delete_role') {
                $result = $this->handleAction(function () use ($manager): void {
                    $manager->deleteRole((int) ($_POST['role_id'] ?? 0));
                }, 'Rol eliminado correctamente.', 'role', ['audit' => true, 'auditAction' => 'DELETE', 'userId' => (int) ($_SESSION['user_id'] ?? 0)]);
            }

            $error = $result['error'] ?? null;
            $success = $result['success'] ?? null;
        }

        $selectedRole = null;
        if (isset($_GET['edit_role_id'])) {
            $selectedRole = $manager->findRole((int) $_GET['edit_role_id']);
        }

        $showRoleForm = isset($_GET['new_role']) || isset($_GET['edit_role_id']);

        return [
            'roles' => $manager->roles(),
            'permissions' => $manager->permissions(),
            'role_permissions' => $manager->rolePermissionsMap(),
            'can_manage_roles' => $manager->canManageRoles(),
            'selected_role' => $selectedRole,
            'show_role_form' => $showRoleForm,
            'error' => $error,
            'success' => $success,
        ];
    }
}
