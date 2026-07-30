INSERT IGNORE INTO permissions (code, name, module) VALUES
('tasks.view', 'Ver tareas', 'tasks'),
('tasks.create', 'Crear tareas', 'tasks'),
('tasks.update', 'Actualizar tareas', 'tasks'),
('calendar.view', 'Ver calendario', 'calendar'),
('calendar.create', 'Crear eventos de calendario', 'calendar');

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r CROSS JOIN permissions p
WHERE r.is_system = 1 AND p.code IN ('tasks.view', 'tasks.create', 'tasks.update', 'calendar.view', 'calendar.create');
