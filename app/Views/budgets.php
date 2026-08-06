<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Presupuestos | Sistema de Gestión Agrícola PCCURICO</title>
    <link rel="stylesheet" href="assets/css/app.css">
</head>

<body class="admin-page">
    <main class="admin-shell"><?php require dirname(__DIR__) . '/Views/partials/module-navigation.php'; ?>
        <section class="module-content module-v2 budgets-v2">
            <header class="page-hero">
                <div class="hero-meta">
                    <div class="hero-title">
                        <p class="eyebrow">Planificación financiera</p>
                        <h1>Presupuestos</h1>
                        <p class="lead-text">Define montos por temporada y centro de costo, y compáralos contra lo ejecutado.</p>
                    </div>
                    <div class="hero-actions"><a class="btn btn-outline" href="./">Volver al dashboard</a></div>
                </div>
            </header>

            <div class="page-grid v2">
                <main class="main-column">
                    <section class="section-card">
                        <div class="panel-header"><div><h2>Nuevo presupuesto</h2></div></div>
                        <div class="panel-body">
                        <form method="post">
                            <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                            <div class="form-row">
                                <label>Temporada<select name="season_id" required><?php foreach ($seasons as $season): ?><option value="<?= (int) $season['id'] ?>"><?= htmlspecialchars($season['name']) ?></option><?php endforeach; ?></select></label>
                                <label>Centro de costo<select name="cost_center_id" required><?php foreach ($centers as $center): ?><option value="<?= (int) $center['id'] ?>"><?= htmlspecialchars($center['name']) ?></option><?php endforeach; ?></select></label>
                            </div>
                            <div class="form-row">
                                <label>Inicio<input type="date" name="period_start" required></label>
                                <label>Término<input type="date" name="period_end" required></label>
                            </div>
                            <div class="form-row">
                                <label>Monto<input type="number" name="amount" min="0.01" step="0.01" required></label>
                                <label>Notas<input name="notes"></label>
                            </div>
                            <div class="form-actions"><button class="btn" type="submit">Crear presupuesto</button></div>
                        </form>
                    </section>

                    <section class="section-card">
                        <div class="panel-header"><div><h2>Control presupuestario</h2></div></div>
                        <div class="panel-body">
                        <div class="table-scroll"><table class="data-table"><thead><tr><th>Período</th><th>Centro</th><th>Presupuesto</th><th>Ejecutado</th></tr></thead><tbody><?php foreach ($budgets as $budget): ?><tr><td><b><?= htmlspecialchars(($budget['season_name'] ?? '')) ?></b><small><?= htmlspecialchars(($budget['period_start'] ?? '')) ?> al <?= htmlspecialchars(($budget['period_end'] ?? '')) ?></small></td><td><?= htmlspecialchars(($budget['center_name'] ?? '')) ?></td><td>$<?= number_format((float) ($budget['amount'] ?? 0), 0, ',', '.') ?></td><td><b>$<?= number_format((float) ($budget['actual_amount'] ?? 0), 0, ',', '.') ?></b></td></tr><?php endforeach; ?></tbody></table></div>
                        </div>
                    </section>
                </main>

                <aside class="sidebar-column v2">
                    <section class="section-card compact">
                        <div class="panel-header"><h4>Acciones</h4></div>
                        <div class="panel-body">
                        <nav class="stack-nav"><a class="link" href="?module=budgets&view=import">Importar</a><a class="link" href="?module=budgets&view=export">Exportar</a></nav>
                        </div>
                    </section>
                </aside>
            </div>
        </section>
    </main>
</body>

</html>