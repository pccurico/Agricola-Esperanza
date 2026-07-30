INSERT IGNORE INTO permissions (code, name, module) VALUES ('api_tokens.manage', 'Administrar tokens API', 'api');

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r CROSS JOIN permissions p
WHERE r.is_system = 1 AND p.code = 'api_tokens.manage';
