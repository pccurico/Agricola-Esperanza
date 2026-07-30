<?php

declare(strict_types=1);

namespace CampoSur\Services;

use PDO;
use RuntimeException;

final class NotificationManagement extends BaseService
{
    public function __construct(private readonly PDO $connection, private readonly int $companyId, private readonly int $userId)
    {
    }

    public function unreadCount(): int
    {
        $query = $this->connection->prepare('SELECT COUNT(*) FROM notifications WHERE company_id = ? AND user_id = ? AND read_at IS NULL');
        $query->execute([$this->companyId, $this->userId]);
        return (int) $query->fetchColumn();
    }

    public function recent(): array
    {
        $query = $this->connection->prepare('SELECT id, notification_type, title, message, read_at, created_at FROM notifications WHERE company_id = ? AND user_id = ? ORDER BY created_at DESC, id DESC LIMIT 100');
        $query->execute([$this->companyId, $this->userId]);
        return $query->fetchAll();
    }

    public function create(int $recipientId, string $type, string $title, string $message): int
    {
        if (trim($type) === '' || trim($title) === '' || trim($message) === '') {
            throw new RuntimeException('La notificaciÃ³n requiere tipo, tÃ­tulo y mensaje.');
        }
        $recipient = $this->connection->prepare('SELECT id FROM users WHERE id = ? AND company_id = ? AND active = 1');
        $recipient->execute([$recipientId, $this->companyId]);
        if (!$recipient->fetchColumn()) {
            throw new RuntimeException('El destinatario no pertenece a esta empresa.');
        }
        $query = $this->connection->prepare('INSERT INTO notifications (company_id, user_id, notification_type, title, message) VALUES (?, ?, ?, ?, ?)');
        $query->execute([$this->companyId, $recipientId, strtoupper(trim($type)), trim($title), trim($message)]);
        return (int) $this->connection->lastInsertId();
    }

    public function markRead(int $notificationId): void
    {
        $query = $this->connection->prepare('UPDATE notifications SET read_at = CURRENT_TIMESTAMP WHERE id = ? AND company_id = ? AND user_id = ? AND read_at IS NULL');
        $query->execute([$notificationId, $this->companyId, $this->userId]);
    }

    public function markAllRead(): void
    {
        $query = $this->connection->prepare('UPDATE notifications SET read_at = CURRENT_TIMESTAMP WHERE company_id = ? AND user_id = ? AND read_at IS NULL');
        $query->execute([$this->companyId, $this->userId]);
    }
}
