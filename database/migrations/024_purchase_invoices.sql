CREATE TABLE IF NOT EXISTS purchase_invoices (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    supplier_id BIGINT UNSIGNED NOT NULL,
    purchase_order_id BIGINT UNSIGNED NULL,
    purchase_reception_id BIGINT UNSIGNED NULL,
    document_id BIGINT UNSIGNED NULL,
    invoice_number VARCHAR(100) NOT NULL,
    issue_date DATE NOT NULL,
    due_date DATE NULL,
    currency CHAR(3) NOT NULL DEFAULT 'CLP',
    net_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
    tax_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
    total_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
    status VARCHAR(30) NOT NULL DEFAULT 'DRAFT',
    notes VARCHAR(500) NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_purchase_invoices_company FOREIGN KEY (company_id) REFERENCES companies(id),
    CONSTRAINT fk_purchase_invoices_supplier FOREIGN KEY (supplier_id) REFERENCES suppliers(id),
    CONSTRAINT fk_purchase_invoices_order FOREIGN KEY (purchase_order_id) REFERENCES purchase_orders(id) ON DELETE SET NULL,
    CONSTRAINT fk_purchase_invoices_reception FOREIGN KEY (purchase_reception_id) REFERENCES purchase_receptions(id) ON DELETE SET NULL,
    CONSTRAINT fk_purchase_invoices_document FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE SET NULL,
    CONSTRAINT fk_purchase_invoices_user FOREIGN KEY (created_by) REFERENCES users(id),
    UNIQUE KEY uq_purchase_invoices_supplier_number (company_id, supplier_id, invoice_number),
    KEY idx_purchase_invoices_status_date (company_id, status, due_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO permissions (code, name, module) VALUES
('purchase_invoices.view', 'Ver facturas de compra', 'purchase_invoices'),
('purchase_invoices.create', 'Registrar facturas de compra', 'purchase_invoices');

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
INNER JOIN permissions p
WHERE r.is_system = 1
  AND p.code IN ('purchase_invoices.view', 'purchase_invoices.create');
