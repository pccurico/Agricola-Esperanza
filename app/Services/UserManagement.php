<?php

declare(strict_types=1);

namespace CampoSur\Services;

use PDO;
use RuntimeException;

final class UserManagement extends BaseService
{
    public function __construct(private readonly PDO $connection, private readonly int $companyId, private readonly int $actorRoleId, private readonly int $actorUserId)
    {
    }

    private function actorCan(string $permission): bool
    {
        $query = $this->connection->prepare('SELECT 1 FROM role_permissions rp INNER JOIN permissions p ON p.id = rp.permission_id WHERE rp.role_id = ? AND p.code = ? LIMIT 1');
        $query->execute([$this->actorRoleId, $permission]);
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

    public function permissions(): array
    {
        return $this->connection->query('SELECT id, code, name, module FROM permissions ORDER BY module, name')->fetchAll();
    }

    public function createUser(array $input): void
    {
        $emailInput = (string) ($input['email'] ?? '');
        $password = (string) ($input['password'] ?? '');
        $fullName = trim((string) ($input['full_name'] ?? ''));
        if (!filter_var($emailInput, FILTER_VALIDATE_EMAIL) || strlen($password) < 10 || $fullName === '') {
            throw new RuntimeException('Completa el nombre, un correo vÃ¡lido y una contraseÃ±a de al menos 10 caracteres.');
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
        $emailCheck = $this->connection->prepare('SELECT 1 FROM users WHERE email = ? LIMIT 1');
        $emailCheck->execute([$email]);
        if ($emailCheck->fetchColumn()) {
            throw new RuntimeException('Ya existe un usuario con este correo electrónico.');
        }
        $statement = $this->connection->prepare('INSERT INTO users (company_id, role_id, full_name, email, password_hash, phone) VALUES (?, ?, ?, ?, ?, ?)');
        $statement->execute([$this->companyId, (int) ($input['role_id'] ?? 0), $fullName, $email, password_hash($password, PASSWORD_DEFAULT), trim((string) ($input['phone'] ?? '')) ?: null]);
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
            if (!empty($input['permissions'])) {
                $permissionIds = array_values(array_unique(array_map('intval', $input['permissions'])));
                $placeholders = implode(',', array_fill(0, count($permissionIds), '?'));
                $permissionCheck = $this->connection->prepare('SELECT COUNT(*) FROM permissions WHERE id IN (' . $placeholders . ')');
                $permissionCheck->execute($permissionIds);
                if ((int) $permissionCheck->fetchColumn() !== count($permissionIds)) {
                    throw new RuntimeException('Uno de los permisos seleccionados no es vÃ¡lido.');
                }
                $permission = $this->connection->prepare('INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)');
                foreach ($permissionIds as $permissionId) {
                    $permission->execute([$roleId, $permissionId]);
                }
            }
        });
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
