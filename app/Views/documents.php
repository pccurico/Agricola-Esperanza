<?php
$suppliers = $suppliers ?? [];
$clients = $clients ?? [];
$documents = $documents ?? [];
$error = $error ?? null;
$success = $success ?? null;
$csrf = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Documentos | Sistema de Gestión Agrícola PCCURICO</title>
    <link rel="stylesheet" href="assets/css/app.css">
</head>
<body class="admin-page">
    <main class="admin-shell">
        <?php require dirname(__DIR__) . '/Views/partials/module-navigation.php'; ?>
        <section class="module-content">
            <header class="admin-header">
                <div>
                    <p class="eyebrow">Trazabilidad</p>
                    <h1>Documentos y adjuntos</h1>
                    <p class="setup-copy">Registra facturas, contratos, guías y archivos asociados a la operación.</p>
                </div>
                <a class="secondary-link" href="./">Volver al dashboard</a>
            </header>
            <?php if ($error): ?>
                <div class="setup-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="setup-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
            <section class="admin-columns">
                <article class="admin-panel">
                    <header class="panel-header">
                        <h2>Nuevo documento</h2>
                    </header>
                    <form method="post" enctype="multipart/form-data" class="admin-form">
                        <input type="hidden" name="csrf" value="<?= $csrf ?>">
                        <input type="hidden" name="action" value="create_document">
                        <label>Tipo<input name="document_type" required maxlength="80"></label>
                        <label>Número<input name="document_number" maxlength="100"></label>
                        <label>Fecha<input type="date" name="issue_date"></label>
                        <label>Proveedor
                            <select name="supplier_id">
                                <option value="">Sin proveedor</option>
                                <?php foreach ($suppliers as $supplier): ?>
                                    <option value="<?= (int) $supplier['id'] ?>"><?= htmlspecialchars((string) ($supplier['business_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <label>Cliente
                            <select name="client_id">
                                <option value="">Sin cliente</option>
                                <?php foreach ($clients as $client): ?>
                                    <option value="<?= (int) $client['id'] ?>"><?= htmlspecialchars((string) ($client['business_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <label>Adjunto<input type="file" name="attachment" accept="application/pdf,image/jpeg,image/png,image/webp,.xlsx"></label>
                        <button class="primary-button" type="submit">Guardar documento</button>
                    </form>
                </article>
                <article class="admin-panel">
                    <header class="panel-header">
                        <h2>Documentos registrados</h2>
                    </header>
                    <div class="table-scroll">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Tipo</th>
                                    <th>Número</th>
                                    <th>Fecha</th>
                                    <th>Proveedor</th>
                                    <th>Cliente</th>
                                    <th>Estado</th>
                                    <th>Adjuntos</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($documents === []): ?>
                                    <tr>
                                        <td colspan="7" class="empty-state">No hay documentos registrados para tu empresa.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($documents as $document): ?>
                                        <tr>
                                            <td><?= htmlspecialchars((string) ($document['document_type'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars((string) ($document['document_number'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars((string) ($document['issue_date'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars((string) ($document['supplier_name'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars((string) ($document['client_name'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars((string) ($document['status'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= (int) ($document['attachment_count'] ?? 0) ?> adjunto(s)</td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </article>
            </section>
        </section>
    </main>
</body>
</html>
