CREATE TABLE budgets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    season_id BIGINT UNSIGNED NOT NULL,
    cost_center_id BIGINT UNSIGNED NOT NULL,
    period_start DATE NOT NULL,
    period_end DATE NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    status ENUM('DRAFT','APPROVED','CLOSED') NOT NULL DEFAULT 'DRAFT',
    notes VARCHAR(255) NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_budgets_company FOREIGN KEY (company_id) REFERENCES companies(id),
    CONSTRAINT fk_budgets_season FOREIGN KEY (season_id) REFERENCES seasons(id),
    CONSTRAINT fk_budgets_center FOREIGN KEY (cost_center_id) REFERENCES cost_centers(id),
    CONSTRAINT fk_budgets_user FOREIGN KEY (created_by) REFERENCES users(id),
    UNIQUE KEY uq_budgets_scope (company_id, season_id, cost_center_id, period_start, period_end),
    KEY idx_budgets_status (company_id, status, period_start)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO permissions (code, name, module) VALUES ('budgets.manage', 'Administrar presupuestos', 'budgets');
