<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Trabajador | Sistema de Gestión Agrícola PCCURICO</title>
    <link rel="stylesheet" href="assets/css/app.css">
</head>

<body class="admin-page">
    <main class="admin-shell"><?php require dirname(__DIR__) . '/Views/partials/module-navigation.php'; ?><section class="module-content">
            <header class="admin-header">
                <div>
                    <p class="eyebrow">RR.HH</p>
                    <h1>Trabajador</h1>
                    <p class="setup-copy">Gestiona trabajadores, perfiles profesionales y registros de labor.</p>
                </div>
                <div class="header-actions">
                    <a class="primary-button" href="?module=labor&view=worker-form">Agregar Trabajador</a>
                    <a class="secondary-link" href="./">Volver al dashboard</a>
                </div>
            </header><?php if ($error): ?><div class="setup-error"><?= htmlspecialchars($error) ?></div><?php endif; ?><?php if ($success): ?><div class="setup-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

            <section class="admin-columns">
                <article class="admin-panel">
                    <header class="panel-header">
                        <h2>Registrar labor</h2>
                        <p>El total se calcula con cantidad por tarifa</p>
                    </header>
                    <form method="post" class="admin-form"><input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="action" value="create_labor"><label>Trabajador<select name="worker_id" required><?php foreach ($workers as $worker): ?><option value="<?= (int) $worker['id'] ?>"><?= htmlspecialchars($worker['full_name']) ?></option><?php endforeach; ?></select></label><label>Temporada<select name="season_id" required><?php foreach ($seasons as $season): ?><option value="<?= (int) $season['id'] ?>"><?= htmlspecialchars($season['name']) ?></option><?php endforeach; ?></select></label><label>Fundo<select name="farm_id">
                                <option value="">Sin fundo</option><?php foreach ($farms as $farm): ?><option value="<?= (int) $farm['id'] ?>"><?= htmlspecialchars($farm['name']) ?></option><?php endforeach; ?>
                            </select></label><label>Cuartel<select name="block_id">
                                <option value="">Sin cuartel</option><?php foreach ($blocks as $block): ?><option value="<?= (int) $block['id'] ?>"><?= htmlspecialchars($block['code'] . ' · ' . $block['name']) ?></option><?php endforeach; ?>
                            </select></label><label>Fecha<input type="date" name="labor_date" required value="<?= date('Y-m-d') ?>"></label><label>Labor<input name="labor_type" required placeholder="Poda, raleo, cosecha"></label><label>Cantidad<input type="number" name="quantity" min="0.01" step="0.01" required></label><label>Tarifa unitaria<input type="number" name="unit_rate" min="0" step="0.01" required></label><button class="primary-button" type="submit">Registrar labor</button></form>
                </article>
            </section>

            <section class="admin-panel">
                <header class="panel-header">
                    <h2>Perfil Profesional</h2>
                    <p>Listado de trabajadores con acceso a ver, editar e inactivar registros.</p>
                </header>
                <div class="table-scroll" style="padding: 0 0 22px;">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Trabajador</th>
                                <th>RUT</th>
                                <th>Tipo</th>
                                <th>Departamento</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
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
                                    <td>
                                        <div class="table-action-cell">
                                            <a class="table-action" href="?module=labor&worker_id=<?= (int) $worker['id'] ?>&view=worker-form">Editar</a>
                                            <form method="post" style="margin:0;">
                                                <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                                                <input type="hidden" name="action" value="toggle_worker">
                                                <input type="hidden" name="worker_id" value="<?= (int) $worker['id'] ?>">
                                                <input type="hidden" name="active" value="<?= (int) (($worker['active'] ?? 1) === 1 ? 0 : 1) ?>">
                                                <button class="table-action" type="submit"><?= (int) ($worker['active'] ?? 1) === 1 ? 'Inactivar' : 'Activar' ?></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="admin-panel labor-entries">
                <header class="panel-header">
                    <h2>Últimas labores</h2>
                    <p>Historial operativo</p>
                </header>
                <div class="table-scroll">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Trabajador</th>
                                <th>Labor</th>
                                <th>Ubicación</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody><?php foreach ($entries as $entry): ?><tr>
                                    <td><?= htmlspecialchars($entry['labor_date']) ?></td>
                                    <td><?= htmlspecialchars($entry['full_name']) ?></td>
                                    <td><?= htmlspecialchars($entry['labor_type']) ?></td>
                                    <td><?= htmlspecialchars($entry['farm_name'] ?: 'Sin fundo') ?></td>
                                    <td><b>$<?= number_format((float) $entry['total_amount'], 0, ',', '.') ?></b></td>
                                </tr><?php endforeach; ?></tbody>
                    </table>
                </div>
            </section>
        </section>
    </main>
</body>

</html>