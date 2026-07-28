<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ingresar | CampoSur</title>
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="setup-page">
    <main class="setup-card login-card">
        <span class="setup-mark">✦</span>
        <p class="eyebrow">Gestión agrícola</p>
        <h1>Bienvenido a CampoSur</h1>
        <p class="setup-copy">Ingresa con tus credenciales para continuar.</p>
        <?php if ($error): ?><div class="setup-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
        <form method="post" class="setup-form">
            <input type="hidden" name="action" value="login"><input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
            <fieldset>
                <div class="form-grid single-column">
                    <label>Correo electrónico<input type="email" name="email" required autocomplete="email"></label>
                    <label>Contraseña<input type="password" name="password" required autocomplete="current-password"></label>
                </div>
            </fieldset>
            <button class="primary-button" type="submit">Ingresar al sistema</button>
        </form>
    </main>
</body>
</html>
