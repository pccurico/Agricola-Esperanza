<?php

declare(strict_types=1);

namespace CampoSur\Services;

use PDO;
use RuntimeException;

final class TaskCalendarManagement
{
    public function __construct(private readonly PDO $connection, private readonly int $companyId)
    {
    }

    public function tasks(): array
    {
        $query = $this->connection->prepare('SELECT t.id, t.title, t.description, t.due_date, t.priority, t.status, c.full_name AS creator_name, a.full_name AS assignee_name FROM tasks t INNER JOIN users c ON c.id = t.created_by LEFT JOIN users a ON a.id = t.assigned_to WHERE t.company_id = ? ORDER BY t.due_date IS NULL, t.due_date, t.id DESC');
        $query->execute([$this->companyId]);
        return $query->fetchAll();
    }

    public function events(): array
    {
        $query = $this->connection->prepare('SELECT e.id, e.title, e.description, e.starts_at, e.ends_at, e.event_type, f.name AS farm_name, u.full_name AS creator_name FROM calendar_events e INNER JOIN users u ON u.id = e.created_by LEFT JOIN farms f ON f.id = e.farm_id WHERE e.company_id = ? ORDER BY e.starts_at, e.id');
        $query->execute([$this->companyId]);
        return $query->fetchAll();
    }

    public function options(): array
    {
        $users = $this->connection->prepare('SELECT id, full_name FROM users WHERE company_id = ? AND active = 1 ORDER BY full_name');
        $users->execute([$this->companyId]);
        $farms = $this->connection->prepare('SELECT id, name FROM farms WHERE company_id = ? AND active = 1 ORDER BY name');
        $farms->execute([$this->companyId]);
        return ['users' => $users->fetchAll(), 'farms' => $farms->fetchAll()];
    }

    public function createTask(array $input, int $userId): int
    {
        if (trim((string) ($input['title'] ?? '')) === '') {
            throw new RuntimeException('El título de la tarea es obligatorio.');
        }
        $priority = strtoupper(trim((string) ($input['priority'] ?? 'NORMAL')));
        if (!(new CatalogLookup($this->connection, $this->companyId))->exists('TASK_PRIORITY', $priority)) {
            throw new RuntimeException('La prioridad no está habilitada.');
        }
        $assignedTo = $input['assigned_to'] ?: null;
        if ($assignedTo) {
            $this->belongsUser($assignedTo);
        }
        $query = $this->connection->prepare('INSERT INTO tasks (company_id, assigned_to, created_by, title, description, due_date, priority) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $query->execute([$this->companyId, $assignedTo, $userId, trim($input['title']), trim((string) ($input['description'] ?? '')) ?: null, $input['due_date'] ?: null, $priority]);
        $id = (int) $this->connection->lastInsertId();
        if ($assignedTo) {
            (new NotificationManagement($this->connection, $this->companyId, $userId))->create((int) $assignedTo, 'TASK_ASSIGNED', 'Nueva tarea asignada', 'Se te asignó la tarea: ' . trim($input['title']));
        }
        $this->audit($userId, 'CREATE', 'tasks', $id);
        return $id;
    }

    public function updateTaskStatus(int $taskId, string $status, int $userId): void
    {
        $allowed = ['OPEN', 'IN_PROGRESS', 'DONE', 'CANCELLED'];
        $status = strtoupper(trim($status));
        if (!in_array($status, $allowed, true)) {
            throw new RuntimeException('El estado de la tarea no es válido.');
        }
        $query = $this->connection->prepare('UPDATE tasks SET status = ? WHERE id = ? AND company_id = ?');
        $query->execute([$status, $taskId, $this->companyId]);
        if ($query->rowCount() === 0) {
            throw new RuntimeException('La tarea no existe para esta empresa.');
        }
        $this->audit($userId, 'UPDATE', 'tasks', $taskId, ['status' => $status]);
    }

    public function createEvent(array $input, int $userId): int
    {
        if (trim((string) ($input['title'] ?? '')) === '' || trim((string) ($input['starts_at'] ?? '')) === '') {
            throw new RuntimeException('El título y el inicio del evento son obligatorios.');
        }
        if (!empty($input['ends_at']) && $input['ends_at'] < $input['starts_at']) {
            throw new RuntimeException('El término debe ser posterior al inicio.');
        }
        if (!empty($input['farm_id'])) {
            $this->belongsFarm($input['farm_id']);
        }
        $query = $this->connection->prepare('INSERT INTO calendar_events (company_id, created_by, title, description, starts_at, ends_at, event_type, farm_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $query->execute([$this->companyId, $userId, trim($input['title']), trim((string) ($input['description'] ?? '')) ?: null, $input['starts_at'], $input['ends_at'] ?: null, strtoupper(trim((string) ($input['event_type'] ?? 'GENERAL'))), $input['farm_id'] ?: null]);
        $id = (int) $this->connection->lastInsertId();
        $this->audit($userId, 'CREATE', 'calendar_events', $id);
        return $id;
    }

    private function belongsUser(mixed $id): void
    {
        $query = $this->connection->prepare('SELECT id FROM users WHERE id = ? AND company_id = ? AND active = 1');
        $query->execute([(int) $id, $this->companyId]);
        if (!$query->fetchColumn()) {
            throw new RuntimeException('El usuario asignado no pertenece a esta empresa.');
        }
    }

    private function belongsFarm(mixed $id): void
    {
        $query = $this->connection->prepare('SELECT id FROM farms WHERE id = ? AND company_id = ? AND active = 1');
        $query->execute([(int) $id, $this->companyId]);
        if (!$query->fetchColumn()) {
            throw new RuntimeException('El fundo seleccionado no pertenece a esta empresa.');
        }
    }

    private function audit(int $userId, string $action, string $entity, int $id, array $details = []): void
    {
        (new AuditLog($this->connection, $this->companyId))->record($userId, $action, $entity, $id, $details);
    }
}
