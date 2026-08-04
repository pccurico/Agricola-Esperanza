<?php
// New BI-first dashboard view
$company = $dashboard['company'] ?? [];
$companyName = (string) ($company['trade_name'] ?? 'Empresa activa');
$customization = $dashboard['customization'] ?? [];
$savedViews = $customization['saved_views'] ?? [];
$activeView = (string) ($customization['active_view'] ?? '');
$kpis = $dashboard['kpis'] ?? [];
$alerts = $dashboard['alerts'] ?? [];
$availableWidgets = $dashboard['widgets'] ?? [];
$selectedWidgets = $dashboard['selected_widgets'] ?? array_map(fn($w)=>$w['id'] ?? '', $availableWidgets);
$selectedWidgets = is_array($selectedWidgets) ? $selectedWidgets : [];
$selectedWidgetObjects = [];
foreach ($availableWidgets as $w) { if (in_array($w['id'] ?? '', $selectedWidgets, true)) $selectedWidgetObjects[] = $w; }
$filterOptions = $dashboard['filter_options'] ?? [];
$selectedFilters = $dashboard['filters'] ?? ['process' => '', 'farm_id' => 0, 'block_id' => 0, 'date_from' => date('Y-m-01'), 'date_to' => date('Y-m-d')];
?>
<!doctype html>
<html lang="es">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>BI · Centro de Inteligencia | <?= htmlspecialchars($companyName, ENT_QUOTES, 'UTF-8') ?></title>
	<link rel="stylesheet" href="assets/css/bi-dashboard.css">
</head>
<body class="bi-dashboard">
<div class="bi-shell">
	<aside class="bi-sidebar">
		<h2><?= htmlspecialchars($companyName, ENT_QUOTES, 'UTF-8') ?></h2>
		<h3>Filtros globales</h3>
		<form method="get" data-bi-filter>
			<div class="bi-filters">
				<label>Fundo
					<select name="farm_id">
						<option value="0">Todos</option>
						<?php foreach (($filterOptions['farms'] ?? []) as $farm): ?>
							<option value="<?= (int)$farm['id'] ?>" <?= (int)$selectedFilters['farm_id'] === (int)$farm['id'] ? 'selected' : '' ?>><?= htmlspecialchars($farm['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></option>
						<?php endforeach; ?>
					</select>
				</label>
				<label>Cuartel
					<select name="block_id">
						<option value="0">Todos</option>
						<?php foreach (($filterOptions['blocks'] ?? []) as $block): ?>
							<option value="<?= (int)$block['id'] ?>" <?= (int)$selectedFilters['block_id'] === (int)$block['id'] ? 'selected' : '' ?>><?= htmlspecialchars($block['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></option>
						<?php endforeach; ?>
					</select>
				</label>
				<label>Proceso
					<select name="process">
						<option value="">Todos</option>
						<?php foreach (($filterOptions['processes'] ?? []) as $p): $pv = (string)($p['process'] ?? ''); ?>
							<option value="<?= htmlspecialchars($pv, ENT_QUOTES, 'UTF-8') ?>" <?= $pv === ($selectedFilters['process'] ?? '') ? 'selected' : '' ?>><?= htmlspecialchars($pv, ENT_QUOTES, 'UTF-8') ?></option>
						<?php endforeach; ?>
					</select>
				</label>
				<label>Desde<input type="date" name="date_from" value="<?= htmlspecialchars($selectedFilters['date_from'] ?? '', ENT_QUOTES, 'UTF-8') ?>"></label>
				<label>Hasta<input type="date" name="date_to" value="<?= htmlspecialchars($selectedFilters['date_to'] ?? '', ENT_QUOTES, 'UTF-8') ?>"></label>
			</div>
		</form>

		<h3>Dashboards guardados</h3>
		<div>
			<ul>
				<li><strong>Vista principal</strong></li>
				<?php foreach ($savedViews as $view): ?>
					<li><?= htmlspecialchars($view['label'] ?? $view['name'], ENT_QUOTES, 'UTF-8') ?></li>
				<?php endforeach; ?>
			</ul>
		</div>

		<h3>Favoritos</h3>
		<div class="bi-footer-note">Arrastra widgets para reordenar. Guarda la vista para tu rol.</div>
	</aside>

	<div class="bi-topbar">
		<div class="left">
			<div class="meta"><?= date('d/m/Y') ?></div>
			<div class="meta">Temporada: <?= htmlspecialchars((string)($dashboard['period'] ?? 'Mes'), ENT_QUOTES, 'UTF-8') ?></div>
		</div>
		<div style="margin-left:auto;display:flex;gap:10px;align-items:center">
			<a class="meta" href="?module=reports">Informes</a>
			<a class="meta" href="?module=settings">Configuración</a>
		</div>
	</div>

	<main class="bi-main">
		<section class="bi-kpis">
			<?php foreach (array_slice($kpis,0,8) as $kpi):
				$delta = $kpi['delta'] ?? null; $isPositive = ($delta ?? 0) >= 0; ?>
				<div class="bi-kpi">
					<div class="title"><?= htmlspecialchars($kpi['label'] ?? 'KPI', ENT_QUOTES, 'UTF-8') ?></div>
					<div class="value"><?= htmlspecialchars(number_format((float)($kpi['value'] ?? 0), 0, ',', '.'), ENT_QUOTES, 'UTF-8') ?></div>
					<div class="delta" style="color: <?= $isPositive ? 'var(--bi-positive)' : 'var(--bi-danger)' ?>"><?= $delta !== null ? ( $isPositive ? '↑ ' : '↓ ' ) . htmlspecialchars(number_format((float)$delta,2,'.',',')) . '%' : '' ?></div>
				</div>
			<?php endforeach; ?>
		</section>

		<?php $sections = $dashboard['sections'] ?? []; $analyses = $dashboard['analyses'] ?? []; ?>
		<?php if ($analyses !== []): ?>
			<section class="bi-analyses">
				<h2>Análisis</h2>
				<ul>
					<?php foreach ($analyses as $analysis): ?>
						<li><?= htmlspecialchars($analysis, ENT_QUOTES, 'UTF-8') ?></li>
					<?php endforeach; ?>
				</ul>
			</section>
		<?php endif; ?>

		<section class="bi-section-list">
			<?php if (!empty($sections['executive'])): $exec = $sections['executive']; ?>
				<section class="bi-section">
					<header><h2><?= htmlspecialchars($exec['title'] ?? 'Resumen ejecutivo', ENT_QUOTES, 'UTF-8') ?></h2></header>
					<div class="bi-section-grid">
						<?php foreach ($exec['kpis'] as $item): ?>
							<article class="bi-card">
								<div class="card-title"><?= htmlspecialchars($item['label'] ?? '-', ENT_QUOTES, 'UTF-8') ?></div>
								<div class="card-value"><?= htmlspecialchars(number_format((float)($item['value'] ?? 0), 0, ',', '.'), ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars($item['unit'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
								<?php if (!empty($item['note'])): ?><div class="card-note"><?= htmlspecialchars($item['note'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
							</article>
						<?php endforeach; ?>
					</div>
				</section>
			<?php endif; ?>

			<?php if (!empty($sections['production'])): $prod = $sections['production']; ?>
				<section class="bi-section">
					<header><h2><?= htmlspecialchars($prod['title'] ?? 'Producción', ENT_QUOTES, 'UTF-8') ?></h2></header>
					<div class="bi-section-grid">
						<div class="bi-card">
							<div class="card-title">Producción por temporada</div>
							<ul>
								<?php foreach (array_slice($prod['by_season'] ?? [], 0, 6) as $row): ?>
									<li><?= htmlspecialchars($row['name'] ?? '-', ENT_QUOTES, 'UTF-8') ?>: <?= htmlspecialchars(number_format((float)($row['total'] ?? 0), 0, ',', '.'), ENT_QUOTES, 'UTF-8') ?> kg</li>
								<?php endforeach; ?>
							</ul>
						</div>
						<div class="bi-card">
							<div class="card-title">Producción por cultivo</div>
							<ul>
								<?php foreach (array_slice($prod['by_species'] ?? [], 0, 6) as $row): ?>
									<li><?= htmlspecialchars($row['species'] ?? '-', ENT_QUOTES, 'UTF-8') ?>: <?= htmlspecialchars(number_format((float)($row['total'] ?? 0), 0, ',', '.'), ENT_QUOTES, 'UTF-8') ?> kg</li>
								<?php endforeach; ?>
							</ul>
						</div>
						<div class="bi-card">
							<div class="card-title">Producción por fundo</div>
							<ul>
								<?php foreach (array_slice($prod['by_farm'] ?? [], 0, 6) as $row): ?>
									<li><?= htmlspecialchars($row['farm'] ?? '-', ENT_QUOTES, 'UTF-8') ?>: <?= htmlspecialchars(number_format((float)($row['total'] ?? 0), 0, ',', '.'), ENT_QUOTES, 'UTF-8') ?> kg</li>
								<?php endforeach; ?>
							</ul>
						</div>
						<div class="bi-card">
							<div class="card-title">Producción por cuartel</div>
							<ul>
								<?php foreach (array_slice($prod['by_block'] ?? [], 0, 6) as $row): ?>
									<li><?= htmlspecialchars($row['block'] ?? '-', ENT_QUOTES, 'UTF-8') ?>: <?= htmlspecialchars(number_format((float)($row['total'] ?? 0), 0, ',', '.'), ENT_QUOTES, 'UTF-8') ?> kg</li>
								<?php endforeach; ?>
							</ul>
						</div>
					</div>
				</section>
			<?php endif; ?>

			<?php if (!empty($sections['accounting'])): $acc = $sections['accounting']; ?>
				<section class="bi-section">
					<header><h2><?= htmlspecialchars($acc['title'] ?? 'Contabilidad', ENT_QUOTES, 'UTF-8') ?></h2></header>
					<div class="bi-section-grid">
						<div class="bi-card">
							<div class="card-title">Gastos</div>
							<div class="card-value"><?= htmlspecialchars(number_format((float)($acc['expenses'] ?? 0), 0, ',', '.'), ENT_QUOTES, 'UTF-8') ?> CLP</div>
						</div>
						<div class="bi-card">
							<div class="card-title">Costo laboral</div>
							<div class="card-value"><?= htmlspecialchars(number_format((float)($acc['labor_cost'] ?? 0), 0, ',', '.'), ENT_QUOTES, 'UTF-8') ?> CLP</div>
						</div>
						<div class="bi-card">
							<div class="card-title">Facturas</div>
							<div class="card-value"><?= htmlspecialchars(count($acc['purchase_invoices'] ?? []), ENT_QUOTES, 'UTF-8') ?> recibidas</div>
						</div>
						<div class="bi-card">
							<div class="card-title">Presupuestos</div>
							<div class="card-value"><?= htmlspecialchars(count($acc['budgets'] ?? []), ENT_QUOTES, 'UTF-8') ?> registros</div>
						</div>
					</div>
				</section>
			<?php endif; ?>

			<?php if (!empty($sections['costs'])): $cost = $sections['costs']; ?>
				<section class="bi-section">
					<header><h2><?= htmlspecialchars($cost['title'] ?? 'Costos', ENT_QUOTES, 'UTF-8') ?></h2></header>
					<div class="bi-section-grid">
						<div class="bi-card">
							<div class="card-title">Costo por proceso</div>
							<ul>
								<?php foreach (array_slice($cost['by_process'] ?? [], 0, 6) as $row): ?>
									<li><?= htmlspecialchars($row['process'] ?? '-', ENT_QUOTES, 'UTF-8') ?>: <?= htmlspecialchars(number_format((float)($row['total'] ?? 0), 0, ',', '.'), ENT_QUOTES, 'UTF-8') ?> CLP</li>
								<?php endforeach; ?>
							</ul>
						</div>
						<div class="bi-card">
							<div class="card-title">Costo por maquinaria</div>
							<ul>
								<?php foreach (array_slice($cost['by_machinery'] ?? [], 0, 6) as $row): ?>
									<li><?= htmlspecialchars($row['name'] ?? '-', ENT_QUOTES, 'UTF-8') ?>: <?= htmlspecialchars(number_format((float)($row['maintenance_cost'] ?? 0), 0, ',', '.'), ENT_QUOTES, 'UTF-8') ?> CLP</li>
								<?php endforeach; ?>
							</ul>
						</div>
						<div class="bi-card">
							<div class="card-title">Costo por trabajador</div>
							<ul>
								<?php foreach (array_slice($cost['by_worker'] ?? [], 0, 6) as $row): ?>
									<li><?= htmlspecialchars($row['full_name'] ?? '-', ENT_QUOTES, 'UTF-8') ?>: <?= htmlspecialchars(number_format((float)($row['total'] ?? 0), 0, ',', '.'), ENT_QUOTES, 'UTF-8') ?> CLP</li>
								<?php endforeach; ?>
							</ul>
						</div>
					</div>
				</section>
			<?php endif; ?>

			<?php if (!empty($sections['warehouse'])): $warehouse = $sections['warehouse']; ?>
				<section class="bi-section">
					<header><h2><?= htmlspecialchars($warehouse['title'] ?? 'Bodega', ENT_QUOTES, 'UTF-8') ?></h2></header>
					<div class="bi-section-grid">
						<div class="bi-card">
							<div class="card-title">Stock crítico</div>
							<ul>
								<?php foreach (array_slice($warehouse['critical_stock'] ?? [], 0, 6) as $row): ?>
									<li><?= htmlspecialchars($row['name'] ?? '-', ENT_QUOTES, 'UTF-8') ?>: <?= htmlspecialchars(number_format((float)($row['stock'] ?? 0), 0, ',', '.'), ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars($row['unit'] ?? '', ENT_QUOTES, 'UTF-8') ?></li>
								<?php endforeach; ?>
							</ul>
						</div>
						<div class="bi-card">
							<div class="card-title">Rotación</div>
							<ul>
								<?php foreach (array_slice($warehouse['rotation'] ?? [], 0, 6) as $row): ?>
									<li><?= htmlspecialchars($row['name'] ?? '-', ENT_QUOTES, 'UTF-8') ?>: <?= htmlspecialchars(number_format((float)($row['moved'] ?? 0), 0, ',', '.'), ENT_QUOTES, 'UTF-8') ?></li>
								<?php endforeach; ?>
							</ul>
						</div>
					</div>
				</section>
			<?php endif; ?>

			<?php if (!empty($sections['hr'])): $hr = $sections['hr']; ?>
				<section class="bi-section">
					<header><h2><?= htmlspecialchars($hr['title'] ?? 'RRHH', ENT_QUOTES, 'UTF-8') ?></h2></header>
					<div class="bi-section-grid">
						<div class="bi-card">
							<div class="card-title">Trabajadores activos</div>
							<div class="card-value"><?= htmlspecialchars(number_format((int)($hr['workers_active'] ?? 0), 0, ',', '.'), ENT_QUOTES, 'UTF-8') ?></div>
						</div>
						<div class="bi-card">
							<div class="card-title">Horas trabajadas</div>
							<div class="card-value"><?= htmlspecialchars(number_format((float)($hr['total_hours'] ?? 0), 0, ',', '.'), ENT_QUOTES, 'UTF-8') ?></div>
						</div>
						<div class="bi-card">
							<div class="card-title">Costo laboral</div>
							<div class="card-value"><?= htmlspecialchars(number_format((float)($hr['total_labor_cost'] ?? 0), 0, ',', '.'), ENT_QUOTES, 'UTF-8') ?> CLP</div>
						</div>
					</div>
				</section>
			<?php endif; ?>

			<?php if (!empty($sections['machinery'])): $machinery = $sections['machinery']; ?>
				<section class="bi-section">
					<header><h2><?= htmlspecialchars($machinery['title'] ?? 'Maquinaria', ENT_QUOTES, 'UTF-8') ?></h2></header>
					<div class="bi-section-grid">
						<div class="bi-card">
							<div class="card-title">Equipos operativos</div>
							<div class="card-value"><?= htmlspecialchars(number_format(count($machinery['list'] ?? []), 0, ',', '.'), ENT_QUOTES, 'UTF-8') ?></div>
						</div>
						<div class="bi-card">
							<div class="card-title">Mantenciones recientes</div>
							<ul>
								<?php foreach (array_slice($machinery['maintenance'] ?? [], 0, 6) as $row): ?>
									<li><?= htmlspecialchars($row['maintenance_type'] ?? '-', ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars((string)($row['maintenance_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?>: <?= htmlspecialchars(number_format((float)($row['cost'] ?? 0), 0, ',', '.'), ENT_QUOTES, 'UTF-8') ?> CLP</li>
								<?php endforeach; ?>
							</ul>
						</div>
						<div class="bi-card">
							<div class="card-title">Combustible</div>
							<ul>
								<?php foreach (array_slice($machinery['fuel'] ?? [], 0, 6) as $row): ?>
									<li>#<?= htmlspecialchars((string)($row['machinery_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>: <?= htmlspecialchars(number_format((float)($row['liters'] ?? 0), 0, ',', '.'), ENT_QUOTES, 'UTF-8') ?> L</li>
								<?php endforeach; ?>
							</ul>
						</div>
					</div>
				</section>
			<?php endif; ?>

			<?php if (!empty($sections['procurement'])): $proc = $sections['procurement']; ?>
				<section class="bi-section">
					<header><h2><?= htmlspecialchars($proc['title'] ?? 'Compras', ENT_QUOTES, 'UTF-8') ?></h2></header>
					<div class="bi-section-grid">
						<div class="bi-card">
							<div class="card-title">Órdenes</div>
							<div class="card-value"><?= htmlspecialchars(number_format(count($proc['orders'] ?? []), 0, ',', '.'), ENT_QUOTES, 'UTF-8') ?></div>
						</div>
						<div class="bi-card">
							<div class="card-title">Recepciones</div>
							<div class="card-value"><?= htmlspecialchars(number_format(count($proc['receptions'] ?? []), 0, ',', '.'), ENT_QUOTES, 'UTF-8') ?></div>
						</div>
						<div class="bi-card">
							<div class="card-title">Proveedores activos</div>
							<div class="card-value"><?= htmlspecialchars(number_format(count($proc['suppliers'] ?? []), 0, ',', '.'), ENT_QUOTES, 'UTF-8') ?></div>
						</div>
					</div>
				</section>
			<?php endif; ?>
		</section>

		<div class="bi-grid">
			<section class="bi-widgets" data-bi-widgets>
				<?php if ($selectedWidgetObjects !== []): ?>
					<?php foreach ($selectedWidgetObjects as $widget): $wid = htmlspecialchars((string)($widget['id'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
						<article class="bi-widget" data-bi-widget="<?= $wid ?>">
							<div class="widget-head">
								<strong><?= htmlspecialchars($widget['title'] ?? $wid, ENT_QUOTES, 'UTF-8') ?></strong>
								<div>
									<button data-widget-toggle type="button">Size</button>
								</div>
							</div>
							<?php if (($widget['type'] ?? '') === 'bars'): ?>
								<div style="height:220px"><canvas data-bi-chart data-bi-chart-type="bar" data-bi-chart='<?= json_encode(['labels'=>array_column($widget['data'],'period'),'datasets'=>[['label'=>$widget['title'] ?? '','data'=>array_column($widget['data'],'value')]]]) ?>'></canvas></div>
							<?php elseif (($widget['type'] ?? '') === 'list'): ?>
								<?php if (!empty($widget['data'])): ?>
									<div>
										<?php foreach ($widget['data'] as $item): ?>
											<div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px dashed rgba(255,255,255,0.02)"><div><?= htmlspecialchars($item['label'] ?? $item['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></div><div><?= htmlspecialchars((string)($item['value'] ?? $item['stock'] ?? 0), ENT_QUOTES, 'UTF-8') ?></div></div>
										<?php endforeach; ?>
									</div>
								<?php else: ?>
									<div class="empty">No hay datos para este panel.</div>
								<?php endif; ?>
							<?php else: ?>
								<div class="empty">Widget sin implementación visual.</div>
							<?php endif; ?>
						</article>
					<?php endforeach; ?>
				<?php else: ?>
					<div class="bi-widget"><div class="empty">No hay widgets seleccionados. Añade widgets desde configuración o guarda una vista.</div></div>
				<?php endif; ?>
			</section>

			<aside>
				<div class="bi-widget">
					<div class="widget-head"><strong>Alertas</strong></div>
					<div class="bi-alerts">
						<?php foreach ($alerts as $a): $sev = $a['severity'] ?? 'normal'; $cls = $sev === 'critical' ? 'high' : ($sev === 'normal' ? 'low' : ''); ?>
							<div class="bi-alert <?= $cls ?>">
								<div class="label"><?= htmlspecialchars($a['title'] ?? 'Alerta', ENT_QUOTES, 'UTF-8') ?></div>
								<div style="margin-left:auto;color:var(--bi-muted)"><?= htmlspecialchars((string)($a['count'] ?? 0), ENT_QUOTES, 'UTF-8') ?></div>
							</div>
						<?php endforeach; ?>
					</div>
			</div>

			<?php if ($sections !== []): ?>
				<div class="bi-widget">
					<div class="widget-head"><strong>Secciones clave</strong></div>
					<div style="display:flex;flex-direction:column;gap:0.75rem;padding:0.75rem 0">
						<?php foreach ($sections as $section): ?>
							<div>
								<strong><?= htmlspecialchars($section['title'] ?? 'Sección', ENT_QUOTES, 'UTF-8') ?></strong>
								<div style="font-size:0.9rem;color:var(--bi-muted)">
									<?= htmlspecialchars(count($section['by_farm'] ?? $section['by_process'] ?? $section['orders'] ?? []), ENT_QUOTES, 'UTF-8') ?> registros
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>
				</div>

				<div class="bi-widget">
					<div class="widget-head"><strong>Acciones</strong></div>
					<div style="display:flex;flex-direction:column;gap:8px">
						<form method="post" data-bi-save-form>
							<input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
							<input type="hidden" name="action" value="save_dashboard_view">
							<label>Nombre de la vista <input type="text" name="view_name" placeholder="Mi vista personal"></label>
							<button data-bi-save class="primary-button">Guardar vista</button>
						</form>
						<form method="post" onsubmit="return confirm('Restablecer layout por defecto?')">
							<input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
							<input type="hidden" name="action" value="reset_dashboard">
							<button class="secondary-button" type="submit">Restablecer dashboard</button>
						</form>
					</div>
				</div>
			</aside>
		</div>
	</main>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="assets/js/bi-dashboard.js" defer></script>
</body>
</html>
