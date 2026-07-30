ALTER TABLE inventory_movements ADD COLUMN warehouse_id BIGINT UNSIGNED NULL AFTER item_id;
ALTER TABLE inventory_movements ADD CONSTRAINT fk_inventory_movements_warehouse FOREIGN KEY (warehouse_id) REFERENCES warehouses(id) ON DELETE SET NULL;
ALTER TABLE inventory_lots ADD COLUMN warehouse_id BIGINT UNSIGNED NULL AFTER item_id;
ALTER TABLE inventory_lots ADD CONSTRAINT fk_inventory_lots_warehouse FOREIGN KEY (warehouse_id) REFERENCES warehouses(id) ON DELETE SET NULL;
