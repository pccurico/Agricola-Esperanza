<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>CampoSur | Gestión Agrícola</title>
  <style>
    :root { --ink: #18362e; --muted: #73837c; --line: #e6ebe7; --canvas: #f5f7f4; --panel: #fff; --forest: #1f5a45; --lime: #cbe572; --gold: #e9b96a; --blue: #4d91aa; --orange: #d97945; }
    * { box-sizing: border-box; }
    body { margin: 0; background: var(--canvas); color: var(--ink); font: 14px/1.4 Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
    button, select { font: inherit; }
    .shell { display: grid; grid-template-columns: 250px 1fr; min-height: 100vh; }
    .sidebar { display: flex; flex-direction: column; padding: 25px 16px 18px; background: var(--ink); color: #eff6ed; }
    .brand { display: flex; align-items: center; gap: 10px; padding: 0 10px 32px; font-weight: 800; font-size: 21px; letter-spacing: -.8px; }
    .brand-mark { display: grid; place-items: center; width: 31px; height: 31px; border-radius: 9px; color: var(--ink); background: var(--lime); font-size: 18px; }.brand-logo { width: 31px; height: 31px; border-radius: 9px; object-fit: cover; background: #fff; }
    .workspace { margin: 0 10px 8px; color: #90a69d; font-size: 10px; font-weight: 800; letter-spacing: 1.1px; text-transform: uppercase; }
    .farm-select { display: flex; align-items: center; justify-content: space-between; margin: 0 6px 27px; padding: 10px 11px; border: 1px solid #466459; border-radius: 8px; background: #24463c; font-size: 12px; }
    .farm-select b { color: #fff; }
    .nav { display: grid; gap: 4px; }
    .nav-label { margin: 16px 10px 6px; color: #8da198; font-size: 10px; font-weight: 800; letter-spacing: 1.1px; text-transform: uppercase; }
    .nav-item { display: flex; align-items: center; gap: 12px; padding: 11px 12px; border: 0; border-radius: 8px; color: #c6d4ce; background: transparent; cursor: pointer; text-align: left; text-decoration: none; }
    .nav-item:hover, .nav-item.active { color: #fff; background: #315e4e; }
    .nav-icon { width: 19px; color: var(--lime); text-align: center; font-size: 16px; }
    .user { display: flex; align-items: center; gap: 10px; margin-top: auto; padding: 15px 9px 0; border-top: 1px solid #406154; }
    .avatar { display: grid; place-items: center; width: 32px; height: 32px; border-radius: 50%; background: #e4af69; color: var(--ink); font-size: 11px; font-weight: 800; }
    .user small { display: block; color: #9fb3aa; font-size: 11px; }
    .content { padding: 27px 34px 38px; overflow: hidden; }
    .topbar { display: flex; align-items: center; justify-content: space-between; gap: 18px; margin-bottom: 26px; }
    .crumb { color: var(--muted); font-size: 12px; }
    .crumb b { color: var(--ink); }
    .top-actions { display: flex; align-items: center; gap: 11px; }
    .season-select, .action-button { height: 36px; border-radius: 7px; }
    .season-select { padding: 0 12px; border: 1px solid var(--line); color: var(--ink); background: var(--panel); font-size: 12px; }
    .action-button { padding: 0 15px; border: 0; color: #fff; background: var(--forest); font-weight: 700; cursor: pointer; }
    .page-heading { display: flex; align-items: flex-end; justify-content: space-between; gap: 20px; margin-bottom: 23px; }
    h1, h2, h3, p { margin: 0; }
    h1 { font-size: 28px; letter-spacing: -1.2px; }
    .subtitle { margin-top: 5px; color: var(--muted); }
    .updated { padding-bottom: 3px; color: var(--muted); font-size: 12px; }
    .filters { display: flex; gap: 10px; padding: 13px 15px; margin-bottom: 22px; border: 1px solid var(--line); border-radius: 10px; background: var(--panel); }
    .filter { min-width: 175px; padding-right: 13px; border-right: 1px solid var(--line); }
    .filter:last-child { border: 0; }
    .filter label { display: block; margin-bottom: 3px; color: var(--muted); font-size: 10px; font-weight: 800; letter-spacing: .7px; text-transform: uppercase; }
    .filter select { width: 100%; padding: 0; border: 0; outline: 0; color: var(--ink); background: transparent; font-size: 12px; font-weight: 700; }
    .kpis { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 22px; }
    .kpi, .card { border: 1px solid var(--line); border-radius: 11px; background: var(--panel); box-shadow: 0 2px 5px rgb(21 48 39 / 2%); }
    .kpi { padding: 17px; }
    .kpi-top { display: flex; align-items: center; justify-content: space-between; color: var(--muted); font-size: 12px; }
    .kpi-symbol { display: grid; place-items: center; width: 27px; height: 27px; border-radius: 7px; background: #edf5e4; color: var(--forest); font-weight: 900; }
    .kpi-value { margin-top: 13px; font-size: 23px; font-weight: 800; letter-spacing: -1px; }
    .kpi-note { margin-top: 4px; color: #4b896b; font-size: 11px; font-weight: 700; }
    .kpi-note.warn { color: #b17131; }
    .board { display: grid; grid-template-columns: minmax(0, 1.55fr) minmax(310px, .9fr); gap: 20px; }
    .card { padding: 20px; }
    .card-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 10px; margin-bottom: 18px; }
    .card-header h2 { font-size: 15px; letter-spacing: -.3px; }
    .card-header p { margin-top: 3px; color: var(--muted); font-size: 11px; }
    .text-button { padding: 0; border: 0; color: var(--forest); background: none; font-size: 12px; font-weight: 800; cursor: pointer; }
    .season-chart { position: relative; display: grid; grid-template-columns: 32px 1fr; gap: 10px; height: 244px; }
    .y-axis { display: flex; flex-direction: column; justify-content: space-between; padding-bottom: 24px; color: #97a39d; font-size: 10px; text-align: right; }
    .chart-area { display: flex; align-items: flex-end; gap: 21px; padding: 9px 8px 25px; border-bottom: 1px solid var(--line); background: repeating-linear-gradient(to bottom, transparent 0 43px, #edf1ee 44px 45px); }
    .bar-group { display: flex; flex: 1; align-items: flex-end; justify-content: center; gap: 4px; height: 100%; position: relative; }
    .bar-group::after { position: absolute; bottom: -20px; content: attr(data-season); color: var(--muted); font-size: 10px; white-space: nowrap; }
    .bar { width: 11px; border-radius: 3px 3px 0 0; }.bar-h28 { height: 28%; }.bar-h31 { height: 31%; }.bar-h33 { height: 33%; }.bar-h35 { height: 35%; }.bar-h38 { height: 38%; }.bar-h40 { height: 40%; }.bar-h41 { height: 41%; }.bar-h42 { height: 42%; }.bar-h44 { height: 44%; }.bar-h45 { height: 45%; }.bar-h51 { height: 51%; }.bar-h57 { height: 57%; }.bar-h73 { height: 73%; }.bar-h83 { height: 83%; }.bar-h88 { height: 88%; }.bar-h93 { height: 93%; }
    .admin { background: var(--blue); }.labor { background: var(--forest); }.investment { background: var(--gold); }.storage { background: var(--lime); }
    .chart-legend { display: flex; flex-wrap: wrap; gap: 12px; margin: 17px 0 0 42px; }
    .legend { display: flex; align-items: center; gap: 5px; color: var(--muted); font-size: 10px; }
    .dot { width: 7px; height: 7px; border-radius: 50%; }
    .expense-list { display: grid; gap: 15px; }
    .expense-row { display: grid; grid-template-columns: 125px 1fr 70px; gap: 10px; align-items: center; font-size: 12px; }
    .expense-name { font-weight: 700; }
    .progress { height: 7px; overflow: hidden; border-radius: 8px; background: #edf1ee; }.progress span { display: block; height: 100%; border-radius: inherit; background: var(--forest); }.progress-full { width: 100%; }.progress-74 { width: 74%; }.progress-57 { width: 57%; }.progress-42 { width: 42%; }.progress-8 { width: 8%; }.expense-row:nth-child(2) .progress span { background: var(--gold); }.expense-row:nth-child(3) .progress span { background: var(--blue); }.expense-row:nth-child(4) .progress span { background: var(--orange); }.expense-row:nth-child(5) .progress span { background: #8ea9c4; }
    .amount { color: var(--muted); font-size: 11px; font-weight: 700; text-align: right; }
    .lower { display: grid; grid-template-columns: 1.55fr .9fr; gap: 20px; margin-top: 20px; }
    .table-card { padding: 0; overflow: hidden; }
    .table-head { display: flex; align-items: center; justify-content: space-between; padding: 19px 20px 14px; }
    .data-table { width: 100%; border-collapse: collapse; }
    .data-table th { padding: 10px 20px; color: var(--muted); background: #f8f9f7; font-size: 10px; font-weight: 800; letter-spacing: .55px; text-align: left; text-transform: uppercase; }
    .data-table td { padding: 12px 20px; border-top: 1px solid var(--line); font-size: 12px; }
    .data-table td:last-child, .data-table th:last-child { text-align: right; }.data-table td:nth-child(3), .data-table th:nth-child(3) { text-align: right; }
    .tag { display: inline-block; padding: 3px 7px; border-radius: 5px; background: #eef5e6; color: #487234; font-size: 10px; font-weight: 800; }
    .report-stack { display: grid; gap: 10px; }.report { display: flex; align-items: center; gap: 12px; padding: 12px; border: 1px solid var(--line); border-radius: 8px; }.report-icon { display: grid; place-items: center; flex: 0 0 34px; height: 34px; border-radius: 7px; color: var(--forest); background: #edf5e8; font-size: 15px; }.report-copy { min-width: 0; flex: 1; }.report-copy b, .report-copy span { display: block; }.report-copy b { font-size: 12px; }.report-copy span { overflow: hidden; color: var(--muted); font-size: 10px; text-overflow: ellipsis; white-space: nowrap; }.download { color: var(--forest); font-weight: 900; }
    .alert { display: flex; gap: 10px; margin-top: 18px; padding: 12px; border-radius: 8px; color: #79562a; background: #fff8e7; font-size: 11px; }.alert b { display: block; margin-bottom: 2px; }
    @media (max-width: 1050px) { .shell { grid-template-columns: 74px 1fr; }.sidebar { padding: 23px 10px; }.brand { justify-content: center; padding: 0 0 27px; }.brand span, .workspace, .farm-select b, .farm-select span, .nav-item span:not(.nav-icon), .nav-label, .user-info { display: none; }.farm-select { justify-content: center; margin: 0 0 20px; }.nav-item { justify-content: center; }.user { justify-content: center; padding-inline: 0; }.kpis { grid-template-columns: repeat(2, 1fr); }.board, .lower { grid-template-columns: 1fr; } }
    @media (max-width: 650px) { .shell { display: block; }.sidebar { display: none; }.content { padding: 20px 15px; }.topbar { margin-bottom: 22px; }.page-heading { align-items: flex-start; flex-direction: column; }.filters { overflow-x: auto; }.filter { min-width: 150px; }.kpis { grid-template-columns: 1fr 1fr; gap: 10px; }.kpi { padding: 13px; }.kpi-value { font-size: 18px; }.expense-row { grid-template-columns: 100px 1fr 60px; }.data-table th, .data-table td { padding: 10px; }.data-table th:nth-child(2), .data-table td:nth-child(2) { display: none; } }
  </style>
</head>
<body>
  <main class="shell">
    <aside class="sidebar">
      <div class="brand"><?php if ($logoUrl): ?><img class="brand-logo" src="<?= htmlspecialchars($logoUrl) ?>" alt="Logo de <?= htmlspecialchars($companyName) ?>"><?php else: ?><span class="brand-mark">✦</span><?php endif; ?><span><?= htmlspecialchars($companyName) ?></span></div>
      <p class="workspace">Empresa activa</p>
      <div class="farm-select"><b><?= htmlspecialchars($companyName) ?></b><span>⌄</span></div>
      <nav class="nav">
        <a class="nav-item active" href="/"><span class="nav-icon">▦</span><span>Resumen ejecutivo</span></a>
        <p class="nav-label">Operación</p>
        <a class="nav-item" href="?module=masters"><span class="nav-icon">◫</span><span>Administración</span></a>
        <a class="nav-item" href="?module=procurement"><span class="nav-icon">▥</span><span>Compras</span></a>
        <a class="nav-item" href="?module=receptions"><span class="nav-icon">⇩</span><span>Recepciones</span></a>
        <a class="nav-item" href="?module=production"><span class="nav-icon">◉</span><span>Producción</span></a>
        <a class="nav-item" href="?module=labor"><span class="nav-icon">♟</span><span>Mano de obra</span></a>
        <a class="nav-item" href="?module=costs&category=INVERSION"><span class="nav-icon">◒</span><span>Inversiones</span></a>
        <a class="nav-item" href="?module=budgets"><span class="nav-icon">▥</span><span>Presupuestos</span></a>
        <a class="nav-item" href="?module=costs&category=SERVICIOS_GASTOS"><span class="nav-icon">▤</span><span>Servicios y gastos</span></a>
        <a class="nav-item" href="?module=inventory"><span class="nav-icon">▣</span><span>Bodega</span></a>
        <a class="nav-item" href="?module=warehouses"><span class="nav-icon">⌂</span><span>Bodegas y lotes</span></a>
        <a class="nav-item" href="?module=requests"><span class="nav-icon">≡</span><span>Solicitudes internas</span></a>
        <a class="nav-item" href="?module=notifications"><span class="nav-icon">◉</span><span>Notificaciones</span></a>
        <a class="nav-item" href="?module=planning"><span class="nav-icon">▣</span><span>Tareas y calendario</span></a>
        <a class="nav-item" href="?module=documents"><span class="nav-icon">▤</span><span>Documentos</span></a>
        <a class="nav-item" href="?module=api"><span class="nav-icon">↔</span><span>API e integraciones</span></a>
        <a class="nav-item" href="?module=machinery"><span class="nav-icon">⚙</span><span>Maquinaria</span></a>
        <p class="nav-label">Gestión</p>
        <a class="nav-item" href="?module=reports"><span class="nav-icon">◌</span><span>Informes</span></a>
        <a class="nav-item" href="?module=users"><span class="nav-icon">♙</span><span>Usuarios y roles</span></a>
        <a class="nav-item" href="?module=settings"><span class="nav-icon">⚙</span><span>Configuración</span></a>
        <a class="nav-item" href="?module=catalogs"><span class="nav-icon">◫</span><span>Catálogos</span></a>
        <a class="nav-item" href="?module=profile"><span class="nav-icon">◉</span><span>Mi perfil</span></a>
        <a class="nav-item" href="?module=audit"><span class="nav-icon">◷</span><span>Actividad</span></a>
      </nav>
      <div class="user"><span class="avatar"><?= htmlspecialchars(strtoupper(substr($currentUser, 0, 2))) ?></span><div class="user-info"><b><?= htmlspecialchars($currentUser) ?></b><small>Administrador</small></div></div>
    </aside>
    <section class="content">
      <header class="topbar"><p class="crumb"><?= htmlspecialchars($companyName) ?> <span> / </span><b>Panel ejecutivo</b></p><div class="top-actions"><select class="season-select"><option>Temporada 2025–2026</option><option>Temporada 2024–2025</option></select><button class="action-button">+ Registrar movimiento</button><a class="logout-link" href="?logout=1">Salir</a></div></header>
      <div class="page-heading"><div><h1>Resumen ejecutivo</h1><p class="subtitle">Visión consolidada de la operación agrícola.</p></div><p class="updated">Actualizado hoy, 09:42 hrs</p></div>
      <section class="filters"><div class="filter"><label>Temporada</label><select><option>2025–2026</option></select></div><div class="filter"><label>Fundo</label><select><option>Todos los fundos</option></select></div><div class="filter"><label>Especie</label><select><option>Todas las especies</option></select></div><div class="filter"><label>Cuartel</label><select><option>Todos los cuarteles</option></select></div></section>
      <section class="kpis"><article class="kpi"><div class="kpi-top"><span>Costo acumulado</span><span class="kpi-symbol">$</span></div><p class="kpi-value">$<?= $totalCost ?></p><p class="kpi-note">↑ 8,4% vs. temporada anterior</p></article><article class="kpi"><div class="kpi-top"><span>Costo promedio / Ha</span><span class="kpi-symbol">◒</span></div><p class="kpi-value">$<?= $hectares !== '0,00' ? number_format((float) $dashboard['totals']['total_cost'] / (float) $dashboard['totals']['hectares'], 0, ',', '.') : '0' ?></p><p class="kpi-note warn">↑ $84.215 por hectárea</p></article><article class="kpi"><div class="kpi-top"><span>Hectáreas operativas</span><span class="kpi-symbol">⌁</span></div><p class="kpi-value"><?= $hectares ?> Ha</p><p class="kpi-note">96,8% de superficie planificada</p></article><article class="kpi"><div class="kpi-top"><span>Movimientos del mes</span><span class="kpi-symbol">↗</span></div><p class="kpi-value"><?= $movements ?></p><p class="kpi-note">↑ 12% respecto a junio</p></article></section>
      <section class="board"><article class="card"><header class="card-header"><div><h2>Costos por gran clasificación</h2><p>Comparativo de las últimas cuatro temporadas</p></div><button class="text-button">Ver detalle →</button></header><div class="season-chart"><div class="y-axis"><span>$1.000 M</span><span>$750 M</span><span>$500 M</span><span>$250 M</span><span>$0</span></div><div class="chart-area"><div class="bar-group" data-season="2022–2023"><span class="bar admin bar-h35"></span><span class="bar labor bar-h73"></span><span class="bar investment bar-h28"></span><span class="bar storage bar-h33"></span></div><div class="bar-group" data-season="2023–2024"><span class="bar admin bar-h42"></span><span class="bar labor bar-h93"></span><span class="bar investment bar-h31"></span><span class="bar storage bar-h38"></span></div><div class="bar-group" data-season="2024–2025"><span class="bar admin bar-h51"></span><span class="bar labor bar-h83"></span><span class="bar investment bar-h40"></span><span class="bar storage bar-h41"></span></div><div class="bar-group" data-season="2025–2026"><span class="bar admin bar-h57"></span><span class="bar labor bar-h88"></span><span class="bar investment bar-h44"></span><span class="bar storage bar-h45"></span></div></div></div><div class="chart-legend"><span class="legend"><i class="dot admin"></i>Administración</span><span class="legend"><i class="dot labor"></i>Mano de obra</span><span class="legend"><i class="dot investment"></i>Inversión</span><span class="legend"><i class="dot storage"></i>Bodega</span></div></article>
      <article class="card"><header class="card-header"><div><h2>Distribución de costos</h2><p>Temporada 2025–2026</p></div><button class="text-button">Informe</button></header><div class="expense-list"><div class="expense-row"><b class="expense-name">Mano de obra</b><div class="progress"><span class="progress-full"></span></div><span class="amount">$956,4 M</span></div><div class="expense-row"><b class="expense-name">Administración</b><div class="progress"><span class="progress-74"></span></div><span class="amount">$711,2 M</span></div><div class="expense-row"><b class="expense-name">Bodega</b><div class="progress"><span class="progress-57"></span></div><span class="amount">$548,6 M</span></div><div class="expense-row"><b class="expense-name">Servicios y gastos</b><div class="progress"><span class="progress-42"></span></div><span class="amount">$405,1 M</span></div><div class="expense-row"><b class="expense-name">Inversiones</b><div class="progress"><span class="progress-8"></span></div><span class="amount">$76,5 M</span></div></div><div class="alert"><span>◉</span><div><b>Atención de presupuesto</b>Administración supera en 6,2% el presupuesto mensual.</div></div></article></section>
      <section class="lower"><article class="card table-card"><header class="table-head"><div><h2>Costos por fundo y cuartel</h2><p>Mayor gasto acumulado de la temporada</p></div><button class="text-button">Ver todos →</button></header><table class="data-table"><thead><tr><th>Fundo / cuartel</th><th>Especie</th><th>Hectáreas</th><th>Costo total</th></tr></thead><tbody><tr><td><b>Cerezos · Cuartel 5</b></td><td><span class="tag">Cerezos</span></td><td>91,16 Ha</td><td><b>$289.912.232</b></td></tr><tr><td><b>Kiwis · Cuartel 7</b></td><td><span class="tag">Kiwis</span></td><td>34,49 Ha</td><td><b>$180.437.752</b></td></tr><tr><td><b>Cerezos · Cuartel 1</b></td><td><span class="tag">Cerezos</span></td><td>126,10 Ha</td><td><b>$112.798.562</b></td></tr><tr><td><b>Uva Tintorera · 10A</b></td><td><span class="tag">Vid</span></td><td>40,33 Ha</td><td><b>$54.720.487</b></td></tr></tbody></table></article><article class="card"><header class="card-header"><div><h2>Informes frecuentes</h2><p>Acceso rápido para gestión</p></div></header><div class="report-stack"><div class="report"><span class="report-icon">▤</span><div class="report-copy"><b>Resumen por cuartel</b><span>Costos y costo por Ha</span></div><span class="download">↓</span></div><div class="report"><span class="report-icon">♟</span><div class="report-copy"><b>Control de mano de obra</b><span>Jornales y centros de costo</span></div><span class="download">↓</span></div><div class="report"><span class="report-icon">▣</span><div class="report-copy"><b>Valorización de bodega</b><span>Stock, insumos y movimientos</span></div><span class="download">↓</span></div><div class="report"><span class="report-icon">◒</span><div class="report-copy"><b>Comparativo de temporadas</b><span>Desviaciones y tendencias</span></div><span class="download">↓</span></div></div></article></section>
    </section>
  </main>
</body>
</html>
