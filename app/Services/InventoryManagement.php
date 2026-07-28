<?php

declare(strict_types=1);

namespace CampoSur\Services;

use PDO;
use RuntimeException;

final class InventoryManagement
{
    public function __construct(private readonly PDO $connection, private readonly int $companyId)
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
        $query = $this->connection->prepare('SELECT m.id, m.movement_date, m.movement_type, m.quantity, m.unit_cost, i.name AS item_name, i.unit FROM inventory_movements m INNER JOIN inventory_items i ON i.id = m.item_id WHERE m.company_id = ? ORDER BY m.movement_date DESC, m.id DESC LIMIT 40');
        $query->execute([$this->companyId]);
        return $query->fetchAll();
    }

    public function createItem(array $input): void
    {
        foreach (['sku', 'name', 'unit', 'category'] as $field) {
            if (trim((string) ($input[$field] ?? '')) === '') {
                throw new RuntimeException('Completa los datos del insumo.');
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
        if (!(new CatalogLookup($this->connection, $this->companyId))->exists('INVENTORY_MOVEMENT_TYPE', (string) $input['movement_type'])) {
            throw new RuntimeException('El tipo de movimiento no está habilitado.');
        }
        $this->execute('INSERT INTO inventory_movements (company_id, item_id, movement_type, quantity, unit_cost, movement_date, reference, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)', [$this->companyId, (int) $input['item_id'], $input['movement_type'], $input['quantity'], $input['unit_cost'] ?: 0, $input['movement_date'], trim($input['reference']) ?: null, $userId]);
    }

    public function itemOptions(): array
    {
        $query = $this->connection->prepare('SELECT id, sku, name, unit FROM inventory_items WHERE company_id = ? AND active = 1 ORDER BY name');
        $query->execute([$this->companyId]);
        return $query->fetchAll();
    }

    private function belongs(string $table, mixed $id): void
    {
        if ($table !== 'inventory_items') {
            throw new RuntimeException('Referencia no válida.');
        }
        $query = $this->connection->prepare('SELECT id FROM inventory_items WHERE id = ? AND company_id = ?',);
        $query->execute([(int) $id, $this->companyId]);
        if (!$query->fetchColumn()) {
            throw new RuntimeException('El insumo seleccionado no pertenece a esta agrícola.');
        }
    }

    private function execute(string $sql, array $params): void
    {
        $this->connection->prepare($sql)->execute($params);
    }
}
