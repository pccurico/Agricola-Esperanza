ALTER TABLE inventory_movements ADD COLUMN cost_center_id BIGINT UNSIGNED NULL AFTER season_id;
ALTER TABLE inventory_movements ADD COLUMN farm_id BIGINT UNSIGNED NULL AFTER cost_center_id;
ALTER TABLE inventory_movements ADD CONSTRAINT fk_inventory_movements_center FOREIGN KEY (cost_center_id) REFERENCES cost_centers(id) ON DELETE SET NULL;
ALTER TABLE inventory_movements ADD CONSTRAINT fk_inventory_movements_farm FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE SET NULL;
ALTER TABLE inventory_movements ADD KEY idx_inventory_movements_assignment (company_id, season_id, cost_center_id, farm_id, block_id);
