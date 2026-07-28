<?php

declare(strict_types=1);

$dashboard = (new CampoSur\Services\DashboardService(database()->connection(), (int) $_SESSION['company_id']))->summary();
$company = $dashboard['company'] ?? [];
$totals = $dashboard['totals'] ?? [];
$recent = $dashboard['recent'] ?? [];
$companyName = (string) ($company['trade_name'] ?? 'Empresa activa');
$totalCost = number_format((float) ($totals['total_cost'] ?? 0), 0, ',', '.');
$hectares = number_format((float) ($totals['hectares'] ?? 0), 2, ',', '.');
$movements = number_format((int) ($totals['movements'] ?? 0), 0, ',', '.');
$userName = (string) ($_SESSION['user_name'] ?? 'Administrador');
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Panel ejecutivo | <?= htmlspecialchars($companyName, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="dashboard-page">
    <main class="dashboard-shell">
        <?php require dirname(__DIR__) . '/Views/partials/module-navigation.php'; ?>

        <section class="dashboard-content">
            <header class="dashboard-topbar">
                <p class="dashboard-crumb"><?= htmlspecialchars($companyName, ENT_QUOTES, 'UTF-8') ?> <span>/</span> <strong>Panel ejecutivo</strong></p>
                <div class="dashboard-actions"><a class="secondary-link" href="?module=costs">+ Registrar movimiento</a><a class="secondary-link" href="?logout=1">Salir</a></div>
            </header>

            <header class="admin-header">
                <div><p class="eyebrow">Resumen de la agrícola</p><h1>Panel principal</h1><p class="setup-copy">Aquí puedes revisar lo que se ha registrado en el sistema.</p></div>
                <p class="dashboard-updated">Actualizado <?= date('d/m/Y H:i') ?></p>
            </header>

            <section class="kpis">
                <article class="kpi"><div class="kpi-top"><span>Costo registrado</span><span class="kpi-symbol">$</span></div><p class="kpi-value">$<?= $totalCost ?></p></article>
                <article class="kpi"><div class="kpi-top"><span>Hectáreas registradas</span><span class="kpi-symbol">Ha</span></div><p class="kpi-value"><?= $hectares ?> Ha</p></article>
                <article class="kpi"><div class="kpi-top"><span>Movimientos registrados</span><span class="kpi-symbol">#</span></div><p class="kpi-value"><?= $movements ?></p></article>
            </section>

            <section class="admin-columns">
                <article class="admin-panel">
                    <header class="panel-header"><h2>Últimos movimientos</h2><p>Estos son los registros más recientes.</p></header>
                    <?php if ($recent === []): ?>
                        <p class="empty-state">Aún no existen registros. Utilice los módulos de operación para comenzar.</p>
                    <?php else: ?>
                        <div class="table-wrap"><table class="data-table"><thead><tr><th>Descripción</th><th>Fecha</th><th>Valor</th></tr></thead><tbody>
                            <?php foreach ($recent as $entry): ?>
                                <tr><td><?= htmlspecialchars((string) $entry['label'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars((string) $entry['date'], ENT_QUOTES, 'UTF-8') ?></td><td>$<?= number_format((float) $entry['value'], 0, ',', '.') ?></td></tr>
                            <?php endforeach; ?>
                        </tbody></table></div>
                    <?php endif; ?>
                </article>
                <article class="admin-panel">
                    <header class="panel-header"><h2>Comienza por aquí</h2><p>Agrega la información de tu agrícola para comenzar a trabajar.</p></header>
                    <nav class="module-links"><a href="?module=masters">Administración agrícola</a><a href="?module=costs">Registro de costos</a><a href="?module=labor">Mano de obra</a><a href="?module=inventory">Inventario</a></nav>
                </article>
            </section>
        </section>
    </main>
</body>
</html>
