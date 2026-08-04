<?php

declare(strict_types=1);

namespace AgroPCC\Services;

use PDO;
use RuntimeException;

final class ApiTokenManagement extends BaseService
{
    public function __construct(protected readonly PDO $connection, protected readonly int $companyId, private readonly int $userId)
    {
    }

    public function tokens(): array
    {
        $query = $this->connection->prepare('SELECT id, name, last_used_at, expires_at, revoked_at, created_at FROM api_tokens WHERE company_id = ? AND user_id = ? ORDER BY created_at DESC, id DESC');
        $query->execute([$this->companyId, $this->userId]);
        return $query->fetchAll();
    }

    public function create(string $name, ?string $expiresAt = null): string
    {
        if (trim($name) === '') {
            throw new RuntimeException('El nombre del token es obligatorio.');
        }
        $plainToken = bin2hex(random_bytes(32));
        $hash = hash('sha256', $plainToken);
        $query = $this->connection->prepare('INSERT INTO api_tokens (company_id, user_id, token_hash, name, expires_at) VALUES (?, ?, ?, ?, ?)');
        $query->execute([$this->companyId, $this->userId, $hash, trim($name), $expiresAt ?: null]);
        (new AuditLog($this->connection, $this->companyId))->record($this->userId, 'CREATE', 'api_tokens', (int) $this->connection->lastInsertId(), ['name' => trim($name)]);
        return $plainToken;
    }

    public function revoke(int $tokenId): void
    {
        $query = $this->connection->prepare('UPDATE api_tokens SET revoked_at = CURRENT_TIMESTAMP WHERE id = ? AND company_id = ? AND user_id = ? AND revoked_at IS NULL');
        $query->execute([$tokenId, $this->companyId, $this->userId]);
        if ($query->rowCount() === 0) {
            throw new RuntimeException('El token no estÃ¡ disponible.');
        }
        (new AuditLog($this->connection, $this->companyId))->record($this->userId, 'REVOKE', 'api_tokens', $tokenId);
    }
}

