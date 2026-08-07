<?php
$items = $inventoryItems ?? [];
$editItem = null;
$editItemId = (int) ($_GET['edit_item'] ?? 0);
foreach ($items as $item) {
    if ((int) ($item['id'] ?? 0) === $editItemId) {
        $editItem = $item;
        break;
    }
}
$isEditingItem = $editItem !== null && $editItemId > 0;
$editingItemId = $isEditingItem ? (int) $editItem['id'] : $editItemId;
$inventoryView = (string) ($_GET['view'] ?? '');
$showItemForm = $isEditingItem || $inventoryView === 'item-form';
$showMovementForm = $inventoryView === 'movement-form';
$inventoryTab = $showItemForm ? 'products' : ($showMovementForm ? 'movements' : 'summary');
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inventario | Sistema de Gestión Agrícola PCCURICO</title>
    <link rel="stylesheet" href="assets/css/app.css">
</head>
<body class="admin-page">
<main class="admin-shell">
    <?php require dirname(__DIR__) . '/Views/partials/module-navigation.php'; ?>
    <section class="module-content module-v2 inventory-v2">
        <header class="inventory-page-header">
            <div>
                <p class="eyebrow">Bodega</p>
                <h1>Inventario</h1>
                <p class="lead-text">Administra productos, existencias y movimientos desde un solo lugar.</p>
            </div>
            <div class="inventory-summary-stats" aria-label="Resumen de inventario">
                <div class="inventory-summary-stat"><span>Productos</span><strong><?= count($items) ?></strong></div>
                <div class="inventory-summary-stat"><span>Movimientos</span><strong><?= count($movements) ?></strong></div>
            </div>
        </header>

        <nav class="inventory-tabs" aria-label="Secciones de inventario">
            <a class="inventory-tab <?= $inventoryTab === 'summary' ? 'is-active' : '' ?>" href="<?= htmlspecialchars(module_url('inventory'), ENT_QUOTES, 'UTF-8') ?>">Resumen</a>
            <a class="inventory-tab <?= $inventoryTab === 'products' ? 'is-active' : '' ?>" href="<?= htmlspecialchars(module_url('inventory', ['view' => 'item-form']), ENT_QUOTES, 'UTF-8') ?>">Productos</a>
            <a class="inventory-tab <?= $inventoryTab === 'movements' ? 'is-active' : '' ?>" href="<?= htmlspecialchars(module_url('inventory', ['view' => 'movement-form']), ENT_QUOTES, 'UTF-8') ?>">Movimientos</a>
            <a class="inventory-tab" href="<?= htmlspecialchars(module_url('catalogs', ['catalog' => 'INVENTORY_CATEGORY']), ENT_QUOTES, 'UTF-8') ?>">Categorías</a>
            <a class="inventory-tab" href="<?= htmlspecialchars(module_url('catalogs', ['catalog' => 'INVENTORY_SUBCATEGORY']), ENT_QUOTES, 'UTF-8') ?>">Subcategorías</a>
        </nav>

        <?php if ($error): ?><div class="setup-error inventory-feedback"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
        <?php if ($success): ?><div class="setup-success inventory-feedback"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>

        <?php if ($showItemForm): ?>
        <section class="inventory-form-card section-card">
            <div class="inventory-section-heading"><div><p class="eyebrow">Productos</p><h2><?= $isEditingItem ? 'Editar producto' : 'Nuevo producto' ?></h2><p>Completa los datos para mantener actualizado el catálogo de bodega.</p></div></div>
            <form method="post" action="<?= htmlspecialchars(module_url('inventory'), ENT_QUOTES, 'UTF-8') ?>" class="inventory-inline-form">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="action" value="<?= $editItemId > 0 ? 'update_item' : 'create_item' ?>">
                <?php if ($isEditingItem && $editingItemId > 0): ?><input type="hidden" name="item_id" value="<?= $editingItemId ?>"><?php endif; ?>
                <div class="inventory-field-grid inventory-product-fields">
                    <input type="hidden" id="all-skus" value="<?= htmlspecialchars(json_encode(array_map(function($item) { return $item['sku']; }, $inventoryItems)), ENT_QUOTES, 'UTF-8') ?>">
                    <label for="product_sku">SKU<input id="product_sku" name="sku" required placeholder="INS-0001" value="<?= htmlspecialchars((string) ($editItem['sku'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></label>
                    <label for="product_name">Nombre<input id="product_name" name="name" required placeholder="Fertilizante granulado" value="<?= htmlspecialchars((string) ($editItem['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></label>
                    <label for="product_category">Categoría<select id="product_category" name="category" required><option value="">Selecciona una categoría</option><?php foreach ($categories as $category): ?><option value="<?= htmlspecialchars($category['code'], ENT_QUOTES, 'UTF-8') ?>" <?= (string) ($editItem['category'] ?? '') === (string) $category['code'] ? 'selected' : '' ?>><?= htmlspecialchars($category['label'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
                    <label for="product_subcategory">Subcategoría<select id="product_subcategory" name="subcategory"><option value="">Sin subcategoría</option><?php foreach ($subcategories as $subcategory): ?><option value="<?= htmlspecialchars($subcategory['code'], ENT_QUOTES, 'UTF-8') ?>" <?= (string) ($editItem['subcategory'] ?? '') === (string) $subcategory['code'] ? 'selected' : '' ?>><?= htmlspecialchars($subcategory['label'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
                    <label for="product_unit">Unidad<select id="product_unit" name="unit" required><option value="">Selecciona una unidad</option><?php foreach ($units as $unit): ?><option value="<?= htmlspecialchars($unit['code'], ENT_QUOTES, 'UTF-8') ?>" <?= (string) ($editItem['unit'] ?? '') === (string) $unit['code'] ? 'selected' : '' ?>><?= htmlspecialchars($unit['label'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
                    <label for="product_minimum_stock">Stock mínimo<input id="product_minimum_stock" type="number" name="minimum_stock" min="0" step="0.001" value="<?= htmlspecialchars((string) ($editItem['minimum_stock'] ?? 0), ENT_QUOTES, 'UTF-8') ?>"></label>
                    <label for="product_warehouse">Bodega<select id="product_warehouse" name="warehouse_id"><option value="">Sin bodega</option><?php foreach ($warehouses as $warehouse): ?><option value="<?= (int) $warehouse['id'] ?>"><?= htmlspecialchars($warehouse['name'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
                    <label for="product_initial_stock">Stock inicial<input id="product_initial_stock" type="number" name="initial_stock" min="0" step="0.001" value="0"></label>
                    <label for="product_unit_cost">Costo unitario<input id="product_unit_cost" type="number" name="unit_cost" min="0" step="0.01" value="0"></label>
                </div>
                <div class="inventory-form-actions"><button class="primary-button" type="submit"><?= $isEditingItem ? 'Guardar cambios' : 'Crear producto' ?></button></div>
            </form>
        </section>
        <?php endif; ?>

        <?php if ($showMovementForm): ?>
        <section class="inventory-form-card section-card">
            <div class="inventory-section-heading"><div><p class="eyebrow">Movimientos</p><h2>Registrar movimiento</h2><p>Registra entradas, salidas y ajustes de existencias.</p></div></div>
            <form method="post" action="<?= htmlspecialchars(module_url('inventory'), ENT_QUOTES, 'UTF-8') ?>" class="inventory-inline-form">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="action" value="create_movement">
                <div class="inventory-field-grid inventory-movement-fields">
                    <label for="movement_item_id">Producto<select id="movement_item_id" name="item_id" required><?php foreach ($item_options as $item): ?><option value="<?= (int) $item['id'] ?>"><?= htmlspecialchars($item['sku'] . ' · ' . $item['name'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
                    <label for="movement_warehouse_id">Bodega<select id="movement_warehouse_id" name="warehouse_id"><option value="">Sin bodega</option><?php foreach ($warehouses as $warehouse): ?><option value="<?= (int) $warehouse['id'] ?>"><?= htmlspecialchars($warehouse['name'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
                    <label for="movement_season_id">Temporada<select id="movement_season_id" name="season_id" required><option value="">Selecciona una temporada</option><?php foreach ($seasons as $season): ?><option value="<?= (int) $season['id'] ?>"><?= htmlspecialchars($season['name'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
                    <label for="movement_cost_center_id">Centro de costo<select id="movement_cost_center_id" name="cost_center_id" required><option value="">Selecciona un centro</option><?php foreach ($centers as $center): ?><option value="<?= (int) $center['id'] ?>"><?= htmlspecialchars($center['name'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
                    <label for="movement_farm_id">Fundo<select id="movement_farm_id" name="farm_id"><option value="">Sin fundo</option><?php foreach ($farms as $farm): ?><option value="<?= (int) $farm['id'] ?>"><?= htmlspecialchars($farm['name'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
                    <label for="movement_block_id">Cuartel<select id="movement_block_id" name="block_id"><option value="">Sin cuartel</option><?php foreach ($blocks as $block): ?><option value="<?= (int) $block['id'] ?>"><?= htmlspecialchars($block['code'] . ' · ' . $block['name'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
                    <label for="movement_type">Tipo<select id="movement_type" name="movement_type"><option value="IN">Entrada</option><option value="OUT">Salida / consumo</option><option value="ADJUSTMENT">Ajuste</option></select></label>
                    <label for="movement_quantity">Cantidad<input id="movement_quantity" type="number" name="quantity" min="0.001" step="0.001" required></label>
                    <label for="movement_unit_cost">Costo unitario<input id="movement_unit_cost" type="number" name="unit_cost" min="0" step="0.01"></label>
                    <label for="movement_date">Fecha<input id="movement_date" type="date" name="movement_date" required value="<?= date('Y-m-d') ?>"></label>
                    <label for="movement_reference">Referencia / motivo<input id="movement_reference" name="reference" maxlength="120" required placeholder="Ej.: Aplicación fertilizante cuartel 5A"></label>
                </div>
                <p class="form-hint">Las salidas deben indicar temporada, centro de costo y fundo o cuartel donde se utilizó el producto.</p><div class="inventory-form-actions"><button class="primary-button" type="submit">Registrar movimiento</button></div>
            </form>
        </section>
        <?php endif; ?>

        <?php if (!$showMovementForm): ?>
        <section class="inventory-data-card section-card">
            <div style="display: flex; gap: 12px; margin-bottom: 16px; align-items: flex-end;">
                <div style="flex: 1;">
                    <label for="inventory-search" style="display: block; font-size: 12px; margin-bottom: 4px; font-weight: 600;">Buscar por nombre</label>
                    <input type="text" id="inventory-search" placeholder="Escribe nombre..." style="width: 100%; padding: 6px 8px; border: 1px solid hsl(214 20% 88%); border-radius: 4px; font-size: 12px;">
                </div>
                <div style="flex: 0.8;">
                    <label for="inventory-category-filter" style="display: block; font-size: 12px; margin-bottom: 4px; font-weight: 600;">Categoría</label>
                    <select id="inventory-category-filter" style="width: 100%; padding: 6px 8px; border: 1px solid hsl(214 20% 88%); border-radius: 4px; font-size: 12px;">
                        <option value="">Todas</option>
                        <?php foreach ($categories as $category): ?>
                        <option value="<?= htmlspecialchars($category['code'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($category['label'], ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="flex: 0.6;">
                    <label for="inventory-per-page" style="display: block; font-size: 12px; margin-bottom: 4px; font-weight: 600;">Mostrar</label>
                    <select id="inventory-per-page" style="width: 100%; padding: 6px 8px; border: 1px solid hsl(214 20% 88%); border-radius: 4px; font-size: 12px;">
                        <option value="10">10</option>
                        <option value="50" selected>50</option>
                        <option value="100">100</option>
                        <option value="500">500</option>
                    </select>
                </div>
            </div>
            <table class="data-table" id="inventory-products-table"><thead><tr><th>SKU</th><th>Nombre</th><th>Categoría</th><th>Stock</th><th>Acciones</th></tr></thead><tbody>
                <?php if ($inventoryItems === []): ?><tr><td colspan="5">No hay productos registrados.</td></tr><?php else: foreach ($inventoryItems as $item): ?><tr data-category="<?= htmlspecialchars((string) ($item['category'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    <td><?= htmlspecialchars((string) ($item['sku'] ?? 'Sin SKU'), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars((string) ($item['name'] ?? 'Sin nombre'), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars((string) ($item['category_label'] ?? $item['category'] ?? 'Sin categoría'), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><strong><?= number_format((float) ($item['stock'] ?? 0), 2, ',', '.') ?></strong> <?= htmlspecialchars((string) ($item['unit'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="inventory-row-actions"><a class="table-action table-action-edit" href="<?= htmlspecialchars(module_url('inventory', ['edit_item' => (int) ($item['id'] ?? 0)]), ENT_QUOTES, 'UTF-8') ?>" title="Editar">Editar</a><form method="post" action="<?= htmlspecialchars(module_url('inventory'), ENT_QUOTES, 'UTF-8') ?>" class="inline-form" style="display: inline;"><input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="action" value="delete_item"><input type="hidden" name="item_id" value="<?= (int) ($item['id'] ?? 0) ?>"><button class="table-action table-action-delete" type="submit" title="Eliminar" onclick="return confirm('¿Estás seguro de que quieres eliminar este producto?');">Eliminar</button></form></td>
                </tr><?php endforeach; endif; ?>
            </tbody></table>
        </section>
        <?php endif; ?>

        <?php if (!$showItemForm): ?>
        <section class="inventory-data-card section-card">
            <table class="data-table"><thead><tr><th>Producto</th><th>Tipo</th><th>Cantidad</th><th>Asignación</th><th>Costo</th></tr></thead><tbody>
                <?php if ($movements === []): ?><tr><td colspan="5">No hay movimientos registrados.</td></tr><?php else: foreach ($movements as $movement): ?><tr>
                    <td><strong><?= htmlspecialchars((string) ($movement['item_name'] ?? 'Sin producto'), ENT_QUOTES, 'UTF-8') ?></strong></td><td><?= htmlspecialchars((string) ($movement['movement_type'] ?? 'Sin tipo'), ENT_QUOTES, 'UTF-8') ?></td><td><strong><?= number_format((float) ($movement['quantity'] ?? 0), 2, ',', '.') ?></strong> <?= htmlspecialchars((string) ($movement['unit'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td><td><strong><?= htmlspecialchars((string) ($movement['block_name'] ?? 'Sin cuartel'), ENT_QUOTES, 'UTF-8') ?></strong><small><?= htmlspecialchars((string) ($movement['farm_name'] ?? 'Sin fundo'), ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars((string) ($movement['center_name'] ?? 'Sin centro'), ENT_QUOTES, 'UTF-8') ?></small></td><td><?= number_format((float) ($movement['unit_cost'] ?? 0), 0, ',', '.') ?> CLP</td>
                </tr><?php endforeach; endif; ?>
            </tbody></table>
        </section>
        <?php endif; ?>
    </section>
</main>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('inventory-search');
    const categoryFilter = document.getElementById('inventory-category-filter');
    const perPageSelect = document.getElementById('inventory-per-page');
    const table = document.getElementById('inventory-products-table');
    const rows = table.querySelectorAll('tbody tr');

    function filterTable() {
        const searchTerm = searchInput.value.toLowerCase();
        const categoryValue = categoryFilter.value;
        const perPage = parseInt(perPageSelect.value);

        let visibleCount = 0;

        rows.forEach((row, index) => {
            const nameCell = row.cells[1]?.textContent.toLowerCase() || '';
            const rowCategory = row.getAttribute('data-category') || '';

            const matchesSearch = nameCell.includes(searchTerm);
            const matchesCategory = !categoryValue || rowCategory === categoryValue;
            const shouldShow = matchesSearch && matchesCategory && visibleCount < perPage;

            if (matchesSearch && matchesCategory) {
                if (visibleCount < perPage) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            } else {
                row.style.display = 'none';
            }
        });
    }

    searchInput.addEventListener('keyup', filterTable);
    categoryFilter.addEventListener('change', filterTable);
    perPageSelect.addEventListener('change', filterTable);

    filterTable();

    // Auto-generate SKU based on category
    const categorySelect = document.getElementById('product_category');
    const skuInput = document.getElementById('product_sku');
    const allSkusInput = document.getElementById('all-skus');

    if (categorySelect && skuInput && allSkusInput) {
        const allSkus = JSON.parse(allSkusInput.value || '[]');

        function generateSKU() {
            const categoryCode = categorySelect.value;
            if (!categoryCode) return;

            let prefix = categoryCode.substring(0, 3).toUpperCase();
            let maxNumber = 0;

            // Search through all SKUs for this category
            allSkus.forEach(sku => {
                const parts = sku.split('-');
                if (parts[0] && parts[1]) {
                    // Check if prefix matches category pattern
                    if (parts[0].substring(0, 3).toUpperCase() === prefix.substring(0, 3).toUpperCase()) {
                        prefix = parts[0];
                        const number = parseInt(parts[1]);
                        if (number > maxNumber) maxNumber = number;
                    }
                }
            });

            const newNumber = String(maxNumber + 1).padStart(3, '0');
            skuInput.value = prefix + '-' + newNumber;
        }

        categorySelect.addEventListener('change', generateSKU);
        if (categorySelect.value) generateSKU();
    }
});
</script>
</body>
</html>
