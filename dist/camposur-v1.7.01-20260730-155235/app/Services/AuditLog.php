<?php

declare(strict_types=1);

namespace CampoSur\Services;

use PDO;

final class AuditLog
{
    public function __construct(private readonly PDO $connection, private readonly int $companyId)
    {
    }

    public function record(int $userId, string $action, string $entityType, ?int $entityId = null, array $details = []): void
    {
        $query = $this->connection->prepare('INSERT INTO audit_logs (company_id, user_id, action, entity_type, entity_id, details, ip_address) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $query->execute([$this->companyId, $userId, $action, $entityType, $entityId, $details ? json_encode($details, JSON_UNESCAPED_UNICODE) : null, $_SERVER['REMOTE_ADDR'] ?? null]);
    }

    public function recent(): array
    {
        $query = $this->connection->prepare('SELECT a.id, a.action, a.entity_type, a.entity_id, a.details, a.ip_address, a.created_at, COALESCE(u.full_name, "Sistema") AS user_name FROM audit_logs a LEFT JOIN users u ON u.id = a.user_id WHERE a.company_id = ? ORDER BY a.created_at DESC, a.id DESC LIMIT 100');
        $query->execute([$this->companyId]);
        return $query->fetchAll();
    }
}
