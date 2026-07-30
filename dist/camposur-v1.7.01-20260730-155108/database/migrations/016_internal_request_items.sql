CREATE TABLE IF NOT EXISTS internal_request_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    request_id BIGINT UNSIGNED NOT NULL,
    item_id BIGINT UNSIGNED NOT NULL,
    quantity DECIMAL(15,3) NOT NULL,
    fulfilled_quantity DECIMAL(15,3) NOT NULL DEFAULT 0,
    notes VARCHAR(255) NULL,
    CONSTRAINT fk_request_items_request FOREIGN KEY (request_id) REFERENCES internal_requests(id) ON DELETE CASCADE,
    CONSTRAINT fk_request_items_item FOREIGN KEY (item_id) REFERENCES inventory_items(id),
    UNIQUE KEY uq_request_items_item (request_id, item_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
