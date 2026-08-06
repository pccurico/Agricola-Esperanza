<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= !empty($show_mode) ? 'Ver trabajador' : (!empty($worker_form['worker']['id'] ?? null) ? 'Editar trabajador' : 'Agregar trabajador') ?> | RR.HH</title>
    <link rel="stylesheet" href="assets/css/app.css">
</head>

<body class="admin-page">
    <main class="admin-shell"><?php require dirname(__DIR__) . '/Views/partials/module-navigation.php'; ?><section class="module-content">
            <header class="admin-header">
                <div>
                    <p class="eyebrow">RR.HH</p>
                    <h1><?= !empty($show_mode) ? 'Ver trabajador' : (!empty($worker_form['worker']['id'] ?? null) ? 'Editar trabajador' : 'Agregar trabajador') ?></h1>
                    <p class="setup-copy"><?= !empty($show_mode) ? 'Detalle de trabajador en modo solo lectura.' : 'Registro completo del trabajador con datos personales, contrato, remuneración y perfil profesional.' ?></p>
                </div>
                <div class="header-actions">
                    <a class="secondary-link" href="?module=labor">Volver a Trabajador</a>
                </div>
            </header>
            <?php if ($error): ?><div class="setup-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
            <?php if ($success): ?><div class="setup-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

            <section class="admin-panel">
                <header class="panel-header">
                    <h2>Formulario de registro</h2>
                    <p>Datos de identidad, laborales y perfil profesional.</p>
                </header>
                <?php $isReadOnly = !empty($show_mode); $readonly = $isReadOnly ? 'readonly' : ''; $disabled = $isReadOnly ? 'disabled' : ''; ?>
                <form method="post" class="admin-form">
                    <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                    <?php if (!$isReadOnly): ?>
                        <input type="hidden" name="action" value="<?= !empty($worker_form['worker']['id'] ?? null) ? 'update_worker' : 'create_worker' ?>">
                    <?php endif; ?>
                    <?php if (!empty($worker_form['worker']['id'] ?? null)): ?>
                        <input type="hidden" name="worker_id" value="<?= (int) $worker_form['worker']['id'] ?>">
                    <?php endif; ?>

                    <label>Nombre completo<input <?= $readonly ?> <?= $isReadOnly ? '' : 'required' ?> name="full_name" value="<?= htmlspecialchars((string) ($worker_form['worker']['full_name'] ?? '')) ?>"></label>
                    <label>RUT<input <?= $readonly ?> name="tax_id" value="<?= htmlspecialchars((string) ($worker_form['worker']['tax_id'] ?? '')) ?>"></label>
                    <label>Tipo de trabajador<select <?= $disabled ?> name="worker_type">
                        <option value="PERMANENTE" <?= (($worker_form['worker']['worker_type'] ?? '') === 'PERMANENTE') ? 'selected' : '' ?>>Permanente</option>
                        <option value="TEMPORAL" <?= (($worker_form['worker']['worker_type'] ?? '') === 'TEMPORAL') ? 'selected' : '' ?>>Temporal</option>
                        <option value="CONTRATISTA" <?= (($worker_form['worker']['worker_type'] ?? '') === 'CONTRATISTA') ? 'selected' : '' ?>>Contratista</option>
                    </select></label>
                    <label>Tarifa base<input type="number" step="1" min="0" <?= $readonly ?> name="default_rate" value="<?= htmlspecialchars((string) ((int) ($worker_form['worker']['default_rate'] ?? 0))) ?>"></label>
                    <label>Activo<select <?= $disabled ?> name="active"><option value="1" <?= ((int) ($worker_form['worker']['active'] ?? 1) === 1) ? 'selected' : '' ?>>Sí</option><option value="0" <?= ((int) ($worker_form['worker']['active'] ?? 1) === 0) ? 'selected' : '' ?>>No</option></select></label>

                    <label>Fecha de nacimiento<input type="date" <?= $readonly ?> name="birth_date" value="<?= htmlspecialchars((string) ($worker_form['profile']['birth_date'] ?? '')) ?>"></label>
                    <label>Género<select <?= $disabled ?> name="gender"><option value="">Sin definir</option><option value="MASCULINO" <?= (($worker_form['profile']['gender'] ?? '') === 'MASCULINO') ? 'selected' : '' ?>>Masculino</option><option value="FEMENINO" <?= (($worker_form['profile']['gender'] ?? '') === 'FEMENINO') ? 'selected' : '' ?>>Femenino</option><option value="OTRO" <?= (($worker_form['profile']['gender'] ?? '') === 'OTRO') ? 'selected' : '' ?>>Otro</option></select></label>
                    <label>Estado civil<select <?= $disabled ?> name="marital_status"><option value="">Sin definir</option><option value="SOLTERO" <?= (($worker_form['profile']['marital_status'] ?? '') === 'SOLTERO') ? 'selected' : '' ?>>Soltero</option><option value="CASADO" <?= (($worker_form['profile']['marital_status'] ?? '') === 'CASADO') ? 'selected' : '' ?>>Casado</option><option value="DIVORCIADO" <?= (($worker_form['profile']['marital_status'] ?? '') === 'DIVORCIADO') ? 'selected' : '' ?>>Divorciado</option><option value="VIUDO" <?= (($worker_form['profile']['marital_status'] ?? '') === 'VIUDO') ? 'selected' : '' ?>>Viudo</option></select></label>
                    <label>Nacionalidad<input <?= $readonly ?> name="nationality" value="<?= htmlspecialchars((string) ($worker_form['profile']['nationality'] ?? '')) ?>"></label>
                    <label>Dirección<input <?= $readonly ?> name="address" value="<?= htmlspecialchars((string) ($worker_form['profile']['address'] ?? '')) ?>"></label>
                    <label>Comuna<input <?= $readonly ?> name="commune" value="<?= htmlspecialchars((string) ($worker_form['profile']['commune'] ?? '')) ?>"></label>
                    <label>Región<input <?= $readonly ?> name="region" value="<?= htmlspecialchars((string) ($worker_form['profile']['region'] ?? '')) ?>"></label>
                    <label>Correo<input type="email" <?= $readonly ?> name="email" value="<?= htmlspecialchars((string) ($worker_form['profile']['email'] ?? '')) ?>"></label>
                    <label>Teléfono<input <?= $readonly ?> name="phone" value="<?= htmlspecialchars((string) ($worker_form['profile']['phone'] ?? '')) ?>"></label>
                    <label>Contacto de emergencia<input <?= $readonly ?> name="emergency_contact_name" value="<?= htmlspecialchars((string) ($worker_form['profile']['emergency_contact_name'] ?? '')) ?>"></label>
                    <label>Teléfono emergencia<input <?= $readonly ?> name="emergency_contact_phone" value="<?= htmlspecialchars((string) ($worker_form['profile']['emergency_contact_phone'] ?? '')) ?>"></label>

                    <label>Número de empleado<input <?= $readonly ?> name="employee_number" value="<?= htmlspecialchars((string) ($worker_form['profile']['employee_number'] ?? '')) ?>"></label>
                    <label>Departamento<input <?= $readonly ?> name="department" value="<?= htmlspecialchars((string) ($worker_form['profile']['department'] ?? '')) ?>"></label>
                    <label>Cargo<input <?= $readonly ?> name="position" value="<?= htmlspecialchars((string) ($worker_form['profile']['position'] ?? '')) ?>"></label>
                    <label>Fecha de ingreso<input type="date" <?= $readonly ?> name="hire_date" value="<?= htmlspecialchars((string) ($worker_form['profile']['hire_date'] ?? ($worker_form['contract']['start_date'] ?? ''))) ?>"></label>
                    <label>Tipo de contrato<select <?= $disabled ?> name="contract_type"><option value="">Sin definir</option><option value="PERMANENTE" <?= (($worker_form['profile']['contract_type'] ?? ($worker_form['contract']['contract_type'] ?? '')) === 'PERMANENTE') ? 'selected' : '' ?>>Permanente</option><option value="TEMPORAL" <?= (($worker_form['profile']['contract_type'] ?? ($worker_form['contract']['contract_type'] ?? '')) === 'TEMPORAL') ? 'selected' : '' ?>>Temporal</option><option value="CONTRATISTA" <?= (($worker_form['profile']['contract_type'] ?? ($worker_form['contract']['contract_type'] ?? '')) === 'CONTRATISTA') ? 'selected' : '' ?>>Contratista</option></select></label>
                    <label>Horas semanales<input type="number" step="0.25" min="0" <?= $readonly ?> name="weekly_hours" value="<?= htmlspecialchars((string) ($worker_form['contract']['weekly_hours'] ?? '45')) ?>"></label>
                    <label>Salario base<input type="number" step="1" min="0" <?= $readonly ?> name="base_salary" value="<?= htmlspecialchars((string) ((int) ($worker_form['profile']['base_salary'] ?? ($worker_form['worker']['default_rate'] ?? 0)))) ?>"></label>
                    <label>Moneda<select <?= $disabled ?> name="currency"><option value="CLP" <?= (($worker_form['profile']['currency'] ?? '') === 'CLP' || (($worker_form['profile']['currency'] ?? '') === '')) ? 'selected' : '' ?>>CLP</option><option value="UF" <?= (($worker_form['profile']['currency'] ?? '') === 'UF') ? 'selected' : '' ?>>UF</option><option value="USD" <?= (($worker_form['profile']['currency'] ?? '') === 'USD') ? 'selected' : '' ?>>USD</option></select></label>
                    <label>AFP<input <?= $readonly ?> name="afp_name" value="<?= htmlspecialchars((string) ($worker_form['benefits']['afp_name'] ?? '')) ?>"></label>
                    <label>Salud<input <?= $readonly ?> name="health_system" value="<?= htmlspecialchars((string) ($worker_form['benefits']['health_system'] ?? '')) ?>"></label>
                    <label>Plan de salud<input <?= $readonly ?> name="health_plan" value="<?= htmlspecialchars((string) ($worker_form['benefits']['health_plan'] ?? '')) ?>"></label>
                    <label>Banco<input <?= $readonly ?> name="bank_name" value="<?= htmlspecialchars((string) ($worker_form['bank']['bank_name'] ?? '')) ?>"></label>
                    <label>Tipo de cuenta<select <?= $disabled ?> name="account_type"><option value="">Sin definir</option><option value="CORRIENTE" <?= (($worker_form['bank']['account_type'] ?? '') === 'CORRIENTE') ? 'selected' : '' ?>>Cuenta corriente</option><option value="AHORRO" <?= (($worker_form['bank']['account_type'] ?? '') === 'AHORRO') ? 'selected' : '' ?>>Cuenta ahorro</option></select></label>
                    <label>Número de cuenta<input <?= $readonly ?> name="account_number" value="<?= htmlspecialchars((string) ($worker_form['bank']['account_number'] ?? '')) ?>"></label>
                    <label>Notas<input <?= $readonly ?> name="notes" value="<?= htmlspecialchars((string) ($worker_form['profile']['notes'] ?? '')) ?>"></label>

                    <?php if (!$isReadOnly): ?>
                        <button class="primary-button" type="submit"><?= !empty($worker_form['worker']['id'] ?? null) ? 'Guardar cambios' : 'Crear trabajador' ?></button>
                    <?php endif; ?>
                </form>
            </section>
        </section>
    </main>
</body>

</html>
