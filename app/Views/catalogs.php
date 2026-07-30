<?php

declare(strict_types=1);

$catalogs = $catalogs ?? [];
$values = $values ?? [];
$catalogCode = $catalogCode ?? '';
$error = $error ?? null;
$success = $success ?? null;
$selectedCatalog = null;
foreach ($catalogs as $catalog) {
    if ((string) $catalog['code'] === $catalogCode) {
        $selectedCatalog = $catalog;
        break;
    }
}
$selectedCatalogName = $selectedCatalog['name'] ?? $catalogCode;
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Catálogos | Sistema de Gestión Agrícola PCCURICO</title>
    <link rel="stylesheet" href="assets/css/app.css">
</head>
<body class="admin-page">
    <main class="admin-shell">
        <?php require dirname(__DIR__) . '/Views/partials/module-navigation.php'; ?>
        <section class="module-content">
            <header class="admin-header">
                <div>
                    <p class="eyebrow">Configuración</p>
                    <h1>Listas del sistema</h1>
                    <p class="setup-copy">Administra las opciones que estarán disponibles en los formularios de la agrícola.</p>
                </div>
                <a class="secondary-link" href="./">Volver al resumen</a>
            </header>
            <?php if ($error): ?><div class="setup-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
            <?php if ($success): ?><div class="setup-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
            <section class="admin-columns">
                <article class="admin-panel">
                    <header class="panel-header"><h2>Listas disponibles</h2><p>Elige una lista para revisar o agregar opciones.</p></header>
                    <div class="module-links">
                        <?php foreach ($catalogs as $catalog): ?>
                            <a href="?module=catalogs&amp;catalog=<?= urlencode($catalog['code']) ?>"><?= htmlspecialchars($catalog['name'], ENT_QUOTES, 'UTF-8') ?><small><?= htmlspecialchars($catalog['code'], ENT_QUOTES, 'UTF-8') ?></small></a>
                        <?php endforeach; ?>
                    </div>
                </article>
                <article class="admin-panel">
                    <header class="panel-header"><h2><?= $catalogCode !== '' ? htmlspecialchars((string) $selectedCatalogName, ENT_QUOTES, 'UTF-8') : 'Selecciona una lista' ?></h2><p><?= $catalogCode !== '' ? 'Revisa las opciones que usan los formularios.' : 'Selecciona una lista para ver sus opciones.' ?></p></header>
                    <?php if ($catalogCode !== ''): ?>
                        <form method="post" class="admin-form">
                            <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="action" value="create">
                            <input type="hidden" name="catalog_code" value="<?= htmlspecialchars($catalogCode, ENT_QUOTES, 'UTF-8') ?>">
                            <label>Código<input name="code" required maxlength="80"></label>
                            <label>Nombre visible<input name="label" required maxlength="140"></label>
                            <label>Orden<input type="number" name="sort_order" value="0"></label>
                            <button class="primary-button" type="submit">Agregar opción</button>
                        </form>
                        <div class="table-wrap"><table class="admin-table"><thead><tr><th>Código</th><th>Nombre</th><th>Orden</th><th>Acción</th></tr></thead><tbody>
                        <?php foreach ($values as $value): ?><tr><td><?= htmlspecialchars($value['code'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($value['label'], ENT_QUOTES, 'UTF-8') ?></td><td><?= (int) $value['sort_order'] ?></td><td><?php if ($value['scope'] === 'COMPANY'): ?><form method="post"><input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="action" value="deactivate"><input type="hidden" name="catalog_code" value="<?= htmlspecialchars($catalogCode, ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="value_id" value="<?= (int) $value['id'] ?>"><button class="button button-link" type="submit">Desactivar</button></form><?php else: ?>Base del sistema<?php endif; ?></td></tr><?php endforeach; ?>
                        </tbody></table></div>
                    <?php endif; ?>
                </article>
            </section>
        </section>
    </main>
</body>
</html>
