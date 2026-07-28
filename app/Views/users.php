<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Usuarios y roles | CampoSur</title>
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="admin-page">
    <main class="admin-shell">
        <header class="admin-header"><div><p class="eyebrow">Gestión de accesos</p><h1>Usuarios y roles</h1><p class="setup-copy">Controla quién puede consultar y operar cada módulo.</p></div><a class="secondary-link" href="/">Volver al dashboard</a></header>
        <?php if ($error): ?><div class="setup-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
        <?php if ($success): ?><div class="setup-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
        <section class="admin-columns">
            <article class="admin-panel"><header class="panel-header"><div><h2>Usuarios activos</h2><p><?= count($users) ?> cuentas registradas</p></div></header><div class="table-scroll"><table class="admin-table"><thead><tr><th>Usuario</th><th>Rol</th><th>Estado</th><th>Último acceso</th></tr></thead><tbody><?php foreach ($users as $user): ?><tr><td><b><?= htmlspecialchars($user['full_name']) ?></b><small><?= htmlspecialchars($user['email']) ?></small></td><td><?= htmlspecialchars($user['role_name']) ?></td><td><span class="status-pill <?= $user['active'] ? 'status-active' : 'status-inactive' ?>"><?= $user['active'] ? 'Activo' : 'Inactivo' ?></span></td><td><?= htmlspecialchars($user['last_login_at'] ?: 'Sin ingreso') ?></td></tr><?php endforeach; ?></tbody></table></div></article>
            <article class="admin-panel"><header class="panel-header"><div><h2>Nuevo usuario</h2><p>Crear acceso para un colaborador</p></div></header><form method="post" class="admin-form"><input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="action" value="create_user"><label>Nombre completo<input name="full_name" required></label><label>Correo<input type="email" name="email" required></label><label>Teléfono<input name="phone"></label><label>Rol<select name="role_id" required><?php foreach ($roles as $role): ?><option value="<?= (int) $role['id'] ?>"><?= htmlspecialchars($role['name']) ?></option><?php endforeach; ?></select></label><label>Contraseña<input type="password" name="password" minlength="10" required></label><button class="primary-button" type="submit">Crear usuario</button></form></article>
        </section>
        <section class="admin-columns"><article class="admin-panel"><header class="panel-header"><div><h2>Roles configurados</h2><p>Permisos agrupados por función</p></div></header><div class="role-list"><?php foreach ($roles as $role): ?><div class="role-row"><div><b><?= htmlspecialchars($role['name']) ?></b><small><?= htmlspecialchars($role['description'] ?: 'Sin descripción') ?></small></div><span><?= (int) $role['users_count'] ?> usuarios · <?= (int) $role['permissions_count'] ?> permisos</span></div><?php endforeach; ?></div></article>
            <article class="admin-panel"><header class="panel-header"><div><h2>Nuevo rol</h2><p>Define permisos para un área</p></div></header><form method="post" class="admin-form"><input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="action" value="create_role"><label>Nombre del rol<input name="name" required placeholder="Encargado de bodega"></label><label>Descripción<input name="description"></label><div class="permission-grid"><?php foreach ($permissions as $permission): ?><label class="permission-option"><input type="checkbox" name="permissions[]" value="<?= (int) $permission['id'] ?>"><span><b><?= htmlspecialchars($permission['name']) ?></b><small><?= htmlspecialchars($permission['module']) ?></small></span></label><?php endforeach; ?></div><button class="primary-button" type="submit">Crear rol</button></form></article></section>
    </main>
</body>
</html>
