<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Configuración inicial | CampoSur</title>
    <link rel="stylesheet" href="assets/css/app.css">
</head>
<body class="setup-page">
    <main class="setup-card setup-wide">
        <span class="setup-mark">✦</span>
        <p class="eyebrow">Primera configuración</p>
        <h1>Configuremos su agrícola</h1>
        <p class="setup-copy">Registra la identidad de la empresa y el usuario administrador para comenzar.</p>
        <?php if ($error): ?>
            <div class="setup-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
        <form method="post" enctype="multipart/form-data" class="setup-form">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
            <fieldset>
                <legend>Conexión a la base de datos</legend>
                <div class="form-grid">
                    <label>Servidor<input name="db_host" required value="<?= htmlspecialchars((string) $data['db_host'], ENT_QUOTES, 'UTF-8') ?>"></label>
                    <label>Puerto<input type="number" name="db_port" min="1" max="65535" required value="<?= (int) $data['db_port'] ?>"></label>
                    <label>Base de datos<input name="db_name" required value="<?= htmlspecialchars((string) $data['db_name'], ENT_QUOTES, 'UTF-8') ?>"></label>
                    <label>Usuario<input name="db_user" required value="<?= htmlspecialchars((string) $data['db_user'], ENT_QUOTES, 'UTF-8') ?>"></label>
                    <label>Contraseña<input type="password" name="db_password" autocomplete="new-password"><small>Déjala vacía solo si el usuario no tiene contraseña.</small></label>
                </div>
            </fieldset>
            <fieldset>
                <legend>Identidad de la empresa</legend>
                <div class="form-grid">
                    <label>Razón social<input class="uppercase-input" name="legal_name" required value="<?= htmlspecialchars($data['legal_name']) ?>"></label>
                    <label>Nombre visible<input class="uppercase-input" name="trade_name" required value="<?= htmlspecialchars($data['trade_name']) ?>"></label>
                    <label>RUT<input class="uppercase-input" name="tax_id" value="<?= htmlspecialchars($data['tax_id']) ?>"></label>
                    <label>Logo<input type="file" name="logo" accept="image/jpeg,image/png,image/webp"><small>JPG, PNG o WEBP · máximo 2 MB</small></label>
                    <label>Correo de empresa<input type="email" name="company_email" value="<?= htmlspecialchars($data['company_email']) ?>"></label>
                    <label>Teléfono<input name="company_phone" value="<?= htmlspecialchars($data['company_phone']) ?>"></label>
                    <label>Comuna<input class="uppercase-input" name="commune" value="<?= htmlspecialchars($data['commune']) ?>"></label>
                    <label>Región<input class="uppercase-input" name="region" value="<?= htmlspecialchars($data['region']) ?>"></label>
                </div>
            </fieldset>
            <fieldset>
                <legend>Usuario administrador</legend>
                <div class="form-grid">
                    <label>Nombre completo<input class="uppercase-input" name="admin_name" required value="<?= htmlspecialchars($data['admin_name']) ?>"></label>
                    <label>Correo de acceso<input type="email" name="admin_email" required value="<?= htmlspecialchars($data['admin_email']) ?>"></label>
                    <label>Teléfono<input name="admin_phone" value="<?= htmlspecialchars($data['admin_phone']) ?>"></label>
                    <label>Contraseña<input type="password" name="admin_password" minlength="10" required><small>Mínimo 10 caracteres</small></label>
                </div>
            </fieldset>
            <label class="demo-option"><input type="checkbox" name="install_demo" value="1" <?= !empty($data['install_demo']) ? 'checked' : '' ?>><span><b>Instalar datos de demostración</b><small>Agrega información de ejemplo para recorrer todos los módulos. Podrás eliminarla después desde Herramientas.</small></span></label>
            <button class="primary-button" type="submit">Crear instalación y continuar</button>
        </form>
    </main>
</body>
</html>
