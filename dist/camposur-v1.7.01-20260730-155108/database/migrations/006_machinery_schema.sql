CREATE TABLE machinery (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    farm_id BIGINT UNSIGNED NULL,
    code VARCHAR(40) NOT NULL,
    name VARCHAR(160) NOT NULL,
    machinery_type VARCHAR(100) NOT NULL,
    brand VARCHAR(100) NULL,
    model VARCHAR(100) NULL,
    plate VARCHAR(20) NULL,
    meter DECIMAL(12,2) NOT NULL DEFAULT 0,
    status ENUM('ACTIVE','MAINTENANCE','INACTIVE') NOT NULL DEFAULT 'ACTIVE',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_machinery_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    CONSTRAINT fk_machinery_farm FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE SET NULL,
    UNIQUE KEY uq_machinery_company_code (company_id, code),
    KEY idx_machinery_status (company_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE machinery_maintenance (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    machinery_id BIGINT UNSIGNED NOT NULL,
    maintenance_date DATE NOT NULL,
    maintenance_type ENUM('PREVENTIVE','CORRECTIVE') NOT NULL,
    description VARCHAR(255) NOT NULL,
    cost DECIMAL(15,2) NOT NULL DEFAULT 0,
    next_date DATE NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_maintenance_company FOREIGN KEY (company_id) REFERENCES companies(id),
    CONSTRAINT fk_maintenance_machinery FOREIGN KEY (machinery_id) REFERENCES machinery(id),
    CONSTRAINT fk_maintenance_user FOREIGN KEY (created_by) REFERENCES users(id),
    KEY idx_maintenance_date (company_id, maintenance_date, next_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE fuel_movements (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    machinery_id BIGINT UNSIGNED NOT NULL,
    farm_id BIGINT UNSIGNED NULL,
    fuel_date DATE NOT NULL,
    liters DECIMAL(12,3) NOT NULL,
    unit_cost DECIMAL(15,2) NOT NULL,
    meter DECIMAL(12,2) NULL,
    reference VARCHAR(120) NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_fuel_company FOREIGN KEY (company_id) REFERENCES companies(id),
    CONSTRAINT fk_fuel_machinery FOREIGN KEY (machinery_id) REFERENCES machinery(id),
    CONSTRAINT fk_fuel_farm FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE SET NULL,
    CONSTRAINT fk_fuel_user FOREIGN KEY (created_by) REFERENCES users(id),
    KEY idx_fuel_reporting (company_id, machinery_id, fuel_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
