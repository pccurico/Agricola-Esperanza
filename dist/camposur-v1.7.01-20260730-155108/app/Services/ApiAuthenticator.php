<?php

declare(strict_types=1);

namespace CampoSur\Services;

use PDO;

final class ApiAuthenticator
{
    public function __construct(private readonly PDO $connection)
    {
    }

    public function authenticate(string $authorization): ?array
    {
        if (!preg_match('/^Bearer\s+(.+)$/i', trim($authorization), $matches)) {
            return null;
        }
        $hash = hash('sha256', $matches[1]);
        $query = $this->connection->prepare(
            'SELECT t.id AS token_id, t.company_id, t.user_id, u.role_id, u.full_name, u.email
             FROM api_tokens t INNER JOIN users u ON u.id = t.user_id AND u.company_id = t.company_id
             WHERE t.token_hash = ? AND t.revoked_at IS NULL AND (t.expires_at IS NULL OR t.expires_at > CURRENT_TIMESTAMP)
               AND u.active = 1 AND EXISTS (SELECT 1 FROM companies c WHERE c.id = t.company_id AND c.active = 1)
             LIMIT 1'
        );
        $query->execute([$hash]);
        $identity = $query->fetch();
        if (!$identity) {
            return null;
        }
        $this->connection->prepare('UPDATE api_tokens SET last_used_at = CURRENT_TIMESTAMP WHERE id = ?')->execute([(int) $identity['token_id']]);
        return $identity;
    }
}
