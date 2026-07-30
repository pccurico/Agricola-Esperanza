<?php
$receptionLines = $reception_lines ?? [];
$error = $error ?? null;
$success = $success ?? null;
$grouped = [];
foreach ($receptionLines as $line) {
    $grouped[$line['purchase_order_id']]['header'] = $line;
    $grouped[$line['purchase_order_id']]['lines'][] = $line;
}
?>
<!doctype html>
<html lang="es">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Recepciones de compras | CampoSur</title><link rel="stylesheet" href="/assets/css/app.css"></head>
<body class="admin-page"><main class="admin-shell"><?php require dirname(__DIR__) . '/Views/partials/module-navigation.php'; ?><section class="module-content">
	<header class="admin-header"><div><p class="eyebrow">Compras</p><h1>Recepción de compras</h1><p class="setup-copy">Anota lo que recibiste y actualiza las existencias de la bodega.</p></div><a class="secondary-link" href="?module=procurement">Volver a compras</a></header>
<?php if ($error): ?><div class="setup-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
<?php if ($success): ?><div class="setup-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
<?php if ($grouped): ?>
<?php foreach ($grouped as $order): $header = $order['header']; ?>
<section class="admin-panel"><header class="panel-header"><h2>Orden <?= htmlspecialchars($header['order_number'], ENT_QUOTES, 'UTF-8') ?></h2><p><?= htmlspecialchars($header['business_name'], ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars($header['order_date'], ENT_QUOTES, 'UTF-8') ?></p></header>
<form method="post" class="admin-form"><input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="action" value="receive_order"><input type="hidden" name="purchase_order_id" value="<?= (int) $header['purchase_order_id'] ?>"><label>Fecha de recepción<input type="date" name="received_on" value="<?= htmlspecialchars(date('Y-m-d'), ENT_QUOTES, 'UTF-8') ?>" required></label><label>Notas<input name="notes" maxlength="255"></label><div class="table-wrap"><table class="data-table"><thead><tr><th>Artículo</th><th>Solicitado</th><th>Recibido</th><th>Saldo</th><th>Recepción</th></tr></thead><tbody>
<?php foreach ($order['lines'] as $line): ?><tr><td><?= htmlspecialchars($line['description'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars((string) $line['quantity'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars((string) $line['received_quantity'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars((string) ((float) $line['quantity'] - (float) $line['received_quantity']), ENT_QUOTES, 'UTF-8') ?></td><td><input type="number" min="0" step="0.001" name="items[<?= (int) $line['purchase_order_item_id'] ?>]" value="0"></td></tr><?php endforeach; ?>
</tbody></table></div><button class="primary-button" type="submit">Registrar recepción</button></form></section>
<?php endforeach; ?>
<?php else: ?><section class="admin-panel"><h2>No hay órdenes pendientes de recepción</h2><p class="setup-copy">Las órdenes deben estar enviadas o parcialmente recibidas.</p></section><?php endif; ?>
</section></main></body></html>
