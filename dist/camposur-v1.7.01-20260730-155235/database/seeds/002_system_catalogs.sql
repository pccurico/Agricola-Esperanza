INSERT IGNORE INTO system_catalogs (code, name, scope) VALUES
('COST_CENTER_CATEGORY', 'Categorías de centros de costo', 'COMPANY'),
('DOCUMENT_TYPE', 'Tipos de documento', 'COMPANY'),
('MEASUREMENT_UNIT', 'Unidades de medida', 'SYSTEM'),
('CURRENCY', 'Monedas', 'SYSTEM'),
('PAYMENT_METHOD', 'Formas de pago', 'COMPANY'),
('MACHINERY_TYPE', 'Tipos de maquinaria', 'COMPANY'),
('FUEL_TYPE', 'Tipos de combustible', 'COMPANY'),
('LABOR_TYPE', 'Tipos de labor', 'COMPANY'),
('PRODUCTION_QUALITY', 'Calidades de producción', 'COMPANY'),
('CANCELLATION_REASON', 'Motivos de anulación', 'COMPANY'),
('TASK_PRIORITY', 'Prioridades de tareas', 'SYSTEM'),
('RECORD_STATUS', 'Estados generales', 'SYSTEM'),
('COST_CATEGORY', 'Categorías de costos', 'COMPANY'),
('INVENTORY_CATEGORY', 'Categorías de inventario', 'COMPANY'),
('INVENTORY_MOVEMENT_TYPE', 'Tipos de movimiento de inventario', 'SYSTEM'),
('WORKER_TYPE', 'Tipos de trabajador', 'SYSTEM'),
('MAINTENANCE_TYPE', 'Tipos de mantención', 'SYSTEM'),
('PURCHASE_ORDER_STATUS', 'Estados de órdenes de compra', 'SYSTEM'),
('REQUEST_STATUS', 'Estados de solicitudes internas', 'SYSTEM'),
('DOCUMENT_STATUS', 'Estados de documentos', 'SYSTEM'),
('MACHINERY_STATUS', 'Estados de maquinaria', 'SYSTEM'),
('BACKUP_STATUS', 'Estados de respaldos', 'SYSTEM');

INSERT INTO system_catalog_values (catalog_id, company_id, code, label, sort_order)
SELECT catalogs.id, NULL, 'CLP', 'Peso chileno', 10
FROM system_catalogs catalogs
WHERE catalogs.code = 'CURRENCY'
  AND NOT EXISTS (
      SELECT 1 FROM system_catalog_values existing
      WHERE existing.catalog_id = catalogs.id AND existing.company_id IS NULL AND existing.code = 'CLP'
  );

INSERT INTO system_catalog_values (catalog_id, company_id, code, label, sort_order)
SELECT catalogs.id, NULL, values_table.code, values_table.label, values_table.sort_order
FROM system_catalogs catalogs
JOIN (
    SELECT 'MEASUREMENT_UNIT' AS catalog_code, 'UN' AS code, 'Unidad' AS label, 10 AS sort_order
    UNION ALL SELECT 'MEASUREMENT_UNIT', 'KG', 'Kilogramo', 20
    UNION ALL SELECT 'MEASUREMENT_UNIT', 'L', 'Litro', 30
    UNION ALL SELECT 'TASK_PRIORITY', 'LOW', 'Baja', 10
    UNION ALL SELECT 'TASK_PRIORITY', 'NORMAL', 'Normal', 20
    UNION ALL SELECT 'TASK_PRIORITY', 'HIGH', 'Alta', 30
    UNION ALL SELECT 'TASK_PRIORITY', 'URGENT', 'Urgente', 40
) values_table ON values_table.catalog_code = catalogs.code
WHERE catalogs.scope = 'SYSTEM'
  AND NOT EXISTS (
      SELECT 1 FROM system_catalog_values existing
      WHERE existing.catalog_id = catalogs.id AND existing.company_id IS NULL AND existing.code = values_table.code
  );
