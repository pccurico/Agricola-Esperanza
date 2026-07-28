<?php

declare(strict_types=1);

namespace CampoSur\Services;

use PDO;
use RuntimeException;

final class ProcurementManagement
{
    public function __construct(private readonly PDO $connection, private readonly int $companyId)
    {
    }

    public function suppliers(): array
    {
        $query = $this->connection->prepare('SELECT id, tax_id, business_name, contact_name, email, phone, active FROM suppliers WHERE company_id = ? ORDER BY business_name');
        $query->execute([$this->companyId]);
        return $query->fetchAll();
    }

    public function orders(): array
    {
        $query = $this->connection->prepare('SELECT p.id, p.order_number, p.order_date, p.status, s.business_name, COUNT(i.id) AS items_count FROM purchase_orders p INNER JOIN suppliers s ON s.id = p.supplier_id LEFT JOIN purchase_order_items i ON i.purchase_order_id = p.id WHERE p.company_id = ? GROUP BY p.id ORDER BY p.order_date DESC, p.id DESC');
        $query->execute([$this->companyId]);
        return $query->fetchAll();
    }

    public function createSupplier(array $input): void
    {
        if (trim((string) ($input['business_name'] ?? '')) === '') {
            throw new RuntimeException('La razón social del proveedor es obligatoria.');
        }
        $query = $this->connection->prepare('INSERT INTO suppliers (company_id, tax_id, business_name, contact_name, email, phone, address) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $query->execute([$this->companyId, trim($input['tax_id']) ?: null, trim($input['business_name']), trim($input['contact_name']) ?: null, trim($input['email']) ?: null, trim($input['phone']) ?: null, trim($input['address']) ?: null]);
    }

    public function createOrder(array $input, int $userId): void
    {
        foreach (['supplier_id', 'order_number', 'order_date'] as $field) {
            if (trim((string) ($input[$field] ?? '')) === '') {
                throw new RuntimeException('Completa los datos obligatorios de la orden.');
            }
        }
        $supplier = $this->connection->prepare('SELECT id FROM suppliers WHERE id = ? AND company_id = ? AND active = 1');
        $supplier->execute([(int) $input['supplier_id'], $this->companyId]);
        if (!$supplier->fetchColumn()) {
            throw new RuntimeException('El proveedor no pertenece a esta empresa.');
        }
        foreach ([['seasons', $input['season_id'] ?? null], ['farms', $input['farm_id'] ?? null]] as [$table, $id]) {
            if ($id) {
                $reference = $this->connection->prepare('SELECT id FROM ' . $table . ' WHERE id = ? AND company_id = ?');
                $reference->execute([(int) $id, $this->companyId]);
                if (!$reference->fetchColumn()) {
                    throw new RuntimeException('Una referencia seleccionada no pertenece a esta empresa.');
                }
            }
        }
        $query = $this->connection->prepare('INSERT INTO purchase_orders (company_id, supplier_id, season_id, farm_id, order_number, order_date, notes, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $query->execute([$this->companyId, (int) $input['supplier_id'], $input['season_id'] ?: null, $input['farm_id'] ?: null, strtoupper(trim($input['order_number'])), $input['order_date'], trim($input['notes']) ?: null, $userId]);
    }

    public function receiveOrder(array $input, int $userId): int
    {
        $orderId = (int) ($input['purchase_order_id'] ?? 0);
        $receivedOn = trim((string) ($input['received_on'] ?? ''));
        $lines = is_array($input['items'] ?? null) ? $input['items'] : [];
        if ($orderId <= 0 || $receivedOn === '' || $lines === []) {
            throw new RuntimeException('La recepción requiere una orden, fecha y líneas.');
        }

        $this->connection->beginTransaction();
        try {
            $orderQuery = $this->connection->prepare('SELECT id, status, season_id FROM purchase_orders WHERE id = ? AND company_id = ? FOR UPDATE');
            $orderQuery->execute([$orderId, $this->companyId]);
            $order = $orderQuery->fetch();
            if (!$order || in_array($order['status'], ['CANCELLED', 'RECEIVED'], true)) {
                throw new RuntimeException('La orden no está disponible para recepción.');
            }

            $reception = $this->connection->prepare('INSERT INTO purchase_receptions (company_id, purchase_order_id, received_on, notes, created_by) VALUES (?, ?, ?, ?, ?)');
            $reception->execute([$this->companyId, $orderId, $receivedOn, trim((string) ($input['notes'] ?? '')) ?: null, $userId]);
            $receptionId = (int) $this->connection->lastInsertId();
            $lineQuery = $this->connection->prepare('SELECT id, item_id, quantity, received_quantity, unit_price FROM purchase_order_items WHERE id = ? AND purchase_order_id = ? FOR UPDATE');
            $lineInsert = $this->connection->prepare('INSERT INTO purchase_reception_items (reception_id, purchase_order_item_id, item_id, quantity, unit_cost) VALUES (?, ?, ?, ?, ?)');
            $movement = $this->connection->prepare('INSERT INTO inventory_movements (company_id, item_id, season_id, movement_type, quantity, unit_cost, movement_date, reference, created_by) VALUES (?, ?, ?, \'IN\', ?, ?, ?, ?, ?)');
            $receivedAny = false;
            foreach ($lines as $lineId => $quantity) {
                $quantity = (float) $quantity;
                if ($quantity <= 0) {
                    continue;
                }
                $lineQuery->execute([(int) $lineId, $orderId]);
                $line = $lineQuery->fetch();
                if (!$line || $quantity > ((float) $line['quantity'] - (float) $line['received_quantity'])) {
                    throw new RuntimeException('Una cantidad recibida supera el saldo de la orden.');
                }
                $lineInsert->execute([$receptionId, (int) $line['id'], $line['item_id'] ?: null, $quantity, $line['unit_price']]);
                if ($line['item_id']) {
                    $movement->execute([$this->companyId, (int) $line['item_id'], $order['season_id'] ?: null, $quantity, $line['unit_price'], $receivedOn, 'RECEPCION-' . $receptionId, $userId]);
                }
                $update = $this->connection->prepare('UPDATE purchase_order_items SET received_quantity = received_quantity + ? WHERE id = ?');
                $update->execute([$quantity, (int) $line['id']]);
                $receivedAny = true;
            }
            if (!$receivedAny) {
                throw new RuntimeException('La recepción no contiene cantidades válidas.');
            }
            $remaining = $this->connection->prepare('SELECT COUNT(*) FROM purchase_order_items WHERE purchase_order_id = ? AND received_quantity < quantity');
            $remaining->execute([$orderId]);
            $newStatus = (int) $remaining->fetchColumn() === 0 ? 'RECEIVED' : 'PARTIAL';
            $this->connection->prepare('UPDATE purchase_orders SET status = ? WHERE id = ? AND company_id = ?')->execute([$newStatus, $orderId, $this->companyId]);
            $this->connection->commit();
            (new AuditLog($this->connection, $this->companyId))->record($userId, 'CREATE', 'purchase_receptions', $receptionId, ['purchase_order_id' => $orderId, 'status' => $newStatus]);
            return $receptionId;
        } catch (\Throwable $exception) {
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }
            throw $exception;
        }
    }

    public function receptionOptions(): array
    {
        $query = $this->connection->prepare(
            'SELECT p.id AS purchase_order_id, p.order_number, p.order_date, s.business_name,
                    i.id AS purchase_order_item_id, i.description, i.quantity, i.received_quantity,
                    i.unit_price, i.item_id
             FROM purchase_orders p
             INNER JOIN suppliers s ON s.id = p.supplier_id
             INNER JOIN purchase_order_items i ON i.purchase_order_id = p.id
             WHERE p.company_id = ? AND p.status IN (\'SENT\', \'PARTIAL\')
               AND i.received_quantity < i.quantity
             ORDER BY p.order_date DESC, p.id DESC, i.id'
        );
        $query->execute([$this->companyId]);
        return $query->fetchAll();
    }

    public function options(): array
    {
        $suppliers = $this->connection->prepare('SELECT id, business_name FROM suppliers WHERE company_id = ? AND active = 1 ORDER BY business_name');
        $suppliers->execute([$this->companyId]);
        $seasons = $this->connection->prepare('SELECT id, name FROM seasons WHERE company_id = ? AND active = 1 ORDER BY starts_on DESC');
        $seasons->execute([$this->companyId]);
        $farms = $this->connection->prepare('SELECT id, name FROM farms WHERE company_id = ? AND active = 1 ORDER BY name');
        $farms->execute([$this->companyId]);
        return ['supplier_options' => $suppliers->fetchAll(), 'season_options' => $seasons->fetchAll(), 'farm_options' => $farms->fetchAll()];
    }
}
