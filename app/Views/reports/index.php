<?php

declare(strict_types=1);

$reports = $reports ?? [];
$userMetrics = $metrics ?? [];
$permissionMap = $permissions ?? [];
$iconLabels = [
    'chart' => 'Gráfico',
    'plant' => 'Cultivo',
    'dollar' => 'Costo',
    'users' => 'Personal',
    'boxes' => 'Inventario',
    'cart' => 'Compras',
    'wrench' => 'Maquinaria',
];
$labels = [
    'title' => 'Centro de Inteligencia agrícola',
    'description' => 'Selecciona un informe para analizar resultados, tendencias y acciones estratégicas.',
    'open' => 'Abrir',
    'updated' => 'Última actualización',
    'default_indicator' => 'Indicador clave',
];
$money = static fn (mixed $value): string => '$' . number_format((float) $value, 0, ',', '.');
$number = static fn (mixed $value, int $decimals = 0): string => number_format((float) $value, $decimals, ',', '.');
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Centro de Inteligencia | Informes</title>
    <link rel="stylesheet" href="/assets/css/app.css">
    <link rel="stylesheet" href="/assets/css/reports.css">
</head>
<body class="admin-page">
<main class="admin-shell">
    <?php require dirname(__DIR__) . '/partials/module-navigation.php'; ?>
    <section class="module-content reports-center">
        <header class="admin-header report-center-header">
            <div>
                <p class="eyebrow">Gerencia agrícola</p>
                <h1><?php echo htmlspecialchars($labels['title'], ENT_QUOTES, 'UTF-8'); ?></h1>
                <p class="setup-copy"><?php echo htmlspecialchars($labels['description'], ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
        </header>
        <section class="reports-card-grid">
            <?php foreach ($reports as $reportKey => $reportConfig): ?>
                <?php if (!isset($permissionMap[$reportKey]) || $permissionMap[$reportKey] !== true) {
                    continue;
                }
                $summary = $userMetrics[$reportKey] ?? ['value' => 0, 'change' => 0, 'updated_at' => '—'];
                ?>
                <article class="report-card">
                    <div class="report-card-badge"><?php echo htmlspecialchars($iconLabels[$reportConfig['icon']] ?? ucfirst($reportConfig['icon']), ENT_QUOTES, 'UTF-8'); ?></div>
                    <div class="report-card-body">
                        <h2><?php echo htmlspecialchars($reportConfig['title'], ENT_QUOTES, 'UTF-8'); ?></h2>
                        <p><?php echo htmlspecialchars($reportConfig['description'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <div class="report-card-metrics">
                            <div>
                                <strong><?php echo $number($summary['value'] ?? 0, 2); ?></strong>
                                <span><?php echo htmlspecialchars($summary['label'] ?? 'Indicador clave', ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                            <div class="report-card-trend <?php echo ((float) ($summary['change'] ?? 0) >= 0) ? 'positive' : 'negative'; ?>">
                                <span><?php echo ((float) ($summary['change'] ?? 0) >= 0) ? '↑' : '↓'; ?></span>
                                <strong><?php echo $number($summary['change'] ?? 0, 1); ?>%</strong>
                            </div>
                        </div>
                        <div class="report-card-footer">
                            <small><?php echo htmlspecialchars($labels['updated'], ENT_QUOTES, 'UTF-8'); ?>: <?php echo htmlspecialchars((string) ($summary['updated_at'] ?? '—'), ENT_QUOTES, 'UTF-8'); ?></small>
                            <a class="primary-button" href="<?php echo htmlspecialchars($reportConfig['route'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($labels['open'], ENT_QUOTES, 'UTF-8'); ?></a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </section>
    </section>
</main>
</body>
</html>
