<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Trabajador | Sistema de Gestión Agrícola PCCURICO</title>
    <link rel="stylesheet" href="assets/css/app.css">
</head>

<body class="admin-page">
    <main class="admin-shell"><?php require dirname(__DIR__) . '/Views/partials/module-navigation.php'; ?>
        <section class="module-content module-v2 labor-v2">
            <header class="page-hero">
                <div class="hero-meta">
                    <div class="hero-title">
                        <p class="eyebrow">RR.HH</p>
                        <h1>Trabajadores</h1>
                        <p class="lead-text">Gestiona trabajadores, perfiles profesionales y registros de labor.</p>
                    </div>
                    <div class="hero-actions"><a class="btn" href="?module=labor&view=worker-form">Agregar Trabajador</a><a class="btn btn-outline" href="./">Volver al dashboard</a></div>
                </div>
            </header>

            <div class="page-grid v2">
                <main class="main-column">
                    <section class="section-card">
                        <div class="panel-header">
                            <div>
                                <h2>Registrar labor</h2>
                                <p>El total se calcula con cantidad por tarifa</p>
                            </div>
                        </div>
                        <div class="panel-body">
                            <?php if ($error): ?><div class="setup-error"><?= htmlspecialchars($error) ?></div><?php endif; ?><?php if ($success): ?><div class="setup-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
                            <form method="post">
                                <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="action" value="create_labor">
                                <div class="form-group">
                                    <label>Trabajador</label>
                                    <select name="worker_id" required><?php foreach ($workers as $worker): ?><option value="<?= (int) $worker['id'] ?>"<?= ((int) $worker['id'] === (int) ($selected_worker_id ?? 0)) ? ' selected' : '' ?>><?= htmlspecialchars($worker['full_name']) ?></option><?php endforeach; ?></select>
                                </div>
                                <div class="form-group">
                                    <label>Temporada</label>
                                    <select name="season_id" required><?php foreach ($seasons as $season): ?><option value="<?= (int) $season['id'] ?>"<?= ((int) $season['id'] === (int) ($selected_season_id ?? 0)) ? ' selected' : '' ?>><?= htmlspecialchars($season['name']) ?></option><?php endforeach; ?></select>
                                </div>
                                <div class="form-group">
                                    <label>Fundo</label>
                                    <select name="farm_id"><option value="">Sin fondo</option><?php foreach ($farms as $farm): ?><option value="<?= (int) $farm['id'] ?>"><?= htmlspecialchars($farm['name']) ?></option><?php endforeach; ?></select>
                                </div>
                                <div class="form-group">
                                    <label>Cuartel</label>
                                    <select name="block_id"><option value="">Sin cuartel</option><?php foreach ($blocks as $block): ?><option value="<?= (int) $block['id'] ?>"><?= htmlspecialchars($block['code'] . ' · ' . $block['name']) ?></option><?php endforeach; ?></select>
                                </div>
                                <div class="form-group">
                                    <label>Fecha</label>
                                    <input type="date" name="labor_date" required value="<?= date('Y-m-d') ?>">
                                </div>
                                <div class="form-group">
                                    <label>Labor</label>
                                    <input name="labor_type" required placeholder="Poda, raleo, cosecha">
                                </div>
                                <div class="form-group">
                                    <label>Cantidad</label>
                                    <input type="number" name="quantity" min="0.01" step="0.01" required>
                                </div>
                                <div class="form-group">
                                    <label>Tarifa unitaria</label>
                                    <input type="number" name="unit_rate" min="0" step="1" required>
                                </div>
                                <div class="form-actions"><button class="primary-button" type="submit">Registrar labor</button></div>
                            </form>
                        </div>
                    </section>

                    <section class="section-card">
                        <div class="panel-header"><div><h2>Trabajadores</h2></div></div>
                        <div class="panel-body">
                            <div class="table-wrap">
                                <table class="data-table">
                                    <thead>
                                        <tr><th>Trabajador</th><th>RUT</th><th>Tipo</th><th>Departamento</th><th>Estado</th><th>Acciones</th></tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($workers as $worker): ?>
                                            <?php $profileInfo = $worker['profile'] ?? []; ?>
                                            <tr>
                                                <td><b><?= htmlspecialchars((string) ($worker['full_name'] ?? '')) ?></b><small><?= htmlspecialchars((string) ($worker['position'] ?? 'Sin cargo')) ?></small></td>
                                                <td><?= htmlspecialchars((string) ($worker['tax_id'] ?: 'Sin RUT')) ?></td>
                                                <td><?= htmlspecialchars((string) ($worker['worker_type'] ?: 'Sin tipo')) ?></td>
                                                <td><?= htmlspecialchars((string) ($worker['department'] ?: '-')) ?></td>
                                                <td><span class="status-pill <?= (int) ($worker['active'] ?? 1) === 1 ? 'status-active' : 'status-inactive' ?>"><?= (int) ($worker['active'] ?? 1) === 1 ? 'Activo' : 'Inactivo' ?></span></td>
                                                <td class="table-action-cell">
                                                    <a class="table-action" href="?module=labor&worker_id=<?= (int) $worker['id'] ?>&view=worker-form">Editar</a>
                                                    <a class="table-action" href="?module=labor&worker_id=<?= (int) $worker['id'] ?>&view=worker-form&show=1">Ver</a>
                                                    <form method="post" class="inline-form"><input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="action" value="toggle_worker"><input type="hidden" name="worker_id" value="<?= (int) $worker['id'] ?>"><input type="hidden" name="active" value="<?= (int) (($worker['active'] ?? 1) === 1 ? 0 : 1) ?>"><button class="table-action" type="submit"><?= (int) ($worker['active'] ?? 1) === 1 ? 'Inactivar' : 'Activar' ?></button></form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>

                    <section class="section-card">
                        <div class="panel-header"><div><h2>Últimas labores</h2><p>Historial operativo</p></div></div>
                        <div class="panel-body">
                            <div class="table-wrap">
                                <table class="data-table">
                                    <thead><tr><th>Fecha</th><th>Trabajador</th><th>Labor</th><th>Ubicación</th><th>Total</th></tr></thead>
                                    <tbody><?php foreach ($entries as $entry): ?><tr><td><?= htmlspecialchars($entry['labor_date']) ?></td><td><?= htmlspecialchars($entry['full_name']) ?></td><td><?= htmlspecialchars($entry['labor_type']) ?></td><td><?= htmlspecialchars($entry['farm_name'] ?: 'Sin fundo') ?></td><td><b>$<?= number_format((float) $entry['total_amount'], 0, ',', '.') ?></b></td></tr><?php endforeach; ?></tbody>
                                </table>
                            </div>
                        </div>
                    </section>
                </main>

                <aside class="admin-panel sidebar-column">
                    <div class="panel-header"><h4>Acciones</h4></div>
                    <div class="panel-body">
                        <nav class="module-links"><a href="?module=labor&view=import">Importar</a><a href="?module=labor&view=workers">Gestionar</a></nav>
                    </div>
                </aside>
            </div>
        </section>
    </main>
</body>

</html>