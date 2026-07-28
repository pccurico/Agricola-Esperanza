CREATE TABLE workers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    full_name VARCHAR(160) NOT NULL,
    tax_id VARCHAR(20) NULL,
    worker_type ENUM('PERMANENTE','TEMPORAL','CONTRATISTA') NOT NULL DEFAULT 'TEMPORAL',
    default_rate DECIMAL(15,2) NOT NULL DEFAULT 0,
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_workers_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    UNIQUE KEY uq_workers_company_tax_id (company_id, tax_id),
    KEY idx_workers_active (company_id, active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE labor_entries (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    worker_id BIGINT UNSIGNED NOT NULL,
    season_id BIGINT UNSIGNED NOT NULL,
    farm_id BIGINT UNSIGNED NULL,
    block_id BIGINT UNSIGNED NULL,
    labor_date DATE NOT NULL,
    labor_type VARCHAR(120) NOT NULL,
    quantity DECIMAL(12,2) NOT NULL,
    unit_rate DECIMAL(15,2) NOT NULL,
    total_amount DECIMAL(15,2) AS (quantity * unit_rate) STORED,
    status ENUM('DRAFT','POSTED','VOID') NOT NULL DEFAULT 'POSTED',
    created_by BIGINT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_labor_company FOREIGN KEY (company_id) REFERENCES companies(id),
    CONSTRAINT fk_labor_worker FOREIGN KEY (worker_id) REFERENCES workers(id),
    CONSTRAINT fk_labor_season FOREIGN KEY (season_id) REFERENCES seasons(id),
    CONSTRAINT fk_labor_farm FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE SET NULL,
    CONSTRAINT fk_labor_block FOREIGN KEY (block_id) REFERENCES blocks(id) ON DELETE SET NULL,
    CONSTRAINT fk_labor_user FOREIGN KEY (created_by) REFERENCES users(id),
    KEY idx_labor_reporting (company_id, season_id, farm_id, block_id, labor_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
