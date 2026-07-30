<?php

declare(strict_types=1);

namespace CampoSur\Services;

use PDO;

final class Auth extends BaseService
{
    public function __construct(private readonly PDO $connection)
    {
    }

    public function login(string $email, string $password): bool
    {
        $statement = $this->connection->prepare(
            'SELECT id, company_id, role_id, full_name, email, password_hash FROM users WHERE email = ? AND active = 1 LIMIT 1'
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

        $this->connection->prepare('UPDATE users SET last_login_at = NOW() WHERE id = ?')->execute([$user['id']]);
        return true;
    }

    public function can(int $roleId, string $permission): bool
    {
        $query = $this->connection->prepare('SELECT 1 FROM role_permissions rp INNER JOIN permissions p ON p.id = rp.permission_id WHERE rp.role_id = ? AND p.code = ? LIMIT 1');
        $query->execute([$roleId, $permission]);
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
