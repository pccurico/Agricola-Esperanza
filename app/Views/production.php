<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Producción | Sistema de Gestión Agrícola PCCURICO</title>
    <link rel="stylesheet" href="assets/css/app.css">
</head>

<body class="admin-page">
    <main class="admin-shell"><?php require dirname(__DIR__) . '/Views/partials/module-navigation.php'; ?>
        <section class="module-content module-v2 production-v2">
            <header class="page-hero">
                <div class="hero-meta">
                    <div class="hero-title">
                        <p class="eyebrow">Cosechas y labores</p>
                        <h1>Producción</h1>
                        <p class="lead-text">Anota tus cosechas y resultados por fundo y cuartel.</p>
                    </div>
                    <div class="hero-actions">
                        <a class="btn btn-outline" href="./">Volver al dashboard</a>
                    </div>
                </div>
                <div class="hero-kpis">
                    <div class="kpi-grid">
                        <div class="stat-card"><small>Registros</small><strong><?= number_format((int) $summary['entries'], 0, ',', '.') ?></strong></div>
                        <div class="stat-card"><small>Cantidad acumulada</small><strong><?= number_format((float) $summary['quantity'], 3, ',', '.') ?></strong></div>
                    </div>
                </div>
            </header>

            <div class="page-grid v2">
                <main class="main-column">
                    <section class="section-card">
                        <div class="panel-header"><div><h2>Registrar producción</h2></div></div>
                        <div class="panel-body">
                        <?php if ($error): ?><div class="setup-error"><?= htmlspecialchars($error) ?></div><?php endif; ?><?php if ($success): ?><div class="setup-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
                        <form method="post">
                            <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                            <div class="form-row form-row--wrap">
                                <label>Temporada<select name="season_id" required><?php foreach ($seasons as $season): ?><option value="<?= (int) $season['id'] ?>"><?= htmlspecialchars($season['name']) ?></option><?php endforeach; ?></select></label>
                                <label>Fundo<select name="farm_id"><option value="">Sin fundo</option><?php foreach ($farms as $farm): ?><option value="<?= (int) $farm['id'] ?>"><?= htmlspecialchars($farm['name']) ?></option><?php endforeach; ?></select></label>
                                <label>Cuartel<select name="block_id"><option value="">Sin cuartel</option><?php foreach ($blocks as $block): ?><option value="<?= (int) $block['id'] ?>"><?= htmlspecialchars($block['code'] . ' · ' . $block['name']) ?></option><?php endforeach; ?></select></label>
                                <label>Especie<select name="species_id"><option value="">Sin especie</option><?php foreach ($species as $item): ?><option value="<?= (int) $item['id'] ?>"><?= htmlspecialchars($item['name'] . ($item['variety'] ? ' · ' . $item['variety'] : '')) ?></option><?php endforeach; ?></select></label>
                            </div>
                            <div class="form-row">
                                <label>Fecha<input type="date" name="production_date" value="<?= date('Y-m-d') ?>" required></label>
                                <label>Actividad<input name="activity" required placeholder="Cosecha, poda, raleo"></label>
                                <label>Cantidad<input type="number" name="quantity" min="0.001" step="0.001" required></label>
                                <label>Unidad<input name="unit" required placeholder="kg, bins, cajas"></label>
                            </div>
                            <div class="form-row">
                                <label>Calidad<input name="quality" placeholder="Exportación, mercado interno"></label>
                                <label>Observaciones<input name="notes"></label>
                            </div>
                            <div class="form-actions"><button class="btn" type="submit">Registrar producción</button></div>
                        </form>
                    </section>

                    <section class="section-card">
                        <div class="panel-header"><div><h2>Últimos registros</h2></div></div>
                        <div class="panel-body">
                        <div class="table-scroll">
                            <table class="data-table">
                                <thead><tr><th>Fecha</th><th>Actividad</th><th>Ubicación</th><th>Cantidad</th></tr></thead>
                                <tbody><?php foreach ($entries as $entry): ?><tr>
                                            <td><?= htmlspecialchars($entry['production_date']) ?></td>
                                            <td><b><?= htmlspecialchars($entry['activity']) ?></b><small><?= htmlspecialchars($entry['species_name'] ?: 'Sin especie') ?></small></td>
                                            <td><?= htmlspecialchars($entry['farm_name'] ?: 'Sin fundo') ?></td>
                                            <td><b><?= number_format((float) $entry['quantity'], 3, ',', '.') ?> <?= htmlspecialchars($entry['unit']) ?></b></td>
                                        </tr><?php endforeach; ?></tbody>
                            </table>
                        </div>
                    </section>
                </main>

                <aside class="sidebar-column v2">
                    <section class="card compact">
                        <h4>Acciones rápidas</h4>
                        <nav class="stack-nav"><a class="link" href="?module=production&view=import">Importar</a><a class="link" href="?module=production&view=export">Exportar</a></nav>
                    </section>
                </aside>
            </div>
        </section>
    </main>
</body>

</html>