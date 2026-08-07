<?php

declare(strict_types=1);

$catalogs = $catalogs ?? [];
$categories = $categories ?? [];
$values = $values ?? [];
$catalogCode = $catalogCode ?? '';
$editValueId = $editValueId ?? 0;
$isSubcategoryCatalog = $catalogCode === 'INVENTORY_SUBCATEGORY';
$isInventoryCategoryCatalog = $catalogCode === 'INVENTORY_CATEGORY';
$isInventoryCatalog = $isSubcategoryCatalog || $isInventoryCategoryCatalog;
$catalogValueLabel = $isSubcategoryCatalog ? 'subcategoría' : 'categoría';
$error = $error ?? null;
$success = $success ?? null;
$selectedCatalog = null;
foreach ($catalogs as $catalog) {
    if ((string) $catalog['code'] === $catalogCode) {
        $selectedCatalog = $catalog;
        break;
    }
}
$selectedCatalogName = $selectedCatalog['name'] ?? $catalogCode;
$editingValue = null;
if ($isSubcategoryCatalog && $editValueId > 0) {
    foreach ($values as $value) {
        if ((int) ($value['id'] ?? 0) === $editValueId) {
            $editingValue = $value;
            break;
        }
    }
}
$editingMetadata = $editingValue ? json_decode((string) ($editingValue['metadata_json'] ?? ''), true) : [];
$editingCategory = is_array($editingMetadata) ? (string) ($editingMetadata['category'] ?? '') : '';
if ($isSubcategoryCatalog) {
    $uniqueValues = [];
    foreach ($values as $value) {
        $valueMetadata = json_decode((string) ($value['metadata_json'] ?? ''), true);
        $valueCategory = is_array($valueMetadata) ? (string) ($valueMetadata['category'] ?? '') : '';
        $uniqueKey = strtolower(trim((string) ($value['label'] ?? ''))) . '|' . strtoupper($valueCategory);
        $uniqueValues[$uniqueKey] ??= $value;
    }
    $values = array_values($uniqueValues);
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Catálogos | Sistema de Gestión Agrícola PCCURICO</title>
    <link rel="stylesheet" href="assets/css/app.css">
</head>
<body class="admin-page">
    <main class="admin-shell">
        <?php require dirname(__DIR__) . '/Views/partials/module-navigation.php'; ?>
        <section class="module-content module-v2 inventory-v2">
            <header class="admin-header">
                <div>
                    <p class="eyebrow">Inventario</p>
                    <h1><?= $isSubcategoryCatalog ? 'Subcategorías de inventario' : ($catalogCode === 'INVENTORY_CATEGORY' ? 'Categorías de inventario' : 'Listas del sistema') ?></h1>
                    <p class="setup-copy"><?= $isSubcategoryCatalog ? 'Crea una subcategoría y vincúlala con una categoría de inventario.' : ($catalogCode === 'INVENTORY_CATEGORY' ? 'Crea y administra las categorías disponibles para los productos.' : 'Administra las opciones que estarán disponibles en los formularios de la agrícola.') ?></p>
                </div>
                <a class="secondary-link" href="<?= htmlspecialchars(module_url('inventory'), ENT_QUOTES, 'UTF-8') ?>">Volver al inventario</a>
            </header>
            <nav class="inventory-tabs" aria-label="Secciones de inventario">
                <a class="inventory-tab" href="<?= htmlspecialchars(module_url('inventory'), ENT_QUOTES, 'UTF-8') ?>">Resumen</a>
                <a class="inventory-tab" href="<?= htmlspecialchars(module_url('inventory', ['view' => 'item-form']), ENT_QUOTES, 'UTF-8') ?>">Productos</a>
                <a class="inventory-tab" href="<?= htmlspecialchars(module_url('inventory', ['view' => 'movement-form']), ENT_QUOTES, 'UTF-8') ?>">Movimientos</a>
                <a class="inventory-tab <?= $isInventoryCategoryCatalog ? 'is-active' : '' ?>" href="<?= htmlspecialchars(module_url('catalogs', ['catalog' => 'INVENTORY_CATEGORY']), ENT_QUOTES, 'UTF-8') ?>">Categorías</a>
                <a class="inventory-tab <?= $isSubcategoryCatalog ? 'is-active' : '' ?>" href="<?= htmlspecialchars(module_url('catalogs', ['catalog' => 'INVENTORY_SUBCATEGORY']), ENT_QUOTES, 'UTF-8') ?>">Subcategorías</a>
            </nav>
            <?php if ($error): ?><div class="setup-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
            <?php if ($success): ?><div class="setup-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
            <section class="admin-columns <?= $isInventoryCatalog ? 'catalog-focused' : '' ?>">
                <?php if (!$isInventoryCatalog): ?>
                <article class="admin-panel">
                    <header class="panel-header"><h2>Listas disponibles</h2><p>Elige una lista para revisar o agregar opciones.</p></header>
                    <div class="module-links">
                        <?php foreach ($catalogs as $catalog): ?>
                            <a href="<?= htmlspecialchars(module_url('catalogs', ['catalog' => $catalog['code']]), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($catalog['name'], ENT_QUOTES, 'UTF-8') ?><small><?= htmlspecialchars($catalog['code'], ENT_QUOTES, 'UTF-8') ?></small></a>
                        <?php endforeach; ?>
                    </div>
                </article>
                <?php endif; ?>
                <article class="admin-panel">
                    <header class="panel-header"><h2><?= $catalogCode !== '' ? htmlspecialchars((string) $selectedCatalogName, ENT_QUOTES, 'UTF-8') : 'Selecciona una lista' ?></h2><p><?= $catalogCode !== '' ? 'Revisa las opciones que usan los formularios.' : 'Selecciona una lista para ver sus opciones.' ?></p></header>
                    <?php if ($catalogCode !== ''): ?>
                        <form method="post" class="admin-form <?= $isSubcategoryCatalog ? 'subcategory-form' : '' ?>">
                            <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="action" value="<?= $editingValue ? 'update' : 'create' ?>">
                            <input type="hidden" name="catalog_code" value="<?= htmlspecialchars($catalogCode, ENT_QUOTES, 'UTF-8') ?>">
                            <?php if ($editingValue): ?><input type="hidden" name="value_id" value="<?= (int) $editingValue['id'] ?>"><?php endif; ?>
                            <label>Nombre de <?= $catalogValueLabel ?><input name="label" required maxlength="140" placeholder="<?= $isSubcategoryCatalog ? 'Fertilizantes foliares' : 'Fertilizantes' ?>" value="<?= htmlspecialchars((string) ($editingValue['label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></label>
                            <?php if ($isSubcategoryCatalog): ?>
                                <label>Categoría vinculada<select name="parent_category" required>
                                    <option value="">Selecciona una categoría</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?= htmlspecialchars($category['code'], ENT_QUOTES, 'UTF-8') ?>" <?= $editingCategory === (string) $category['code'] ? 'selected' : '' ?>><?= htmlspecialchars($category['label'], ENT_QUOTES, 'UTF-8') ?></option>
                                    <?php endforeach; ?>
                                </select></label>
                            <?php endif; ?>
                            <button class="primary-button" type="submit"><?= $editingValue ? 'Guardar cambios' : 'Crear ' . $catalogValueLabel ?></button>
                            <?php if ($editingValue): ?><a class="secondary-link" href="<?= htmlspecialchars(module_url('catalogs', ['catalog' => $catalogCode]), ENT_QUOTES, 'UTF-8') ?>">Cancelar edición</a><?php endif; ?>
                        </form>
                        <section class="catalog-values-section">
                            <div class="panel-header"><h3><?= ucfirst($catalogValueLabel) ?>s creadas</h3><p>Revisa y administra las <?= $catalogValueLabel ?>s disponibles para los formularios.</p></div>
                            <div class="table-wrap catalog-values-table"><table class="data-table"><thead><tr><th><?= ucfirst($catalogValueLabel) ?></th><?php if ($isSubcategoryCatalog): ?><th>Categoría</th><?php endif; ?><th>Acciones</th></tr></thead><tbody>
                        <?php if ($values === []): ?><tr><td colspan="<?= $isSubcategoryCatalog ? '3' : '2' ?>" class="empty-state">Aún no hay <?= $catalogValueLabel ?>s creadas.</td></tr><?php endif; ?>
                        <?php foreach ($values as $value): ?>
                            <?php $valueMetadata = json_decode((string) ($value['metadata_json'] ?? ''), true); $valueCategory = (string) ($valueMetadata['category'] ?? 'Sin categoría'); ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($value['label'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                                <?php if ($isSubcategoryCatalog): ?><td><?php $valueCategoryLabel = 'Sin categoría'; foreach ($categories as $category) { if ((string) $category['code'] === $valueCategory) { $valueCategoryLabel = (string) $category['label']; break; } } ?><?= htmlspecialchars($valueCategoryLabel, ENT_QUOTES, 'UTF-8') ?></td><?php endif; ?>
                                <td class="catalog-actions">
                                    <?php if ($value['scope'] === 'COMPANY'): ?>
                                        <a class="table-action" href="<?= htmlspecialchars(module_url('catalogs', ['catalog' => $catalogCode, 'edit_value' => (int) $value['id']]), ENT_QUOTES, 'UTF-8') ?>">Editar</a>
                                        <form method="post"><input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="action" value="deactivate"><input type="hidden" name="catalog_code" value="<?= htmlspecialchars($catalogCode, ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="value_id" value="<?= (int) $value['id'] ?>"><button class="table-action" type="submit">Eliminar</button></form>
                                    <?php else: ?>Base del sistema<?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                            </tbody></table></div>
                        </section>
                    <?php endif; ?>
                </article>
            </section>
        </section>
    </main>
</body>
</html>
