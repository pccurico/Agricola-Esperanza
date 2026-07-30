<?php

declare(strict_types=1);

namespace CampoSur\Services;

use PDO;
use RuntimeException;

final class ProcurementManagement extends BaseService
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

    public function invoices(): array
    {
        $query = $this->connection->prepare('SELECT i.id, i.invoice_number, i.issue_date, i.due_date, i.total_amount, i.status, s.business_name, o.order_number FROM purchase_invoices i INNER JOIN suppliers s ON s.id = i.supplier_id LEFT JOIN purchase_orders o ON o.id = i.purchase_order_id WHERE i.company_id = ? ORDER BY i.issue_date DESC, i.id DESC');
        $query->execute([$this->companyId]);
        return $query->fetchAll();
    }

    public function createInvoice(array $input, int $userId): int
    {
        foreach (['supplier_id', 'invoice_number', 'issue_date', 'total_amount'] as $field) {
            if (trim((string) ($input[$field] ?? '')) === '') {
                throw new RuntimeException('Proveedor, numero, fecha y total son obligatorios para la factura.');
            }
        }
        $total = (float) $input['total_amount'];
        $net = (float) ($input['net_amount'] ?? 0);
        $tax = (float) ($input['tax_amount'] ?? 0);
        if ($total <= 0 || $net < 0 || $tax < 0 || round($net + $tax, 2) !== round($total, 2)) {
            throw new RuntimeException('Los montos de la factura no son consistentes.');
        }
        $supplier = $this->connection->prepare('SELECT id FROM suppliers WHERE id = ? AND company_id = ? AND active = 1');
        $supplier->execute([(int) $input['supplier_id'], $this->companyId]);
        if (!$supplier->fetchColumn()) {
            throw new RuntimeException('El proveedor no pertenece a esta empresa.');
        }
        $orderId = !empty($input['purchase_order_id']) ? (int) $input['purchase_order_id'] : null;
        if ($orderId !== null) {
            $order = $this->connection->prepare('SELECT id FROM purchase_orders WHERE id = ? AND company_id = ? AND supplier_id = ?');
            $order->execute([$orderId, $this->companyId, (int) $input['supplier_id']]);
            if (!$order->fetchColumn()) {
                throw new RuntimeException('La orden no corresponde al proveedor seleccionado.');
            }
        }
        $query = $this->connection->prepare('INSERT INTO purchase_invoices (company_id, supplier_id, purchase_order_id, invoice_number, issue_date, due_date, net_amount, tax_amount, total_amount, status, notes, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, \'DRAFT\', ?, ?)');
        $query->execute([$this->companyId, (int) $input['supplier_id'], $orderId, strtoupper(trim((string) $input['invoice_number'])), $input['issue_date'], $input['due_date'] ?: null, $net, $tax, $total, trim((string) ($input['notes'] ?? '')) ?: null, $userId]);
        return (int) $this->connection->lastInsertId();
    }

    public function createSupplier(array $input): void
    {
        if (trim((string) ($input['business_name'] ?? '')) === '') {
            throw new RuntimeException('La razÃ³n social del proveedor es obligatoria.');
        }
        $query = $this->connection->prepare('INSERT INTO suppliers (company_id, tax_id, business_name, contact_name, email, phone, address) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $query->execute([$this->companyId, trim($input['tax_id']) ?: null, trim($input['business_name']), trim($input['contact_name']) ?: null, trim($input['email']) ?: null, trim($input['phone']) ?: null, trim($input['address']) ?: null]);
    }

    public function createOrder(array $input, int $userId): void
    {
        foreach (['supplier_id', 'order_number', 'order_date', 'description', 'quantity', 'unit_price'] as $field) {
            if (trim((string) ($input[$field] ?? '')) === '') {
                throw new RuntimeException('Por favor, completa los datos de la orden y su primera lÃ­nea.');
            }
        }
        if ((float) $input['quantity'] <= 0 || (float) $input['unit_price'] < 0) {
            throw new RuntimeException('La cantidad debe ser mayor que cero y el precio no puede ser negativo.');
        }
        $supplier = $this->connection->prepare('SELECT id FROM suppliers WHERE id = ? AND company_id = ? AND active = 1');
        $supplier->execute([(int) $input['supplier_id'], $this->companyId]);
        if (!$supplier->fetchColumn()) {
            throw new RuntimeException('El proveedor no pertenece a esta agrÃ­cola.');
        }
        foreach ([['seasons', $input['season_id'] ?? null], ['farms', $input['farm_id'] ?? null]] as [$table, $id]) {
            if ($id) {
                $reference = $this->connection->prepare('SELECT id FROM ' . $table . ' WHERE id = ? AND company_id = ?');
                $reference->execute([(int) $id, $this->companyId]);
                if (!$reference->fetchColumn()) {
                    throw new RuntimeException('Una referencia seleccionada no pertenece a esta agrÃ­cola.');
                }
            }
        }
        $itemId = !empty($input['item_id']) ? (int) $input['item_id'] : null;
        if ($itemId !== null) {
            $item = $this->connection->prepare('SELECT id FROM inventory_items WHERE id = ? AND company_id = ? AND active = 1');
            $item->execute([$itemId, $this->companyId]);
            if (!$item->fetchColumn()) {
                throw new RuntimeException('El insumo seleccionado no pertenece a esta agrÃ­cola.');
            }
        }
        $this->connection->beginTransaction();
        try {
            $query = $this->connection->prepare('INSERT INTO purchase_orders (company_id, supplier_id, season_id, farm_id, order_number, order_date, status, notes, created_by) VALUES (?, ?, ?, ?, ?, ?, \'SENT\', ?, ?)');
            $query->execute([$this->companyId, (int) $input['supplier_id'], $input['season_id'] ?: null, $input['farm_id'] ?: null, strtoupper(trim($input['order_number'])), $input['order_date'], trim($input['notes']) ?: null, $userId]);
            $orderId = (int) $this->connection->lastInsertId();
            $line = $this->connection->prepare('INSERT INTO purchase_order_items (purchase_order_id, item_id, description, quantity, unit_price) VALUES (?, ?, ?, ?, ?)');
            $line->execute([$orderId, $itemId, trim($input['description']), $input['quantity'], $input['unit_price']]);
            $this->connection->commit();
        } catch (\Throwable $exception) {
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }
            throw $exception;
        }
    }

    public function receiveOrder(array $input, int $userId): int
    {
        $orderId = (int) ($input['purchase_order_id'] ?? 0);
        $receivedOn = trim((string) ($input['received_on'] ?? ''));
        $lines = is_array($input['items'] ?? null) ? $input['items'] : [];
        if ($orderId <= 0 || $receivedOn === '' || $lines === []) {
            throw new RuntimeException('La recepciÃ³n requiere una orden, fecha y lÃ­neas.');
        }

        $this->connection->beginTransaction();
        try {
            $orderQuery = $this->connection->prepare('SELECT id, status, season_id FROM purchase_orders WHERE id = ? AND company_id = ? FOR UPDATE');
            $orderQuery->execute([$orderId, $this->companyId]);
            $order = $orderQuery->fetch();
            if (!$order || in_array($order['status'], ['CANCELLED', 'RECEIVED'], true)) {
                throw new RuntimeException('La orden no estÃ¡ disponible para recepciÃ³n.');
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
                throw new RuntimeException('La recepciÃ³n no contiene cantidades vÃ¡lidas.');
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

    public function receptionHistory(): array
    {
        $query = $this->connection->prepare(
            'SELECT r.id, r.purchase_order_id, r.received_on, r.status, r.notes, r.created_at,
                    p.order_number, s.business_name, COALESCE(SUM(ri.quantity), 0) AS total_quantity,
                    COUNT(ri.id) AS lines_count
             FROM purchase_receptions r
             INNER JOIN purchase_orders p ON p.id = r.purchase_order_id
             INNER JOIN suppliers s ON s.id = p.supplier_id
             LEFT JOIN purchase_reception_items ri ON ri.reception_id = r.id
             WHERE r.company_id = ?
             GROUP BY r.id, r.purchase_order_id, r.received_on, r.status, r.notes, r.created_at, p.order_number, s.business_name
             ORDER BY r.received_on DESC, r.id DESC'
        );
        $query->execute([$this->companyId]);
        return $query->fetchAll();
    }

    public function reception(int $receptionId): ?array
    {
        $headerQuery = $this->connection->prepare(
            'SELECT r.id, r.purchase_order_id, r.received_on, r.status, r.notes, p.order_number, s.business_name
             FROM purchase_receptions r
             INNER JOIN purchase_orders p ON p.id = r.purchase_order_id
             INNER JOIN suppliers s ON s.id = p.supplier_id
             WHERE r.id = ? AND r.company_id = ? LIMIT 1'
        );
        $headerQuery->execute([$receptionId, $this->companyId]);
        $header = $headerQuery->fetch();
        if (!$header) {
            return null;
        }
        $items = $this->connection->prepare(
            'SELECT ri.purchase_order_item_id, ri.quantity, poi.description, poi.quantity AS ordered_quantity,
                    poi.received_quantity, ri.unit_cost
             FROM purchase_reception_items ri
             INNER JOIN purchase_order_items poi ON poi.id = ri.purchase_order_item_id
             WHERE ri.reception_id = ? ORDER BY ri.id'
        );
        $items->execute([$receptionId]);
        $header['items'] = $items->fetchAll();
        return $header;
    }

    public function updateReception(array $input, int $userId): void
    {
        $receptionId = (int) ($input['reception_id'] ?? 0);
        $receivedOn = trim((string) ($input['received_on'] ?? ''));
        $newLines = is_array($input['items'] ?? null) ? $input['items'] : [];
        if ($receptionId <= 0 || $receivedOn === '' || $newLines === []) {
            throw new RuntimeException('La ediciÃ³n requiere una recepciÃ³n, fecha y lÃ­neas.');
        }

        $this->connection->beginTransaction();
        try {
            $receptionQuery = $this->connection->prepare('SELECT id, purchase_order_id FROM purchase_receptions WHERE id = ? AND company_id = ? FOR UPDATE');
            $receptionQuery->execute([$receptionId, $this->companyId]);
            $reception = $receptionQuery->fetch();
            if (!$reception) {
                throw new RuntimeException('La recepciÃ³n no existe o no pertenece a esta agrÃ­cola.');
            }
            $oldQuery = $this->connection->prepare('SELECT purchase_order_item_id, quantity FROM purchase_reception_items WHERE reception_id = ? FOR UPDATE');
            $oldQuery->execute([$receptionId]);
            $oldLines = [];
            foreach ($oldQuery->fetchAll() as $line) {
                $oldLines[(int) $line['purchase_order_item_id']] = (float) $line['quantity'];
            }
            $itemQuery = $this->connection->prepare('SELECT id, item_id, quantity, received_quantity, unit_price FROM purchase_order_items WHERE id = ? AND purchase_order_id = ? FOR UPDATE');
            $changes = [];
            foreach ($newLines as $lineId => $quantity) {
                $quantity = (float) $quantity;
                if ($quantity < 0) {
                    throw new RuntimeException('Las cantidades no pueden ser negativas.');
                }
                $itemQuery->execute([(int) $lineId, (int) $reception['purchase_order_id']]);
                $line = $itemQuery->fetch();
                if (!$line) {
                    throw new RuntimeException('Una lÃ­nea no pertenece a la orden de compra.');
                }
                $available = (float) $line['quantity'] - (float) $line['received_quantity'] + ($oldLines[(int) $lineId] ?? 0);
                if ($quantity > $available) {
                    throw new RuntimeException('Una cantidad editada supera el saldo disponible.');
                }
                $changes[(int) $lineId] = ['quantity' => $quantity, 'item' => $line];
            }
            foreach ($oldLines as $lineId => $oldQuantity) {
                if (!isset($changes[$lineId])) {
                    $changes[$lineId] = ['quantity' => 0.0, 'item' => null];
                    $itemQuery->execute([$lineId, (int) $reception['purchase_order_id']]);
                    $changes[$lineId]['item'] = $itemQuery->fetch();
                }
            }
            $this->connection->prepare('DELETE FROM inventory_movements WHERE company_id = ? AND reference = ?')->execute([$this->companyId, 'RECEPCION-' . $receptionId]);
            $this->connection->prepare('DELETE FROM purchase_reception_items WHERE reception_id = ?')->execute([$receptionId]);
            $updateOrderItem = $this->connection->prepare('UPDATE purchase_order_items SET received_quantity = received_quantity - ? + ? WHERE id = ?');
            $insertReceptionItem = $this->connection->prepare('INSERT INTO purchase_reception_items (reception_id, purchase_order_item_id, item_id, quantity, unit_cost) VALUES (?, ?, ?, ?, ?)');
            $insertMovement = $this->connection->prepare('INSERT INTO inventory_movements (company_id, item_id, season_id, movement_type, quantity, unit_cost, movement_date, reference, created_by) VALUES (?, ?, ?, \'IN\', ?, ?, ?, ?, ?)');
            $order = $this->connection->prepare('SELECT season_id FROM purchase_orders WHERE id = ? AND company_id = ?');
            $order->execute([(int) $reception['purchase_order_id'], $this->companyId]);
            $seasonId = $order->fetchColumn();
            $receivedAny = false;
            foreach ($changes as $lineId => $change) {
                $item = $change['item'];
                $quantity = $change['quantity'];
                $updateOrderItem->execute([$oldLines[$lineId] ?? 0, $quantity, $lineId]);
                if ($quantity <= 0) {
                    continue;
                }
                $insertReceptionItem->execute([$receptionId, $lineId, $item['item_id'] ?: null, $quantity, $item['unit_price']]);
                if ($item['item_id']) {
                    $insertMovement->execute([$this->companyId, (int) $item['item_id'], $seasonId ?: null, $quantity, $item['unit_price'], $receivedOn, 'RECEPCION-' . $receptionId, $userId]);
                }
                $receivedAny = true;
            }
            if (!$receivedAny) {
                throw new RuntimeException('La recepciÃ³n debe conservar al menos una cantidad positiva.');
            }
            $this->connection->prepare('UPDATE purchase_receptions SET received_on = ?, notes = ? WHERE id = ? AND company_id = ?')->execute([$receivedOn, trim((string) ($input['notes'] ?? '')) ?: null, $receptionId, $this->companyId]);
            $this->refreshOrderStatus((int) $reception['purchase_order_id']);
            $this->connection->commit();
            (new AuditLog($this->connection, $this->companyId))->record($userId, 'UPDATE', 'purchase_receptions', $receptionId);
        } catch (\Throwable $exception) {
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }
            throw $exception;
        }
    }

    public function deleteReception(int $receptionId, int $userId): void
    {
        if ($receptionId <= 0) {
            throw new RuntimeException('La recepciÃ³n seleccionada no es vÃ¡lida.');
        }
        $this->connection->beginTransaction();
        try {
            $query = $this->connection->prepare('SELECT id, purchase_order_id FROM purchase_receptions WHERE id = ? AND company_id = ? FOR UPDATE');
            $query->execute([$receptionId, $this->companyId]);
            $reception = $query->fetch();
            if (!$reception) {
                throw new RuntimeException('La recepciÃ³n no existe o no pertenece a esta agrÃ­cola.');
            }
            $lines = $this->connection->prepare('SELECT purchase_order_item_id, quantity FROM purchase_reception_items WHERE reception_id = ? FOR UPDATE');
            $lines->execute([$receptionId]);
            $update = $this->connection->prepare('UPDATE purchase_order_items SET received_quantity = received_quantity - ? WHERE id = ? AND purchase_order_id = ?');
            foreach ($lines->fetchAll() as $line) {
                $update->execute([(float) $line['quantity'], (int) $line['purchase_order_item_id'], (int) $reception['purchase_order_id']]);
            }
            $this->connection->prepare('DELETE FROM inventory_movements WHERE company_id = ? AND reference = ?')->execute([$this->companyId, 'RECEPCION-' . $receptionId]);
            $this->connection->prepare('DELETE FROM purchase_receptions WHERE id = ? AND company_id = ?')->execute([$receptionId, $this->companyId]);
            $this->refreshOrderStatus((int) $reception['purchase_order_id']);
            $this->connection->commit();
            (new AuditLog($this->connection, $this->companyId))->record($userId, 'DELETE', 'purchase_receptions', $receptionId);
        } catch (\Throwable $exception) {
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }
            throw $exception;
        }
    }

    private function refreshOrderStatus(int $orderId): void
    {
        $remaining = $this->connection->prepare('SELECT COUNT(*) FROM purchase_order_items WHERE purchase_order_id = ? AND received_quantity < quantity');
        $remaining->execute([$orderId]);
        $received = $this->connection->prepare('SELECT COALESCE(SUM(received_quantity), 0) FROM purchase_order_items WHERE purchase_order_id = ?');
        $received->execute([$orderId]);
        $status = (int) $remaining->fetchColumn() === 0 ? 'RECEIVED' : ((float) $received->fetchColumn() > 0 ? 'PARTIAL' : 'SENT');
        $this->connection->prepare('UPDATE purchase_orders SET status = ? WHERE id = ? AND company_id = ?')->execute([$status, $orderId, $this->companyId]);
    }

    public function receptionOptions(): array
    {
        $query = $this->connection->prepare(
            'SELECT p.id AS purchase_order_id, p.order_number, p.order_date, p.status, s.business_name,
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
        $items = $this->connection->prepare('SELECT id, sku, name FROM inventory_items WHERE company_id = ? AND active = 1 ORDER BY name');
        $items->execute([$this->companyId]);
        return ['supplier_options' => $suppliers->fetchAll(), 'season_options' => $seasons->fetchAll(), 'farm_options' => $farms->fetchAll(), 'item_options' => $items->fetchAll()];
    }
}
