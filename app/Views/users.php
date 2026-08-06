<?php
$users = $users ?? [];
$roles = $roles ?? [];
$permissions = $permissions ?? [];
$can_manage_users = $can_manage_users ?? false;
$can_manage_roles = $can_manage_roles ?? false;
$selected_user = $selected_user ?? null;
$error = $error ?? null;
$success = $success ?? null;
$rolePermissions = $role_permissions ?? [];
$selected_user_permissions = isset($selected_user['permissions']) ? array_map('intval', (array) $selected_user['permissions']) : [];
$selected_user_effective_permissions = $selected_user_permissions;
if (! empty($selected_user['role_id']) && isset($rolePermissions[$selected_user['role_id']])) {
    $selected_user_effective_permissions = array_values(array_unique(array_merge($selected_user_effective_permissions, $rolePermissions[$selected_user['role_id']])));
}
$activeCount = count(array_filter($users, fn($item) => (bool) ($item['active'] ?? false)));
$inactiveCount = count($users) - $activeCount;
$show_user_form = $show_user_form ?? ($selected_user || isset($_GET['new_user']));
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Usuarios | Sistema de Gestión Agrícola PCCURICO</title>
    <link rel="stylesheet" href="assets/css/app.css">
</head>
<body class="admin-page users-page">
<main class="admin-shell">
    <?php require dirname(__DIR__) . '/Views/partials/module-navigation.php'; ?>
    <section class="module-content users-v2 module-v2">
        <main class="main-column">
            <section class="section-card">
                <div class="section-head">
                    <div>
                        <h2>Usuarios</h2>
                        <p class="lead-text">Listado de usuarios del sistema</p>
                    </div>
                    <div class="header-actions">
                        <a class="btn" href="<?= htmlspecialchars(module_url('users', ['new_user' => 1]), ENT_QUOTES, 'UTF-8') ?>">Crear usuario</a>
                        <span class="badge badge-secondary"><?= count($users) ?></span>
                    </div>
                </div>
                <?php if (!empty($show_user_form)): ?>
                    <form method="post">
                        <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="action" value="<?= $selected_user ? 'update_user' : 'create_user' ?>">
                        <?php if ($selected_user): ?>
                            <input type="hidden" name="user_id" value="<?= (int) ($selected_user['id'] ?? 0) ?>">
                        <?php endif; ?>
                        <div class="form-group">
                            <label>Nombre completo</label>
                            <input name="full_name" required value="<?= htmlspecialchars((string) ($selected_user['full_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="form-group">
                            <label>Correo</label>
                            <input type="email" name="email" required value="<?= htmlspecialchars((string) ($selected_user['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="form-group">
                            <label>Teléfono</label>
                            <input name="phone" value="<?= htmlspecialchars((string) ($selected_user['phone'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="form-group">
                            <label>Rol</label>
                            <select name="role_id" required><?php foreach ($roles as $role): ?><option value="<?= (int) $role['id'] ?>" <?= ((int) ($selected_user['role_id'] ?? 0) === (int) $role['id']) ? 'selected' : '' ?>><?= htmlspecialchars($role['name'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select>
                        </div>
                        <div class="form-group">
                            <label>Contraseña</label>
                            <input type="password" name="password" <?= $selected_user ? '' : 'required minlength="10"' ?>></div>
                        <div class="form-actions"><button class="primary-button" type="submit"><?= $selected_user ? 'Guardar usuario' : 'Crear usuario' ?></button> <a class="btn btn-outline" href="<?= htmlspecialchars(module_url('users'), ENT_QUOTES, 'UTF-8') ?>">Cancelar</a></div>
                    </form>
                <?php endif; ?>
                <div class="table-wrap">
                    <table class="data-table minimal-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Correo</th>
                            <th>Rol</th>
                            <th>Estado</th>
                            <th>Último ingreso</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <?php $userActive = (bool) ($user['active'] ?? false); ?>
                            <tr>
                                <td><?= (int) ($user['id'] ?? 0) ?></td>
                                <td class="truncate"><?= htmlspecialchars((string) ($user['full_name'] ?? 'Usuario sin nombre'), ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="truncate"><?= htmlspecialchars((string) ($user['email'] ?? 'Sin correo'), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string) ($user['role_name'] ?? 'Sin rol'), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= $userActive ? '<span class="badge badge-success">Activo</span>' : '<span class="badge badge-danger">Inactivo</span>' ?></td>
                                <td><?= htmlspecialchars((string) ($user['last_login_at'] ?? 'Nunca'), ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="table-action-cell">
                                    <?php if ($can_manage_users): ?>
                                        <a class="table-action" href="<?= htmlspecialchars(module_url('users', ['edit_user_id' => (int) $user['id']]), ENT_QUOTES, 'UTF-8') ?>">Editar</a>
                                        <form method="post" class="inline-form" onsubmit="return confirm('¿Eliminar este usuario?');">
                                            <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                                            <input type="hidden" name="action" value="delete_user">
                                            <input type="hidden" name="user_id" value="<?= (int) ($user['id'] ?? 0) ?>">
                                            <button class="table-action danger-action" type="submit">Eliminar</button>
                                        </form>
                                        <form method="post" class="inline-form">
                                            <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                                            <input type="hidden" name="action" value="toggle_user">
                                            <input type="hidden" name="user_id" value="<?= (int) ($user['id'] ?? 0) ?>">
                                            <button class="table-action" type="submit"><?= $userActive ? 'Desactivar' : 'Activar' ?></button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            </section>
        </main>
    </section>
</main>
</body>
</html>
