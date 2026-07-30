<?php
$receptionLines = $reception_lines ?? [];
$error = $error ?? null;
$success = $success ?? null;
$grouped = [];
foreach ($receptionLines as $line) {
    $grouped[$line['purchase_order_id']]['header'] = $line;
    $grouped[$line['purchase_order_id']]['lines'][] = $line;
}
$pendingOrders = count($grouped);
$pendingLines = 0;
$pendingBalance = 0.0;
$partialOrders = 0;
foreach ($grouped as $order) {
    $partialOrders += $order['header']['status'] === 'PARTIAL' ? 1 : 0;
    foreach ($order['lines'] as $line) {
        $pendingLines++;
        $pendingBalance += (float) $line['quantity'] - (float) $line['received_quantity'];
    }
}
?>
<!doctype html>
<html lang="es">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Recepciones de compras | CampoSur</title><link rel="stylesheet" href="assets/css/app.css"></head>
<body class="admin-page"><main class="admin-shell"><?php require dirname(__DIR__) . '/Views/partials/module-navigation.php'; ?><section class="module-content">
	<header class="admin-header"><div><p class="eyebrow">Compras</p><h1>Recepción de compras</h1><p class="setup-copy">Anota lo que recibiste y actualiza las existencias de la bodega.</p></div><a class="secondary-link" href="?module=procurement">Volver a compras</a></header>
<?php if ($error): ?><div class="setup-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
<?php if ($success): ?><div class="setup-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
    <section class="reception-summary" aria-label="Resumen de recepciones">
        <article class="reception-summary-card"><span>Órdenes pendientes</span><strong><?= $pendingOrders ?></strong></article>
        <article class="reception-summary-card"><span>Líneas por recibir</span><strong><?= $pendingLines ?></strong></article>
        <article class="reception-summary-card"><span>Unidades pendientes</span><strong><?= number_format($pendingBalance, 3, ',', '.') ?></strong></article>
        <article class="reception-summary-card"><span>Recepciones parciales</span><strong><?= $partialOrders ?></strong></article>
    </section>
	<?php if ($grouped): ?>
    <section class="reception-toolbar" aria-label="Controles de recepciones">
        <div><strong>Órdenes por recibir</strong><span><?= $pendingOrders ?> orden<?= $pendingOrders === 1 ? '' : 'es' ?> · <?= number_format($pendingBalance, 3, ',', '.') ?> unidades pendientes</span></div>
        <label>Buscar orden o proveedor<input type="search" class="reception-search" placeholder="Buscar..." autocomplete="off"></label>
    </section>
	<?php foreach ($grouped as $order): $header = $order['header']; ?>
	<section class="admin-panel reception-panel" data-reception-card data-search-text="<?= htmlspecialchars(strtolower($header['order_number'] . ' ' . $header['business_name']), ENT_QUOTES, 'UTF-8') ?>"><header class="panel-header"><h2>Orden <?= htmlspecialchars($header['order_number'], ENT_QUOTES, 'UTF-8') ?> <span class="reception-status <?= $header['status'] === 'PARTIAL' ? 'partial' : 'pending' ?>"><?= $header['status'] === 'PARTIAL' ? 'Parcial' : 'Pendiente' ?></span></h2><p><?= htmlspecialchars($header['business_name'], ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars($header['order_date'], ENT_QUOTES, 'UTF-8') ?></p></header>
<form method="post" class="admin-form reception-form"><input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="action" value="receive_order"><input type="hidden" name="purchase_order_id" value="<?= (int) $header['purchase_order_id'] ?>"><label>Fecha de recepción<input type="date" name="received_on" value="<?= htmlspecialchars(date('Y-m-d'), ENT_QUOTES, 'UTF-8') ?>" required></label><label>Notas<input name="notes" maxlength="255"></label><div class="table-wrap reception-table-wrap"><table class="data-table reception-table"><thead><tr><th>Artículo</th><th>Solicitado</th><th>Recibido</th><th>Saldo</th><th>Recepción</th><th>Acciones</th></tr></thead><tbody>
<?php foreach ($order['lines'] as $lineIndex => $line): ?><tr><td><?= htmlspecialchars($line['description'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars((string) $line['quantity'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars((string) $line['received_quantity'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars((string) ((float) $line['quantity'] - (float) $line['received_quantity']), ENT_QUOTES, 'UTF-8') ?></td><td><input type="number" min="0" max="<?= htmlspecialchars((string) ((float) $line['quantity'] - (float) $line['received_quantity']), ENT_QUOTES, 'UTF-8') ?>" step="0.001" name="items[<?= (int) $line['purchase_order_item_id'] ?>]" value="0" aria-label="Cantidad a recibir de <?= htmlspecialchars($line['description'], ENT_QUOTES, 'UTF-8') ?>"></td><?php if ($lineIndex === 0): ?><td rowspan="<?= count($order['lines']) ?>"><button class="primary-button reception-submit" type="submit">Registrar</button></td><?php endif; ?></tr><?php endforeach; ?>
</tbody></table></div></form></section>
<?php endforeach; ?>
<?php else: ?><section class="admin-panel reception-empty"><h2>No hay órdenes pendientes de recepción</h2><p class="setup-copy">Las órdenes deben estar enviadas o parcialmente recibidas.</p></section><?php endif; ?>
    <?php if ($selected_reception): ?>
    <section class="admin-panel reception-edit-panel">
        <header class="panel-header"><h2>Editar recepción <?= htmlspecialchars($selected_reception['order_number'], ENT_QUOTES, 'UTF-8') ?></h2><p><?= htmlspecialchars($selected_reception['business_name'], ENT_QUOTES, 'UTF-8') ?></p></header>
        <form method="post" class="admin-form reception-form">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="action" value="update_reception"><input type="hidden" name="reception_id" value="<?= (int) $selected_reception['id'] ?>">
            <label>Fecha de recepción<input type="date" name="received_on" value="<?= htmlspecialchars($selected_reception['received_on'], ENT_QUOTES, 'UTF-8') ?>" required></label>
            <label>Notas<input name="notes" maxlength="255" value="<?= htmlspecialchars((string) $selected_reception['notes'], ENT_QUOTES, 'UTF-8') ?>"></label>
            <div class="table-wrap reception-table-wrap"><table class="data-table reception-table"><thead><tr><th>Artículo</th><th>Ordenado</th><th>Recepción actual</th><th>Nueva cantidad</th></tr></thead><tbody>
            <?php foreach ($selected_reception['items'] as $item): ?><tr><td><?= htmlspecialchars($item['description'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars((string) $item['ordered_quantity'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars((string) $item['quantity'], ENT_QUOTES, 'UTF-8') ?></td><td><input type="number" min="0" max="<?= htmlspecialchars((string) ((float) $item['ordered_quantity'] - (float) $item['received_quantity'] + (float) $item['quantity']), ENT_QUOTES, 'UTF-8') ?>" step="0.001" name="items[<?= (int) $item['purchase_order_item_id'] ?>]" value="<?= htmlspecialchars((string) $item['quantity'], ENT_QUOTES, 'UTF-8') ?>"></td></tr><?php endforeach; ?>
            </tbody></table></div><div class="reception-edit-actions"><button class="primary-button reception-submit" type="submit">Guardar cambios</button><a class="secondary-link" href="?module=receptions">Cancelar</a></div>
        </form>
    </section>
    <?php endif; ?>
    <section class="admin-panel reception-history-panel"><header class="panel-header"><h2>Historial de recepciones</h2><p>Consulta, edita o elimina recepciones registradas.</p></header><div class="table-wrap reception-table-wrap"><table class="data-table reception-table"><thead><tr><th>Fecha</th><th>Orden</th><th>Proveedor</th><th>Líneas</th><th>Cantidad</th><th>Acciones</th></tr></thead><tbody>
    <?php foreach ($reception_history as $history): ?><tr><td><?= htmlspecialchars($history['received_on'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($history['order_number'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($history['business_name'], ENT_QUOTES, 'UTF-8') ?></td><td><?= (int) $history['lines_count'] ?></td><td><?= htmlspecialchars((string) $history['total_quantity'], ENT_QUOTES, 'UTF-8') ?></td><td class="reception-actions"><a class="table-action" href="?module=receptions&reception_id=<?= (int) $history['id'] ?>">Editar</a><form method="post" onsubmit="return confirm('¿Eliminar esta recepción y revertir sus existencias?');"><input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="action" value="delete_reception"><input type="hidden" name="reception_id" value="<?= (int) $history['id'] ?>"><button class="table-action danger-action" type="submit">Eliminar</button></form></td></tr><?php endforeach; ?>
    </tbody></table></div></section>
</section><script src="assets/js/receptions.js" defer></script></main></body></html>
