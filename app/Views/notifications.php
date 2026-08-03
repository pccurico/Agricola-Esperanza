<?php
$notifications = $notifications ?? [];
$unreadCount = (int) ($unreadCount ?? 0);
$error = $error ?? null;
$readCount = max(0, count($notifications) - $unreadCount);
$csrf = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Notificaciones | Sistema de Gestión Agrícola PCCURICO</title>
    <link rel="stylesheet" href="assets/css/app.css">
</head>
<body class="admin-page">
    <main class="admin-shell">
        <?php require dirname(__DIR__) . '/Views/partials/module-navigation.php'; ?>
        <section class="module-content">
            <header class="admin-header"><div><p class="eyebrow">Centro de actividad</p><div class="notification-summary-cards"><article class="notification-summary-card notification-summary-card-unread"><span class="notification-summary-label">No leídas</span><strong><?= $unreadCount ?></strong></article><article class="notification-summary-card notification-summary-card-read"><span class="notification-summary-label">Leídas</span><strong><?= $readCount ?></strong></article></div><p class="setup-copy">Alertas y recordatorios de la operación agrícola.</p></div><a class="secondary-link" href="./">Volver al dashboard</a></header>
            <?php if ($error): ?><div class="setup-error"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
            <?php if ($unreadCount > 0): ?><form method="post" class="notifications-toolbar"><input type="hidden" name="csrf" value="<?= $csrf ?>"><input type="hidden" name="action" value="read_all"><button class="secondary-link" type="submit">Marcar todas como leídas</button></form><?php endif; ?>
            <section class="admin-panel notification-list-panel"><header class="panel-header"><div><h2>Detalle de notificaciones</h2><p>Revisa tus alertas y recordatorios.</p></div></header><div class="table-scroll"><table class="data-table notifications-table"><thead><tr><th>Fecha</th><th>Título</th><th>Mensaje</th><th>Estado</th><th></th></tr></thead><tbody>
                <?php if ($notifications === []): ?><tr><td colspan="5" class="notification-list-empty">No hay notificaciones para mostrar.</td></tr><?php else: ?><?php foreach ($notifications as $notification): ?><?php $isRead = !empty($notification['read_at']); ?><tr><td><?= htmlspecialchars((string) ($notification['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars((string) ($notification['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars((string) ($notification['message'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td><td><span class="notification-state <?= $isRead ? 'notification-state-read' : 'notification-state-unread' ?>" role="img" aria-label="<?= $isRead ? 'Leída' : 'Pendiente' ?>" title="<?= $isRead ? 'Leída' : 'Pendiente' ?>"></span></td><td><?php if (!$isRead): ?><form method="post" class="table-action-form"><input type="hidden" name="csrf" value="<?= $csrf ?>"><input type="hidden" name="action" value="read"><input type="hidden" name="notification_id" value="<?= (int) ($notification['id'] ?? 0) ?>"><button class="button button-link" type="submit">Marcar leída</button></form><?php else: ?>Leída<?php endif; ?></td></tr><?php endforeach; ?><?php endif; ?>
            </tbody></table></div></section>
        </section>
    </main>
</body>
</html>
