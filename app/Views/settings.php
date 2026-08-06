<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Configuración | Sistema de Gestión Agrícola PCCURICO</title>
    <link rel="stylesheet" href="assets/css/app.css">
</head>

<body class="admin-page">
    <main class="admin-shell"><?php require dirname(__DIR__) . '/Views/partials/module-navigation.php'; ?>
        <section class="module-content settings-v2 module-v2">
            <header class="page-hero">
                <div class="hero-meta">
                    <div class="hero-title">
                        <p class="eyebrow">Datos de la empresa</p>
                        <h1>Configuración de la empresa</h1>
                        <p class="lead-text">Actualiza aquí los datos que verás en el sistema y en tus informes.</p>
                    </div>
                    <div class="hero-actions"><a class="btn btn-outline" href="./">Volver al dashboard</a></div>
                </div>
            </header>

            <div class="page-grid v2">
                <main class="main-column">
                    <section class="section-card settings-panel">
                        <?php if ($error): ?><div class="setup-error"><?= htmlspecialchars($error) ?></div><?php endif; ?><?php if ($success): ?><div class="setup-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
                        <form method="post" enctype="multipart/form-data">
                            <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                            <div class="form-group">
                                <label>Razón social</label>
                                <input name="legal_name" required value="<?= htmlspecialchars($company['legal_name'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label>Nombre visible</label>
                                <input name="trade_name" required value="<?= htmlspecialchars($company['trade_name'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label>RUT</label>
                                <input name="tax_id" value="<?= htmlspecialchars($company['tax_id'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label>Logo</label>
                                <input type="file" name="logo" accept="image/jpeg,image/png,image/webp"><small>JPG, PNG o WEBP · máximo 2 MB</small>
                            </div>
                            <div class="form-group">
                                <label>Correo</label>
                                <input type="email" name="email" value="<?= htmlspecialchars($company['email'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label>Teléfono</label>
                                <input name="phone" value="<?= htmlspecialchars($company['phone'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label>Dirección</label>
                                <input name="address" value="<?= htmlspecialchars($company['address'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label>Comuna</label>
                                <input name="commune" value="<?= htmlspecialchars($company['commune'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label>Región</label>
                                <input name="region" value="<?= htmlspecialchars($company['region'] ?? '') ?>">
                            </div>
                            <div class="form-actions"><button class="primary-button" type="submit">Guardar configuración</button></div>
                        </form>
                    </section>
                </main>

                <aside class="sidebar-column v2">
                    <section class="section-card compact">
                        <div class="panel-header"><h4>Información</h4></div>
                        <div class="panel-body">
                        <p class="muted">Los datos de identidad se usarán en cabeceras de informes y documentos.</p>
                        </div>
                    </section>
                </aside>
            </div>
        </section>
    </main>
</body>

</html>