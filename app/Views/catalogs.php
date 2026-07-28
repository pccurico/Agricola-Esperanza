<?php

$catalogs = $catalogs ?? [];
$values = $values ?? [];
$catalogCode = $catalogCode ?? '';
$error = $error ?? null;
$success = $success ?? null;
?>
<section class="page-header">
    <div>
        <p class="eyebrow">Configuración</p>
        <h1>Catálogos parametrizables</h1>
        <p class="muted">Administra valores reutilizados por los módulos del ERP sin listas fijas en el código.</p>
    </div>
</section>
<?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
<div class="content-grid">
    <article class="panel">
        <div class="panel-heading"><h2>Catálogos</h2></div>
        <div class="list-stack">
            <?php foreach ($catalogs as $catalog): ?>
                <a class="list-row" href="?module=catalogs&amp;catalog=<?= urlencode($catalog['code']) ?>">
                    <span><?= htmlspecialchars($catalog['name'], ENT_QUOTES, 'UTF-8') ?></span>
                    <small><?= htmlspecialchars($catalog['code'], ENT_QUOTES, 'UTF-8') ?></small>
                </a>
            <?php endforeach; ?>
        </div>
    </article>
    <article class="panel">
        <div class="panel-heading"><h2><?= $catalogCode !== '' ? htmlspecialchars($catalogCode, ENT_QUOTES, 'UTF-8') : 'Selecciona un catálogo' ?></h2></div>
        <?php if ($catalogCode !== ''): ?>
            <form method="post" class="form-grid compact-form">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="action" value="create">
                <input type="hidden" name="catalog_code" value="<?= htmlspecialchars($catalogCode, ENT_QUOTES, 'UTF-8') ?>">
                <label>Código<input name="code" required maxlength="80"></label>
                <label>Etiqueta<input name="label" required maxlength="140"></label>
                <label>Orden<input type="number" name="sort_order" value="0"></label>
                <button class="button button-primary" type="submit">Agregar valor</button>
            </form>
            <div class="table-wrap"><table><thead><tr><th>Código</th><th>Etiqueta</th><th>Orden</th><th></th></tr></thead><tbody>
            <?php foreach ($values as $value): ?><tr><td><?= htmlspecialchars($value['code'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($value['label'], ENT_QUOTES, 'UTF-8') ?></td><td><?= (int) $value['sort_order'] ?></td><td><?php if ($value['scope'] === 'COMPANY'): ?><form method="post"><input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="action" value="deactivate"><input type="hidden" name="catalog_code" value="<?= htmlspecialchars($catalogCode, ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="value_id" value="<?= (int) $value['id'] ?>"><button class="button button-link" type="submit">Desactivar</button></form><?php endif; ?></td></tr><?php endforeach; ?>
            </tbody></table></div>
        <?php endif; ?>
    </article>
</div>
