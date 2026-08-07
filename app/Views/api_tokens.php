<?php
$tokens = $tokens ?? [];
$newToken = $newToken ?? null;
$error = $error ?? null;
$csrf = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');
?>
<!doctype html>
<html lang="es">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Tokens API | PCCURICO</title>
	<link rel="stylesheet" href="assets/css/app.css">
</head>
<body class="admin-page">
<main class="admin-shell">
	<?php require dirname(__DIR__) . '/Views/partials/module-navigation.php'; ?>
	<section class="module-content">
		<div class="page-hero">
			<div class="hero-meta">
				<div class="hero-title">
					<p class="eyebrow">Integraciones</p>
					<h1>Tokens API</h1>
					<p class="lead-text">Administra credenciales para integraciones externas.</p>
				</div>
				<div class="hero-actions">
					<nav class="hero-nav">
						<a class="secondary-link" href="./" onclick="if (window.history.length > 1) { window.history.back(); return false; }">Volver al dashboard</a>
					</nav>
				</div>
			</div>
		</div>

		<?php if ($error): ?><div class="setup-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
		<?php if ($newToken): ?><div class="setup-success"><strong>Token generado (guárdalo ahora):</strong><code><?= htmlspecialchars($newToken, ENT_QUOTES, 'UTF-8') ?></code></div><?php endif; ?>

		<div class="page-grid">
			<main class="main-column">
				<section class="section-card">
					<div class="panel-header"><div><h2>Nuevo token</h2><p>Genera credenciales para acceso a la API</p></div></div>
					<div class="panel-body">
						<form method="post" class="form-group">
							<input type="hidden" name="csrf" value="<?= $csrf ?>">
							<input type="hidden" name="action" value="create">
							<label>Nombre
								<input name="name" required maxlength="100">
							</label>
							<label>Expira el
								<input type="datetime-local" name="expires_at">
							</label>
							<div class="form-actions"><button class="primary-button" type="submit">Generar token</button></div>
						</form>
					</div>
				</section>

				<section class="section-card">
					<div class="panel-header"><div><h2>Tokens emitidos</h2></div></div>
					<div class="panel-body">
						<div class="table-scroll">
							<table class="data-table">
								<thead>
									<tr><th>Nombre</th><th>Creado</th><th>Último uso</th><th>Expiración</th><th>Estado</th><th></th></tr>
								</thead>
								<tbody>
								<?php foreach ($tokens as $token): ?>
									<tr>
										<td><?= htmlspecialchars($token['name'], ENT_QUOTES, 'UTF-8') ?></td>
										<td><?= htmlspecialchars($token['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
										<td><?= htmlspecialchars($token['last_used_at'] ?: '—', ENT_QUOTES, 'UTF-8') ?></td>
										<td><?= htmlspecialchars($token['expires_at'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
										<td><span class="status-pill <?= (!empty($token['revoked']) ? 'status-inactive' : 'status-active') ?>"><?= !empty($token['revoked']) ? 'Revocado' : 'Activo' ?></span></td>
										<td>
											<form method="post" style="display:inline">
												<input type="hidden" name="csrf" value="<?= $csrf ?>">
												<input type="hidden" name="action" value="revoke">
												<input type="hidden" name="id" value="<?= htmlspecialchars($token['id'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
												<button class="table-action" type="submit">Revocar</button>
											</form>
										</td>
									</tr>
								<?php endforeach; ?>
								</tbody>
							</table>
						</div>
					</div>
				</section>
			</main>

			<aside class="sidebar-column">
				<section class="section-card">
					<div class="panel-header"><h3>Información</h3></div>
					<div class="panel-body">
						<p class="lead-text">Los tokens son secretos: se mostrarán sólo una vez al generarlos.</p>
						<div class="simple-list">
							<div><strong>Seguridad</strong><small>Mantén los tokens seguros y regenera si sospechas compromiso.</small></div>
							<div><strong>Alcance</strong><small>Los tokens usan permisos del usuario que los creó.</small></div>
						</div>
					</div>
				</section>
			</aside>
		</div>
	</section>
</main>
<script src="assets/js/table-layout.js" defer></script>
</body>
</html>
