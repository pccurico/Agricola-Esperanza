<?php
$user = $user ?? [];
$error = $error ?? null;
$success = $success ?? null;
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mi perfil | Sistema de Gestión Agrícola PCCURICO</title>
    <link rel="stylesheet" href="assets/css/app.css">
</head>
<body class="admin-page">
    <main class="admin-shell">
        <?php require dirname(__DIR__) . '/Views/partials/module-navigation.php'; ?>
        <section class="module-content profile-content">
            <header class="admin-header"><div><p class="eyebrow">Cuenta personal</p><h1>Mi perfil</h1><p class="setup-copy">Actualiza tus datos de contacto y contraseña.</p></div><a class="secondary-link" href="./">Volver al dashboard</a></header>
            <?php if ($error): ?><div class="setup-error"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
            <?php if ($success): ?><div class="setup-success"><?= htmlspecialchars((string) $success, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
            <section class="profile-grid">
                <article class="admin-panel profile-summary-card"><div class="profile-avatar"><?= htmlspecialchars(strtoupper(substr((string) ($user['full_name'] ?? 'Usuario'), 0, 2)), ENT_QUOTES, 'UTF-8') ?></div><h2><?= htmlspecialchars((string) ($user['full_name'] ?? 'Usuario'), ENT_QUOTES, 'UTF-8') ?></h2><p><?= htmlspecialchars((string) ($user['role_name'] ?? 'Sin rol'), ENT_QUOTES, 'UTF-8') ?></p><small><?= htmlspecialchars((string) ($user['trade_name'] ?? 'Empresa activa'), ENT_QUOTES, 'UTF-8') ?></small><dl><div><dt>Último acceso</dt><dd><?= htmlspecialchars((string) ($user['last_login_at'] ?? 'Sin registro'), ENT_QUOTES, 'UTF-8') ?></dd></div><div><dt>Correo actual</dt><dd><?= htmlspecialchars((string) ($user['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?></dd></div></dl></article>
                <article class="admin-panel profile-form-card"><header class="panel-header"><div><h2>Datos de acceso</h2><p>Los cambios se aplican a tu cuenta actual.</p></div></header><form method="post" class="admin-form settings-form"><input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>"><label>Nombre completo<input name="full_name" required value="<?= htmlspecialchars((string) ($user['full_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></label><label>Correo<input type="email" name="email" required value="<?= htmlspecialchars((string) ($user['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></label><label>Teléfono<input name="phone" value="<?= htmlspecialchars((string) ($user['phone'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></label><label>Nueva contraseña<input type="password" name="new_password" minlength="10" autocomplete="new-password"><small>Déjalo vacío para mantener la contraseña actual.</small></label><button class="primary-button" type="submit">Guardar cambios</button></form></article>
            </section>
        </section>
    </main>
</body>
</html>
