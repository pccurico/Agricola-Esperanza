<?php
$roles = $roles ?? [];
$permissions = $permissions ?? [];
$can_manage_roles = $can_manage_roles ?? false;
$selected_role = $selected_role ?? null;
$error = $error ?? null;
$success = $success ?? null;
$role_permissions = $role_permissions ?? [];
$selected_role_permissions = isset($selected_role['permissions']) ? array_map('intval', (array) $selected_role['permissions']) : [];
$show_role_form = $show_role_form ?? ($selected_role || isset($_GET['new_role']));
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Roles | Sistema de Gestión Agrícola PCCURICO</title>
    <link rel="stylesheet" href="assets/css/app.css">
</head>
<body class="admin-page roles-page">
<main class="admin-shell">
    <?php require dirname(__DIR__) . '/Views/partials/module-navigation.php'; ?>
    <section class="module-content">
        <main class="main-column">
            <section class="section-card">
                <div class="section-head">
                    <div>
                        <h2>Roles definidos</h2>
                        <p class="lead-text">Gestiona roles y permisos del sistema</p>
                    </div>
                    <div class="header-actions">
                        <a class="primary-button" href="<?= htmlspecialchars(module_url('roles', ['new_role' => 1]), ENT_QUOTES, 'UTF-8') ?>">Crear rol</a>
                        <span class="badge badge-secondary"><?= count($roles) ?></span>
                    </div>
                </div>
                <?php if (!empty($show_role_form)): ?>
                    <form method="post" class="admin-form" style="margin-bottom:16px;">
                        <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="action" value="<?= $selected_role ? 'update_role' : 'create_role' ?>">
                        <?php if ($selected_role): ?>
                            <input type="hidden" name="role_id" value="<?= (int) ($selected_role['id'] ?? 0) ?>">
                        <?php endif; ?>
                        <label>Nombre del rol
                            <input name="name" required value="<?= htmlspecialchars((string) ($selected_role['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </label>
                        <label>Descripción
                            <input name="description" value="<?= htmlspecialchars((string) ($selected_role['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </label>
                        <?php if ($permissions !== []): ?>
                            <div class="permission-grid">
                                <?php foreach ($permissions as $permission): ?>
                                    <?php $pid = (int) ($permission['id'] ?? 0); ?>
                                    <label class="permission-option">
                                        <input type="checkbox" name="permissions[]" value="<?= $pid ?>" <?= in_array($pid, $selected_role_permissions ?? [], true) ? 'checked' : '' ?>>
                                        <span><strong><?= htmlspecialchars((string) ($permission['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong><small><?= htmlspecialchars((string) ($permission['module'] ?? ''), ENT_QUOTES, 'UTF-8') ?></small></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        <div class="form-actions">
                            <button class="primary-button" type="submit"><?= $selected_role ? 'Guardar rol' : 'Crear rol' ?></button>
                            <a class="btn btn-outline" href="<?= htmlspecialchars(module_url('roles'), ENT_QUOTES, 'UTF-8') ?>">Cancelar</a>
                        </div>
                    </form>
                <?php endif; ?>
                <div class="table-wrap">
                    <table class="data-table minimal-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Descripción</th>
                                    <th>Usuarios</th>
                                    <th>Permisos</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($roles as $role): ?>
                                    <tr>
                                        <td><?= (int) ($role['id'] ?? 0) ?></td>
                                        <td class="truncate"><?= htmlspecialchars((string) ($role['name'] ?? 'Rol sin nombre'), ENT_QUOTES, 'UTF-8') ?></td>
                                        <td class="truncate" data-label="Descripción"><?= htmlspecialchars((string) ($role['description'] ?? 'Sin descripción'), ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= (int) ($role['users_count'] ?? 0) ?></td>
                                        <td><?= (int) ($role['permissions_count'] ?? 0) ?></td>
                                        <td class="table-action-cell">
                                            <?php if ($can_manage_roles): ?>
                                                <a class="table-action" href="<?= htmlspecialchars(module_url('roles', ['edit_role_id' => (int) $role['id']]), ENT_QUOTES, 'UTF-8') ?>">Editar</a>
                                                <?php if ((int) ($role['is_system'] ?? 0) === 0): ?>
                                                    <form method="post" class="inline-form" onsubmit="return confirm('¿Eliminar este rol?');">
                                                        <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                                                        <input type="hidden" name="action" value="delete_role">
                                                        <input type="hidden" name="role_id" value="<?= (int) ($role['id'] ?? 0) ?>">
                                                        <button class="table-action danger-action" type="submit">Eliminar</button>
                                                    </form>
                                                <?php endif; ?>
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
