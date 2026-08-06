<?php

declare(strict_types=1);

$suppliers = $suppliers ?? [];
$supplier_options = $supplier_options ?? [];
$season_options = $season_options ?? [];
$farm_options = $farm_options ?? [];
$item_options = $item_options ?? [];
$orders = $orders ?? [];
$purchase_invoices = $purchase_invoices ?? [];
$error = $error ?? null;
$success = $success ?? null;
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Compras | Sistema de Gestión Agrícola PCCURICO</title>
    <link rel="stylesheet" href="assets/css/app.css">
</head>
<body class="admin-page">
    <main class="admin-shell">
        <?php require dirname(__DIR__) . '/Views/partials/module-navigation.php'; ?>
        <section class="module-content">
            <header class="admin-header">
                <div><p class="eyebrow">Compras</p><h1>Compras y proveedores</h1><p class="setup-copy">Registra proveedores, prepara órdenes y recibe los insumos en la bodega.</p></div>
                <a class="secondary-link" href="./">Volver al resumen</a>
            </header>
            <section class="module-content procurement-v2 module-v2">
                <header class="page-hero">
                    <div class="hero-meta">
                        <div class="hero-title">
                            <p class="eyebrow">Compras</p>
                            <h1>Compras y proveedores</h1>
                            <p class="lead-text">Registra proveedores, prepara órdenes y recibe los insumos en la bodega.</p>
                        </div>
                        <div class="hero-actions"><a class="btn btn-outline" href="./">Volver al resumen</a></div>
                    </div>
                    <div class="hero-kpis"><div class="kpi-grid"><div class="stat-card"><small>Proveedores</small><strong><?= count($suppliers) ?></strong></div><div class="stat-card"><small>Órdenes</small><strong><?= count($orders) ?></strong></div></div></div>
                </header>

                <div class="page-grid v2">
                    <main class="main-column">
                        <section class="section-card">
                            <div class="panel-header"><div><h2>Agregar proveedor</h2></div></div>
                            <div class="panel-body">
                            <form method="post">
                                <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="action" value="create_supplier">
                                <div class="form-row">
                                    <label>Razón social<input name="business_name" required></label>
                                    <label>RUT<input name="tax_id"></label>
                                </div>
                                <div class="form-row">
                                    <label>Contacto<input name="contact_name"></label>
                                    <label>Correo<input type="email" name="email"></label>
                                    <label>Teléfono<input name="phone"></label>
                                </div>
                                <div class="form-row"><label>Dirección<input name="address"></label></div>
                                <div class="form-actions"><button class="btn" type="submit">Guardar proveedor</button></div>
                            </form>
                        </section>

                        <section class="section-card">
                            <div class="panel-header"><div><h2>Órdenes de compra</h2></div></div>
                            <div class="panel-body">
                            <div class="table-scroll"><table class="data-table"><thead><tr><th>Número</th><th>Proveedor</th><th>Fecha</th><th>Estado</th><th>Líneas</th></tr></thead><tbody><?php foreach ($orders as $order): ?><tr><td><?= htmlspecialchars($order['order_number'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($order['business_name'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($order['order_date'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($order['status'], ENT_QUOTES, 'UTF-8') ?></td><td><?= (int) $order['items_count'] ?></td></tr><?php endforeach; ?></tbody></table></div>
                            </div>
                        </section>
                    </main>

                    <aside class="sidebar-column v2">
                        <section class="section-card compact">
                            <div class="panel-header"><h4>Crear orden</h4></div>
                            <div class="panel-body">
                            <form method="post">
                                <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="action" value="create_order">
                                <label>Proveedor<select name="supplier_id" required><option value="">Selecciona un proveedor</option><?php foreach ($supplier_options as $supplier): ?><option value="<?= (int) $supplier['id'] ?>"><?= htmlspecialchars($supplier['business_name'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
                                <label>Número de orden<input name="order_number" required></label>
                                <label>Fecha<input type="date" name="order_date" value="<?= date('Y-m-d') ?>" required></label>
                                <label>Insumo<select name="item_id"><option value="">Solo descripción</option><?php foreach ($item_options as $item): ?><option value="<?= (int) $item['id'] ?>"><?= htmlspecialchars($item['name'] . ' · ' . $item['sku'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
                                <div class="form-actions"><button class="btn" type="submit">Crear orden</button></div>
                            </form>
                        </section>
                        <section class="section-card compact">
                            <div class="panel-header"><h4>Proveedores</h4></div>
                            <div class="panel-body">
                            <div class="table-scroll"><table class="data-table"><thead><tr><th>Proveedor</th><th>RUT</th><th>Contacto</th><th>Correo</th></tr></thead><tbody><?php foreach ($suppliers as $supplier): ?><tr><td><?= htmlspecialchars($supplier['business_name'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($supplier['tax_id'] ?: 'Sin RUT', ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($supplier['contact_name'] ?: 'Sin contacto', ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($supplier['email'] ?: 'Sin correo', ENT_QUOTES, 'UTF-8') ?></td></tr><?php endforeach; ?></tbody></table></div>
                            </div>
                        </section>
                    </aside>
                </div>
            </section>
        </section>
    </main>
</body>
</html>
