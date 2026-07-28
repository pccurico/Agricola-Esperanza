INSERT IGNORE INTO permissions (code, name, module) VALUES
('requests.view', 'Ver solicitudes internas', 'requests'),
('requests.create', 'Crear solicitudes internas', 'requests'),
('requests.approve', 'Aprobar solicitudes internas', 'requests'),
('requests.fulfill', 'Atender solicitudes internas', 'requests');

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r CROSS JOIN permissions p
WHERE r.is_system = 1
  AND p.code IN ('requests.view', 'requests.create', 'requests.approve', 'requests.fulfill');
