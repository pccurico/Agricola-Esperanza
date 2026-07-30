<?php

declare(strict_types=1);

namespace CampoSur\Services;

use PDO;
use RuntimeException;

final class ProfileService
{
    public function __construct(private readonly PDO $connection, private readonly int $userId, private readonly int $companyId)
    {
    }

    public function user(): array
    {
        $query = $this->connection->prepare('SELECT u.id, u.full_name, u.email, u.phone, u.last_login_at, r.name AS role_name, c.trade_name FROM users u INNER JOIN roles r ON r.id = u.role_id INNER JOIN companies c ON c.id = u.company_id WHERE u.id = ? AND u.company_id = ? LIMIT 1');
        $query->execute([$this->userId, $this->companyId]);
        return $query->fetch() ?: [];
    }

    public function update(array $input): void
    {
        if (trim((string) ($input['full_name'] ?? '')) === '' || !filter_var($input['email'] ?? '', FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('Ingresa un nombre y correo válidos.');
        }
        $query = $this->connection->prepare('UPDATE users SET full_name = ?, email = ?, phone = ? WHERE id = ? AND company_id = ?');
        $query->execute([trim($input['full_name']), strtolower(trim($input['email'])), trim($input['phone']) ?: null, $this->userId, $this->companyId]);
        if (trim((string) ($input['new_password'] ?? '')) !== '') {
            if (strlen($input['new_password']) < 10) {
                throw new RuntimeException('La nueva contraseña debe tener al menos 10 caracteres.');
            }
            $password = $this->connection->prepare('UPDATE users SET password_hash = ? WHERE id = ? AND company_id = ?');
            $password->execute([password_hash($input['new_password'], PASSWORD_DEFAULT), $this->userId, $this->companyId]);
        }
    }
}
