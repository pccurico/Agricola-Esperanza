# Catálogo maestro de entidades del ERP Agrícola

## Estado del catálogo

Todas las entidades listadas están definidas arquitectónicamente. Ninguna se considera módulo COMPLETADO hasta satisfacer el checklist global de migración, tabla, índice, claves foráneas, seeds, entidad, modelo, repositorio, servicio, controlador, middleware, ruta, vista, componentes, formularios, validaciones, permisos, roles, auditoría, logs, configuración y documentación.

## Convenciones

- Toda entidad operativa incluye `company_id` cuando pertenece a una empresa.
- Toda entidad creada por un usuario incluye `created_by` cuando corresponde.
- Toda operación importante debe crear una entrada en `audit_logs`.
- Los reportes consumen repositorios o Services, nunca tablas directamente desde las vistas.
- Las exportaciones y la API reutilizan los mismos Services de consulta.

| Entidad | Propósito y módulo | Relaciones y dependencias | Consumidores, reportes y dashboard | Permisos, auditoría, exportación y API | Reglas principales |
|---|---|---|---|---|---|
| `companies` | Identidad de la empresa y raíz multiempresa. Configuración. | `roles`, `users`, `farms`, `seasons`, todos los módulos con `company_id`. | Todos los módulos, encabezado, dashboard y documentos. | `setup.manage`; cambios auditados; exportación de datos maestros; API de empresa. | Una empresa activa por instalación; datos aislados por `company_id`. |
| `company_settings` | Parámetros configurables por empresa. Configuración. | Pertenece a `companies`. | Dashboard, reportes, formatos, notificaciones. | `setup.manage`; cambios auditados; no se exportan secretos. | Clave única por empresa; valores validados por tipo. |
| `users` | Cuentas de acceso. Seguridad. | `companies`, `roles`, auditoría, tareas, notificaciones. | Menús, permisos, auditoría y reportes de actividad. | `users.view/manage`; login, logout y cambios auditados; exportación restringida. | Correo único por empresa; contraseña con hash; usuario inactivo no ingresa. |
| `roles` | Agrupación de permisos. Seguridad. | `companies`, `permissions`, `users`. | Control de rutas y menús. | `roles.manage`; cambios auditados; no se exportan hashes. | Rol de sistema protegido; permisos asignados mediante tabla intermedia. |
| `permissions` | Acciones autorizables. Seguridad. | `role_permissions`. | Middleware y navegación. | `roles.manage`; cambios auditados; exportable para administración. | Código único; cada módulo debe usar sus propios permisos. |
| `audit_logs` | Trazabilidad de operaciones. Auditoría. | `companies`, `users`; referencia polimórfica a entidades. | Pantalla de actividad, auditorías, reportes de seguridad. | `reports.view`; creación automática; exportación CSV; API restringida. | No se elimina físicamente; registra acción, usuario, IP y detalle. |
| `system_logs` | Errores y eventos técnicos. Logs. | Empresa opcional, usuario opcional. | Diagnóstico y mantenimiento. | `maintenance.view`; acceso restringido; no exposición pública. | No guardar contraseñas ni tokens. |
| `farms` | Unidades productivas. Maestros. | `companies`, `blocks`, `seasons`, `machinery`, `warehouses`. | Costos, producción, mano de obra, inventario y dashboard. | `masters.view/create/update`; auditoría; exportación XLSX/CSV; API. | Código único por empresa; superficie no negativa. |
| `blocks` | Cuarteles productivos. Maestros. | `farms`, `species`, `companies`. | Costos por hectárea, producción, labores y dashboard. | `masters.view/create/update`; auditoría; exportación y API. | Código único por fundo; superficie compatible con fundo. |
| `species` | Especies y variedades productivas. Maestros. | `companies`, `blocks`, producción. | Producción, dashboard y reportes comparativos. | `masters.view/create/update`; auditoría; exportación y API. | Catálogo sin duplicar especie/variedad por empresa. |
| `seasons` | Períodos agrícolas. Maestros. | `companies`; costos, producción, mano de obra, compras, presupuestos. | Todos los comparativos y filtros del dashboard. | `masters.view/create/update`; auditoría; exportación y API. | Fechas coherentes y temporadas no duplicadas. |
| `cost_centers` | Clasificación financiera. Administración. | `companies`, gastos, presupuestos. | Costos por categoría y presupuesto versus real. | `costs.view/create/update`; auditoría; exportación y API. | Código único por empresa; categorías administrables desde catálogo. |
| `suppliers` | Empresas abastecedoras. Compras. | `companies`, órdenes, documentos. | Compras, reportes de abastecimiento y dashboard. | `procurement.view/create/update/delete`; auditoría; CSV/XLSX; API. | RUT único cuando existe; proveedor inactivo no recibe órdenes nuevas. |
| `clients` | Clientes y destinos comerciales. Ventas/producción. | `companies`, documentos, entregas futuras. | Ingresos, producción comercial y reportes. | `clients.view/create/update`; auditoría; exportación y API. | RUT único; cliente inactivo no recibe documentos nuevos. |
| `purchase_orders` | Órdenes formales de compra. Compras. | `suppliers`, `seasons`, `farms`, usuarios, líneas. | Compras, recepción, inventario, costos y dashboard. | `procurement.view/create/update`; auditoría de estados; exportación PDF/XLSX; API. | Número único; transición de estados controlada. |
| `purchase_order_items` | Detalle de artículos solicitados. Compras. | `purchase_orders`, `inventory_items`. | Recepción e inventario. | Hereda permisos de compras; auditoría con orden; exportación. | Cantidad positiva; recibido no supera solicitado. |
| `documents` | Facturas, guías, contratos y documentos tributarios. Documentos. | Empresa, proveedor, cliente, adjuntos. | Compras, costos, reportes y auditoría. | `documents.view/create`; auditoría; PDF/CSV; API restringida. | Tipo y estado desde catálogos; número y fecha validados. |
| `attachments` | Archivos asociados a entidades. Adjuntos. | `documents`, usuario, entidad polimórfica. | Documentos, compras, costos, maquinaria y auditoría. | `attachments.view/create/delete`; descarga auditada; no exposición directa. | MIME, tamaño, extensión y ruta segura validados. |
| `inventory_items` | Catálogo de artículos. Inventario. | Empresa, movimientos, lotes, órdenes. | Bodega, compras, costos y dashboard de stock. | `inventory.view/create/update`; auditoría; exportación y API. | SKU único; unidad y categoría desde catálogo. |
| `warehouses` | Bodegas físicas. Bodega. | Empresa, fundo, ubicaciones, transferencias. | Stock por bodega y reportes. | `warehouse.view/create/update`; auditoría; exportación y API. | Código único; bodega inactiva no recibe movimientos. |
| `warehouse_locations` | Ubicaciones internas. Bodega. | `warehouses`, empresa. | Inventario físico y picking. | `warehouse.view/create/update`; auditoría; exportación. | Código único dentro de bodega. |
| `inventory_lots` | Lotes y vencimientos. Inventario. | `inventory_items`, empresa, movimientos. | Stock crítico, vencimientos y reportes. | `inventory.view/create/update`; auditoría; CSV/XLSX; API. | Lote único por artículo; cantidad no negativa. |
| `inventory_movements` | Entradas, salidas y ajustes. Inventario. | Artículo, temporada, cuartel, usuario. | Stock, costos, dashboard y reportes. | `inventory.view/create/update`; auditoría; exportación; API. | Cantidad positiva; salida no puede superar stock salvo ajuste autorizado. |
| `inventory_transfers` | Transferencias entre bodegas. Inventario. | Artículo, bodegas, usuarios. | Stock por ubicación y auditoría. | `transfer.view/create/approve`; auditoría de estado; exportación; API. | Bodegas distintas; recepción confirma la transferencia. |
| `internal_requests` | Solicitudes internas de materiales. Bodega. | Usuario, fundo, artículos futuros. | Compras, inventario, tareas y notificaciones. | `requests.view/create/approve`; auditoría; exportación. | Flujo solicitado/aprobado/atendido/rechazado. |
| `workers` | Personas que ejecutan labores. Mano de obra. | Empresa, labores, cuadrillas. | Costos, mano de obra, dashboard y reportes. | `labor.view/create/update`; auditoría; exportación. | Tipo de trabajador y tarifas desde catálogo. |
| `contractors` | Contratistas externos. Mano de obra. | Empresa, labores, documentos. | Costos y compras de servicios. | `contractors.view/create/update`; auditoría; exportación/API. | RUT único; contrato y estado vigentes. |
| `crews` | Cuadrillas de trabajo. Mano de obra. | Empresa, trabajadores, supervisor. | Planificación, tareas, labores y calendario. | `crews.view/create/update`; auditoría; exportación. | Trabajador no debe duplicarse en la misma cuadrilla activa. |
| `crew_workers` | Relación cuadrilla/trabajador. Mano de obra. | `crews`, `workers`. | Labores y planificación. | Hereda permisos de cuadrillas; auditoría. | Relación única. |
| `labor_entries` | Jornales y labores ejecutadas. Mano de obra. | Trabajador, temporada, fundo, cuartel. | Costos, dashboard y reportes. | `labor.view/create/update`; auditoría; exportación/API. | Cantidad y tarifa no negativas; total calculado. |
| `production_entries` | Producción, cosecha y faenas. Producción. | Temporada, fundo, cuartel, especie, usuario. | Costos, inventario, dashboard y reportes. | `production.view/create/update`; auditoría; CSV/XLSX/API. | Cantidad positiva; unidad y calidad desde catálogo. |
| `machinery` | Activos agrícolas. Maquinaria. | Empresa, fundo, mantenciones, combustible. | Costos, dashboard, reportes y calendario. | `machinery.view/create/update/delete`; auditoría; exportación/API. | Código único; estado controla operaciones permitidas. |
| `machinery_maintenance` | Mantenciones preventivas/correctivas. Mantenciones. | Maquinaria, usuario, documentos. | Costos, alertas, dashboard y calendario. | `maintenance.view/create/update/delete`; auditoría; exportación/API. | Próxima fecha no anterior; costo no negativo. |
| `fuel_movements` | Consumo de combustible. Combustible. | Maquinaria, fundo, usuario. | Costos por hora, dashboard y reportes. | `fuel.view/create/update/delete`; auditoría; exportación/API. | Litros positivos; equipo activo. |
| `expense_entries` | Costos y gastos registrados. Finanzas. | Empresa, temporada, fundo, cuartel, centro, usuario. | Presupuestos, dashboard, reportes y exportaciones. | `costs.view/create/update/delete`; auditoría; CSV/XLSX/API. | Estado controlado; monto positivo; anulación trazable. |
| `budgets` | Presupuestos por período y centro. Finanzas. | Temporada, centro, empresa, usuario. | Costos, dashboard y reportes de desviación. | `budgets.view/create/update/delete/approve`; auditoría; XLSX/PDF/API. | Período válido; monto positivo; aprobación separada. |
| `company_settings` | Parámetros por empresa. Configuración. | Empresa. | Todos los módulos y formatos. | `setup.manage`; auditoría; no exportar secretos. | Clave única y tipo validado. |
| `notifications` | Alertas internas. Notificaciones. | Usuario y empresa. | Dashboard, tareas, stock y mantenciones. | `notifications.view/update`; auditoría de lectura. | Solo destinatario autorizado; fecha de lectura. |
| `calendar_events` | Actividades y fechas agrícolas. Calendario. | Empresa, usuario, fundo. | Producción, mantenciones, tareas y dashboard. | `calendar.view/create/update/delete`; auditoría; exportación ICS/CSV/API. | Fecha final posterior al inicio. |
| `tasks` | Tareas asignables. Gestión. | Empresa, creador, usuario asignado. | Calendario, notificaciones y dashboard. | `tasks.view/create/update/delete`; auditoría; exportación/API. | Estados y prioridades desde catálogos. |
| `api_tokens` | Credenciales para API futura. Integración. | Empresa, usuario. | API y auditoría. | `api_tokens.manage`; nunca exportar token plano. | Solo hash; revocación y expiración. |
| `backup_records` | Historial de respaldos. Mantenimiento. | Empresa, usuario. | Diagnóstico y administración. | `backup.create/view`; auditoría; no exposición pública. | Checksum, estado y ruta segura. |
| `restore_records` | Historial de restauraciones. Mantenimiento. | Backup, empresa, usuario. | Diagnóstico y auditoría. | `restore.create/view`; auditoría crítica. | Requiere backup válido y registro del resultado. |
| `system_catalogs` | Definición de catálogos parametrizables. Configuración. | Catálogo de valores. | Formularios, validaciones, reportes y API. | `setup.manage`; cambios auditados; exportación administrativa. | Código único; define alcance del catálogo. |
| `system_catalog_values` | Valores administrables de estados, tipos, unidades y categorías. Configuración. | `system_catalogs`, empresa opcional. | Todos los módulos consumidores de catálogos. | `setup.manage`; alta y baja lógica auditadas; API restringida. | No se elimina físicamente; código único por catálogo y empresa. |

## Catálogos que deben migrar desde listas fijas

- Estados generales.
- Tipos de documentos.
- Categorías de artículos.
- Unidades de medida.
- Monedas.
- Bancos.
- Formas de pago.
- Tipos de maquinaria.
- Tipos de combustible.
- Tipos de labores.
- Tipos de cultivo.
- Calidades de producción.
- Motivos de anulación.
- Prioridades.
- Estados de órdenes.
- Estados de mantenciones.
- Regiones y comunas de Chile.

## Dependencias globales

```text
Empresa
├── Usuarios / Roles / Permisos
├── Fundos
│   └── Cuarteles
│       └── Producción / Costos / Mano de obra
├── Temporadas
├── Centros de costo
├── Proveedores
│   └── Compras
│       └── Recepciones / Inventario / Costos
├── Bodegas
│   └── Ubicaciones / Lotes / Movimientos
├── Maquinaria
│   └── Mantenciones / Combustible / Costos
└── Dashboard / Reportes / Exportaciones / Auditoría
```

## Reglas arquitectónicas sincronizadas

- Este catálogo es obligatorio antes de implementar o modificar cualquier módulo funcional.
- Ninguna entidad se considera aislada: debe declarar origen, consumidores, efectos, permisos, auditoría, dashboard, reportes, exportaciones y API.
- Las operaciones que produzcan efectos cruzados deben ejecutarse mediante Services transaccionales y eventos de dominio o mecanismos equivalentes.
- Las entidades críticas deben conservar historial, bajas lógicas o anulaciones; no se elimina físicamente información necesaria para la trazabilidad.
- Los comportamientos configurables deben utilizar `company_settings` o catálogos parametrizables, nunca listas fijas en PHP.
- Las relaciones de documentos deben permitir seguir el origen y los efectos de una operación completa.
- Las nuevas entidades deben ser extensibles para integraciones futuras sin acoplar el núcleo a un proveedor externo.

## Regla de implementación

Antes de trabajar sobre una entidad se debe revisar esta ficha, actualizar sus dependencias y comprobar el impacto en `schema.sql`, migraciones, seeds, permisos, Services, repositorios, controladores, vistas, dashboard, reportes, exportaciones, API, auditoría y documentación. Después se debe realizar una auditoría estática global y registrar cualquier validación runtime pendiente.
