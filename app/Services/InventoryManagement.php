<?php

declare(strict_types=1);

namespace AgroPCC\Services;

use PDO;
use RuntimeException;

final class InventoryManagement extends BaseService
{
    public function __construct(protected readonly PDO $connection, protected readonly int $companyId)
    {
    }

    public function items(): array
    {
        $query = $this->connection->prepare('SELECT i.id, i.sku, i.name, i.category, i.unit, i.minimum_stock, COALESCE(SUM(CASE WHEN m.movement_type = "IN" THEN m.quantity WHEN m.movement_type = "OUT" THEN -m.quantity ELSE m.quantity END), 0) AS stock FROM inventory_items i LEFT JOIN inventory_movements m ON m.item_id = i.id AND m.company_id = i.company_id WHERE i.company_id = ? GROUP BY i.id ORDER BY i.name');
        $query->execute([$this->companyId]);
        return $query->fetchAll();
    }

    public function movements(): array
    {
        $query = $this->connection->prepare('SELECT m.id, m.movement_date, m.movement_type, m.quantity, m.unit_cost, m.reference, i.name AS item_name, i.unit, w.name AS warehouse_name FROM inventory_movements m INNER JOIN inventory_items i ON i.id = m.item_id LEFT JOIN warehouses w ON w.id = m.warehouse_id WHERE m.company_id = ? ORDER BY m.movement_date DESC, m.id DESC LIMIT 40');
        $query->execute([$this->companyId]);
        return $query->fetchAll();
    }

    public function createItem(array $input): void
    {
        foreach (['sku', 'name', 'unit', 'category'] as $field) {
            if (trim((string) ($input[$field] ?? '')) === '') {
                throw new RuntimeException('Por favor, completa los datos del insumo.');
            }
        }
        $catalogs = new CatalogLookup($this->connection, $this->companyId);
        if (!$catalogs->exists('INVENTORY_CATEGORY', (string) $input['category']) || !$catalogs->exists('MEASUREMENT_UNIT', (string) $input['unit'])) {
            throw new RuntimeException('La categoría o unidad del insumo no está habilitada.');
        }
        $this->execute('INSERT INTO inventory_items (company_id, sku, name, category, unit, minimum_stock) VALUES (?, ?, ?, ?, ?, ?)', [$this->companyId, strtoupper(trim($input['sku'])), trim($input['name']), strtoupper(trim($input['category'])), strtoupper(trim($input['unit'])), $input['minimum_stock'] ?: 0]);
    }

    public function createMovement(array $input, int $userId): void
    {
        if (empty($input['item_id']) || empty($input['movement_type']) || empty($input['quantity']) || empty($input['movement_date'])) {
            throw new RuntimeException('Completa los datos del movimiento.');
        }
        if (!is_numeric($input['quantity']) || (float) $input['quantity'] <= 0) {
            throw new RuntimeException('La cantidad debe ser mayor que cero.');
        }
        $this->belongs('inventory_items', $input['item_id']);
        if (!empty($input['warehouse_id'])) {
            $this->belongs('warehouses', $input['warehouse_id']);
        }
        if (!(new CatalogLookup($this->connection, $this->companyId))->exists('INVENTORY_MOVEMENT_TYPE', (string) $input['movement_type'])) {
            throw new RuntimeException('El tipo de movimiento no está habilitado.');
        }
        $this->execute('INSERT INTO inventory_movements (company_id, item_id, warehouse_id, movement_type, quantity, unit_cost, movement_date, reference, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)', [$this->companyId, (int) $input['item_id'], $input['warehouse_id'] ?: null, $input['movement_type'], $input['quantity'], $input['unit_cost'] ?: 0, $input['movement_date'], trim($input['reference']) ?: null, $userId]);
    }

    public function itemOptions(): array
    {
        $query = $this->connection->prepare('SELECT id, sku, name, unit FROM inventory_items WHERE company_id = ? AND active = 1 ORDER BY name');
        $query->execute([$this->companyId]);
        return $query->fetchAll();
    }

    public function options(): array
    {
        return [
            'categories' => $this->catalogValues('INVENTORY_CATEGORY'),
            'units' => $this->catalogValues('MEASUREMENT_UNIT'),
            'warehouses' => $this->fetch('SELECT id, name FROM warehouses WHERE company_id = ? AND active = 1 ORDER BY name'),
        ];
    }

    private function catalogValues(string $catalogCode): array
    {
        return $this->fetchRows(
            'SELECT v.code, v.label
             FROM system_catalog_values v
             INNER JOIN system_catalogs c ON c.id = v.catalog_id
             WHERE c.code = ? AND c.active = 1 AND v.active = 1
               AND (v.company_id IS NULL OR v.company_id = ?)
             ORDER BY v.sort_order, v.label',
            [$catalogCode, $this->companyId],
        );
    }

    private function belongs(string $table, mixed $id): void
    {
        if (!in_array($table, ['inventory_items', 'warehouses'], true)) {
            throw new RuntimeException('Referencia no válida.');
        }
        $query = $this->connection->prepare('SELECT id FROM ' . $table . ' WHERE id = ? AND company_id = ?' . ($table === 'warehouses' ? ' AND active = 1' : ''));
        $query->execute([(int) $id, $this->companyId]);
        if (!$query->fetchColumn()) {
            throw new RuntimeException('El insumo seleccionado no pertenece a esta agrícola.');
        }
    }

}
