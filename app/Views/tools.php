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
                        <div class="stat-card"><span>App local</span><strong class="version-label" title="<?= htmlspecialchars($localAppVersion, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($localAppVersion, ENT_QUOTES, 'UTF-8') ?></strong></div>
                        <div class="stat-card"><span>Release GitHub</span><strong class="version-label" title="<?= htmlspecialchars($remoteVersion, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($remoteVersion, ENT_QUOTES, 'UTF-8') ?></strong></div>
                        <div class="stat-card"><span>Respaldos disponibles</span><strong><?= $backupCount ?></strong></div>
                        <div class="stat-card"><span>Drift de esquema</span><strong><?= count($missingTables) + $missingColumns ?></strong></div>
                    </div>
                    <?php if (!empty($remoteError)): ?><div class="panel-header"><p class="setup-copy">Aviso: <?= htmlspecialchars($remoteError, ENT_QUOTES, 'UTF-8') ?></p></div><?php endif; ?>
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
                    </div>
                    <div class="toolbar-actions">
                        <form method="post" class="table-action-form"><input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="action" value="backup"><button class="primary-button" type="submit">Crear respaldo</button></form>
                        <form method="post" class="table-action-form"><input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="action" value="sync_schema"><button class="secondary-link" type="submit">Sincronizar esquema</button></form>
                        <form method="post" class="table-action-form"><input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="action" value="repair"><button class="secondary-link" type="submit">Reparar sistema</button></form>
                        <?php if ($remoteUpdateAvailable): ?><form id="remote-update-form" action="?module=tools" method="post" class="table-action-form"><input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="action" value="remote_update"><button id="remote-update-button" class="primary-button" type="submit">Descargar y aplicar actualización</button></form><?php endif; ?>
                    </div>
                </article>
                <article class="admin-panel">
                    <header class="panel-header"><div><h2>Progreso de actualización remota</h2><p>Seguimiento del proceso de descarga e instalación.</p></div></header>
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
