<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Maquinaria | Sistema de Gestión Agrícola PCCURICO</title>
    <link rel="stylesheet" href="assets/css/app.css">
</head>
<body class="admin-page">
<?php
if (!isset($machinery) || !is_array($machinery)) { $machinery = []; }
if (!isset($maintenance) || !is_array($maintenance)) { $maintenance = []; }
if (!isset($fuel) || !is_array($fuel)) { $fuel = []; }
if (!isset($farms) || !is_array($farms)) { $farms = []; }
if (!isset($dashboard) || !is_array($dashboard)) { $dashboard = []; }
if (!isset($error)) { $error = null; }
if (!isset($success)) { $success = null; }
$machineryRows = $machinery;
$maintenanceRows = $maintenance;
$fuelRows = $fuel;
$farmsRows = $farms;
$dashboardMetrics = $dashboard;
$dashboardKpis = is_array($dashboardMetrics['kpis'] ?? null) ? $dashboardMetrics['kpis'] : [];
?>
<main class="admin-shell">
    <?php require dirname(__DIR__) . '/Views/partials/module-navigation.php'; ?>
    <section class="module-content">
        <header class="admin-header">
            <div>
                <p class="eyebrow">Operación</p>
                <h1>Maquinaria</h1>
                <p class="setup-copy">Registra equipos, mantenciones y consumo de combustible.</p>
            </div>
            <a class="secondary-link" href="./">Volver al dashboard</a>
        </header>

        <?php if ($error): ?>
            <div class="setup-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="setup-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <section class="kpi-grid">
            <article class="report-kpi">
                <span>Total equipos</span>
                <b><?= number_format((int) ($dashboardKpis['total_equipment'] ?? 0), 0, ',', '.') ?></b>
            </article>
            <article class="report-kpi">
                <span>Operativos</span>
                <b><?= number_format((int) ($dashboardKpis['operational'] ?? 0), 0, ',', '.') ?></b>
            </article>
            <article class="report-kpi">
                <span>En mantención</span>
                <b><?= number_format((int) ($dashboardKpis['maintenance'] ?? 0), 0, ',', '.') ?></b>
            </article>
            <article class="report-kpi">
                <span>Combustible (L)</span>
                <b><?= number_format((float) ($dashboardKpis['fuel_total_liters'] ?? 0), 2, ',', '.') ?></b>
            </article>
            <article class="report-kpi">
                <span>Horas acumuladas</span>
                <b><?= number_format((float) ($dashboardKpis['accumulated_hours'] ?? 0), 2, ',', '.') ?></b>
            </article>
            <article class="report-kpi">
                <span>Utilización promedio</span>
                <b><?= number_format((float) ($dashboardKpis['average_utilization'] ?? 0), 1, ',', '.') ?>%</b>
            </article>
        </section>

        <section class="admin-columns">
            <article class="admin-panel">
                <header class="panel-header">
                    <h2>Nuevo equipo</h2>
                    <p>Registra una máquina o implemento agrícola</p>
                </header>
                <form method="post" class="admin-form">
                    <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="action" value="create_machinery">
                    <label>Código<input name="code" required placeholder="M-001"></label>
                    <label>Nombre<input name="name" required placeholder="Tractor 4x4"></label>
                    <label>Tipo
                        <select name="machinery_type" required>
                            <option value="TRACTOR">TRACTOR</option>
                            <option value="PULVERIZADOR">PULVERIZADOR</option>
                            <option value="CAMIÓN">CAMIÓN</option>
                            <option value="MOTOCULTOR">MOTOCULTOR</option>
                            <option value="IMPLEMENTO">IMPLEMENTO</option>
                        </select>
                    </label>
                    <label>Marca<input name="brand" placeholder="John Deere"></label>
                    <label>Modelo<input name="model" placeholder="6120J"></label>
                    <label>Patente<input name="plate" placeholder="AB-1234"></label>
                    <label>Horómetro<input type="number" name="meter" min="0" step="0.01" value="0"></label>
                    <label>Fundo
                        <select name="farm_id">
                            <option value="">Sin fundo</option>
                            <?php foreach ($farmsRows as $farm): ?>
                                <option value="<?= (int) ($farm['id'] ?? 0) ?>"><?= htmlspecialchars((string) ($farm['name'] ?? '')) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <button class="primary-button" type="submit">Crear equipo</button>
                </form>
            </article>

            <article class="admin-panel">
                <header class="panel-header">
                    <h2>Registrar mantención</h2>
                    <p>Controla intervenciones y próximas fechas</p>
                </header>
                <form method="post" class="admin-form">
                    <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="action" value="create_maintenance">
                    <label>Equipo
                        <select name="machinery_id" required>
                            <?php foreach ($machineryRows as $item): ?>
                                <option value="<?= (int) ($item['id'] ?? 0) ?>"><?= htmlspecialchars((string) ($item['code'] ?? '')) ?> - <?= htmlspecialchars((string) ($item['name'] ?? '')) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>Fecha<input type="date" name="maintenance_date" required></label>
                    <label>Tipo
                        <select name="maintenance_type" required>
                            <option value="PREVENTIVE">Preventiva</option>
                            <option value="CORRECTIVE">Correctiva</option>
                            <option value="INSPECTION">Inspección</option>
                        </select>
                    </label>
                    <label>Descripción<textarea name="description" required rows="3"></textarea></label>
                    <label>Costo<input type="number" name="cost" min="0" step="0.01" value="0"></label>
                    <label>Próxima fecha<input type="date" name="next_date"></label>
                    <button class="primary-button" type="submit">Guardar mantención</button>
                </form>
            </article>

            <article class="admin-panel">
                <header class="panel-header">
                    <h2>Registrar combustible</h2>
                    <p>Asocia litros, costo y referencia</p>
                </header>
                <form method="post" class="admin-form">
                    <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="action" value="create_fuel">
                    <label>Equipo
                        <select name="machinery_id" required>
                            <?php foreach ($machineryRows as $item): ?>
                                <option value="<?= (int) ($item['id'] ?? 0) ?>"><?= htmlspecialchars((string) ($item['code'] ?? '')) ?> - <?= htmlspecialchars((string) ($item['name'] ?? '')) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>Fecha<input type="date" name="fuel_date" required></label>
                    <label>Litros<input type="number" name="liters" min="0" step="0.01" required></label>
                    <label>Costo unitario<input type="number" name="unit_cost" min="0" step="0.01" required></label>
                    <label>Horómetro<input type="number" name="meter" min="0" step="0.01"></label>
                    <label>Referencia<input name="reference" placeholder="Factura / guía"></label>
                    <label>Fundo
                        <select name="farm_id">
                            <option value="">Sin fundo</option>
                            <?php foreach ($farmsRows as $farm): ?>
                                <option value="<?= (int) ($farm['id'] ?? 0) ?>"><?= htmlspecialchars((string) ($farm['name'] ?? '')) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <button class="primary-button" type="submit">Registrar combustible</button>
                </form>
            </article>
        </section>

        <section class="admin-columns">
            <article class="admin-panel">
                <header class="panel-header">
                    <h2>Equipos</h2>
                    <p>Listado activo por compañía</p>
                </header>
                <div class="table-scroll">
                    <table class="data-table">
                        <thead>
                        <tr>
                            <th>Código</th>
                            <th>Nombre</th>
                            <th>Tipo</th>
                            <th>Marca</th>
                            <th>Estado</th>
                            <th>Fundo</th>
                            <th>Horómetro</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if ($machineryRows === []): ?>
                            <tr><td colspan="7">No hay equipos registrados.</td></tr>
                        <?php else: ?>
                            <?php foreach ($machineryRows as $item): ?>
                                <tr>
                                    <td><?= htmlspecialchars((string) ($item['code'] ?? '')) ?></td>
                                    <td><?= htmlspecialchars((string) ($item['name'] ?? '')) ?></td>
                                    <td><?= htmlspecialchars((string) ($item['machinery_type'] ?? '')) ?></td>
                                    <td><?= htmlspecialchars((string) ($item['brand'] ?? '')) ?></td>
                                    <td><?= htmlspecialchars((string) ($item['status'] ?? '')) ?></td>
                                    <td><?= htmlspecialchars((string) ($item['farm_name'] ?? '')) ?></td>
                                    <td><?= htmlspecialchars((string) ($item['meter'] ?? '0')) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </article>

            <article class="admin-panel">
                <header class="panel-header">
                    <h2>Últimas mantenciones</h2>
                    <p>Registro histórico</p>
                </header>
                <div class="table-scroll">
                    <table class="data-table">
                        <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Equipo</th>
                            <th>Tipo</th>
                            <th>Costo</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if ($maintenanceRows === []): ?>
                            <tr><td colspan="4">No hay mantenciones registradas.</td></tr>
                        <?php else: ?>
                            <?php foreach ($maintenanceRows as $entry): ?>
                                <tr>
                                    <td><?= htmlspecialchars((string) ($entry['maintenance_date'] ?? '')) ?></td>
                                    <td><?= htmlspecialchars((string) ($entry['machinery_name'] ?? '')) ?></td>
                                    <td><?= htmlspecialchars((string) ($entry['maintenance_type'] ?? '')) ?></td>
                                    <td><?= htmlspecialchars((string) ($entry['cost'] ?? '0')) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </article>

            <article class="admin-panel">
                <header class="panel-header">
                    <h2>Combustible</h2>
                    <p>Movimientos recientes</p>
                </header>
                <div class="table-scroll">
                    <table class="data-table">
                        <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Equipo</th>
                            <th>Litros</th>
                            <th>Costo</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if ($fuelRows === []): ?>
                            <tr><td colspan="4">No hay combustible registrado.</td></tr>
                        <?php else: ?>
                            <?php foreach ($fuelRows as $entry): ?>
                                <tr>
                                    <td><?= htmlspecialchars((string) ($entry['fuel_date'] ?? '')) ?></td>
                                    <td><?= htmlspecialchars((string) ($entry['machinery_name'] ?? '')) ?></td>
                                    <td><?= htmlspecialchars((string) ($entry['liters'] ?? '0')) ?></td>
                                    <td><?= htmlspecialchars((string) ($entry['unit_cost'] ?? '0')) ?></td>
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
