INSERT IGNORE INTO permissions (code, name, module) VALUES
('documents.view', 'Ver documentos', 'documents'),
('documents.create', 'Crear documentos', 'documents'),
('attachments.create', 'Adjuntar archivos', 'documents');

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r CROSS JOIN permissions p
WHERE r.is_system = 1 AND p.code IN ('documents.view', 'documents.create', 'attachments.create');
