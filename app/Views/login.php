<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ingresar | <?= htmlspecialchars((string) ($company['trade_name'] ?? 'Sistema de Gestión Agrícola PCCURICO'), ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="assets/css/app.css">
</head>
<body class="setup-page login-page">
    <main class="setup-card login-card">
        <section class="login-brand" aria-label="Identidad de la empresa">
            <?php if (!empty($company['logo_path'])): ?>
                <img class="login-company-logo" src="?asset=logo" alt="Logo de <?= htmlspecialchars((string) $company['trade_name'], ENT_QUOTES, 'UTF-8') ?>">
            <?php else: ?>
                <span class="setup-mark">✦</span>
            <?php endif; ?>
            <p class="eyebrow login-eyebrow">Gestión agrícola</p>
            <h1 class="login-company-name"><?= htmlspecialchars((string) ($company['trade_name'] ?? 'Sistema de Gestión Agrícola PCCURICO'), ENT_QUOTES, 'UTF-8') ?></h1>
        </section>
        <section class="login-form-panel" aria-label="Acceso al sistema">
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
        </section>
    </main>
    <footer class="login-footer"><a href="https://www.pccurico.cl" target="_blank" rel="noopener noreferrer">www.pccurico.cl</a> · JCares · 2026 · v1.7.01</footer>
</body>
</html>
