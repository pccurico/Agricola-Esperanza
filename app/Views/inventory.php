<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bodega | Sistema de Gestión Agrícola PCCURICO</title>
    <link rel="stylesheet" href="assets/css/app.css">
</head>

<body class="admin-page">
    <main class="admin-shell"><?php require dirname(__DIR__) . '/Views/partials/module-navigation.php'; ?>
        <section class="module-content module-v2 inventory-v2">
            <header class="page-hero">
                <div class="hero-meta">
                    <div class="hero-title">
                        <p class="eyebrow">Bodega</p>
                        <h1>Inventario</h1>
                        <p class="lead-text">Registra tus insumos y revisa las entradas y salidas de la bodega.</p>
                    </div>
                    <div class="hero-actions"><a class="btn btn-outline" href="./">Volver al dashboard</a></div>
                </div>
                <div class="hero-kpis">
                    <div class="kpi-grid">
                        <div class="stat-card"><small>Artículos</small><strong><?= count($items) ?></strong></div>
                        <div class="stat-card"><small>Movimientos</small><strong><?= count($movements) ?></strong></div>
                    </div>
                </div>
            </header>

            <div class="page-grid v2">
                <main class="main-column">
                    <section class="section-card">
                        <div class="panel-header"><div><h2>Nuevo artículo</h2></div></div>
                        <div class="panel-body">
                        <form method="post">
                            <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="action" value="create_item">
                            <div class="form-row">
                                <label>SKU<input name="sku" required placeholder="INS-0001"></label>
                                <label>Nombre<input name="name" required placeholder="Fertilizante granulado"></label>
                            </div>
                            <div class="form-row">
                                <label>Categoría<select name="category"><option value="INSUMO">Insumo</option><option value="FERRETERIA">Ferretería</option><option value="MAQUINARIA">Maquinaria</option><option value="HERRAMIENTA">Herramienta</option><option value="OTRO">Otro</option></select></label>
                                <label>Unidad<input name="unit" required placeholder="kg, lt, un"></label>
                                <label>Stock mínimo<input type="number" name="minimum_stock" min="0" step="0.001"></label>
                            </div>
                            <div class="form-actions"><button class="btn" type="submit">Crear artículo</button></div>
                        </form>
                    </section>

                    <section class="section-card">
                        <div class="panel-header"><div><h2>Stock actual</h2></div></div>
                        <div class="panel-body">
                        <div class="table-scroll">
                            <table class="data-table">
                                <thead><tr><th>Artículo</th><th>Categoría</th><th>Unidad</th><th>Stock</th></tr></thead>
                                <tbody><?php foreach ($items as $item): ?><tr>
                                            <td><b><?= htmlspecialchars($item['name']) ?></b><small><?= htmlspecialchars($item['sku']) ?></small></td>
                                            <td><?= htmlspecialchars($item['category']) ?></td>
                                            <td><?= htmlspecialchars($item['unit']) ?></td>
                                            <td><b><?= number_format((float) $item['stock'], 3, ',', '.') ?></b></td>
                                        </tr><?php endforeach; ?></tbody>
                            </table>
                        </div>
                    </section>

                    <section class="section-card">
                        <div class="panel-header"><div><h2>Movimientos recientes</h2></div></div>
                        <div class="panel-body">
                        <div class="activity-list">
                            <?php foreach ($movements as $movement): ?><div class="activity-row">
                                <div><b><?= htmlspecialchars($movement['item_name']) ?></b><small><?= htmlspecialchars($movement['movement_date']) ?> · <?= htmlspecialchars($movement['reference'] ?? '') ?></small></div>
                                <div class="meta"><?= htmlspecialchars($movement['movement_type']) ?> · <?= number_format((float) $movement['quantity'], 3, ',', '.') ?> <?= htmlspecialchars($movement['unit']) ?></div>
                            </div><?php endforeach; ?>
                        </div>
                    </section>
                </main>

                <aside class="sidebar-column v2">
                    <section class="card compact">
                        <h4>Registrar movimiento</h4>
                        <form method="post" class="stack-form">
                            <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="action" value="create_movement">
                            <label>Artículo<select name="item_id" required><?php foreach ($item_options as $item): ?><option value="<?= (int) $item['id'] ?>"><?= htmlspecialchars($item['sku'] . ' · ' . $item['name']) ?></option><?php endforeach; ?></select></label>
                            <label>Tipo<select name="movement_type"><option value="IN">Entrada</option><option value="OUT">Salida</option><option value="ADJUSTMENT">Ajuste</option></select></label>
                            <label>Cantidad<input type="number" name="quantity" min="0.001" step="0.001" required></label>
                            <label>Fecha<input type="date" name="movement_date" required value="<?= date('Y-m-d') ?>"></label>
                            <div class="form-actions"><button class="btn" type="submit">Registrar</button></div>
                        </form>
                    </section>
                </aside>
            </div>
        </section>
    </main>
</body>

</html>