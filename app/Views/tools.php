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
$pendingMigrations = (array) ($toolsStatus['pending_migrations'] ?? []);
$operation = (array) ($operation ?? []);
$operationAction = (string) ($operation_action ?? '');
$demoStatus = (array) ($demo_status ?? []);
$activeDemo = $demoStatus['active'] ?? null;
$remoteVersion = (string) ($toolsStatus['remote_version'] ?? 'no disponible');
$remoteUrl = (string) ($toolsStatus['remote_url'] ?? '');
$remoteError = (string) ($toolsStatus['remote_error'] ?? '');
$remoteUpdateAvailable = (bool) ($toolsStatus['remote_update_available'] ?? false);
$remoteProgress = $toolsStatus['remote_progress'] ?? ['current' => 0, 'total' => 0, 'status' => 'idle', 'message' => ''];
$localAppVersion = (string) ($toolsStatus['local_app_version'] ?? 'no definido');
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Herramientas | Sistema de Gestión Agrícola PCCURICO</title>
    <link rel="stylesheet" href="assets/css/app.css">
</head>
<body class="admin-page">
    <main class="admin-shell">
        <?php require dirname(__DIR__) . '/Views/partials/module-navigation.php'; ?>
        <section class="module-content tools-page">
            <header class="admin-header tools-page-header"><div><p class="eyebrow">Centro de Herramientas</p><h1>Herramientas del sistema</h1><p class="setup-copy">Mantenimiento y seguridad del sistema.</p></div><a class="secondary-link" href="./">Volver al dashboard</a></header>
            <?php if ($error): ?><div class="setup-error"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
            <?php if ($success): ?><div class="setup-success"><?= htmlspecialchars((string) $success, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
            <?php if ($operationAction === 'sync_schema' && $operation !== []): ?>
            <section class="tools-operation-result <?= !empty($operation['verified']) ? 'tools-operation-success' : 'tools-operation-warning' ?>">
                <div><strong>Sincronización <?= !empty($operation['verified']) ? 'completa' : 'con pendientes' ?></strong><span>Resultado de la última ejecución.</span></div>
                <div class="tools-operation-details"><span>Antes: <?= htmlspecialchars((string) ($operation['before'] ?? 'sin-migraciones'), ENT_QUOTES, 'UTF-8') ?></span><span>Ahora: <?= htmlspecialchars((string) ($operation['after'] ?? 'sin-migraciones'), ENT_QUOTES, 'UTF-8') ?></span><span>Aplicadas: <?= count((array) ($operation['applied'] ?? [])) ?></span><span>Pendientes: <?= count((array) ($operation['pending'] ?? [])) ?></span></div>
            </section>
            <?php endif; ?>
            <section class="tools-demo-panel">
                <div class="tools-demo-copy"><span class="tools-section-kicker">Datos de prueba</span><h2>Demo agrícola</h2><p><?= $activeDemo ? 'Hay datos demo instalados para explorar los módulos.' : 'Carga información de ejemplo para probar el sistema.' ?></p></div>
                <div class="tools-demo-status"><strong><?= $activeDemo ? 'Instalada' : 'No instalada' ?></strong><?php if ($activeDemo): ?><span><?= (int) ($activeDemo['records_count'] ?? 0) ?> registros · versión <?= htmlspecialchars((string) ($activeDemo['version'] ?? '2.0'), ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?></div>
                <div class="tools-demo-actions">
                    <?php if (!$activeDemo): ?><form method="post"><input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="action" value="demo_install"><button class="primary-button" type="submit">Cargar demo</button></form><?php else: ?><form method="post" onsubmit="return confirm('¿Reemplazar todos los datos demo actuales?');"><input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="action" value="demo_reinstall"><button class="secondary-link" type="submit">Recargar demo</button></form><form method="post" onsubmit="return confirm('¿Eliminar todos los datos demo?');"><input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="action" value="demo_remove"><button class="table-action table-action--danger" type="submit">Eliminar demo</button></form><?php endif; ?>
                </div>
            </section>
            <section class="admin-columns tools-overview-grid">
                <article class="admin-panel tools-actions-panel">
                    <header class="panel-header"><div><span class="tools-section-kicker">Acciones</span><h2>Mantenimiento</h2><p>Ejecuta una acción a la vez.</p></div></header>
                    <div class="step-list">
                        <div class="step-card">
                            <strong>1. Crear respaldo</strong>
                            <span>Copia de seguridad antes de modificar.</span>
                            <span class="step-status"><?= $backupCount > 0 ? 'OK: Ya existe al menos un respaldo' : 'Pendiente: crea un respaldo primero' ?></span>
                            <div class="toolbar-actions">
                                <form method="post" class="table-action-form"><input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="action" value="backup"><button class="primary-button" type="submit">Crear respaldo</button></form>
                            </div>
                        </div>
                        <div class="step-card">
                            <strong>2. Sincronizar esquema</strong>
                            <span>Aplica cambios de estructura pendientes.</span>
                            <span class="step-status"><?= ($missingTables === [] && $missingColumns === 0 && $pendingMigrations === []) ? 'OK: Estructura verificada' : 'Pendiente: hay cambios por aplicar' ?></span>
                            <div class="toolbar-actions">
                                <form method="post" class="table-action-form"><input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="action" value="sync_schema"><button class="secondary-link" type="submit">Verificar y sincronizar</button></form>
                            </div>
                        </div>
                        <?php if ($updateAvailable): ?>
                        <div class="step-card">
                            <strong>3. Actualizar ERP</strong>
                            <span>Respalda y aplica la actualización.</span>
                            <span class="step-status">Pendiente: hay una actualización disponible</span>
                            <div class="toolbar-actions">
                                <form method="post" class="table-action-form"><input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="action" value="update"><button class="primary-button" type="submit">Actualizar ERP</button></form>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </article>
                <article class="admin-panel tools-status-panel">
                    <header class="panel-header"><div><span class="tools-section-kicker">Diagnóstico</span><h2>Estado del sistema</h2><p>Resumen de instalación y estructura.</p></div></header>
                    <div class="stats-grid">
                        <div class="stat-card"><span>Estado de esquema</span><strong><?= ($missingTables === [] && $missingColumns === 0 && $pendingMigrations === []) ? 'Sincronizado' : 'Pendiente' ?></strong></div>
                        <div class="stat-card"><span>Migraciones pendientes</span><strong><?= count($pendingMigrations) ?></strong></div>
                        <div class="stat-card"><span>Versión instalada</span><strong class="version-label" title="<?= htmlspecialchars($installedVersion, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($installedVersion, ENT_QUOTES, 'UTF-8') ?></strong></div>
                        <div class="stat-card"><span>Versión disponible</span><strong class="version-label" title="<?= htmlspecialchars($availableVersion, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($availableVersion, ENT_QUOTES, 'UTF-8') ?></strong></div>
                        <div class="stat-card"><span>App local</span><strong class="version-label" title="<?= htmlspecialchars($localAppVersion, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($localAppVersion, ENT_QUOTES, 'UTF-8') ?></strong></div>
                        <div class="stat-card"><span>Release GitHub</span><strong class="version-label" title="<?= htmlspecialchars($remoteVersion, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($remoteVersion, ENT_QUOTES, 'UTF-8') ?></strong></div>
                        <div class="stat-card"><span>Respaldos disponibles</span><strong><?= $backupCount ?></strong></div>
                        <div class="stat-card"><span>Drift de esquema</span><strong><?= count($missingTables) + $missingColumns ?></strong></div>
                    </div>
                    <?php if ($pendingMigrations !== []): ?><div class="tools-pending-list"><strong>Cambios detectados para aplicar</strong><span><?= htmlspecialchars(implode(', ', $pendingMigrations), ENT_QUOTES, 'UTF-8') ?></span></div><?php endif; ?>
                    <?php if (!empty($remoteError)): ?><div class="tools-pending-list"><strong>Actualización remota no disponible</strong><span>La sincronización local sigue operativa.</span></div><?php endif; ?>
                    <?php if ($remoteUpdateAvailable): ?><div class="panel-header"><p class="setup-copy">Nueva versión disponible en GitHub. Esta actualización se descargará y aplicará automáticamente.</p></div><?php endif; ?>
                    <?php if ($remoteProgress['status'] !== 'idle'): ?>
                        <div class="panel-header">
                            <div>
                                <p class="setup-copy">Estado: <?= htmlspecialchars($remoteProgress['message'], ENT_QUOTES, 'UTF-8') ?></p>
                                <p class="setup-copy">Progreso: <?= (int) ($remoteProgress['current'] ?? 0) ?> / <?= (int) ($remoteProgress['total'] ?? 0) ?></p>
                            </div>
                        </div>
                        <div class="progress-panel">
                            <div class="progress-meter">
                                <div id="remote-update-progress-bar-summary" class="progress-meter-inner" data-progress-current="<?= (int) ($remoteProgress['current'] ?? 0) ?>" data-progress-total="<?= (int) ($remoteProgress['total'] ?? 0) ?>"></div>
                            </div>
                        </div>
                    <?php endif; ?>
                </article>
                <?php if ($remoteProgress['status'] !== 'idle' || $remoteUpdateAvailable): ?>
                <article class="admin-panel tools-remote-panel">
                    <header class="panel-header"><div><span class="tools-section-kicker">Actualización remota</span><h2>Progreso de actualización</h2><p>Seguimiento del proceso de descarga e instalación.</p></div></header>
                    <div class="progress-panel">
                        <div class="step-card step-card--flush">
                            <strong>Estado actual</strong>
                            <span id="remote-update-status-message"><?= htmlspecialchars($remoteProgress['message'] ?: 'idle', ENT_QUOTES, 'UTF-8') ?></span>
                            <span id="remote-update-status-values">Progreso: <?= (int) ($remoteProgress['current'] ?? 0) ?> / <?= (int) ($remoteProgress['total'] ?? 0) ?></span>
                            <div class="progress-meter">
                                <div id="remote-update-progress-bar" class="progress-meter-inner" data-progress-current="<?= (int) ($remoteProgress['current'] ?? 0) ?>" data-progress-total="<?= (int) ($remoteProgress['total'] ?? 0) ?>"></div>
                            </div>
                            <div id="remote-update-feedback" class="setup-copy remote-update-feedback"></div>
                        </div>
                    </div>
                    <?php if ($remoteUpdateAvailable): ?><div class="toolbar-actions"><form id="remote-update-form" action="?module=tools" method="post" class="table-action-form"><input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="action" value="remote_update"><button id="remote-update-button" class="primary-button" type="submit">Descargar y aplicar actualización</button></form></div><?php endif; ?>
                </article>
                <?php endif; ?>
                <article class="admin-panel tools-schema-panel">
                    <header class="panel-header"><div><span class="tools-section-kicker">Integridad</span><h2>Estructura</h2><p>Tablas y columnas.</p></div></header>
                    <?php if ($missingTables !== []): ?><ul class="simple-list"><?php foreach ($missingTables as $missingTable): ?><li><?= htmlspecialchars((string) $missingTable, ENT_QUOTES, 'UTF-8') ?></li><?php endforeach; ?></ul><?php else: ?><p class="setup-copy">No hay tablas faltantes detectadas por el comparador actual.</p><?php endif; ?>
                    <p class="setup-copy">Columnas faltantes detectadas: <?= $missingColumns ?></p>
                </article>
            </section>
            <section class="admin-columns tools-history-grid">
                <article class="admin-panel tools-backups-panel">
                    <header class="panel-header"><div><span class="tools-section-kicker">Protección</span><h2>Backups</h2><p>Copias disponibles.</p></div></header>
                    <div class="table-scroll tools-table-wrap">
                        <table class="admin-table tools-table">
                            <thead><tr><th>ID</th><th>Archivo</th><th>Tamaño</th><th>Estado</th><th>Fecha</th><th>Acción</th></tr></thead>
                            <tbody>
                            <?php if ($backups === []): ?><tr><td colspan="6"><div class="empty-state">No hay respaldos disponibles. Crea el primer respaldo antes de realizar cambios estructurales.</div></td></tr><?php endif; ?>
                            <?php foreach ($backups as $backup): ?>
                                <tr>
                                    <td><?= (int) ($backup['id'] ?? 0) ?></td>
                                    <td><?= htmlspecialchars((string) ($backup['file_path'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= (int) ($backup['file_size'] ?? 0) ?> bytes</td>
                                    <td><span class="tools-status-badge <?= strtoupper((string) ($backup['status'] ?? '')) === 'COMPLETED' ? 'is-success' : 'is-neutral' ?>"><?= htmlspecialchars((string) ($backup['status'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span></td>
                                    <td><?= htmlspecialchars((string) ($backup['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td>
                                        <form method="post" class="table-action-form">
                                            <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                                            <input type="hidden" name="action" value="restore">
                                            <input type="hidden" name="backup_id" value="<?= (int) ($backup['id'] ?? 0) ?>">
                                            <button class="table-action" type="submit">Restaurar</button>
                                        </form>
                                        <form method="post" class="table-action-form" onsubmit="return confirm('¿Eliminar este backup de forma permanente?');">
                                            <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                                            <input type="hidden" name="action" value="delete_backup">
                                            <input type="hidden" name="backup_id" value="<?= (int) ($backup['id'] ?? 0) ?>">
                                            <button class="table-action table-action--danger" type="submit">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </article>
                <article class="admin-panel tools-log-panel">
                    <header class="panel-header"><div><span class="tools-section-kicker">Auditoría</span><h2>Actividad</h2><p>Últimas operaciones.</p></div></header>
                    <div class="table-scroll tools-table-wrap">
                        <table class="admin-table tools-table">
                            <thead><tr><th>Fecha</th><th>Mensaje</th><th>Usuario</th><th>Canal</th></tr></thead>
                            <tbody>
                            <?php if ($logs === []): ?><tr><td colspan="4"><div class="empty-state">Todavía no hay operaciones registradas.</div></td></tr><?php endif; ?>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td><?= htmlspecialchars((string) ($log['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td class="tools-message-cell"><?= htmlspecialchars((string) ($log['message'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars((string) ($log['user_name'] ?? 'Sistema'), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><span class="tools-channel-badge"><?= htmlspecialchars((string) preg_replace('/^tools\./', '', (string) ($log['channel'] ?? '')), ENT_QUOTES, 'UTF-8') ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </article>
            </section>
        </section>
    </main>
    <script>
        (function () {
            var statusMessage = document.getElementById('remote-update-status-message');
            var statusValues = document.getElementById('remote-update-status-values');
            var progressBars = [
                document.getElementById('remote-update-progress-bar'),
                document.getElementById('remote-update-progress-bar-summary'),
            ].filter(function (element) { return element !== null; });
            var feedback = document.getElementById('remote-update-feedback');
            var remoteUpdateForm = document.getElementById('remote-update-form');
            var remoteUpdateButton = document.getElementById('remote-update-button');
            var pollingInterval = null;

            function updateProgress(data) {
                if (!data || typeof data !== 'object') {
                    return;
                }
                var current = Number(data.current || 0);
                var total = Number(data.total || 0);
                var message = String(data.message || 'idle');
                var percentage = total > 0 ? Math.min(100, Math.round((current / total) * 100)) : 0;

                if (statusMessage) {
                    statusMessage.textContent = message;
                }
                if (statusValues) {
                    statusValues.textContent = 'Progreso: ' + current + ' / ' + total;
                }
                progressBars.forEach(function (bar) {
                    bar.style.width = percentage + '%';
                });
            }

            function fetchRemoteProgress() {
                fetch('?module=tools&remote_progress=1', { headers: { Accept: 'application/json' } })
                    .then(function (response) {
                        if (!response.ok) {
                            throw new Error('Error de red');
                        }
                        return response.json();
                    })
                    .then(function (data) {
                        updateProgress(data);
                        if (data.status === 'COMPLETED') {
                            clearInterval(pollingInterval);
                            pollingInterval = null;
                            if (remoteUpdateButton) {
                                remoteUpdateButton.disabled = false;
                            }
                            if (feedback) {
                                feedback.textContent = 'Actualización completa.';
                            }
                        }
                    })
                    .catch(function () {
                        if (feedback) {
                            feedback.textContent = 'No se pudo obtener el progreso de actualización.';
                        }
                    });
            }

            if (remoteUpdateForm) {
                remoteUpdateForm.addEventListener('submit', function (event) {
                    event.preventDefault();
                    if (!remoteUpdateButton) {
                        return;
                    }

                    remoteUpdateButton.disabled = true;
                    if (feedback) {
                        feedback.textContent = 'Iniciando actualización remota...';
                    }
                    if (pollingInterval) {
                        clearInterval(pollingInterval);
                    }
                    pollingInterval = setInterval(fetchRemoteProgress, 2000);
                    fetchRemoteProgress();

                    var formData = new FormData(remoteUpdateForm);
                    formData.append('ajax', '1');
                    fetch('?module=tools', {
                        method: 'POST',
                        headers: { Accept: 'application/json' },
                        body: formData,
                    })
                        .then(function (response) {
                            if (!response.ok) {
                                throw new Error('Error de servidor');
                            }
                            return response.json();
                        })
                        .then(function (data) {
                            if (data.error) {
                                feedback.textContent = data.error;
                                if (remoteUpdateButton) {
                                    remoteUpdateButton.disabled = false;
                                }
                            } else if (data.success) {
                                feedback.textContent = data.success;
                            }
                            updateProgress(data.progress || {});
                        })
                        .catch(function () {
                            if (feedback) {
                                feedback.textContent = 'Falló la comunicación con el servidor.';
                            }
                            if (remoteUpdateButton) {
                                remoteUpdateButton.disabled = false;
                            }
                        });
                });
            }
        })();
    </script>
</body>
</html>
