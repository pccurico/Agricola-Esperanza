<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Configuración inicial | CampoSur</title>
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="setup-page">
    <main class="setup-card setup-wide">
        <span class="setup-mark">✦</span>
        <p class="eyebrow">Primera configuración</p>
        <h1>Configuremos su agrícola</h1>
        <p class="setup-copy">Registra la identidad de la empresa, el primer fundo, la temporada activa y el usuario administrador.</p>
        <?php if ($error): ?>
            <div class="setup-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
        <form method="post" enctype="multipart/form-data" class="setup-form">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
            <fieldset>
                <legend>Identidad de la empresa</legend>
                <div class="form-grid">
                    <label>Razón social<input name="legal_name" required value="<?= htmlspecialchars($data['legal_name']) ?>"></label>
                    <label>Nombre visible<input name="trade_name" required value="<?= htmlspecialchars($data['trade_name']) ?>"></label>
                    <label>RUT<input name="tax_id" value="<?= htmlspecialchars($data['tax_id']) ?>"></label>
                    <label>Logo<input type="file" name="logo" accept="image/jpeg,image/png,image/webp"><small>JPG, PNG o WEBP · máximo 2 MB</small></label>
                    <label>Correo de empresa<input type="email" name="company_email" value="<?= htmlspecialchars($data['company_email']) ?>"></label>
                    <label>Teléfono<input name="company_phone" value="<?= htmlspecialchars($data['company_phone']) ?>"></label>
                    <label>Comuna<input name="commune" value="<?= htmlspecialchars($data['commune']) ?>"></label>
                    <label>Región<input name="region" value="<?= htmlspecialchars($data['region']) ?>"></label>
                </div>
            </fieldset>
            <fieldset>
                <legend>Primer fundo y temporada</legend>
                <div class="form-grid">
                    <label>Nombre del fundo<input name="farm_name" required value="<?= htmlspecialchars($data['farm_name']) ?>"></label>
                    <label>Código del fundo<input name="farm_code" required value="<?= htmlspecialchars($data['farm_code']) ?>"></label>
                    <label>Ubicación<input name="farm_location" value="<?= htmlspecialchars($data['farm_location']) ?>"></label>
                    <label>Superficie en hectáreas<input type="number" name="farm_hectares" min="0" step="0.01" required value="<?= htmlspecialchars($data['farm_hectares']) ?>"></label>
                    <label>Nombre de temporada<input name="season_name" required placeholder="2025-2026" value="<?= htmlspecialchars($data['season_name']) ?>"></label>
                    <label>Inicio<input type="date" name="season_start" required value="<?= htmlspecialchars($data['season_start']) ?>"></label>
                    <label>Término<input type="date" name="season_end" required value="<?= htmlspecialchars($data['season_end']) ?>"></label>
                </div>
            </fieldset>
            <fieldset>
                <legend>Usuario administrador</legend>
                <div class="form-grid">
                    <label>Nombre completo<input name="admin_name" required value="<?= htmlspecialchars($data['admin_name']) ?>"></label>
                    <label>Correo de acceso<input type="email" name="admin_email" required value="<?= htmlspecialchars($data['admin_email']) ?>"></label>
                    <label>Teléfono<input name="admin_phone" value="<?= htmlspecialchars($data['admin_phone']) ?>"></label>
                    <label>Contraseña<input type="password" name="admin_password" minlength="10" required><small>Mínimo 10 caracteres</small></label>
                </div>
            </fieldset>
            <button class="primary-button" type="submit">Crear instalación y continuar</button>
        </form>
    </main>
</body>
</html>
