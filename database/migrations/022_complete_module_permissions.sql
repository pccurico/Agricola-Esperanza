INSERT IGNORE INTO permissions (code, name, module) VALUES
('masters.view', 'Ver maestros agrícolas', 'masters'),
('masters.create', 'Crear maestros agrícolas', 'masters'),
('production.view', 'Ver producción', 'production'),
('production.create', 'Registrar producción', 'production'),
('procurement.view', 'Ver compras', 'procurement'),
('procurement.create', 'Crear compras', 'procurement'),
('budgets.view', 'Ver presupuestos', 'budgets'),
('budgets.create', 'Crear presupuestos', 'budgets'),
('machinery.view', 'Ver maquinaria', 'machinery'),
('machinery.create', 'Crear maquinaria', 'machinery'),
('costs.create', 'Registrar costos', 'costs'),
('inventory.create', 'Crear insumos y movimientos', 'inventory'),
('labor.view', 'Ver mano de obra', 'labor'),
('labor.create', 'Registrar mano de obra', 'labor');

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT roles.id, permissions.id
FROM roles
INNER JOIN permissions
WHERE roles.is_system = 1
  AND permissions.code IN (
      'masters.view', 'masters.create',
      'production.view', 'production.create',
      'procurement.view', 'procurement.create',
      'budgets.view', 'budgets.create',
      'machinery.view', 'machinery.create',
      'costs.create', 'inventory.create',
      'labor.view', 'labor.create'
  );
