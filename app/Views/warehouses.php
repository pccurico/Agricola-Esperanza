<?php
declare(strict_types=1);

$warehouses = array_map(static function (mixed $warehouse): array {
    $warehouse = (array) $warehouse;
    return ['id' => (int) ($warehouse['id'] ?? 0), 'name' => (string) ($warehouse['name'] ?? 'Sin bodega')];
}, $warehouses ?? []);
$items = array_map(static function (mixed $item): array {
    $item = (array) $item;
    return ['id' => (int) ($item['id'] ?? 0), 'name' => (string) ($item['name'] ?? 'Sin artículo'), 'sku' => (string) ($item['sku'] ?? 'Sin SKU')];
}, $items ?? []);
$farms = array_map(static function (mixed $farm): array {
    $farm = (array) $farm;
    return ['id' => (int) ($farm['id'] ?? 0), 'name' => (string) ($farm['name'] ?? 'Sin fundo')];
}, $farms ?? []);
$locations = $locations ?? [];
$lots = $lots ?? [];
$transfers = $transfers ?? [];
$error = $error ?? null;
$success = $success ?? null;
$csrf = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bodegas | Sistema de Gestión Agrícola PCCURICO</title>
    <link rel="stylesheet" href="assets/css/app.css">
</head>
<body class="admin-page">
<main class="admin-shell">
    <?php require dirname(__DIR__) . '/Views/partials/module-navigation.php'; ?>
    <section class="module-content module-v2 inventory-v2 warehouses-v2">
        <header class="admin-header warehouses-page-header">
            <div>
                <p class="eyebrow">Inventario</p>
                <h1>Bodegas</h1>
                <p class="setup-copy">Organiza bodegas, ubicaciones, lotes y transferencias internas.</p>
            </div>
            <a class="secondary-link" href="?module=inventory">Volver a inventario</a>
        </header>

        <?php if ($error): ?><div class="setup-error"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
        <?php if ($success): ?><div class="setup-success"><?= htmlspecialchars((string) $success, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>

        <nav class="warehouse-tabs" aria-label="Acciones de bodegas">
            <button class="warehouse-tab is-active" type="button" data-warehouse-form="warehouse">Crear bodega</button>
            <button class="warehouse-tab" type="button" data-warehouse-form="location">Crear ubicación</button>
            <button class="warehouse-tab" type="button" data-warehouse-form="lot">Crear lote</button>
            <button class="warehouse-tab" type="button" data-warehouse-form="transfer">Crear transferencia</button>
        </nav>

        <section class="warehouse-form-card" aria-label="Formulario de bodegas">
            <form class="warehouse-form is-active" data-warehouse-form-panel="warehouse" method="post">
                <div class="warehouse-form-heading"><div><p class="eyebrow">Bodegas</p><h2>Nueva bodega</h2><p>Define un espacio de almacenamiento para la operación.</p></div></div>
                <input type="hidden" name="csrf" value="<?= $csrf ?>">
                <input type="hidden" name="action" value="create_warehouse">
                <div class="warehouse-form-fields">
                    <label>Código<input name="code" required maxlength="40" placeholder="BOD-001"></label>
                    <label>Nombre<input name="name" required maxlength="140" placeholder="Bodega principal"></label>
                    <label>Fundo<select name="farm_id"><option value="">Sin fundo</option><?php foreach ($farms as $farm): ?><option value="<?= $farm['id'] ?>"><?= htmlspecialchars($farm['name'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
                </div>
                <div class="warehouse-form-actions"><button class="primary-button" type="submit">Crear bodega</button></div>
            </form>

            <form class="warehouse-form" data-warehouse-form-panel="location" method="post" hidden>
                <div class="warehouse-form-heading"><div><p class="eyebrow">Ubicaciones</p><h2>Nueva ubicación</h2><p>Asocia una ubicación física a una bodega.</p></div></div>
                <input type="hidden" name="csrf" value="<?= $csrf ?>">
                <input type="hidden" name="action" value="create_location">
                <div class="warehouse-form-fields">
                    <label>Bodega<select name="warehouse_id" required><option value="">Selecciona una bodega</option><?php foreach ($warehouses as $warehouse): ?><option value="<?= $warehouse['id'] ?>"><?= htmlspecialchars($warehouse['name'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
                    <label>Código<input name="code" required maxlength="40" placeholder="EST-001"></label>
                    <label>Nombre<input name="name" required maxlength="120" placeholder="Estantería de insumos"></label>
                </div>
                <div class="warehouse-form-actions"><button class="primary-button" type="submit">Crear ubicación</button></div>
            </form>

            <form class="warehouse-form" data-warehouse-form-panel="lot" method="post" hidden>
                <div class="warehouse-form-heading"><div><p class="eyebrow">Trazabilidad</p><h2>Nuevo lote</h2><p>Registra cantidades y vencimientos por artículo.</p></div></div>
                <input type="hidden" name="csrf" value="<?= $csrf ?>">
                <input type="hidden" name="action" value="create_lot">
                <div class="warehouse-form-fields warehouse-form-fields-wide">
                    <label>Artículo<select name="item_id" required><option value="">Selecciona un artículo</option><?php foreach ($items as $item): ?><option value="<?= $item['id'] ?>"><?= htmlspecialchars($item['sku'] . ' · ' . $item['name'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
                    <label>Bodega<select name="warehouse_id"><option value="">Sin bodega</option><?php foreach ($warehouses as $warehouse): ?><option value="<?= $warehouse['id'] ?>"><?= htmlspecialchars($warehouse['name'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
                    <label>Número de lote<input name="lot_number" required maxlength="80" placeholder="LOTE-2026-001"></label>
                    <label>Vencimiento<input type="date" name="expires_on"></label>
                    <label>Cantidad<input type="number" name="quantity" min="0" step="0.001" required value="0"></label>
                </div>
                <div class="warehouse-form-actions"><button class="primary-button" type="submit">Crear lote</button></div>
            </form>

            <form class="warehouse-form" data-warehouse-form-panel="transfer" method="post" hidden>
                <div class="warehouse-form-heading"><div><p class="eyebrow">Movimiento interno</p><h2>Nueva transferencia</h2><p>Mueve existencias entre bodegas de forma controlada.</p></div></div>
                <input type="hidden" name="csrf" value="<?= $csrf ?>">
                <input type="hidden" name="action" value="create_transfer">
                <div class="warehouse-form-fields warehouse-form-fields-wide">
                    <label>Artículo<select name="item_id" required><option value="">Selecciona un artículo</option><?php foreach ($items as $item): ?><option value="<?= $item['id'] ?>"><?= htmlspecialchars($item['sku'] . ' · ' . $item['name'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
                    <label>Origen<select name="from_warehouse_id" required><option value="">Selecciona origen</option><?php foreach ($warehouses as $warehouse): ?><option value="<?= $warehouse['id'] ?>"><?= htmlspecialchars($warehouse['name'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
                    <label>Destino<select name="to_warehouse_id" required><option value="">Selecciona destino</option><?php foreach ($warehouses as $warehouse): ?><option value="<?= $warehouse['id'] ?>"><?= htmlspecialchars($warehouse['name'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
                    <label>Cantidad<input type="number" min="0.001" step="0.001" name="quantity" required></label>
                    <label>Fecha<input type="date" name="transfer_date" value="<?= htmlspecialchars(date('Y-m-d'), ENT_QUOTES, 'UTF-8') ?>" required></label>
                </div>
                <div class="warehouse-form-actions"><button class="primary-button" type="submit">Crear transferencia</button></div>
            </form>
        </section>

        <section class="warehouse-data-section">
            <div class="section-card warehouse-data-card">
                <div class="panel-header"><div><p class="eyebrow">Almacenamiento</p><h2>Bodegas y ubicaciones</h2><p>Espacios disponibles para organizar las existencias.</p></div></div>
                <div class="table-scroll"><table class="data-table"><thead><tr><th>Bodega</th><th>Código</th><th>Ubicación</th></tr></thead><tbody><?php if ($locations === []): ?><tr><td colspan="3" class="empty-state">No hay ubicaciones registradas.</td></tr><?php else: foreach ($locations as $location): ?><tr><td><?= htmlspecialchars((string) ($location['warehouse_name'] ?? 'Sin bodega'), ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars((string) ($location['warehouse_code'] ?? $location['code'] ?? 'Sin código'), ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars((string) ($location['name'] ?? 'Sin ubicación'), ENT_QUOTES, 'UTF-8') ?></td></tr><?php endforeach; endif; ?></tbody></table></div>
            </div>
            <div class="section-card warehouse-data-card">
                <div class="panel-header"><div><p class="eyebrow">Existencias</p><h2>Lotes</h2><p>Control de cantidades y fechas de vencimiento.</p></div></div>
                <div class="table-scroll"><table class="data-table"><thead><tr><th>Artículo</th><th>Lote</th><th>Vencimiento</th><th>Cantidad</th><th>Bodega</th></tr></thead><tbody><?php if ($lots === []): ?><tr><td colspan="5" class="empty-state">No hay lotes registrados.</td></tr><?php else: foreach ($lots as $lot): ?><tr><td><?= htmlspecialchars((string) ($lot['item_name'] ?? 'Sin artículo'), ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars((string) ($lot['lot_number'] ?? 'Sin lote'), ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars((string) ($lot['expires_on'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars((string) ($lot['quantity'] ?? 0), ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars((string) ($lot['warehouse_name'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td></tr><?php endforeach; endif; ?></tbody></table></div>
            </div>
            <div class="section-card warehouse-data-card">
                <div class="panel-header"><div><p class="eyebrow">Movimientos</p><h2>Transferencias</h2><p>Seguimiento de movimientos entre bodegas.</p></div></div>
                <div class="table-scroll"><table class="data-table"><thead><tr><th>Artículo</th><th>Origen</th><th>Destino</th><th>Estado</th><th>Acciones</th></tr></thead><tbody><?php if ($transfers === []): ?><tr><td colspan="5" class="empty-state">No hay transferencias registradas.</td></tr><?php else: foreach ($transfers as $transfer): ?><tr><td><?= htmlspecialchars((string) ($transfer['item_name'] ?? 'Sin artículo'), ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars((string) ($transfer['from_warehouse'] ?? 'Sin bodega'), ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars((string) ($transfer['to_warehouse'] ?? 'Sin bodega'), ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars((string) ($transfer['status'] ?? 'Sin estado'), ENT_QUOTES, 'UTF-8') ?></td><td><?php if (($transfer['status'] ?? '') === 'DRAFT'): ?><form method="post" class="inline-form"><input type="hidden" name="csrf" value="<?= $csrf ?>"><input type="hidden" name="action" value="approve_transfer"><input type="hidden" name="transfer_id" value="<?= (int) ($transfer['id'] ?? 0) ?>"><button class="table-action" type="submit">Aprobar</button></form><?php else: ?>—<?php endif; ?></td></tr><?php endforeach; endif; ?></tbody></table></div>
            </div>
        </section>
    </section>
</main>
<script>
    document.querySelectorAll('[data-warehouse-form]').forEach((tab) => {
        tab.addEventListener('click', () => {
            const target = tab.dataset.warehouseForm;
            document.querySelectorAll('[data-warehouse-form]').forEach((button) => button.classList.toggle('is-active', button === tab));
            document.querySelectorAll('[data-warehouse-form-panel]').forEach((form) => {
                const visible = form.dataset.warehouseFormPanel === target;
                form.hidden = !visible;
                form.classList.toggle('is-active', visible);
            });
        });
    });
</script>
</body>
</html>
