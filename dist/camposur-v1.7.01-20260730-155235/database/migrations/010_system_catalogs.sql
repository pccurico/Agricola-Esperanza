CREATE TABLE IF NOT EXISTS system_catalogs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(80) NOT NULL,
    name VARCHAR(140) NOT NULL,
    scope ENUM('SYSTEM','COMPANY') NOT NULL DEFAULT 'COMPANY',
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_system_catalogs_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS system_catalog_values (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    catalog_id BIGINT UNSIGNED NOT NULL,
    company_id BIGINT UNSIGNED NULL,
    code VARCHAR(80) NOT NULL,
    label VARCHAR(140) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    active TINYINT(1) NOT NULL DEFAULT 1,
    metadata_json JSON NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_catalog_values_catalog FOREIGN KEY (catalog_id) REFERENCES system_catalogs(id) ON DELETE CASCADE,
    CONSTRAINT fk_catalog_values_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    UNIQUE KEY uq_catalog_values_scope_code (catalog_id, company_id, code),
    KEY idx_catalog_values_lookup (catalog_id, company_id, active, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
