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
        .toolbar-actions { display: flex; flex-wrap: wrap; gap: 8px; margin: 16px 0 0; padding: 0 22px 22px; }
        .table-action-form { margin-right: 8px; }
        .table-scroll { width: 100%; overflow: auto; }
        .admin-table { width: 100%; min-width: 0; border-collapse: collapse; table-layout: auto; }
        .admin-table th { padding: 11px 16px; background: #f7f9f6; color: #6c7c74; font-size: 10px; text-align: left; text-transform: uppercase; }
        .admin-table td { padding: 13px 16px; border-top: 1px solid #d9e1da; overflow-wrap: anywhere; vertical-align: top; white-space: normal; }
        .progress-meter { width: 100%; height: 14px; background: #f2f7f2; border-radius: 999px; overflow: hidden; margin-top: 10px; }
        .progress-meter-inner { height: 100%; background: #3c8269; width: 0%; transition: width .2s ease; }
        .status-chip { display: inline-flex; align-items: center; gap: 6px; padding: 6px 10px; border-radius: 999px; background: #f4f9f4; color: #3c8269; font-size: 12px; font-weight: 700; }
        @media (max-width: 780px) { .admin-shell { display: block; } .module-content { padding: 24px 16px 32px; } }
    </style>
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
                <div class="progress-meter"><div id="backup-progress-bar" class="progress-meter-inner" style="width: <?= $progress['total'] > 0 ? min(100, (int) (($progress['current'] / $progress['total']) * 100)) : 0 ?>%;"></div></div>
            </article>
            <article class="admin-panel">
                <header class="panel-header"><div><h2>Respaldos disponibles</h2><p>Descarga, restaura o elimina los respaldos que ya existen en el servidor.</p></div></header>
                <div class="table-scroll">
                    <table class="admin-table">
                        <thead><tr><th>ID</th><th>Archivo</th><th>Tamaño</th><th>Estado</th><th>Fecha</th><th>Usuario</th><th>Acciones</th></tr></thead>
                        <tbody>
                        <?php if (empty($backups)): ?>
                            <tr><td colspan="7" style="text-align:center; padding: 20px 16px;">No se encontraron respaldos.</td></tr>
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
                                        <div style="display: flex; flex-wrap: wrap; gap: 8px;">
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
