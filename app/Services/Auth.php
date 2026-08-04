<?php

declare(strict_types=1);

namespace CampoSur\Services;

use PDO;

final class Auth extends BaseService
{
    public function __construct(protected readonly PDO $connection)
    {
    }

    public function login(string $email, string $password): bool
    {
        $statement = $this->connection->prepare(
            'SELECT u.id, u.company_id, u.role_id, u.full_name, u.email, u.password_hash, r.name AS role_name, r.description AS role_description, r.is_system AS role_is_system FROM users u LEFT JOIN roles r ON r.id = u.role_id WHERE u.email = ? AND u.active = 1 LIMIT 1'
        );
        $statement->execute([strtolower(trim($email))]);
        $user = $statement->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return false;
        }

        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['company_id'] = (int) $user['company_id'];
        $_SESSION['role_id'] = (int) $user['role_id'];
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['role_name'] = (string) ($user['role_name'] ?? 'Sin rol');
        $_SESSION['role_description'] = (string) ($user['role_description'] ?? '');
        $_SESSION['role_is_system'] = (bool) ((int) ($user['role_is_system'] ?? 0) === 1);
        $_SESSION['role_department'] = $this->normalizeDepartment((string) ($user['role_name'] ?? ''));

        $this->connection->prepare('UPDATE users SET last_login_at = NOW() WHERE id = ?')->execute([$user['id']]);
        return true;
    }

    public function roleDepartment(int $roleId): string
    {
        $query = $this->connection->prepare('SELECT name FROM roles WHERE id = ? LIMIT 1');
        $query->execute([$roleId]);
        return $this->normalizeDepartment((string) $query->fetchColumn());
    }

    private function normalizeDepartment(string $roleName): string
    {
        $normalized = mb_strtolower(trim($roleName));
        if ($normalized === '') {
            return 'general';
        }
        if (str_contains($normalized, 'gerencia') || str_contains($normalized, 'management') || str_contains($normalized, 'dirección') || str_contains($normalized, 'direccion')) {
            return 'gerencia';
        }
        if (str_contains($normalized, 'rrhh') || str_contains($normalized, 'recursos humanos') || str_contains($normalized, 'contabilidad') || str_contains($normalized, 'finanzas')) {
            return 'rrhh';
        }
        if (str_contains($normalized, 'produccion') || str_contains($normalized, 'producción') || str_contains($normalized, 'plantación') || str_contains($normalized, 'operación') || str_contains($normalized, 'operacion')) {
            return 'produccion';
        }
        if (str_contains($normalized, 'administración') || str_contains($normalized, 'administracion') || str_contains($normalized, 'admin') || str_contains($normalized, 'coordinador')) {
            return 'administracion';
        }
        if (str_contains($normalized, 'bodega') || str_contains($normalized, 'inventario') || str_contains($normalized, 'compras') || str_contains($normalized, 'abastecimiento') || str_contains($normalized, 'recepcion') || str_contains($normalized, 'recepción')) {
            return 'bodega';
        }
        if (str_contains($normalized, 'sistema') || str_contains($normalized, 'superadmin') || str_contains($normalized, 'root')) {
            return 'sistema';
        }

        return 'general';
    }

    public function can(int $roleId, string $permission, ?int $userId = null): bool
    {
        $sql = 'SELECT 1 FROM role_permissions rp INNER JOIN permissions p ON p.id = rp.permission_id WHERE rp.role_id = ? AND p.code = ?';
        $params = [$roleId, $permission];

        if ($userId !== null) {
            $sql = 'SELECT 1 FROM (' . $sql . ' UNION ALL SELECT 1 FROM user_permissions up INNER JOIN permissions p2 ON p2.id = up.permission_id WHERE up.user_id = ? AND p2.code = ?) AS effective_permissions LIMIT 1';
            $params[] = $userId;
            $params[] = $permission;
        } else {
            $sql .= ' LIMIT 1';
        }

        $query = $this->connection->prepare($sql);
        $query->execute($params);

        return (bool) $query->fetchColumn();
    }

    public function company(): array
    {
        $query = $this->connection->query('SELECT trade_name, logo_path FROM companies WHERE active = 1 ORDER BY id LIMIT 1');

        return $query->fetch() ?: [];
    }

    public function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
    }
}

