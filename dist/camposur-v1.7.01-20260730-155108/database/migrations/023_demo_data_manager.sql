CREATE TABLE IF NOT EXISTS demo_batches (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    installation_id CHAR(32) NOT NULL,
    version VARCHAR(40) NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'INSTALLED',
    installed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    removed_at DATETIME NULL,
    created_by BIGINT UNSIGNED NULL,
    CONSTRAINT fk_demo_batches_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    CONSTRAINT fk_demo_batches_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    KEY idx_demo_batches_company_status (company_id, status, installed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS demo_records (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    batch_id BIGINT UNSIGNED NOT NULL,
    table_name VARCHAR(80) NOT NULL,
    record_id BIGINT UNSIGNED NOT NULL,
    record_key VARCHAR(120) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_demo_records_batch FOREIGN KEY (batch_id) REFERENCES demo_batches(id) ON DELETE CASCADE,
    UNIQUE KEY uq_demo_records_batch_key (batch_id, record_key),
    UNIQUE KEY uq_demo_records_table_id (batch_id, table_name, record_id),
    KEY idx_demo_records_lookup (table_name, record_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO permissions (code, name, module) VALUES ('demo.manage', 'Administrar datos demo', 'demo');
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r CROSS JOIN permissions p
WHERE p.code = 'demo.manage' AND r.is_system = 1;
