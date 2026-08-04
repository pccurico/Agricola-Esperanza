<?php

declare(strict_types=1);

namespace AgroPCC\Services;

use PDO;
use RuntimeException;

final class UserManagement extends BaseService
{
    public function __construct(protected readonly PDO $connection, protected readonly int $companyId, private readonly int $actorRoleId, private readonly int $actorUserId)
    {
    }

    private function actorCan(string $permission): bool
    {
        $query = $this->connection->prepare('SELECT 1 FROM role_permissions rp INNER JOIN permissions p ON p.id = rp.permission_id WHERE rp.role_id = ? AND p.code = ?'
            . ($this->actorUserId > 0 ? ' UNION ALL SELECT 1 FROM user_permissions up INNER JOIN permissions p2 ON p2.id = up.permission_id WHERE up.user_id = ? AND p2.code = ?' : '')
            . ' LIMIT 1');
        $params = [$this->actorRoleId, $permission];
        if ($this->actorUserId > 0) {
            $params[] = $this->actorUserId;
            $params[] = $permission;
        }
        $query->execute($params);
        return (bool) $query->fetchColumn();
    }

    public function canManageUsers(): bool
    {
        return $this->actorCan('users.manage');
    }

    public function canManageRoles(): bool
    {
        return $this->actorCan('roles.manage');
    }

    public function users(): array
    {
        $query = $this->connection->prepare(
            'SELECT u.id, u.full_name, u.email, u.phone, u.active, u.last_login_at, r.name AS role_name FROM users u INNER JOIN roles r ON r.id = u.role_id WHERE u.company_id = ? ORDER BY u.full_name'
        );
        $query->execute([$this->companyId]);
        return $query->fetchAll();
    }

    public function roles(): array
    {
        $query = $this->connection->prepare(
            'SELECT r.id, r.name, r.description, r.is_system, COUNT(DISTINCT u.id) AS users_count, COUNT(DISTINCT rp.permission_id) AS permissions_count FROM roles r LEFT JOIN users u ON u.role_id = r.id LEFT JOIN role_permissions rp ON rp.role_id = r.id WHERE r.company_id = ? GROUP BY r.id ORDER BY r.is_system DESC, r.name'
        );
        $query->execute([$this->companyId]);
        return $query->fetchAll();
    }

    public function rolePermissionsMap(): array
    {
        $query = $this->connection->prepare(
            'SELECT rp.role_id, rp.permission_id FROM role_permissions rp INNER JOIN roles r ON r.id = rp.role_id WHERE r.company_id = ?'
        );
        $query->execute([$this->companyId]);

        $map = [];
        foreach ($query->fetchAll() as $row) {
            $roleId = (int) $row['role_id'];
            $map[$roleId][] = (int) $row['permission_id'];
        }

        return $map;
    }

    public function permissions(): array
    {
        return $this->connection->query('SELECT id, code, name, module FROM permissions ORDER BY module, name')->fetchAll();
    }

    public function findUser(int $userId): ?array
    {
        $query = $this->connection->prepare('SELECT id, full_name, email, phone, role_id, active FROM users WHERE id = ? AND company_id = ? LIMIT 1');
        $query->execute([$userId, $this->companyId]);
        $user = $query->fetch();
        if ($user === false) {
            return null;
        }

        $permissions = $this->connection->prepare('SELECT permission_id FROM user_permissions WHERE user_id = ?');
        $permissions->execute([$userId]);
        $user['permissions'] = array_map('intval', array_column($permissions->fetchAll(), 'permission_id'));

        return $user;
    }

    public function findRole(int $roleId): ?array
    {
        $query = $this->connection->prepare('SELECT id, name, description, is_system FROM roles WHERE id = ? AND company_id = ? LIMIT 1');
        $query->execute([$roleId, $this->companyId]);
        $role = $query->fetch();
        if ($role === false) {
            return null;
        }

        $permissions = $this->connection->prepare('SELECT permission_id FROM role_permissions WHERE role_id = ?');
        $permissions->execute([$roleId]);
        $permissionIds = array_column($permissions->fetchAll(), 'permission_id');

        $role['permissions'] = array_map('intval', $permissionIds);
        return $role;
    }

    public function createUser(array $input): void
    {
        $emailInput = (string) ($input['email'] ?? '');
        $password = (string) ($input['password'] ?? '');
        $fullName = trim((string) ($input['full_name'] ?? ''));
        if (!filter_var($emailInput, FILTER_VALIDATE_EMAIL) || strlen($password) < 10 || $fullName === '') {
            throw new RuntimeException('Completa el nombre, un correo válido y una contraseña de al menos 10 caracteres.');
        }
        $email = strtolower(trim($emailInput));
        $roleCheck = $this->connection->prepare('SELECT id, is_system FROM roles WHERE id = ? AND company_id = ? LIMIT 1');
        $roleCheck->execute([(int) $input['role_id'], $this->companyId]);
        $role = $roleCheck->fetch();
        if (!$role) {
            throw new RuntimeException('El rol seleccionado no pertenece a esta agrícola.');
        }
        if ((int) $role['is_system'] === 1 && !$this->actorCan('roles.manage')) {
            throw new RuntimeException('No puedes asignar un rol del sistema.');
        }
        $emailCheck = $this->connection->prepare('SELECT 1 FROM users WHERE email = ? AND company_id = ? LIMIT 1');
        $emailCheck->execute([$email, $this->companyId]);
        if ($emailCheck->fetchColumn()) {
            throw new RuntimeException('Ya existe un usuario con este correo electrónico.');
        }
        $statement = $this->connection->prepare('INSERT INTO users (company_id, role_id, full_name, email, password_hash, phone) VALUES (?, ?, ?, ?, ?, ?)');
        $statement->execute([$this->companyId, (int) ($input['role_id'] ?? 0), $fullName, $email, password_hash($password, PASSWORD_DEFAULT), trim((string) ($input['phone'] ?? '')) ?: null]);
        $userId = (int) $this->connection->lastInsertId();
        $this->syncUserPermissions($userId, (array) ($input['permissions'] ?? []));
    }

    public function updateUser(array $input): void
    {
        $userId = (int) ($input['user_id'] ?? 0);
        if ($userId <= 0) {
            throw new RuntimeException('Usuario inválido.');
        }
        $user = $this->findUser($userId);
        if ($user === null) {
            throw new RuntimeException('El usuario no existe.');
        }
        $fullName = trim((string) ($input['full_name'] ?? ''));
        $emailInput = (string) ($input['email'] ?? '');
        if (!filter_var($emailInput, FILTER_VALIDATE_EMAIL) || $fullName === '') {
            throw new RuntimeException('Completa el nombre y un correo válido.');
        }
        $email = strtolower(trim($emailInput));
        $roleCheck = $this->connection->prepare('SELECT id, is_system FROM roles WHERE id = ? AND company_id = ? LIMIT 1');
        $roleCheck->execute([(int) $input['role_id'], $this->companyId]);
        $role = $roleCheck->fetch();
        if (!$role) {
            throw new RuntimeException('El rol seleccionado no pertenece a esta agrícola.');
        }
        if ((int) $role['is_system'] === 1 && !$this->actorCan('roles.manage')) {
            throw new RuntimeException('No puedes asignar un rol del sistema.');
        }
        $emailCheck = $this->connection->prepare('SELECT 1 FROM users WHERE email = ? AND company_id = ? AND id <> ? LIMIT 1');
        $emailCheck->execute([$email, $this->companyId, $userId]);
        if ($emailCheck->fetchColumn()) {
            throw new RuntimeException('Ya existe un usuario con este correo electrónico.');
        }
        $params = [$fullName, $email, (int) ($input['role_id'] ?? 0), trim((string) ($input['phone'] ?? '')) ?: null, $userId, $this->companyId];
        $this->execute('UPDATE users SET full_name = ?, email = ?, role_id = ?, phone = ? WHERE id = ? AND company_id = ?', $params);
        $this->syncUserPermissions($userId, (array) ($input['permissions'] ?? []));
    }

    private function syncUserPermissions(int $userId, array $permissionIds): void
    {
        $this->connection->prepare('DELETE FROM user_permissions WHERE user_id = ?')->execute([$userId]);
        $permissionIds = $this->normalizePermissionIds($permissionIds, 'Uno de los permisos asignados directamente no es válido.');
        if ($permissionIds === []) {
            return;
        }
        $insert = $this->connection->prepare('INSERT INTO user_permissions (user_id, permission_id) VALUES (?, ?)');
        foreach ($permissionIds as $permissionId) {
            $insert->execute([$userId, $permissionId]);
        }
    }

    private function normalizePermissionIds(array $permissionIds, string $errorMessage): array
    {
        $permissionIds = array_values(array_unique(array_map('intval', $permissionIds)));
        if ($permissionIds === []) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($permissionIds), '?'));
        $permissionCheck = $this->connection->prepare('SELECT COUNT(*) FROM permissions WHERE id IN (' . $placeholders . ')');
        $permissionCheck->execute($permissionIds);
        if ((int) $permissionCheck->fetchColumn() !== count($permissionIds)) {
            throw new RuntimeException($errorMessage);
        }

        return $permissionIds;
    }

    public function deleteUser(int $userId): void
    {
        if ($userId === $this->actorUserId) {
            throw new RuntimeException('No puedes eliminar tu propio usuario.');
        }
        $user = $this->findUser($userId);
        if ($user === null) {
            throw new RuntimeException('El usuario no existe.');
        }
        $this->execute('DELETE FROM users WHERE id = ? AND company_id = ?', [$userId, $this->companyId]);
    }

    public function createRole(array $input): void
    {
        if (trim($input['name']) === '') {
            throw new RuntimeException('El nombre del rol es obligatorio.');
        }
        $this->transaction($this->connection, function () use ($input): void {
            $statement = $this->connection->prepare('INSERT INTO roles (company_id, name, description) VALUES (?, ?, ?)');
            $statement->execute([$this->companyId, trim((string) ($input['name'] ?? '')), trim((string) ($input['description'] ?? '')) ?: null]);
            $roleId = (int) $this->connection->lastInsertId();
            $permissionIds = $this->normalizePermissionIds((array) ($input['permissions'] ?? []), 'Uno de los permisos seleccionados no es válido.');
            if ($permissionIds !== []) {
                $permission = $this->connection->prepare('INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)');
                foreach ($permissionIds as $permissionId) {
                    $permission->execute([$roleId, $permissionId]);
                }
            }
        });
    }

    public function updateRole(array $input): void
    {
        $roleId = (int) ($input['role_id'] ?? 0);
        if ($roleId <= 0) {
            throw new RuntimeException('Rol inválido.');
        }
        $role = $this->findRole($roleId);
        if ($role === null) {
            throw new RuntimeException('El rol no existe.');
        }
        if ((int) $role['is_system'] === 1 && !$this->actorCan('roles.manage')) {
            throw new RuntimeException('No puedes editar un rol del sistema.');
        }
        if (trim((string) ($input['name'] ?? '')) === '') {
            throw new RuntimeException('El nombre del rol es obligatorio.');
        }
        $this->transaction($this->connection, function () use ($input, $roleId): void {
            $statement = $this->connection->prepare('UPDATE roles SET name = ?, description = ? WHERE id = ? AND company_id = ?');
            $statement->execute([trim((string) ($input['name'] ?? '')), trim((string) ($input['description'] ?? '')) ?: null, $roleId, $this->companyId]);
            $this->connection->prepare('DELETE FROM role_permissions WHERE role_id = ?')->execute([$roleId]);
            $permissionIds = $this->normalizePermissionIds((array) ($input['permissions'] ?? []), 'Uno de los permisos seleccionados no es válido.');
            if ($permissionIds !== []) {
                $permission = $this->connection->prepare('INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)');
                foreach ($permissionIds as $permissionId) {
                    $permission->execute([$roleId, $permissionId]);
                }
            }
        });
    }

    public function deleteRole(int $roleId): void
    {
        if ($roleId <= 0) {
            throw new RuntimeException('Rol inválido.');
        }
        $role = $this->findRole($roleId);
        if ($role === null) {
            throw new RuntimeException('El rol no existe.');
        }
        if ((int) $role['is_system'] === 1 && !$this->actorCan('roles.manage')) {
            throw new RuntimeException('No puedes eliminar un rol del sistema.');
        }
        $userCheck = $this->connection->prepare('SELECT COUNT(*) FROM users WHERE role_id = ? AND company_id = ?');
        $userCheck->execute([$roleId, $this->companyId]);
        if ((int) $userCheck->fetchColumn() > 0) {
            throw new RuntimeException('No puedes eliminar un rol asignado a usuarios. Primero reasigna o elimina los usuarios.');
        }
        $this->execute('DELETE FROM roles WHERE id = ? AND company_id = ?', [$roleId, $this->companyId]);
    }

    public function toggleUser(int $userId): bool
    {
        if ($userId === $this->actorUserId) {
            throw new RuntimeException('No puedes desactivar tu propio acceso.');
        }
        $userQuery = $this->connection->prepare('SELECT u.active, r.is_system FROM users u INNER JOIN roles r ON r.id = u.role_id WHERE u.id = ? AND u.company_id = ? LIMIT 1');
        $userQuery->execute([$userId, $this->companyId]);
        $user = $userQuery->fetch();
        if (!$user) {
            throw new RuntimeException('El usuario no pertenece a esta agrícola.');
        }
        if ((int) $user['is_system'] === 1 && !$this->actorCan('roles.manage')) {
            throw new RuntimeException('No puedes cambiar el estado de un administrador del sistema.');
        }
        $active = !(bool) $user['active'];
        $statement = $this->connection->prepare('UPDATE users SET active = ? WHERE id = ? AND company_id = ?');
        $statement->execute([(int) $active, $userId, $this->companyId]);
        return $active;
    }
}

