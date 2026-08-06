<!doctype html>
<?php $cash_transactions = $cash_transactions ?? []; $cash_summary = $cash_summary ?? ['income' => 0, 'expense' => 0, 'balance' => 0]; ?>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($category_label) ?> | Sistema de Gestión Agrícola PCCURICO</title>
    <link rel="stylesheet" href="assets/css/app.css">
</head>

<body class="admin-page">
    <main class="admin-shell"><?php require dirname(__DIR__) . '/Views/partials/module-navigation.php'; ?><section class="module-content">
            <header class="admin-header">
                <div>
                    <p class="eyebrow">Control financiero</p>
                    <h1><?= htmlspecialchars($category_label) ?></h1>
                    <p class="setup-copy">Registra cada movimiento y asígnalo a una temporada, fundo y centro de costo.</p>
                </div><a class="secondary-link" href="./">Volver al dashboard</a>
            </header><?php if ($error): ?><div class="setup-error"><?= htmlspecialchars($error) ?></div><?php endif; ?><?php if ($success): ?><div class="setup-success"><?= htmlspecialchars($success) ?></div><?php endif; ?><section class="admin-columns">
                <article class="admin-panel">
                    <header class="panel-header">
                        <h2>Registrar movimiento</h2>
                        <p>La información quedará disponible para informes.</p>
                    </header>
                    <form method="post" class="admin-form"><input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>"><label>Temporada<select name="season_id" required><?php foreach ($seasons as $season): ?><option value="<?= (int) $season['id'] ?>"><?= htmlspecialchars($season['name']) ?></option><?php endforeach; ?></select></label><label>Centro de costo<select name="cost_center_id" required><?php foreach ($centers as $center): ?><option value="<?= (int) $center['id'] ?>"><?= htmlspecialchars($center['name']) ?></option><?php endforeach; ?></select></label><label>Fundo<select name="farm_id">
                                <option value="">Sin fundo</option><?php foreach ($farms as $farm): ?><option value="<?= (int) $farm['id'] ?>"><?= htmlspecialchars($farm['name']) ?></option><?php endforeach; ?>
                            </select></label><label>Cuartel<select name="block_id">
                                <option value="">Sin cuartel</option><?php foreach ($blocks as $block): ?><option value="<?= (int) $block['id'] ?>"><?= htmlspecialchars($block['code'] . ' · ' . $block['name']) ?></option><?php endforeach; ?>
                            </select></label><label>Fecha<input type="date" name="entry_date" required value="<?= date('Y-m-d') ?>"></label><label>Monto<input type="number" name="amount" min="0.01" step="0.01" required></label><label>Descripción<input name="description" required></label><label>N° documento<input name="document_number"></label><button class="primary-button" type="submit">Registrar costo</button></form>
                </article>
                <article class="admin-panel">
                    <header class="panel-header">
                        <h2>Últimos movimientos</h2>
                        <p><?= count($entries) ?> registros visibles</p>
                    </header>
                    <div class="table-scroll">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Descripción</th>
                                    <th>Centro</th>
                                    <th>Monto</th>
                                </tr>
                            </thead>
                            <tbody><?php foreach ($entries as $entry): ?><tr>
                                        <td><?= htmlspecialchars($entry['entry_date']) ?></td>
                                        <td><b><?= htmlspecialchars($entry['description']) ?></b><small><?= htmlspecialchars($entry['farm_name'] ?: 'Sin fundo') ?></small></td>
                                        <td><?= htmlspecialchars($entry['center_name']) ?></td>
                                        <td><b>$<?= number_format((float) $entry['amount'], 0, ',', '.') ?></b></td>
                                    </tr><?php endforeach; ?></tbody>
                        </table>
                    </div>
                </article>
            </section>
            <section class="admin-columns">
                <article class="admin-panel">
                    <header class="panel-header"><div><h2>Flujo de caja</h2><p>Registra ingresos y egresos reales para conocer el saldo.</p></div></header>
                    <div class="stats-grid"><div class="stat-card"><span>Ingresos</span><strong>$<?= number_format((float) ($cash_summary['income'] ?? 0), 0, ',', '.') ?></strong></div><div class="stat-card"><span>Egresos</span><strong>$<?= number_format((float) ($cash_summary['expense'] ?? 0), 0, ',', '.') ?></strong></div><div class="stat-card"><span>Saldo</span><strong>$<?= number_format((float) ($cash_summary['balance'] ?? 0), 0, ',', '.') ?></strong></div></div>
                    <form method="post" class="admin-form"><input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="action" value="create_cash_transaction"><div class="form-row"><label>Tipo<select name="transaction_type" required><option value="INCOME">Ingreso</option><option value="EXPENSE">Egreso</option></select></label><label>Fecha<input type="date" name="transaction_date" value="<?= date('Y-m-d') ?>" required></label><label>Categoría<input name="category" maxlength="80" required></label><label>Monto<input type="number" name="amount" min="0.01" step="0.01" required></label></div><div class="form-row"><label>Descripción<input name="description" maxlength="255" required></label><label>Referencia<input name="reference" maxlength="120"></label></div><div class="form-actions"><button class="primary-button" type="submit">Registrar movimiento</button></div></form>
                </article>
                <article class="admin-panel">
                    <header class="panel-header"><h2>Movimientos de caja</h2><p><?= count($cash_transactions) ?> registros contabilizados</p></header>
                    <div class="table-scroll"><table class="admin-table"><thead><tr><th>Fecha</th><th>Tipo</th><th>Descripción</th><th>Categoría</th><th>Monto</th></tr></thead><tbody><?php foreach ($cash_transactions as $cash): ?><tr><td><?= htmlspecialchars($cash['transaction_date'], ENT_QUOTES, 'UTF-8') ?></td><td><span class="status-pill <?= $cash['transaction_type'] === 'INCOME' ? 'status-active' : 'status-inactive' ?>"><?= $cash['transaction_type'] === 'INCOME' ? 'Ingreso' : 'Egreso' ?></span></td><td><?= htmlspecialchars($cash['description'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($cash['category'], ENT_QUOTES, 'UTF-8') ?></td><td><b>$<?= number_format((float) $cash['amount'], 0, ',', '.') ?></b></td></tr><?php endforeach; ?></tbody></table></div>
                </article>
            </section>
        </section>
    </main>
</body>

</html>
