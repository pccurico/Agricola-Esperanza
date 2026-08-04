<?php
$users = $users ?? [];
$roles = $roles ?? [];
$permissions = $permissions ?? [];
$can_manage_users = $can_manage_users ?? false;
$can_manage_roles = $can_manage_roles ?? false;
$selected_user = $selected_user ?? null;
$selected_role = $selected_role ?? null;
$error = $error ?? null;
$success = $success ?? null;
$rolePermissions = $role_permissions ?? [];
$selected_role_permissions = isset($selected_role['permissions']) ? array_map('intval', (array) $selected_role['permissions']) : [];
$selected_user_permissions = isset($selected_user['permissions']) ? array_map('intval', (array) $selected_user['permissions']) : [];
$selected_user_effective_permissions = $selected_user_permissions;
if (! empty($selected_user['role_id']) && isset($rolePermissions[$selected_user['role_id']])) {
    $selected_user_effective_permissions = array_values(array_unique(array_merge($selected_user_effective_permissions, $rolePermissions[$selected_user['role_id']])));
}
$activeCount = count(array_filter($users, fn($item) => (bool) ($item['active'] ?? false)));
$inactiveCount = count($users) - $activeCount;
$show_user_form = $selected_user || isset($_GET['new_user']);
$show_role_form = $selected_role || isset($_GET['new_role']);
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Usuarios y roles | Sistema de Gestión Agrícola PCCURICO</title>
    <link rel="stylesheet" href="assets/css/app.css">
</head>
<body class="admin-page users-page">
    <main class="admin-shell">
        <?php require dirname(__DIR__) . '/Views/partials/module-navigation.php'; ?>
        <section class="module-content">
            <div class="page-header">
                <div>
                    <p class="eyebrow">Seguridad y acceso</p>
                    <h1>Usuarios y roles</h1>
                    <p class="lead-text">Panel moderno para administrar cuentas, roles y permisos con una experiencia más limpia y ordenada.</p>
                </div>
                <div class="header-actions">
                    <?php if ($can_manage_users): ?>
                        <a class="btn btn-primary" href="<?= htmlspecialchars(module_url('users', ['new_user' => 1]), ENT_QUOTES, 'UTF-8') ?>">Nuevo usuario</a>
                    <?php endif; ?>
                    <?php if ($can_manage_roles): ?>
                        <a class="btn btn-secondary" href="<?= htmlspecialchars(module_url('users', ['new_role' => 1]), ENT_QUOTES, 'UTF-8') ?>">Nuevo rol</a>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars((string) $success, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <div class="card-row stats-row">
                <article class="stat-card">
                    <span class="stat-label">Usuarios totales</span>
                    <strong><?= count($users) ?></strong>
                </article>
                <article class="stat-card">
                    <span class="stat-label">Roles definidos</span>
                    <strong><?= count($roles) ?></strong>
                </article>
                <article class="stat-card">
                    <span class="stat-label">Permisos disponibles</span>
                    <strong><?= count($permissions) ?></strong>
                </article>
            </div>

            <div class="page-grid">
                <main class="main-column">
                    <section class="section-card">
                        <div class="section-head">
                            <div>
                                <h2>Lista de usuarios</h2>
                                <p>Revisa el acceso, estado y roles asignados con fichas compactas.</p>
                            </div>
                            <span class="badge badge-neutral"><?= htmlspecialchars($activeCount, ENT_QUOTES, 'UTF-8') ?> activos · <?= htmlspecialchars($inactiveCount, ENT_QUOTES, 'UTF-8') ?> inactivos</span>
                        </div>
                        <?php if ($users === []): ?>
                            <div class="empty-state">No hay usuarios registrados aún.</div>
                        <?php else: ?>
                            <div class="card-list">
                                <?php foreach ($users as $user): ?>
                                    <?php $userActive = (bool) ($user['active'] ?? false); ?>
                                    <article class="item-card">
                                        <div class="item-card-head">
                                            <div>
                                                <h3><?= htmlspecialchars((string) ($user['full_name'] ?? 'Usuario sin nombre'), ENT_QUOTES, 'UTF-8') ?></h3>
                                                <p><?= htmlspecialchars((string) ($user['email'] ?? 'Sin correo'), ENT_QUOTES, 'UTF-8') ?></p>
                                            </div>
                                            <span class="badge <?= $userActive ? 'badge-success' : 'badge-danger' ?>"><?= $userActive ? 'Activo' : 'Inactivo' ?></span>
                                        </div>
                                        <div class="item-card-meta">
                                            <span class="meta-chip">Rol: <?= htmlspecialchars((string) ($user['role_name'] ?? 'Sin rol'), ENT_QUOTES, 'UTF-8') ?></span>
                                            <span class="meta-chip">Último ingreso: <?= htmlspecialchars((string) ($user['last_login_at'] ?? 'Nunca'), ENT_QUOTES, 'UTF-8') ?></span>
                                        </div>
                                        <?php if ($can_manage_users): ?>
                                            <div class="item-actions">
                                                <a class="btn btn-outline" href="<?= htmlspecialchars(module_url('users', ['edit_user_id' => (int) $user['id']]), ENT_QUOTES, 'UTF-8') ?>">Editar</a>
                                                <form method="post" class="inline-form" onsubmit="return confirm('¿Eliminar este usuario?');">
                                                    <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                                                    <input type="hidden" name="action" value="delete_user">
                                                    <input type="hidden" name="user_id" value="<?= (int) ($user['id'] ?? 0) ?>">
                                                    <button class="btn btn-danger" type="submit">Eliminar</button>
                                                </form>
                                            </div>
                                        <?php endif; ?>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </section>

                    <section class="section-card">
                        <div class="section-head">
                            <div>
                                <h2>Roles del sistema</h2>
                                <p>Roles configurados para controlar permisos y acceso.</p>
                            </div>
                            <span class="badge badge-secondary"><?= count($roles) ?></span>
                        </div>
                        <div class="role-grid">
                            <?php if ($roles === []): ?>
                                <div class="empty-state">No hay roles creados todavía.</div>
                            <?php endif; ?>
                            <?php foreach ($roles as $role): ?>
                                <article class="role-card">
                                    <div>
                                        <h3><?= htmlspecialchars((string) ($role['name'] ?? 'Rol sin nombre'), ENT_QUOTES, 'UTF-8') ?></h3>
                                        <p><?= htmlspecialchars((string) ($role['description'] ?? 'Sin descripción'), ENT_QUOTES, 'UTF-8') ?></p>
                                    </div>
                                    <div class="role-card-footer">
                                        <span class="meta-chip"><?= (int) ($role['users_count'] ?? 0) ?> usuarios</span>
                                        <span class="meta-chip"><?= (int) ($role['permissions_count'] ?? 0) ?> permisos</span>
                                    </div>
                                    <?php if ($can_manage_roles): ?>
                                        <div class="item-actions">
                                            <a class="btn btn-outline" href="<?= htmlspecialchars(module_url('users', ['edit_role_id' => (int) $role['id']]), ENT_QUOTES, 'UTF-8') ?>">Editar</a>
                                            <?php if ((int) ($role['is_system'] ?? 0) === 0): ?>
                                                <form method="post" class="inline-form" onsubmit="return confirm('¿Eliminar este rol?');">
                                                    <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                                                    <input type="hidden" name="action" value="delete_role">
                                                    <input type="hidden" name="role_id" value="<?= (int) ($role['id'] ?? 0) ?>">
                                                    <button class="btn btn-danger" type="submit">Eliminar</button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </section>
                </main>

                <aside class="sidebar-column">
                    <div class="sidebar-card">
                        <h3>Idea rápida</h3>
                        <p class="small-text">Utiliza las acciones rápidas para crear usuarios y roles sin saturar la vista principal.</p>
                        <ul class="shortcut-list">
                            <li>Revisa usuarios activos e inactivos rápidamente.</li>
                            <li>Gestiona roles con un solo clic.</li>
                            <li>Evita formularios largos usando el panel lateral.</li>
                        </ul>
                    </div>

                    <?php if ($can_manage_users): ?>
                        <div class="sidebar-card">
                            <div class="card-head">
                                <h3><?= $selected_user ? 'Editar usuario' : 'Crear usuario' ?></h3>
                                <?php if (! $selected_user): ?>
                                    <span class="badge badge-secondary">Paso único</span>
                                <?php endif; ?>
                            </div>
                            <form method="post" class="admin-form compact-form">
                                <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="action" value="<?= $selected_user ? 'update_user' : 'create_user' ?>">
                                <?php if ($selected_user): ?>
                                    <input type="hidden" name="user_id" value="<?= (int) ($selected_user['id'] ?? 0) ?>">
                                <?php endif; ?>

                                <label class="form-group">
                                    Nombre completo
                                    <input name="full_name" required value="<?= htmlspecialchars((string) ($selected_user['full_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                </label>
                                <label class="form-group">
                                    Correo electrónico
                                    <input type="email" name="email" required value="<?= htmlspecialchars((string) ($selected_user['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                </label>
                                <label class="form-group">
                                    Rol asignado
                                    <select name="role_id" required>
                                        <?php foreach ($roles as $role): ?>
                                            <option value="<?= (int) $role['id'] ?>" <?= (int) ($selected_user['role_id'] ?? 0) === (int) $role['id'] ? 'selected' : '' ?>><?= htmlspecialchars((string) ($role['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </label>
                                <?php if (! $selected_user): ?>
                                    <label class="form-group">
                                        Contraseña inicial
                                        <input type="password" name="password" minlength="10" required>
                                    </label>
                                <?php endif; ?>

                                <?php if ($permissions !== []): ?>
                                    <details class="details-panel" <?= $selected_user_effective_permissions ? 'open' : '' ?>>
                                        <summary>Permisos directos</summary>
                                        <div class="permission-grid">
                                            <?php foreach ($permissions as $permission): ?>
                                                <?php $permissionId = (int) ($permission['id'] ?? 0); ?>
                                                <?php $isInherited = !in_array($permissionId, $selected_user_permissions, true) && in_array($permissionId, $selected_user_effective_permissions, true); ?>
                                                <label class="permission-option<?= $isInherited ? ' inherited' : '' ?>">
                                                    <input type="checkbox" name="permissions[]" value="<?= $permissionId ?>" <?= in_array($permissionId, $selected_user_effective_permissions, true) ? 'checked' : '' ?>>
                                                    <span>
                                                        <strong><?= htmlspecialchars((string) ($permission['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
                                                        <small><?= htmlspecialchars((string) ($permission['module'] ?? ''), ENT_QUOTES, 'UTF-8') ?></small>
                                                    </span>
                                                </label>
                                            <?php endforeach; ?>
                                        </div>
                                    </details>
                                <?php endif; ?>

                                <div class="form-actions">
                                    <button class="btn btn-primary" type="submit"><?= $selected_user ? 'Guardar usuario' : 'Crear usuario' ?></button>
                                    <?php if ($selected_user): ?>
                                        <a class="btn btn-outline" href="<?= htmlspecialchars(module_url('users'), ENT_QUOTES, 'UTF-8') ?>">Cancelar</a>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                    <?php endif; ?>

                    <?php if ($can_manage_roles): ?>
                        <div class="sidebar-card">
                            <div class="card-head">
                                <h3><?= $selected_role ? 'Editar rol' : 'Crear rol' ?></h3>
                            </div>
                            <form method="post" class="admin-form compact-form">
                                <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="action" value="<?= $selected_role ? 'update_role' : 'create_role' ?>">
                                <?php if ($selected_role): ?>
                                    <input type="hidden" name="role_id" value="<?= (int) ($selected_role['id'] ?? 0) ?>">
                                <?php endif; ?>

                                <label class="form-group">
                                    Nombre del rol
                                    <input name="name" required placeholder="Encargado de bodega" value="<?= htmlspecialchars((string) ($selected_role['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                </label>
                                <label class="form-group">
                                    Descripción
                                    <input name="description" value="<?= htmlspecialchars((string) ($selected_role['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                </label>

                                <?php if ($permissions !== []): ?>
                                    <details class="details-panel" <?= $selected_role_permissions ? 'open' : '' ?>>
                                        <summary>Permisos del rol</summary>
                                        <div class="permission-grid">
                                            <?php foreach ($permissions as $permission): ?>
                                                <?php $permissionId = (int) ($permission['id'] ?? 0); ?>
                                                <label class="permission-option">
                                                    <input type="checkbox" name="permissions[]" value="<?= $permissionId ?>" <?= in_array($permissionId, $selected_role_permissions, true) ? 'checked' : '' ?>>
                                                    <span>
                                                        <strong><?= htmlspecialchars((string) ($permission['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
                                                        <small><?= htmlspecialchars((string) ($permission['module'] ?? ''), ENT_QUOTES, 'UTF-8') ?></small>
                                                    </span>
                                                </label>
                                            <?php endforeach; ?>
                                        </div>
                                    </details>
                                <?php endif; ?>

                                <div class="form-actions">
                                    <button class="btn btn-primary" type="submit"><?= $selected_role ? 'Guardar rol' : 'Crear rol' ?></button>
                                    <?php if ($selected_role): ?>
                                        <a class="btn btn-outline" href="<?= htmlspecialchars(module_url('users'), ENT_QUOTES, 'UTF-8') ?>">Cancelar</a>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                    <?php endif; ?>
                </aside>
            </div>
        </section>
    </main>
    <script>
        (function () {
            const rolePermissions = <?= json_encode($rolePermissions, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
            const roleSelect = document.querySelector('select[name="role_id"]');
            const permissionInputs = Array.from(document.querySelectorAll('input[name="permissions[]"]'));
            const panelSummary = document.querySelector('.details-panel summary');
            if (!roleSelect || permissionInputs.length === 0 || !panelSummary) {
                return;
            }

            function updateInheritedPermissions() {
                const selectedId = roleSelect.value;
                const inherited = rolePermissions[selectedId] || [];
                permissionInputs.forEach((input) => {
                    const value = Number(input.value);
                    const label = input.closest('.permission-option');
                    if (inherited.includes(value)) {
                        input.checked = true;
                        label?.classList.add('inherited');
                    } else {
                        label?.classList.remove('inherited');
                    }
                });
                panelSummary.textContent = inherited.length > 0 ? `Permisos heredados (${inherited.length})` : 'Permisos heredados';
            }

            roleSelect.addEventListener('change', updateInheritedPermissions);
            updateInheritedPermissions();
        })();
    </script>
</body>
</html>
