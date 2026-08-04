<?php
$toolsStatus = $status ?? [];
$backups = $backups ?? [];
$logs = $logs ?? [];
$error = $error ?? null;
$success = $success ?? null;
$installedVersion = (string) ($toolsStatus['installed_version'] ?? 'sin-migraciones');
$availableVersion = (string) ($toolsStatus['available_version'] ?? 'sin-migraciones');
$missingTables = (array) ($toolsStatus['missing_tables'] ?? []);
$missingColumns = (int) ($toolsStatus['missing_columns'] ?? 0);
$backupCount = (int) ($toolsStatus['backup_count'] ?? 0);
$updateAvailable = (bool) ($toolsStatus['can_update'] ?? false);
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Herramientas | Sistema de Gestión Agrícola PCCURICO</title>
    <link rel="stylesheet" href="assets/css/app.css">
    <style>
        .admin-shell { display: grid; grid-template-columns: 300px minmax(0, 1fr); min-height: 100vh; }
        .module-content { grid-column: 2; width: min(100%, 1560px); min-width: 0; margin: 0 auto; padding: 104px clamp(20px, 3vw, 52px) 48px; }
        .admin-header { display: flex; justify-content: space-between; gap: 20px; align-items: center; margin-bottom: 24px; }
        .admin-header h1 { margin: 0; font-size: clamp(28px, 3vw, 42px); letter-spacing: -.04em; }
        .eyebrow { margin: 0 0 5px; color: #3c8269; font-size: 10px; font-weight: 800; letter-spacing: .11em; text-transform: uppercase; }
        .setup-copy { margin: 5px 0 0; color: #6c7c74; font-size: 13px; }
        .secondary-link, .primary-button, .table-action { display: inline-flex; justify-content: center; align-items: center; min-height: 36px; padding: 8px 12px; border: 1px solid #d9e1da; border-radius: 8px; color: #3c8269; background: #fff; font-size: 12px; font-weight: 800; text-decoration: none; }
        .primary-button { border-color: #3c8269; color: #fff; background: #3c8269; }
        .admin-columns { display: flex; flex-direction: column; gap: 20px; width: 100%; margin: 0 0 20px; }
        .admin-panel { width: 100%; overflow: hidden; border: 1px solid #d9e1da; border-radius: 16px; background: #fff; box-shadow: 0 9px 24px rgb(24 54 46 / 6%); }
        .panel-header { display: flex; justify-content: space-between; align-items: baseline; gap: 16px; padding: 17px 22px; border-bottom: 1px solid #d9e1da; }
        .panel-header h2 { margin: 0; font-size: 16px; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 12px; margin: 0 0 18px; padding: 18px 22px 0; }
        .stat-card { display: grid; gap: 5px; padding: 16px; border: 1px solid #d9e1da; border-radius: 12px; background: #fff; box-shadow: 0 5px 16px rgb(24 54 46 / 4%); }
        .stat-card span { color: #6c7c74; font-size: 11px; font-weight: 800; text-transform: uppercase; }
        .stat-card strong { color: #3c8269; font-size: clamp(17px, 2vw, 24px); line-height: 1.15; max-width: 100%; word-break: break-word; overflow-wrap: anywhere; }
        .version-label { display: block; min-width: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .toolbar-actions { display: flex; flex-wrap: wrap; gap: 8px; margin: 16px 0 0; padding: 0 22px 22px; }
        .toolbar-actions form, .table-action-form { margin: 0; }
        .step-list { display: grid; gap: 12px; margin: 0; padding: 18px 22px; }
        .step-card { display: grid; gap: 10px; padding: 18px; border: 1px solid #d9e1da; border-radius: 16px; background: #fbfdfb; }
        .step-card strong { display: block; font-size: 14px; }
        .step-card span { color: #6c7c74; font-size: 12px; }
        .step-card .step-status { font-weight: 700; color: #3c8269; }
        .simple-list { display: grid; gap: 8px; margin: 0; padding: 12px 22px 20px 40px; color: #26332d; font-size: 12px; }
        .table-scroll { width: 100%; overflow: auto; }
        .admin-table { width: 100%; min-width: 0; border-collapse: collapse; table-layout: auto; }
        .admin-table th { padding: 11px 16px; background: #f7f9f6; color: #6c7c74; font-size: 10px; text-align: left; text-transform: uppercase; }
        .admin-table td { padding: 13px 16px; border-top: 1px solid #d9e1da; overflow-wrap: anywhere; vertical-align: top; white-space: normal; }
        @media (max-width: 780px) { .admin-shell { display: block; } .module-content { padding: 24px 16px 32px; } .admin-header { align-items: flex-start; flex-direction: column; } .stats-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body class="admin-page">
    <main class="admin-shell">
        <?php require dirname(__DIR__) . '/Views/partials/module-navigation.php'; ?>
        <section class="module-content">
            <header class="admin-header"><div><p class="eyebrow">Centro de Herramientas</p><h1>Herramientas del sistema</h1><p class="setup-copy">Actualiza, sincroniza y repara el ERP conservando la información del cliente.</p></div><a class="secondary-link" href="./">Volver al dashboard</a></header>
            <?php if ($error): ?><div class="setup-error"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
            <?php if ($success): ?><div class="setup-success"><?= htmlspecialchars((string) $success, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
            <section class="admin-columns">
                <article class="admin-panel">
                    <header class="panel-header"><div><h2>Guía paso a paso</h2><p>Ejecuta estas tareas en orden para aplicar el cambio de esquema con seguridad.</p></div></header>
                    <div class="step-list">
                        <div class="step-card">
                            <strong>Paso 1: Crear respaldo</strong>
                            <span>Genera una copia de seguridad de la base de datos y la configuración antes de realizar cambios.</span>
                            <span class="step-status"><?= $backupCount > 0 ? 'OK: Ya existe al menos un respaldo' : 'Pendiente: crea un respaldo primero' ?></span>
                            <div class="toolbar-actions">
                                <form method="post" class="table-action-form"><input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="action" value="backup"><button class="primary-button" type="submit">Crear respaldo</button></form>
                            </div>
                        </div>
                        <div class="step-card">
                            <strong>Paso 2: Sincronizar esquema</strong>
                            <span>Aplica las migraciones pendientes para actualizar la estructura de la base de datos.</span>
                            <span class="step-status"><?= ($missingTables === [] && $missingColumns === 0) ? 'OK: La estructura está sincronizada' : 'Pendiente: sincroniza la estructura ahora' ?></span>
                            <div class="toolbar-actions">
                                <form method="post" class="table-action-form"><input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="action" value="sync_schema"><button class="secondary-link" type="submit">Sincronizar esquema</button></form>
                            </div>
                        </div>
                        <?php if ($updateAvailable): ?>
                        <div class="step-card">
                            <strong>Paso 3: Actualizar ERP</strong>
                            <span>Realiza un respaldo automático y aplica todas las migraciones adicionales en un solo paso.</span>
                            <span class="step-status">Pendiente: hay una actualización disponible</span>
                            <div class="toolbar-actions">
                                <form method="post" class="table-action-form"><input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="action" value="update"><button class="primary-button" type="submit">Actualizar ERP</button></form>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </article>
                <article class="admin-panel">
                    <header class="panel-header"><div><h2>Estado del sistema</h2><p>Versión instalada, actualización disponible y Estado de sincronización.</p></div></header>
                    <div class="stats-grid">
                        <div class="stat-card"><span>Versión instalada</span><strong class="version-label" title="<?= htmlspecialchars($installedVersion, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($installedVersion, ENT_QUOTES, 'UTF-8') ?></strong></div>
                        <div class="stat-card"><span>Versión disponible</span><strong class="version-label" title="<?= htmlspecialchars($availableVersion, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($availableVersion, ENT_QUOTES, 'UTF-8') ?></strong></div>
                        <div class="stat-card"><span>Respaldos disponibles</span><strong><?= $backupCount ?></strong></div>
                        <div class="stat-card"><span>Drift de esquema</span><strong><?= count($missingTables) + $missingColumns ?></strong></div>
                    </div>
                    <div class="toolbar-actions">
                        <form method="post" class="table-action-form"><input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="action" value="backup"><button class="primary-button" type="submit">Crear respaldo</button></form>
                        <form method="post" class="table-action-form"><input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="action" value="sync_schema"><button class="secondary-link" type="submit">Sincronizar esquema</button></form>
                        <form method="post" class="table-action-form"><input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="action" value="repair"><button class="secondary-link" type="submit">Reparar sistema</button></form>
                        <?php if ($updateAvailable): ?><form method="post" class="table-action-form"><input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="action" value="update"><button class="primary-button" type="submit">Actualizar ERP</button></form><?php endif; ?>
                    </div>
                </article>
                <article class="admin-panel">
                    <header class="panel-header"><div><h2>Sincronización de estructura</h2><p>Tablas, columnas y integridad de acceso.</p></div></header>
                    <?php if ($missingTables !== []): ?><ul class="simple-list"><?php foreach ($missingTables as $missingTable): ?><li><?= htmlspecialchars((string) $missingTable, ENT_QUOTES, 'UTF-8') ?></li><?php endforeach; ?></ul><?php else: ?><p class="setup-copy">No hay tablas faltantes detectadas por el comparador actual.</p><?php endif; ?>
                    <p class="setup-copy">Columnas faltantes detectadas: <?= $missingColumns ?></p>
                </article>
            </section>
            <section class="admin-columns">
                <article class="admin-panel">
                    <header class="panel-header"><div><h2>Backups disponibles</h2><p>Restauración segura desde copias actuales.</p></div></header>
                    <div class="table-scroll">
                        <table class="admin-table">
                            <thead><tr><th>ID</th><th>Archivo</th><th>Tamaño</th><th>Estado</th><th>Fecha</th><th>Acción</th></tr></thead>
                            <tbody>
                            <?php foreach ($backups as $backup): ?>
                                <tr>
                                    <td><?= (int) ($backup['id'] ?? 0) ?></td>
                                    <td><?= htmlspecialchars((string) ($backup['file_path'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= (int) ($backup['file_size'] ?? 0) ?> bytes</td>
                                    <td><?= htmlspecialchars((string) ($backup['status'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars((string) ($backup['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td>
                                        <form method="post" class="table-action-form">
                                            <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                                            <input type="hidden" name="action" value="restore">
                                            <input type="hidden" name="backup_id" value="<?= (int) ($backup['id'] ?? 0) ?>">
                                            <button class="table-action" type="submit">Restaurar</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </article>
                <article class="admin-panel">
                    <header class="panel-header"><div><h2>Log de operaciones</h2><p>Historial del centro de herramientas.</p></div></header>
                    <div class="table-scroll">
                        <table class="admin-table">
                            <thead><tr><th>Canal</th><th>Usuario</th><th>Mensaje</th><th>Fecha</th></tr></thead>
                            <tbody>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td><?= htmlspecialchars((string) ($log['channel'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars((string) ($log['user_name'] ?? 'Sistema'), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars((string) ($log['message'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars((string) ($log['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </article>
            </section>
        </section>
    </main>
</body>
</html>
