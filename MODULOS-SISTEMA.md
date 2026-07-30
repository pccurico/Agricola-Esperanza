# Módulos del sistema

## Criterio de visibilidad

Los módulos aparecen en el sidebar según el orden definido en la navegación y solo se muestran cuando el rol del usuario tiene el permiso indicado.

| Orden | Grupo | Módulo | Ruta | Permiso de visualización | Visibilidad |
|---:|---|---|---|---|---|
| 1 | Inicio | Resumen ejecutivo | `/` | `dashboard.view` | Según acceso al dashboard |
| 2 | Operación | Administración | `?module=masters` | `masters.view` | Según permiso |
| 3 | Operación | Compras | `?module=procurement` | `procurement.view` | Según permiso |
| 4 | Operación | Recepciones | `?module=receptions` | `procurement.receive` | Según permiso |
| 5 | Operación | Producción | `?module=production` | `production.view` | Según permiso |
| 6 | Operación | Mano de obra | `?module=labor` | `labor.view` | Según permiso |
| 7 | Operación | Costos | `?module=costs` | `costs.view` | Según permiso |
| 8 | Operación | Presupuestos | `?module=budgets` | `budgets.view` | Según permiso |
| 9 | Operación | Inventario | `?module=inventory` | `inventory.view` | Según permiso |
| 10 | Operación | Bodegas y lotes | `?module=warehouses` | `warehouse.view` | Según permiso |
| 11 | Operación | Solicitudes internas | `?module=requests` | `requests.view` | Según permiso |
| 12 | Operación | Notificaciones | `?module=notifications` | `notifications.view` | Según permiso |
| 13 | Operación | Tareas y calendario | `?module=planning` | `tasks.view` | Según permiso |
| 14 | Operación | Documentos | `?module=documents` | `documents.view` | Según permiso |
| 15 | Operación | API e integraciones | `?module=api` | `api_tokens.manage` | Según permiso |
| 16 | Operación | Maquinaria | `?module=machinery` | `machinery.view` | Según permiso |
| 17 | Gestión | Informes | `?module=reports` | `reports.view` | Según permiso |
| 18 | Gestión | Usuarios y roles | `?module=users` | `users.view` | Según permiso |
| 19 | Gestión | Configuración | `?module=settings` | `setup.manage` | Según permiso |
| 20 | Gestión | Catálogos | `?module=catalogs` | `setup.manage` | Según permiso |
| 21 | Gestión | Mi perfil | `?module=profile` | `dashboard.view` | Según permiso |
| 22 | Gestión | Actividad | `?module=audit` | `reports.view` | Según permiso |
| 23 | Herramientas | Demo Data Manager | `?module=demo` | `demo.manage` | Según permiso |

## Módulos de operación

| Orden | Módulo | Descripción | Acciones principales |
|---:|---|---|---|
| 1 | Administración | Gestiona los maestros base de la operación agrícola. | Crear, editar y consultar datos maestros. |
| 2 | Compras | Administra el ciclo de adquisición de productos, insumos y servicios. | Registrar órdenes de compra, consultar proveedores y dar seguimiento a compras. |
| 3 | Recepciones | Registra la llegada y recepción de bienes asociados a órdenes de compra. | Recibir pedidos, validar cantidades y actualizar existencias. |
| 4 | Producción | Controla la planificación y los registros productivos de la agrícola. | Gestionar temporadas, cultivos, bloques y actividades de producción. |
| 5 | Mano de obra | Administra trabajadores y registros relacionados con labores agrícolas. | Gestionar trabajadores, labores y costos de mano de obra. |
| 6 | Costos | Registra y consulta los costos asociados a la operación. | Crear, clasificar y analizar costos operacionales. |
| 7 | Presupuestos | Permite planificar y controlar presupuestos por temporada y operación. | Crear presupuestos, asignar montos y revisar ejecución. |
| 8 | Inventario | Controla productos, insumos y movimientos de existencias. | Registrar entradas, salidas, ajustes y consultar saldos. |
| 9 | Bodegas y lotes | Administra bodegas, ubicaciones y lotes de inventario. | Crear bodegas, gestionar lotes y realizar transferencias. |
| 10 | Solicitudes internas | Gestiona requerimientos internos de materiales y recursos. | Crear, aprobar, rechazar y atender solicitudes. |
| 11 | Notificaciones | Centraliza avisos y pendientes generados por el sistema. | Consultar notificaciones y marcar estados de atención. |
| 12 | Tareas y calendario | Organiza tareas operativas y eventos del calendario. | Crear tareas, actualizar estados y programar eventos. |
| 13 | Documentos | Centraliza documentos vinculados a la operación. | Cargar, consultar y descargar archivos autorizados. |
| 14 | API e integraciones | Administra credenciales para conectar servicios externos. | Crear, revisar y revocar tokens de API. |
| 15 | Maquinaria | Controla maquinaria, equipos, combustible y mantenimiento. | Registrar equipos, consumos y actividades de mantenimiento. |

## Módulos de gestión

| Orden | Módulo | Descripción | Acciones principales |
|---:|---|---|---|
| 1 | Informes | Presenta información consolidada para el análisis y control de gestión. | Consultar indicadores, filtrar información y exportar reportes autorizados. |
| 2 | Usuarios y roles | Administra usuarios, perfiles y permisos de acceso. | Crear usuarios, asignar roles, activar o desactivar accesos. |
| 3 | Configuración | Mantiene la identidad y los datos generales de la empresa. | Actualizar razón social, nombre visible, logo, datos de contacto y ubicación. |
| 4 | Catálogos | Administra listas y valores reutilizados por los módulos. | Crear, editar, activar y desactivar valores de catálogo. |
| 5 | Mi perfil | Permite al usuario consultar y actualizar sus propios datos. | Editar información personal, teléfono y contraseña. |
| 6 | Actividad | Registra acciones relevantes realizadas dentro del sistema. | Consultar auditoría, usuario, fecha y operación ejecutada. |

## Herramientas

| Orden | Módulo | Descripción | Acciones principales |
|---:|---|---|---|
| 1 | Demo Data Manager | Administra la información de demostración utilizada para recorrer el sistema. | Instalar y eliminar datos de ejemplo. |

## Reglas de visibilidad y permisos

| Regla | Descripción |
|---|---|
| Menú por rol | Cada módulo se muestra solo si el rol tiene su permiso de visualización. |
| Acciones de escritura | Crear, editar, aprobar, recibir o eliminar información requiere permisos adicionales según el módulo. |
| Exportación de informes | La exportación de reportes requiere el permiso `reports.export`, además de `reports.view`. |
| Configuración y catálogos | Ambos módulos utilizan el permiso `setup.manage`. |
| Acciones administrativas | La administración de usuarios utiliza permisos específicos de gestión de usuarios. |
| Herramientas de demostración | Demo Data Manager requiere `demo.manage`. |
