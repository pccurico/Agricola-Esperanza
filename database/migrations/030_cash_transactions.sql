CREATE TABLE IF NOT EXISTS cash_transactions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    transaction_date DATE NOT NULL,
    transaction_type ENUM('INCOME', 'EXPENSE') NOT NULL,
    category VARCHAR(80) NOT NULL,
    description VARCHAR(255) NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    reference VARCHAR(120) NULL,
    status ENUM('DRAFT', 'POSTED', 'VOID') NOT NULL DEFAULT 'POSTED',
    created_by BIGINT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_cash_transactions_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    CONSTRAINT fk_cash_transactions_user FOREIGN KEY (created_by) REFERENCES users(id),
    KEY idx_cash_transactions_company_date (company_id, transaction_date),
    KEY idx_cash_transactions_company_type (company_id, transaction_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
