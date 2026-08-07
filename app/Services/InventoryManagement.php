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
        $productsQuery = $this->connection->prepare('SELECT id, sku, name, category, subcategory, unit, minimum_stock FROM inventory_items WHERE company_id = ? AND active = 1 ORDER BY name, sku');
        $productsQuery->execute([$this->companyId]);
        $products = $productsQuery->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $categoryLabels = [];
        foreach ($this->catalogValues('INVENTORY_CATEGORY') as $value) {
            $categoryLabels[(string) $value['code']] = (string) $value['label'];
        }
        $subcategoryLabels = [];
        foreach ($this->catalogValues('INVENTORY_SUBCATEGORY') as $value) {
            $subcategoryLabels[(string) $value['code']] = (string) $value['label'];
        }
        $unitLabels = [];
        foreach ($this->catalogValues('MEASUREMENT_UNIT') as $value) {
            $unitLabels[(string) $value['code']] = (string) $value['label'];
        }

        $locationsQuery = $this->connection->prepare('SELECT m.item_id, m.warehouse_id, COALESCE(w.name, "Sin bodega") AS warehouse_name, COALESCE(f.name, "Sin campo") AS farm_name, SUM(CASE WHEN m.movement_type = "IN" THEN m.quantity WHEN m.movement_type = "OUT" THEN -m.quantity ELSE m.quantity END) AS stock FROM inventory_movements m LEFT JOIN warehouses w ON w.id = m.warehouse_id AND w.company_id = m.company_id LEFT JOIN farms f ON f.id = w.farm_id WHERE m.company_id = ? GROUP BY m.item_id, m.warehouse_id');
        $locationsQuery->execute([$this->companyId]);
        $locationsByItem = [];
        foreach ($locationsQuery->fetchAll(PDO::FETCH_ASSOC) ?: [] as $location) {
            $location = array_change_key_case($location, CASE_LOWER);
            $locationsByItem[(int) $location['item_id']][] = $location;
        }

        $result = [];
        foreach ($products as $product) {
            $product = array_change_key_case($product, CASE_LOWER);
            $itemId = (int) $product['id'];
            $locations = $locationsByItem[$itemId] ?? [[
                'warehouse_id' => 0,
                'warehouse_name' => 'Sin bodega',
                'farm_name' => 'Sin campo',
                'stock' => 0,
            ]];
            foreach ($locations as $location) {
                $category = (string) ($product['category'] ?? '');
                $subcategory = (string) ($product['subcategory'] ?? '');
                $unit = (string) ($product['unit'] ?? '');
                $result[] = [
                    'id' => $itemId,
                    'sku' => (string) $product['sku'],
                    'name' => (string) $product['name'],
                    'category' => $category,
                    'category_label' => $categoryLabels[$category] ?? $category,
                    'subcategory' => $subcategory,
                    'subcategory_label' => $subcategoryLabels[$subcategory] ?? $subcategory,
                    'unit' => $unit,
                    'unit_label' => $unitLabels[$unit] ?? $unit,
                    'minimum_stock' => (float) $product['minimum_stock'],
                    'stock' => (float) $location['stock'],
                    'warehouse_id' => (int) ($location['warehouse_id'] ?? 0),
                    'warehouse_name' => (string) $location['warehouse_name'],
                    'farm_name' => (string) $location['farm_name'],
                ];
            }
        }

        return $result;
    }

    public function movements(): array
    {
        $query = $this->connection->prepare('SELECT m.id, m.movement_date, m.movement_type, m.quantity, m.unit_cost, i.name AS item_name, i.unit, COALESCE(w.name, "Sin bodega") AS warehouse_name, COALESCE(f.name, "Sin fundo") AS farm_name, COALESCE(b.name, "Sin cuartel") AS block_name, COALESCE(c.name, "Sin centro") AS center_name, COALESCE(s.name, "Sin temporada") AS season_name FROM inventory_movements m INNER JOIN inventory_items i ON i.id = m.item_id LEFT JOIN warehouses w ON w.id = m.warehouse_id AND w.company_id = m.company_id LEFT JOIN farms f ON f.id = m.farm_id AND f.company_id = m.company_id LEFT JOIN blocks b ON b.id = m.block_id AND b.company_id = m.company_id LEFT JOIN cost_centers c ON c.id = m.cost_center_id AND c.company_id = m.company_id LEFT JOIN seasons s ON s.id = m.season_id AND s.company_id = m.company_id WHERE m.company_id = ? ORDER BY m.movement_date DESC, m.id DESC LIMIT 40');
        $query->execute([$this->companyId]);
        return $query->fetchAll();
    }

    public function assignmentOptions(): array
    {
        return [
            'seasons' => $this->fetch('SELECT id, name FROM seasons WHERE company_id = ? AND active = 1 ORDER BY starts_on DESC'),
            'centers' => $this->fetch('SELECT id, name, category FROM cost_centers WHERE company_id = ? AND active = 1 ORDER BY category, name'),
            'farms' => $this->fetch('SELECT id, name FROM farms WHERE company_id = ? AND active = 1 ORDER BY name'),
            'blocks' => $this->fetch('SELECT id, farm_id, code, name FROM blocks WHERE company_id = ? AND active = 1 ORDER BY name, code'),
        ];
    }

    public function createItem(array $input): void
    {
        foreach (['sku', 'name', 'unit', 'category'] as $field) {
            if (trim((string) ($input[$field] ?? '')) === '') {
                throw new RuntimeException('Por favor, completa los datos del insumo.');
            }
        }
        $this->validateCatalogValues($input);
        $itemId = $this->execute('INSERT INTO inventory_items (company_id, sku, name, category, subcategory, unit, minimum_stock) VALUES (?, ?, ?, ?, ?, ?, ?)', [$this->companyId, strtoupper(trim($input['sku'])), trim($input['name']), strtoupper(trim($input['category'])), $this->optionalCode($input['subcategory'] ?? null), strtoupper(trim($input['unit'])), $input['minimum_stock'] ?: 0]);

        // Crear movimiento inicial si se proporciona stock inicial
        if (!empty($input['initial_stock']) && (float) $input['initial_stock'] > 0) {
            $warehouseId = (int) ($input['warehouse_id'] ?? 0);
            if ($warehouseId > 0) {
                $this->belongsWarehouse($warehouseId);
            }

            $query = $this->connection->prepare('SELECT LASTVAL() as id');
            $query->execute();
            $result = $query->fetch();
            $newItemId = $result ? (int) $result['id'] : null;

            if ($newItemId) {
                $this->execute('INSERT INTO inventory_movements (company_id, item_id, warehouse_id, movement_type, quantity, unit_cost, movement_date, reference, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)', [$this->companyId, $newItemId, $warehouseId > 0 ? $warehouseId : null, 'IN', $input['initial_stock'], $input['unit_cost'] ?: 0, date('Y-m-d'), 'Stock inicial', 0]);
            }
        }
    }

    public function updateItem(array $input): void
    {
        $id = (int) ($input['item_id'] ?? 0);
        if ($id <= 0) {
            throw new RuntimeException('El producto seleccionado no es válido.');
        }
        $this->belongs('inventory_items', $id);
        foreach (['sku', 'name', 'unit', 'category'] as $field) {
            if (trim((string) ($input[$field] ?? '')) === '') {
                throw new RuntimeException('Por favor, completa los datos del producto.');
            }
        }
        $this->validateCatalogValues($input);
        $this->execute('UPDATE inventory_items SET sku = ?, name = ?, category = ?, subcategory = ?, unit = ?, minimum_stock = ? WHERE id = ? AND company_id = ?', [strtoupper(trim($input['sku'])), trim($input['name']), strtoupper(trim($input['category'])), $this->optionalCode($input['subcategory'] ?? null), strtoupper(trim($input['unit'])), $input['minimum_stock'] ?: 0, $id, $this->companyId]);
    }

    public function deleteItem(int $itemId): void
    {
        if ($itemId <= 0) {
            throw new RuntimeException('El producto seleccionado no es válido.');
        }
        $this->belongs('inventory_items', $itemId);
        $this->execute('UPDATE inventory_items SET active = 0 WHERE id = ? AND company_id = ?', [$itemId, $this->companyId]);
    }

    public function categories(): array
    {
        return $this->catalogValues('INVENTORY_CATEGORY');
    }

    public function subcategories(): array
    {
        $query = $this->connection->prepare('SELECT v.code, v.label, v.metadata_json FROM system_catalog_values v INNER JOIN system_catalogs c ON c.id = v.catalog_id WHERE c.code = ? AND c.active = 1 AND v.active = 1 AND (v.company_id IS NULL OR v.company_id = ?) ORDER BY v.sort_order, v.label');
        $query->execute(['INVENTORY_SUBCATEGORY', $this->companyId]);

        $subcategories = [];
        foreach ($query->fetchAll(PDO::FETCH_ASSOC) ?: [] as $subcategory) {
            $metadata = json_decode((string) ($subcategory['metadata_json'] ?? ''), true);
            $category = is_array($metadata) ? (string) ($metadata['category'] ?? '') : '';
            $key = strtolower(trim((string) $subcategory['label'])) . '|' . strtoupper($category);
            $subcategories[$key] ??= $subcategory;
        }

        return array_values($subcategories);
    }

    public function units(): array
    {
        return $this->catalogValues('MEASUREMENT_UNIT');
    }

    public function warehouses(): array
    {
        $query = $this->connection->prepare('SELECT id, name FROM warehouses WHERE company_id = ? AND active = 1 ORDER BY name');
        $query->execute([$this->companyId]);
        return $query->fetchAll();
    }

    public function createMovement(array $input, int $userId): void
    {
        if (empty($input['item_id']) || empty($input['movement_type']) || empty($input['quantity']) || empty($input['movement_date']) || empty($input['season_id']) || empty($input['cost_center_id'])) {
            throw new RuntimeException('Completa el producto, temporada, centro de costo, tipo y fecha del movimiento.');
        }
        if (!is_numeric($input['quantity']) || (float) $input['quantity'] <= 0) {
            throw new RuntimeException('La cantidad debe ser mayor que cero.');
        }
        $this->belongs('inventory_items', $input['item_id']);
        $warehouseId = (int) ($input['warehouse_id'] ?? 0);
        if ($warehouseId > 0) {
            $this->belongsWarehouse($warehouseId);
        }
        $seasonId = (int) $input['season_id'];
        $costCenterId = (int) $input['cost_center_id'];
        $farmId = (int) ($input['farm_id'] ?? 0);
        $blockId = (int) ($input['block_id'] ?? 0);
        $this->belongs('seasons', $seasonId);
        $this->belongs('cost_centers', $costCenterId);
        if ($farmId > 0) {
            $this->belongs('farms', $farmId);
        }
        if ($blockId > 0) {
            $this->belongs('blocks', $blockId);
        }
        if ($input['movement_type'] === 'OUT' && $farmId <= 0 && $blockId <= 0) {
            throw new RuntimeException('Toda salida de producto debe asignarse a un fundo o cuartel.');
        }
        if (!(new CatalogLookup($this->connection, $this->companyId))->exists('INVENTORY_MOVEMENT_TYPE', (string) $input['movement_type'])) {
            throw new RuntimeException('El tipo de movimiento no está habilitado.');
        }
        $this->execute('INSERT INTO inventory_movements (company_id, item_id, warehouse_id, season_id, cost_center_id, farm_id, block_id, movement_type, quantity, unit_cost, movement_date, reference, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [$this->companyId, (int) $input['item_id'], $warehouseId > 0 ? $warehouseId : null, $seasonId, $costCenterId, $farmId > 0 ? $farmId : null, $blockId > 0 ? $blockId : null, $input['movement_type'], $input['quantity'], $input['unit_cost'] ?: 0, $input['movement_date'], trim($input['reference']) ?: null, $userId]);
    }

    public function itemOptions(): array
    {
        $query = $this->connection->prepare('SELECT id, sku, name, unit FROM inventory_items WHERE company_id = ? AND active = 1 ORDER BY name');
        $query->execute([$this->companyId]);
        return $query->fetchAll();
    }

    private function validateCatalogValues(array $input): void
    {
        $catalogs = new CatalogLookup($this->connection, $this->companyId);
        if (!$catalogs->exists('INVENTORY_CATEGORY', (string) $input['category']) || !$catalogs->exists('MEASUREMENT_UNIT', (string) $input['unit'])) {
            throw new RuntimeException('La categoría o unidad del producto no está habilitada.');
        }
        if ($this->optionalCode($input['subcategory'] ?? null) !== null && !$catalogs->exists('INVENTORY_SUBCATEGORY', (string) $input['subcategory'])) {
            throw new RuntimeException('La subcategoría seleccionada no está habilitada.');
        }
    }

    private function catalogValues(string $catalogCode): array
    {
        $query = $this->connection->prepare('SELECT v.code, v.label FROM system_catalog_values v INNER JOIN system_catalogs c ON c.id = v.catalog_id WHERE c.code = ? AND c.active = 1 AND v.active = 1 AND (v.company_id IS NULL OR v.company_id = ?) ORDER BY v.sort_order, v.label');
        $query->execute([$catalogCode, $this->companyId]);
        return $query->fetchAll();
    }

    private function optionalCode(mixed $value): ?string
    {
        $value = trim((string) $value);
        return $value === '' ? null : strtoupper($value);
    }

    private function belongsWarehouse(int $warehouseId): void
    {
        $query = $this->connection->prepare('SELECT id FROM warehouses WHERE id = ? AND company_id = ? AND active = 1');
        $query->execute([$warehouseId, $this->companyId]);
        if (!$query->fetchColumn()) {
            throw new RuntimeException('La bodega seleccionada no pertenece a esta agrícola.');
        }
    }

    private function belongs(string $table, mixed $id): void
    {
        $allowed = ['inventory_items', 'seasons', 'cost_centers', 'farms', 'blocks'];
        if (!in_array($table, $allowed, true)) {
            throw new RuntimeException('Referencia no válida.');
        }
        $query = $this->connection->prepare('SELECT id FROM ' . $table . ' WHERE id = ? AND company_id = ?');
        $query->execute([(int) $id, $this->companyId]);
        if (!$query->fetchColumn()) {
            throw new RuntimeException('La referencia seleccionada no pertenece a esta agrícola.');
        }
    }

}
