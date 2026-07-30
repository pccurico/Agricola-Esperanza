CREATE TABLE suppliers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    tax_id VARCHAR(20) NULL,
    business_name VARCHAR(180) NOT NULL,
    contact_name VARCHAR(160) NULL,
    email VARCHAR(160) NULL,
    phone VARCHAR(40) NULL,
    address VARCHAR(255) NULL,
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_suppliers_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    UNIQUE KEY uq_suppliers_company_tax_id (company_id, tax_id),
    KEY idx_suppliers_active (company_id, active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE purchase_orders (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    supplier_id BIGINT UNSIGNED NOT NULL,
    season_id BIGINT UNSIGNED NULL,
    farm_id BIGINT UNSIGNED NULL,
    order_number VARCHAR(40) NOT NULL,
    order_date DATE NOT NULL,
    status ENUM('DRAFT','SENT','PARTIAL','RECEIVED','CANCELLED') NOT NULL DEFAULT 'DRAFT',
    notes VARCHAR(255) NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_purchase_orders_company FOREIGN KEY (company_id) REFERENCES companies(id),
    CONSTRAINT fk_purchase_orders_supplier FOREIGN KEY (supplier_id) REFERENCES suppliers(id),
    CONSTRAINT fk_purchase_orders_season FOREIGN KEY (season_id) REFERENCES seasons(id) ON DELETE SET NULL,
    CONSTRAINT fk_purchase_orders_farm FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE SET NULL,
    CONSTRAINT fk_purchase_orders_user FOREIGN KEY (created_by) REFERENCES users(id),
    UNIQUE KEY uq_purchase_orders_company_number (company_id, order_number),
    KEY idx_purchase_orders_status (company_id, status, order_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE purchase_order_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    purchase_order_id BIGINT UNSIGNED NOT NULL,
    item_id BIGINT UNSIGNED NULL,
    description VARCHAR(180) NOT NULL,
    quantity DECIMAL(15,3) NOT NULL,
    unit_price DECIMAL(15,2) NOT NULL,
    received_quantity DECIMAL(15,3) NOT NULL DEFAULT 0,
    CONSTRAINT fk_purchase_items_order FOREIGN KEY (purchase_order_id) REFERENCES purchase_orders(id) ON DELETE CASCADE,
    CONSTRAINT fk_purchase_items_inventory FOREIGN KEY (item_id) REFERENCES inventory_items(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
