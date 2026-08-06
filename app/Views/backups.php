<?php
$backups = $backups ?? [];
$error = $error ?? null;
$success = $success ?? null;
$progress = $progress ?? ['current' => 0, 'total' => 0, 'status' => 'idle'];

function backupDisplayName(string $path): string
{
    if ($path === '') {
        return '—';
    }
    return basename($path);
}

function backupDisplayStatus(string $status): string
{
    return match (strtoupper($status)) {
        'STARTED' => 'En progreso',
        'COMPLETED' => 'Completado',
        'FAILED' => 'Fallido',
        default => $status !== '' ? $status : '—',
    };
}

function backupDisplayValue(mixed $value): string
{
    if ($value === null || $value === '' || $value === 0) {
        return '—';
    }
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Respaldos | Sistema de Gestión Agrícola PCCURICO</title>
    <link rel="stylesheet" href="assets/css/app.css">
</head>
<body class="admin-page">
    <main class="admin-shell">
        <?php require dirname(__DIR__) . '/Views/partials/module-navigation.php'; ?>
        <section class="module-content">
            <header class="admin-header"><div><p class="eyebrow">Administración</p><h1>Respaldos</h1><p class="setup-copy">Crea, descarga, restaura y elimina copias de seguridad de la base de datos.</p></div><a class="secondary-link" href="./">Volver al dashboard</a></header>
            <?php if ($error): ?><div class="setup-error"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
            <?php if ($success): ?><div class="setup-success"><?= htmlspecialchars((string) $success, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
            <article class="admin-panel">
                <header class="panel-header"><div><h2>Crear respaldo</h2><p>Genera una copia de seguridad completa de la base de datos sin depender de comandos del sistema.</p></div></header>
                <div class="toolbar-actions">
                    <form method="post" class="table-action-form"><input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="action" value="create_backup"><button class="primary-button" type="submit">Crear respaldo</button></form>
                </div>
                <div class="toolbar-actions">
                    <span class="status-chip">Estado: <?= htmlspecialchars($progress['status'] ?? 'idle', ENT_QUOTES, 'UTF-8') ?></span>
                    <span class="status-chip">Progreso: <?= (int) ($progress['current'] ?? 0) ?> / <?= (int) ($progress['total'] ?? 0) ?></span>
                </div>
                <div class="progress-meter"><div id="backup-progress-bar" class="progress-meter-inner" data-progress-current="<?= (int) ($progress['current'] ?? 0) ?>" data-progress-total="<?= (int) ($progress['total'] ?? 0) ?>"></div></div>
            </article>
            <article class="admin-panel">
                <header class="panel-header"><div><h2>Respaldos disponibles</h2><p>Descarga, restaura o elimina los respaldos que ya existen en el servidor.</p></div></header>
                <div class="table-scroll">
                    <table class="admin-table">
                        <thead><tr><th>ID</th><th>Archivo</th><th>Tamaño</th><th>Estado</th><th>Fecha</th><th>Usuario</th><th>Acciones</th></tr></thead>
                        <tbody>
                        <?php if (empty($backups)): ?>
                            <tr><td colspan="7" class="table-empty-state">No se encontraron respaldos.</td></tr>
                        <?php else: ?>
                            <?php foreach ($backups as $backup): ?>
                                <tr>
                                    <td><?= (int) ($backup['id'] ?? 0) ?></td>
                                    <td><?= backupDisplayName((string) ($backup['file_path'] ?? '')) ?></td>
                                    <td><?= ($backup['file_size'] ?? 0) > 0 ? number_format((int) ($backup['file_size']), 0, ',', '.') . ' bytes' : '0 bytes' ?></td>
                                    <td><?= backupDisplayStatus((string) ($backup['status'] ?? '')) ?></td>
                                    <td><?= backupDisplayValue($backup['created_at'] ?? '') ?></td>
                                    <td><?= backupDisplayValue($backup['created_by'] ?? 'Sistema') ?></td>
                                    <td>
                                        <div class="toolbar-actions">
                                            <form method="get" class="table-action-form"><input type="hidden" name="download_backup_id" value="<?= (int) ($backup['id'] ?? 0) ?>"><button class="table-action" type="submit">Descargar</button></form>
                                            <form method="post" class="table-action-form"><input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="action" value="restore_backup"><input type="hidden" name="backup_id" value="<?= (int) ($backup['id'] ?? 0) ?>"><button class="table-action" type="submit">Restaurar</button></form>
                                            <form method="post" class="table-action-form"><input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="action" value="delete_backup"><input type="hidden" name="backup_id" value="<?= (int) ($backup['id'] ?? 0) ?>"><button class="table-action" type="submit">Eliminar</button></form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </article>
        </section>
    </main>
    <script>
        function syncProgressBars() {
            document.querySelectorAll('.progress-meter-inner[data-progress-current]').forEach(function (bar) {
                var current = Number(bar.dataset.progressCurrent || 0);
                var total = Number(bar.dataset.progressTotal || 0);
                bar.style.width = total > 0 ? Math.min(100, Math.round((current / total) * 100)) + '%' : '0%';
            });
        }

        syncProgressBars();

        setInterval(function () {
            fetch('?module=backups&progress=1')
                .then(function (response) { return response.json(); })
                .then(function (data) {
                    var bar = document.getElementById('backup-progress-bar');
                    if (!bar) return;
                    var width = data.total > 0 ? Math.min(100, Math.round((data.current / data.total) * 100)) : 0;
                    bar.style.width = width + '%';
                });
        }, 2000);
    </script>
</body>
</html>
