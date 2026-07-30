<?php

declare(strict_types=1);

$active = $active ?? null;
$history = $history ?? [];
$error = $error ?? null;
$success = $success ?? null;
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Demo Data Manager | CampoSur</title>
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="admin-page">
    <main class="admin-shell">
        <?php require dirname(__DIR__) . '/Views/partials/module-navigation.php'; ?>
        <section class="module-content">
            <header class="admin-header">
                <div><p class="eyebrow">Herramientas</p><h1>Demo Data Manager</h1><p class="setup-copy">Carga un entorno de demostración completo usando únicamente datos de prueba identificados.</p></div>
                <a class="secondary-link" href="/">Volver al resumen</a>
            </header>
            <?php if ($error): ?><div class="setup-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
            <?php if ($success): ?><div class="setup-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
            <section class="admin-columns">
                <article class="admin-panel">
                    <header class="panel-header"><h2>Estado de los datos demo</h2><p>La información demo se vincula a la empresa actual y no reemplaza sus datos.</p></header>
                    <?php if ($active): ?>
                        <div class="role-list"><div class="role-row"><div><b>Conjunto instalado</b><small>Instalado el <?= htmlspecialchars($active['installed_at'], ENT_QUOTES, 'UTF-8') ?></small></div><span><?= (int) ($active['records_count'] ?? 0) ?> registros</span></div><div class="role-row"><div><b>Identificador</b><small><?= htmlspecialchars($active['installation_id'], ENT_QUOTES, 'UTF-8') ?></small></div><span><?= htmlspecialchars($active['status'], ENT_QUOTES, 'UTF-8') ?></span></div></div>
                        <div class="module-actions"><form method="post"><input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="action" value="reinstall"><button class="secondary-link" type="submit">Reinstalar datos demo</button></form><form method="post"><input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="action" value="remove"><button class="danger-button" type="submit">Eliminar datos demo</button></form></div>
                    <?php else: ?>
                        <p class="empty-state">No hay datos demo instalados. La empresa se encuentra limpia.</p>
                        <form method="post" class="admin-form"><input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="action" value="install"><button class="primary-button" type="submit">Instalar datos demo</button></form>
                    <?php endif; ?>
                </article>
                <article class="admin-panel">
                    <header class="panel-header"><h2>Qué incluye</h2><p>El conjunto está preparado para recorrer la operación completa.</p></header>
                    <ul class="setup-steps"><li>Maestros agrícolas<span>Fundos, cuarteles, especies, temporadas y centros de costo.</span></li><li>Operación<span>Compras, recepciones, inventario, producción, labores y maquinaria.</span></li><li>Gestión<span>Costos, presupuestos, documentos, tareas, calendario y notificaciones.</span></li></ul>
                </article>
            </section>
            <section class="admin-panel"><header class="panel-header"><h2>Historial de instalaciones</h2><p>Consulta cuándo se cargaron o retiraron conjuntos demo.</p></header><div class="table-scroll"><table class="admin-table"><thead><tr><th>Identificador</th><th>Versión</th><th>Estado</th><th>Instalado</th><th>Retirado</th></tr></thead><tbody><?php foreach ($history as $batch): ?><tr><td><?= htmlspecialchars($batch['installation_id'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($batch['version'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($batch['status'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($batch['installed_at'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($batch['removed_at'] ?: '—', ENT_QUOTES, 'UTF-8') ?></td></tr><?php endforeach; ?></tbody></table></div></section>
        </section>
    </main>
</body>
</html>
