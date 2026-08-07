INSERT IGNORE INTO system_catalogs (code, name, scope)
VALUES ('INVENTORY_SUBCATEGORY', 'Subcategorías de inventario', 'COMPANY');

ALTER TABLE inventory_items
    ADD COLUMN subcategory VARCHAR(80) NULL AFTER category;
