<?php

declare(strict_types=1);

namespace CampoSur\Services;

use PDO;
use RuntimeException;

final class UserManagement
{
    public function __construct(private readonly PDO $connection, private readonly int $companyId)
    {
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
        if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL) || strlen($input['password']) < 10 || trim($input['full_name']) === '') {
            throw new RuntimeException('Completa el nombre, un correo válido y una contraseña de al menos 10 caracteres.');
        }
        $roleCheck = $this->connection->prepare('SELECT id FROM roles WHERE id = ? AND company_id = ? LIMIT 1');
        $roleCheck->execute([(int) $input['role_id'], $this->companyId]);
        if (!$roleCheck->fetchColumn()) {
            throw new RuntimeException('El rol seleccionado no pertenece a esta agrícola.');
        }
        $statement = $this->connection->prepare('INSERT INTO users (company_id, role_id, full_name, email, password_hash, phone) VALUES (?, ?, ?, ?, ?, ?)');
        $statement->execute([$this->companyId, (int) $input['role_id'], trim($input['full_name']), strtolower(trim($input['email'])), password_hash($input['password'], PASSWORD_DEFAULT), trim($input['phone']) ?: null]);
    }

    public function createRole(array $input): void
    {
        if (trim($input['name']) === '') {
            throw new RuntimeException('El nombre del rol es obligatorio.');
        }
        $this->connection->beginTransaction();
        try {
            $statement = $this->connection->prepare('INSERT INTO roles (company_id, name, description) VALUES (?, ?, ?)');
            $statement->execute([$this->companyId, trim($input['name']), trim($input['description']) ?: null]);
            $roleId = (int) $this->connection->lastInsertId();
            if ($input['permissions']) {
                $permissionIds = array_values(array_unique(array_map('intval', $input['permissions'])));
                $placeholders = implode(',', array_fill(0, count($permissionIds), '?'));
                $permissionCheck = $this->connection->prepare('SELECT COUNT(*) FROM permissions WHERE id IN (' . $placeholders . ')');
                $permissionCheck->execute($permissionIds);
                if ((int) $permissionCheck->fetchColumn() !== count($permissionIds)) {
                    throw new RuntimeException('Uno de los permisos seleccionados no es válido.');
                }
                $permission = $this->connection->prepare('INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)');
                foreach ($permissionIds as $permissionId) {
                    $permission->execute([$roleId, $permissionId]);
                }
            }
            $this->connection->commit();
        } catch (\Throwable $exception) {
            $this->connection->rollBack();
            throw $exception;
        }
    }
}
