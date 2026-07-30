CREATE TABLE IF NOT EXISTS purchase_receptions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    purchase_order_id BIGINT UNSIGNED NOT NULL,
    document_id BIGINT UNSIGNED NULL,
    received_on DATE NOT NULL,
    status VARCHAR(40) NOT NULL DEFAULT 'POSTED',
    notes VARCHAR(255) NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_receptions_company FOREIGN KEY (company_id) REFERENCES companies(id),
    CONSTRAINT fk_receptions_order FOREIGN KEY (purchase_order_id) REFERENCES purchase_orders(id),
    CONSTRAINT fk_receptions_document FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE SET NULL,
    CONSTRAINT fk_receptions_user FOREIGN KEY (created_by) REFERENCES users(id),
    KEY idx_receptions_company_date (company_id, received_on, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS purchase_reception_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reception_id BIGINT UNSIGNED NOT NULL,
    purchase_order_item_id BIGINT UNSIGNED NOT NULL,
    item_id BIGINT UNSIGNED NULL,
    quantity DECIMAL(15,3) NOT NULL,
    unit_cost DECIMAL(15,2) NOT NULL DEFAULT 0,
    CONSTRAINT fk_reception_items_reception FOREIGN KEY (reception_id) REFERENCES purchase_receptions(id) ON DELETE CASCADE,
    CONSTRAINT fk_reception_items_order_item FOREIGN KEY (purchase_order_item_id) REFERENCES purchase_order_items(id),
    CONSTRAINT fk_reception_items_inventory FOREIGN KEY (item_id) REFERENCES inventory_items(id) ON DELETE SET NULL,
    KEY idx_reception_items_order (purchase_order_item_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
