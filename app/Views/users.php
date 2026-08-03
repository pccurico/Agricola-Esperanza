<?php
$users = $users ?? [];
$roles = $roles ?? [];
$permissions = $permissions ?? [];
$can_manage_users = $can_manage_users ?? false;
$can_manage_roles = $can_manage_roles ?? false;
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Usuarios y roles | Sistema de Gestión Agrícola PCCURICO</title>
    <link rel="stylesheet" href="assets/css/app.css">
</head>
<body class="admin-page">
    <main class="admin-shell">
        <?php require dirname(__DIR__) . '/Views/partials/module-navigation.php'; ?>
        <section class="module-content">
            <header class="admin-header"><div><p class="eyebrow">Gestión de accesos</p><h1>Usuarios y roles</h1><p class="setup-copy">Controla quién puede consultar y operar cada módulo.</p></div><a class="secondary-link" href="./">Volver al dashboard</a></header>
            <?php if ($error): ?><div class="setup-error"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
            <?php if ($success): ?><div class="setup-success"><?= htmlspecialchars((string) $success, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
            <section class="admin-columns">
                <article class="admin-panel"><header class="panel-header"><div><h2>Usuarios registrados</h2><p><?= count($users) ?> cuentas registradas</p></div></header><div class="table-scroll"><table class="admin-table"><thead><tr><th>Usuario</th><th>Rol</th><th>Estado</th><th>Último acceso</th><?php if ($can_manage_users): ?><th>Acción</th><?php endif; ?></tr></thead><tbody><?php foreach ($users as $user): ?><?php $userActive = (bool) ($user['active'] ?? false); ?><tr><td><b><?= htmlspecialchars((string) ($user['full_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></b><small><?= htmlspecialchars((string) ($user['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?></small></td><td><?= htmlspecialchars((string) ($user['role_name'] ?? 'Sin rol'), ENT_QUOTES, 'UTF-8') ?></td><td><span class="status-pill <?= $userActive ? 'status-active' : 'status-inactive' ?>"><?= $userActive ? 'Activo' : 'Inactivo' ?></span></td><td><?= htmlspecialchars((string) ($user['last_login_at'] ?? 'Sin ingreso'), ENT_QUOTES, 'UTF-8') ?></td><?php if ($can_manage_users): ?><td><form method="post" class="table-action-form"><input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="action" value="toggle_user"><input type="hidden" name="user_id" value="<?= (int) ($user['id'] ?? 0) ?>"><button class="table-action" type="submit"><?= $userActive ? 'Desactivar' : 'Activar' ?></button></form></td><?php endif; ?></tr><?php endforeach; ?></tbody></table></div></article>
                <?php if ($can_manage_users): ?><article class="admin-panel"><header class="panel-header"><div><h2>Nuevo usuario</h2><p>Crear acceso para un colaborador</p></div></header><form method="post" class="admin-form"><input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="action" value="create_user"><label>Nombre completo<input name="full_name" required></label><label>Correo<input type="email" name="email" required></label><label>Teléfono<input name="phone"></label><label>Rol<select name="role_id" required><?php foreach ($roles as $role): ?><option value="<?= (int) $role['id'] ?>"><?= htmlspecialchars((string) ($role['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label><label>Contraseña<input type="password" name="password" minlength="10" required></label><button class="primary-button" type="submit">Crear usuario</button></form></article><?php endif; ?>
            </section>
            <section class="admin-columns"><article class="admin-panel"><header class="panel-header"><div><h2>Roles configurados</h2><p>Permisos agrupados por función</p></div></header><div class="role-list"><?php foreach ($roles as $role): ?><div class="role-row"><div><b><?= htmlspecialchars((string) ($role['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></b><small><?= htmlspecialchars((string) ($role['description'] ?? 'Sin descripción'), ENT_QUOTES, 'UTF-8') ?></small></div><span><?= (int) ($role['users_count'] ?? 0) ?> usuarios · <?= (int) ($role['permissions_count'] ?? 0) ?> permisos</span></div><?php endforeach; ?></div></article>
                <?php if ($can_manage_roles): ?><article class="admin-panel"><header class="panel-header"><div><h2>Nuevo rol</h2><p>Define permisos para un área</p></div></header><form method="post" class="admin-form"><input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="action" value="create_role"><label>Nombre del rol<input name="name" required placeholder="Encargado de bodega"></label><label>Descripción<input name="description"></label><div class="permission-grid"><?php foreach ($permissions as $permission): ?><label class="permission-option"><input type="checkbox" name="permissions[]" value="<?= (int) ($permission['id'] ?? 0) ?>"><span><b><?= htmlspecialchars((string) ($permission['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></b><small><?= htmlspecialchars((string) ($permission['module'] ?? ''), ENT_QUOTES, 'UTF-8') ?></small></span></label><?php endforeach; ?></div><button class="primary-button" type="submit">Crear rol</button></form></article><?php endif; ?></section>
        </section>
    </main>
</body>
</html>
