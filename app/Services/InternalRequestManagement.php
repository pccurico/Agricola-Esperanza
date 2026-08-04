<?php

declare(strict_types=1);

namespace AgroPCC\Services;

use PDO;
use RuntimeException;

final class InternalRequestManagement extends BaseService
{
    public function __construct(protected readonly PDO $connection, protected readonly int $companyId)
    {
    }

    public function requests(): array
    {
        $query = $this->connection->prepare('SELECT r.id, r.requested_on, r.status, r.notes, u.full_name AS requester, f.name AS farm_name, COUNT(i.id) AS items_count FROM internal_requests r INNER JOIN users u ON u.id = r.requested_by LEFT JOIN farms f ON f.id = r.farm_id LEFT JOIN internal_request_items i ON i.request_id = r.id WHERE r.company_id = ? GROUP BY r.id ORDER BY r.requested_on DESC, r.id DESC');
        $query->execute([$this->companyId]);
        return $query->fetchAll();
    }

    public function items(int $requestId): array
    {
        $query = $this->connection->prepare('SELECT i.id, i.item_id, i.quantity, i.fulfilled_quantity, v.name AS item_name, v.sku FROM internal_request_items i INNER JOIN inventory_items v ON v.id = i.item_id INNER JOIN internal_requests r ON r.id = i.request_id WHERE i.request_id = ? AND r.company_id = ? ORDER BY v.name');
        $query->execute([$requestId, $this->companyId]);
        return $query->fetchAll();
    }

    public function fulfillmentOptions(): array
    {
        $query = $this->connection->prepare(
            'SELECT r.id AS request_id, r.requested_on, r.notes, i.id AS request_item_id,
                    i.quantity, i.fulfilled_quantity, v.name AS item_name, v.sku
             FROM internal_requests r
             INNER JOIN internal_request_items i ON i.request_id = r.id
             INNER JOIN inventory_items v ON v.id = i.item_id
             WHERE r.company_id = ? AND r.status = \'APPROVED\'
               AND i.fulfilled_quantity < i.quantity
             ORDER BY r.requested_on, r.id, i.id'
        );
        $query->execute([$this->companyId]);
        return $query->fetchAll();
    }

    public function options(): array
    {
        $items = $this->connection->prepare('SELECT id, sku, name FROM inventory_items WHERE company_id = ? AND active = 1 ORDER BY name');
        $items->execute([$this->companyId]);
        $farms = $this->connection->prepare('SELECT id, name FROM farms WHERE company_id = ? AND active = 1 ORDER BY name');
        $farms->execute([$this->companyId]);
        $warehouses = $this->connection->prepare('SELECT id, name FROM warehouses WHERE company_id = ? AND active = 1 ORDER BY name');
        $warehouses->execute([$this->companyId]);
        return ['items' => $items->fetchAll(), 'farms' => $farms->fetchAll(), 'warehouses' => $warehouses->fetchAll()];
    }

    public function create(array $input, int $userId): int
    {
        $requestedOn = trim((string) ($input['requested_on'] ?? ''));
        $lines = is_array($input['items'] ?? null) ? $input['items'] : [];
        if ($requestedOn === '' || $lines === []) {
            throw new RuntimeException('La solicitud requiere fecha y artÃ­culos.');
        }
        $requestId = $this->transaction($this->connection, function () use ($input, $userId, $requestedOn, $lines): int {
            $request = $this->connection->prepare('INSERT INTO internal_requests (company_id, requested_by, farm_id, status, requested_on, notes) VALUES (?, ?, ?, \'REQUESTED\', ?, ?)');
            $request->execute([$this->companyId, $userId, $input['farm_id'] ?: null, $requestedOn, trim((string) ($input['notes'] ?? '')) ?: null]);
            $requestId = (int) $this->connection->lastInsertId();
            $line = $this->connection->prepare('INSERT INTO internal_request_items (request_id, item_id, quantity, notes) VALUES (?, ?, ?, ?)');
            foreach ($lines as $itemId => $quantity) {
                if ((float) $quantity <= 0) {
                    continue;
                }
                $this->belongs('inventory_items', $itemId);
                $line->execute([$requestId, (int) $itemId, $quantity, null]);
            }
            if ($line->rowCount() === 0) {
                throw new RuntimeException('La solicitud no contiene cantidades vÃ¡lidas.');
            }
            return $requestId;
        });
        $this->audit($userId, 'CREATE', 'internal_requests', $requestId);

        return $requestId;
    }

    public function approve(int $requestId, int $userId): void
    {
        $request = $this->connection->prepare('SELECT requested_by FROM internal_requests WHERE id = ? AND company_id = ? AND status = \'REQUESTED\'');
        $request->execute([$requestId, $this->companyId]);
        $requestedBy = (int) $request->fetchColumn();
        if ($requestedBy <= 0) {
            throw new RuntimeException('La solicitud no estÃ¡ disponible para aprobaciÃ³n.');
        }
        $query = $this->connection->prepare('UPDATE internal_requests SET status = \'APPROVED\' WHERE id = ? AND company_id = ? AND status = \'REQUESTED\'');
        $query->execute([$requestId, $this->companyId]);
        if ($query->rowCount() === 0) {
            throw new RuntimeException('La solicitud no estÃ¡ disponible para aprobaciÃ³n.');
        }
        (new NotificationManagement($this->connection, $this->companyId, $userId))->create($requestedBy, 'REQUEST_APPROVED', 'Solicitud aprobada', 'La solicitud interna #' . $requestId . ' fue aprobada.');
        $this->audit($userId, 'APPROVE', 'internal_requests', $requestId);
    }

    public function fulfill(array $input, int $userId): void
    {
        $requestId = (int) ($input['request_id'] ?? 0);
        $warehouseId = (int) ($input['warehouse_id'] ?? 0);
        $lines = is_array($input['items'] ?? null) ? $input['items'] : [];
        if ($requestId <= 0 || $warehouseId <= 0 || $lines === []) {
            throw new RuntimeException('La atenciÃ³n requiere solicitud, bodega y cantidades.');
        }
        $this->belongs('warehouses', $warehouseId);
        $this->transaction($this->connection, function () use ($requestId, $warehouseId, $lines, $userId): void {
            $request = $this->connection->prepare('SELECT id, status FROM internal_requests WHERE id = ? AND company_id = ? FOR UPDATE');
            $request->execute([$requestId, $this->companyId]);
            $header = $request->fetch();
            if (!$header || $header['status'] !== 'APPROVED') {
                throw new RuntimeException('La solicitud no estÃ¡ aprobada.');
            }
            $lineQuery = $this->connection->prepare('SELECT id, item_id, quantity, fulfilled_quantity FROM internal_request_items WHERE id = ? AND request_id = ? FOR UPDATE');
            $movement = $this->connection->prepare('INSERT INTO inventory_movements (company_id, item_id, warehouse_id, movement_type, quantity, movement_date, reference, created_by) VALUES (?, ?, ?, \'OUT\', ?, ?, ?, ?)');
            $update = $this->connection->prepare('UPDATE internal_request_items SET fulfilled_quantity = fulfilled_quantity + ? WHERE id = ?');
            $fulfilled = false;
            foreach ($lines as $lineId => $quantity) {
                $quantity = (float) $quantity;
                if ($quantity <= 0) {
                    continue;
                }
                $lineQuery->execute([(int) $lineId, $requestId]);
                $line = $lineQuery->fetch();
                if (!$line || $quantity > ((float) $line['quantity'] - (float) $line['fulfilled_quantity'])) {
                    throw new RuntimeException('La cantidad atendida supera el saldo solicitado.');
                }
                $stock = $this->connection->prepare('SELECT COALESCE(SUM(CASE WHEN movement_type = \'IN\' THEN quantity WHEN movement_type = \'OUT\' THEN -quantity ELSE quantity END), 0) FROM inventory_movements WHERE company_id = ? AND item_id = ? AND warehouse_id = ?');
                $stock->execute([$this->companyId, $line['item_id'], $warehouseId]);
                if ((float) $stock->fetchColumn() < $quantity) {
                    throw new RuntimeException('Stock insuficiente para atender la solicitud.');
                }
                $movement->execute([$this->companyId, $line['item_id'], $warehouseId, $quantity, date('Y-m-d'), 'SOLICITUD-' . $requestId, $userId]);
                $update->execute([$quantity, (int) $line['id']]);
                $fulfilled = true;
            }
            if (!$fulfilled) {
                throw new RuntimeException('No se ingresaron cantidades a atender.');
            }
            $remaining = $this->connection->prepare('SELECT COUNT(*) FROM internal_request_items WHERE request_id = ? AND fulfilled_quantity < quantity');
            $remaining->execute([$requestId]);
            if ((int) $remaining->fetchColumn() === 0) {
                $this->connection->prepare('UPDATE internal_requests SET status = \'FULFILLED\' WHERE id = ? AND company_id = ?')->execute([$requestId, $this->companyId]);
            }
        });
        $this->audit($userId, 'FULFILL', 'internal_requests', $requestId);
    }

    private function belongs(string $table, mixed $id): void
    {
        if (!in_array($table, ['inventory_items', 'warehouses'], true)) {
            throw new RuntimeException('Referencia no vÃ¡lida.');
        }
        $query = $this->connection->prepare('SELECT id FROM ' . $table . ' WHERE id = ? AND company_id = ? AND active = 1');
        $query->execute([(int) $id, $this->companyId]);
        if (!$query->fetchColumn()) {
            throw new RuntimeException('La referencia seleccionada no pertenece a esta agrÃ­cola.');
        }
    }

    private function audit(int $userId, string $action, string $entity, int $id): void
    {
        (new AuditLog($this->connection, $this->companyId))->record($userId, $action, $entity, $id);
    }
}

