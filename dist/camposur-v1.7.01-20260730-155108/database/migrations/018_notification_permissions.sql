INSERT IGNORE INTO permissions (code, name, module) VALUES
('notifications.view', 'Ver notificaciones', 'notifications'),
('notifications.update', 'Actualizar notificaciones', 'notifications');

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r CROSS JOIN permissions p
WHERE r.is_system = 1 AND p.code IN ('notifications.view', 'notifications.update');
