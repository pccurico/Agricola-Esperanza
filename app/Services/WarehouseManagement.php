<?php

declare(strict_types=1);

namespace CampoSur\Services;

use PDO;
use RuntimeException;

final class WarehouseManagement
{
    public function __construct(private readonly PDO $connection, private readonly int $companyId)
    {
    }

    public function warehouses(): array
    {
        $query = $this->connection->prepare('SELECT w.id, w.code, w.name, w.active, f.name AS farm_name FROM warehouses w LEFT JOIN farms f ON f.id = w.farm_id WHERE w.company_id = ? ORDER BY w.name');
        $query->execute([$this->companyId]);
        return $query->fetchAll();
    }

    public function locations(): array
    {
        $query = $this->connection->prepare('SELECT l.id, l.code, l.name, w.code AS warehouse_code, w.name AS warehouse_name FROM warehouse_locations l INNER JOIN warehouses w ON w.id = l.warehouse_id WHERE l.company_id = ? ORDER BY w.name, l.code');
        $query->execute([$this->companyId]);
        return $query->fetchAll();
    }

    public function lots(): array
    {
        $query = $this->connection->prepare('SELECT l.id, l.lot_number, l.expires_on, l.quantity, i.name AS item_name, w.name AS warehouse_name FROM inventory_lots l INNER JOIN inventory_items i ON i.id = l.item_id LEFT JOIN warehouses w ON w.id = l.warehouse_id WHERE l.company_id = ? ORDER BY l.expires_on IS NULL, l.expires_on, l.lot_number');
        $query->execute([$this->companyId]);
        return $query->fetchAll();
    }

    public function transfers(): array
    {
        $query = $this->connection->prepare('SELECT t.id, t.quantity, t.transfer_date, t.status, i.name AS item_name, wf.name AS from_warehouse, wt.name AS to_warehouse FROM inventory_transfers t INNER JOIN inventory_items i ON i.id = t.item_id INNER JOIN warehouses wf ON wf.id = t.from_warehouse_id INNER JOIN warehouses wt ON wt.id = t.to_warehouse_id WHERE t.company_id = ? ORDER BY t.transfer_date DESC, t.id DESC');
        $query->execute([$this->companyId]);
        return $query->fetchAll();
    }

    public function createWarehouse(array $input, int $userId): int
    {
        $this->required($input, ['code', 'name']);
        $query = $this->connection->prepare('INSERT INTO warehouses (company_id, farm_id, code, name) VALUES (?, ?, ?, ?)');
        $query->execute([$this->companyId, $input['farm_id'] ?: null, strtoupper(trim($input['code'])), trim($input['name'])]);
        $id = (int) $this->connection->lastInsertId();
        $this->audit($userId, 'CREATE', 'warehouses', $id);
        return $id;
    }

    public function createLocation(array $input, int $userId): int
    {
        $this->required($input, ['warehouse_id', 'code', 'name']);
        $this->belongs('warehouses', $input['warehouse_id']);
        $query = $this->connection->prepare('INSERT INTO warehouse_locations (company_id, warehouse_id, code, name) VALUES (?, ?, ?, ?)');
        $query->execute([$this->companyId, (int) $input['warehouse_id'], strtoupper(trim($input['code'])), trim($input['name'])]);
        $id = (int) $this->connection->lastInsertId();
        $this->audit($userId, 'CREATE', 'warehouse_locations', $id);
        return $id;
    }

    public function createLot(array $input, int $userId): int
    {
        $this->required($input, ['item_id', 'lot_number', 'quantity']);
        if ((float) $input['quantity'] < 0) {
            throw new RuntimeException('La cantidad del lote no puede ser negativa.');
        }
        $this->belongs('inventory_items', $input['item_id']);
        if (!empty($input['warehouse_id'])) {
            $this->belongs('warehouses', $input['warehouse_id']);
        }
        $query = $this->connection->prepare('INSERT INTO inventory_lots (company_id, item_id, warehouse_id, lot_number, expires_on, quantity) VALUES (?, ?, ?, ?, ?, ?)');
        $query->execute([$this->companyId, (int) $input['item_id'], $input['warehouse_id'] ?: null, strtoupper(trim($input['lot_number'])), $input['expires_on'] ?: null, $input['quantity']]);
        $id = (int) $this->connection->lastInsertId();
        $this->audit($userId, 'CREATE', 'inventory_lots', $id);
        return $id;
    }

    public function createTransfer(array $input, int $userId): int
    {
        $this->required($input, ['item_id', 'from_warehouse_id', 'to_warehouse_id', 'quantity', 'transfer_date']);
        if ((int) $input['from_warehouse_id'] === (int) $input['to_warehouse_id'] || (float) $input['quantity'] <= 0) {
            throw new RuntimeException('La transferencia requiere bodegas distintas y una cantidad positiva.');
        }
        $this->belongs('inventory_items', $input['item_id']);
        $this->belongs('warehouses', $input['from_warehouse_id']);
        $this->belongs('warehouses', $input['to_warehouse_id']);
        $query = $this->connection->prepare('INSERT INTO inventory_transfers (company_id, item_id, from_warehouse_id, to_warehouse_id, quantity, transfer_date, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $query->execute([$this->companyId, (int) $input['item_id'], (int) $input['from_warehouse_id'], (int) $input['to_warehouse_id'], $input['quantity'], $input['transfer_date'], $userId]);
        $id = (int) $this->connection->lastInsertId();
        $this->audit($userId, 'CREATE', 'inventory_transfers', $id);
        return $id;
    }

    public function approveTransfer(int $transferId, int $userId): void
    {
        $this->connection->beginTransaction();
        try {
            $query = $this->connection->prepare('SELECT * FROM inventory_transfers WHERE id = ? AND company_id = ? AND status = \'DRAFT\' FOR UPDATE');
            $query->execute([$transferId, $this->companyId]);
            $transfer = $query->fetch();
            if (!$transfer) {
                throw new RuntimeException('La transferencia no está disponible.');
            }
            $stock = $this->connection->prepare('SELECT COALESCE(SUM(CASE WHEN movement_type = \'IN\' THEN quantity WHEN movement_type = \'OUT\' THEN -quantity ELSE quantity END), 0) FROM inventory_movements WHERE company_id = ? AND item_id = ? AND warehouse_id = ?');
            $stock->execute([$this->companyId, $transfer['item_id'], $transfer['from_warehouse_id']]);
            if ((float) $stock->fetchColumn() < (float) $transfer['quantity']) {
                throw new RuntimeException('Stock insuficiente en la bodega de origen.');
            }
            $movement = $this->connection->prepare('INSERT INTO inventory_movements (company_id, item_id, warehouse_id, movement_type, quantity, movement_date, reference, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
            $movement->execute([$this->companyId, $transfer['item_id'], $transfer['from_warehouse_id'], 'OUT', $transfer['quantity'], $transfer['transfer_date'], 'TRANSFERENCIA-' . $transferId, $userId]);
            $movement->execute([$this->companyId, $transfer['item_id'], $transfer['to_warehouse_id'], 'IN', $transfer['quantity'], $transfer['transfer_date'], 'TRANSFERENCIA-' . $transferId, $userId]);
            $this->connection->prepare('UPDATE inventory_transfers SET status = \'RECEIVED\' WHERE id = ? AND company_id = ?')->execute([$transferId, $this->companyId]);
            $this->connection->commit();
            $this->audit($userId, 'APPROVE', 'inventory_transfers', $transferId);
        } catch (\Throwable $exception) {
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }
            throw $exception;
        }
    }

    public function options(): array
    {
        $items = $this->connection->prepare('SELECT id, name, sku FROM inventory_items WHERE company_id = ? AND active = 1 ORDER BY name');
        $items->execute([$this->companyId]);
        $farms = $this->connection->prepare('SELECT id, name FROM farms WHERE company_id = ? AND active = 1 ORDER BY name');
        $farms->execute([$this->companyId]);
        return ['items' => $items->fetchAll(), 'farms' => $farms->fetchAll(), 'warehouses' => $this->warehouses()];
    }

    private function belongs(string $table, mixed $id): void
    {
        if (!in_array($table, ['warehouses', 'inventory_items'], true)) {
            throw new RuntimeException('Referencia no válida.');
        }
        $query = $this->connection->prepare('SELECT id FROM ' . $table . ' WHERE id = ? AND company_id = ? AND active = 1');
        $query->execute([(int) $id, $this->companyId]);
        if (!$query->fetchColumn()) {
            throw new RuntimeException('La referencia seleccionada no pertenece a esta agrícola.');
        }
    }

    private function required(array $input, array $fields): void
    {
        foreach ($fields as $field) {
            if (trim((string) ($input[$field] ?? '')) === '') {
                throw new RuntimeException('Completa los datos obligatorios.');
            }
        }
    }

    private function audit(int $userId, string $action, string $entity, int $id): void
    {
        (new AuditLog($this->connection, $this->companyId))->record($userId, $action, $entity, $id);
    }
}
