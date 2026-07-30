INSERT IGNORE INTO permissions (code, name, module) VALUES
('warehouse.view', 'Ver bodegas', 'warehouse'),
('warehouse.create', 'Crear bodegas y ubicaciones', 'warehouse'),
('warehouse.update', 'Actualizar bodegas y ubicaciones', 'warehouse'),
('lot.create', 'Crear lotes', 'inventory'),
('transfer.create', 'Crear transferencias', 'inventory'),
('transfer.approve', 'Aprobar transferencias', 'inventory');

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r CROSS JOIN permissions p
WHERE r.is_system = 1
  AND p.code IN ('warehouse.view', 'warehouse.create', 'warehouse.update', 'lot.create', 'transfer.create', 'transfer.approve');
