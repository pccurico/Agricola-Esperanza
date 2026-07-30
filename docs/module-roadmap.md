# Sistema de Gestión Agrícola PCCURICO — documento maestro de desarrollo

> Estados: 🟢 terminado en esta etapa · 🔴 pendiente de desarrollo o validación
>
> Documento rector: `docs/documento-maestro-desarrollo.md` — **Especificación Maestra de Arquitectura y Desarrollo del ERP Agrícola v1.0**.
>
> El color verde indica únicamente elementos verificados estáticamente; el rojo indica desarrollo o validación pendiente. Ningún módulo puede marcarse completado sin el checklist completo de la especificación maestra.

## 1. Objetivo del sistema

Sistema de Gestión Agrícola PCCURICO será un sistema web para administrar una agrícola chilena que actualmente trabaja con planillas Excel. El sistema permitirá centralizar la operación, controlar costos por temporada, fundo, cuartel y hectárea, registrar producción, administrar bodega y generar informes para la toma de decisiones.

La instalación será configurable: el nombre de la empresa, razón social, RUT, logo, ubicación y usuario administrador se ingresan durante el wizard inicial. El sistema está preparado para PHP 8.2, MySQL 8/MariaDB compatible, WAMP64 y despliegue en cPanel.

## 🟢 2. Arquitectura técnica

- PHP 8.2 puro con arquitectura MVC liviana.
- PDO con consultas preparadas.
- MySQL 8 o MariaDB compatible.
- Apache y `mod_rewrite`.
- `public/` como único punto de entrada web.
- `app/` para controladores, servicios y vistas.
- `database/migrations/` para cambios versionados.
- `database/seeds/` para catálogos y permisos iniciales.
- `storage/uploads/` para logos y archivos.
- Sesiones protegidas, CSRF, permisos por rol y auditoría.

## 3. Estado general

### 🟢 Implementado

- Estructura PHP base y configuración para WAMP64/cPanel.
- Wizard inicial.
- Instalación de empresa, fundo, temporada, centros de costo y administrador.
- Login, logout y sesiones.
- Usuarios, roles y permisos.
- Maestros agrícolas.
- Costos, inversiones y servicios/gastos.
- Bodega e inventario.
- Mano de obra.
- Producción agrícola.
- Informes y exportación CSV.
- Dashboard con indicadores reales.
- Configuración empresarial y logo.
- Auditoría de operaciones.
- Migraciones versionadas y actualizador automático.
- CSRF, cabeceras HTTP y cookies de sesión endurecidas.

### 🔴 Pendiente de cierre

- Pruebas funcionales completas en WAMP64.
- Pruebas de instalación limpia y actualización.
- Validación de permisos con usuarios no administradores.
- Validación de cálculos contra las planillas Excel originales.
- Exportaciones adicionales a XLSX/PDF.
- Pruebas de carga y revisión de índices.
- Revisión final de seguridad y despliegue en cPanel.

---

# 4. Módulos del sistema

## 🟢 Módulo 01 — Wizard de instalación

### Objetivo

Permitir que una instalación nueva configure la identidad y la estructura mínima de una agrícola sin asumir un nombre fijo.

### Funcionalidades

- Razón social.
- Nombre comercial.
- RUT.
- Logo JPG, PNG o WEBP.
- Correo y teléfono.
- Dirección, comuna y región.
- Primer fundo.
- Código y ubicación del fundo.
- Superficie inicial.
- Primera temporada.
- Fechas de inicio y término.
- Usuario administrador.
- Contraseña segura.
- Generación automática de `config/config.php`.
- Creación de centros de costo iniciales.
- Ejecución de migraciones y permisos.

### Tablas

- `companies`
- `farms`
- `seasons`
- `cost_centers`
- `users`
- `roles`
- `permissions`

### Validaciones pendientes

- Comprobar instalación desde base vacía.
- Comprobar recuperación ante error de permisos de escritura.
- Comprobar logo inválido, demasiado grande o corrupto.

---

## 🟢 Módulo 02 — Autenticación y sesiones

### Funcionalidades

- Inicio de sesión por correo y contraseña.
- Cierre de sesión.
- Regeneración de sesión después del login.
- Cookies `HttpOnly` y `SameSite`.
- Cookie segura en producción.
- Mensajes de credenciales inválidas.
- Actualización de último acceso.
- Protección de rutas privadas.
- Protección CSRF en formularios.
- Cabeceras HTTP de seguridad.

### 🔴 Pendiente

- Recuperación de contraseña.
- Cambio de contraseña desde el perfil.
- Bloqueo temporal por intentos fallidos.
- Registro de inicios de sesión exitosos y fallidos.
- Expiración configurable de sesión.

---

## 🟢 Módulo 03 — Usuarios, roles y permisos

### Funcionalidades

- Listado de usuarios.
- Creación de usuarios.
- Activación e inactivación.
- Asignación de roles.
- Creación de roles.
- Asignación de permisos.
- Control de acceso por módulo.
- Aislamiento por empresa.
- Auditoría de creación de usuarios y roles.

### Permisos actuales

- `dashboard.view`
- `setup.manage`
- `users.view`
- `users.manage`
- `roles.manage`
- `masters.manage`
- `costs.view`
- `costs.manage`
- `labor.manage`
- `inventory.view`
- `inventory.manage`
- `reports.view`
- `reports.export`

### 🔴 Pendiente

- Edición de usuarios.
- Desactivación de usuarios.
- Edición y eliminación controlada de roles.
- Permisos específicos de lectura, creación, edición, anulación y exportación.
- Pantalla de perfil del usuario.

---

## 🟢 Módulo 04 — Maestros agrícolas

### Funcionalidades

- Fundos.
- Código y ubicación.
- Hectáreas.
- Especies.
- Variedades.
- Temporadas.
- Cuarteles.
- Año de plantación.
- Asignación de especie al cuartel.
- Centros de costo.
- Categorías de costos.
- Validación de registros pertenecientes a la empresa.

### Tablas

- `farms`
- `species`
- `seasons`
- `blocks`
- `cost_centers`

### 🔴 Pendiente

- Edición de maestros.
- Inactivación sin borrado físico.
- Historial de cambios.
- Importación inicial desde Excel.
- Validación de hectáreas no duplicadas o inconsistentes.
- Relación entre cuartel, especie y variedad por temporada.

---

## 🟢 Módulo 05 — Administración y centros de costo

### Objetivo

Clasificar los movimientos financieros según las categorías que aparecen en las planillas actuales.

### Categorías

- Administración.
- Mano de obra.
- Inversiones.
- Servicios y gastos.
- Bodega.

### Funcionalidades

- Crear centros de costo.
- Asociar costos a temporada, fundo y cuartel.
- Consultar costos por clasificación.
- Preparar la información para informes y dashboard.

### 🔴 Pendiente

- Presupuesto por centro de costo.
- Comparación presupuesto versus real.
- Alertas de sobregasto.
- Distribución automática de costos indirectos.

---

## 🟢 Módulo 06 — Mano de obra

### Funcionalidades

- Trabajadores permanentes.
- Trabajadores temporales.
- Contratistas.
- RUT.
- Tarifa base.
- Registro de labores.
- Fecha de labor.
- Tipo de labor: poda, raleo, cosecha y otras.
- Cantidad trabajada.
- Tarifa unitaria.
- Cálculo automático del total.
- Asignación a temporada, fundo y cuartel.
- Historial de labores.

### Tablas

- `workers`
- `labor_entries`

### 🔴 Pendiente

- Cuadrillas.
- Jornales diarios.
- Asistencia.
- Horas extras.
- Tarifas por labor.
- Liquidación de temporada.
- Exportación para remuneraciones.
- Comparativo de costo por hectárea y labor.

---

## 🟢 Módulo 07 — Costos, inversiones y servicios/gastos

### Rutas

- Costos generales: `?module=costs`
- Inversiones: `?module=costs&category=INVERSION`
- Servicios y gastos: `?module=costs&category=SERVICIOS_GASTOS`

### Funcionalidades

- Registro de costo.
- Fecha.
- Descripción.
- Monto.
- Número de documento.
- Temporada.
- Fundo.
- Cuartel.
- Centro de costo.
- Estado del registro: borrador, contabilizado o anulado.
- Filtro por categoría.
- Listado de movimientos recientes.
- Auditoría de creación.

### Tabla

- `expense_entries`

### 🔴 Pendiente

- Edición controlada.
- Anulación con motivo obligatorio.
- Adjuntar factura o documento.
- Aprobación por niveles.
- Importación desde Excel/CSV.
- Conciliación mensual.
- Costos recurrentes.
- Presupuesto por temporada.
- Flujo de aprobación de inversiones.

---

## 🟢 Módulo 08 — Bodega e inventario

### Funcionalidades

- Artículos.
- SKU.
- Insumos.
- Ferretería.
- Maquinaria.
- Herramientas.
- Unidad de medida.
- Stock mínimo.
- Entradas.
- Salidas.
- Ajustes.
- Costo unitario.
- Referencia de factura, vale o guía.
- Stock calculado por movimientos.
- Historial de movimientos.

### Tablas

- `inventory_items`
- `inventory_movements`

### 🔴 Pendiente

- Bodegas físicas múltiples.
- Ubicación dentro de bodega.
- Lotes y vencimientos.
- Proveedores.
- Solicitud de materiales.
- Aprobación de salida.
- Alertas de stock mínimo.
- Valorización FIFO o promedio ponderado.
- Control de maquinaria y mantenciones.
- Inventario físico y diferencias.

---

## 🟢 Módulo 09 — Producción agrícola

### Funcionalidades

- Cosechas.
- Faenas.
- Actividades productivas.
- Temporada.
- Fundo.
- Cuartel.
- Especie.
- Fecha.
- Cantidad.
- Unidad.
- Calidad.
- Observaciones.
- Historial de producción.
- Indicadores acumulados.
- Auditoría de registros.

### Tabla

- `production_entries`

### 🔴 Pendiente

- Rendimiento por hectárea.
- Producción por especie y variedad.
- Producción por cuartel.
- Merma.
- Calidades y calibres.
- Packing y destino.
- Precio por unidad.
- Ingreso estimado y real.
- Comparativo de rendimiento entre temporadas.
- Integración con costos para margen por hectárea.

---

## 🔴 Módulo 10 — Maquinaria y mantenciones

### Objetivo

Administrar maquinaria agrícola, horas de uso, mantenciones y costos asociados.

### Desarrollo previsto

- Catálogo de maquinaria.
- Tipo y marca.
- Modelo y patente.
- Horómetro.
- Responsable.
- Estado operativo.
- Mantenciones preventivas.
- Mantenciones correctivas.
- Repuestos utilizados.
- Costo de mantención.
- Próxima mantención.
- Asignación a fundo y temporada.
- Historial de fallas.

### Tablas previstas

- `machinery`
- `machinery_maintenance`
- `machinery_usage`

---

## 🔴 Módulo 11 — Dashboard ejecutivo

### Indicadores

- Costo acumulado.
- Costo promedio por hectárea.
- Hectáreas operativas.
- Movimientos registrados.
- Producción acumulada.
- Rendimiento por hectárea.
- Stock crítico.
- Mano de obra acumulada.
- Inversión acumulada.
- Servicios y gastos.

### Gráficos

- Costos por temporada.
- Costos por clasificación.
- Costos por fundo.
- Costos por cuartel.
- Producción por especie.
- Producción por temporada.
- Mano de obra por labor.
- Inventario por categoría.
- Presupuesto versus real.

### 🔴 Pendiente

- Filtros funcionales por temporada, fundo, especie y cuartel.
- Gráficos alimentados completamente desde base de datos.
- Comparativo contra temporada anterior.
- Alertas visuales configurables.
- Exportación de cada widget.

---

## 🔴 Módulo 12 — Informes y exportaciones

### 🟢 Implementado

- Resumen de costos.
- Costos por clasificación.
- Costos por temporada.
- Costos por fundo.
- Exportación CSV.

### 🔴 Pendiente

- Informe por cuartel.
- Informe de costo por hectárea.
- Informe de mano de obra.
- Informe de producción.
- Informe de bodega.
- Informe de inventario valorizado.
- Informe de inversiones.
- Informe de servicios y gastos.
- Informe de presupuesto.
- Exportación XLSX.
- Exportación PDF.
- Reportes programados por correo.
- Filtros persistentes y guardado de vistas.

---

## 🟢 Módulo 13 — Configuración empresarial

### 🟢 Implementado

- Razón social.
- Nombre comercial.
- RUT.
- Logo.
- Contacto.
- Dirección.
- Comuna.
- Región.
- Endpoint protegido para mostrar el logo.

### 🔴 Pendiente

- Moneda y formato regional.
- Unidades de medida.
- Parámetros de temporada.
- Configuración de impuestos.
- Datos para documentos.
- Configuración de notificaciones.

---

## 🟢 Módulo 14 — Auditoría y seguridad

### 🟢 Implementado

- Tabla de auditoría.
- Usuario, acción, módulo, registro, fecha e IP.
- Auditoría de costos.
- Auditoría de bodega.
- Auditoría de mano de obra.
- Auditoría de producción.
- Auditoría de maestros.
- Auditoría de usuarios y roles.
- Auditoría de configuración.
- CSRF.
- Sesiones endurecidas.
- Cabeceras de seguridad.
- Aislamiento por empresa.

### 🔴 Pendiente

- Auditoría de login exitoso y fallido.
- Auditoría de exportaciones.
- Filtros por usuario, fecha y módulo.
- Retención configurable.
- Descarga de auditoría.
- Alertas ante actividad anómala.

---

## 🔴 Módulo 15 — Importación desde Excel

### Desarrollo previsto

- Plantilla de importación de fundos.
- Plantilla de temporadas.
- Plantilla de cuarteles.
- Plantilla de especies.
- Plantilla de costos.
- Plantilla de trabajadores.
- Plantilla de producción.
- Vista previa antes de importar.
- Validación de columnas.
- Informe de errores por fila.
- Confirmación transaccional.
- Registro de importación en auditoría.

---

## 🔴 Módulo 16 — Despliegue y operación

### WAMP64

- PHP 8.2.29.
- Host local `http://pccurico.local`.
- VirtualHost apuntando a `public/`.
- Base MySQL local.
- Wizard de instalación.
- Migraciones automáticas.

### cPanel

- PHP 8.2.
- Extensiones PDO, pdo_mysql, mbstring, openssl, fileinfo y json.
- Document root a `public/`.
- Base MySQL/MariaDB.
- HTTPS.
- Permisos de `storage/`.
- Configuración fuera del document root.
- Backups de base de datos.
- Logs y monitoreo.

### 🔴 Pendiente

- Guía de actualización por versión.
- Backup automático.
- Restauración de backup.
- Página de salud del sistema.
- Logs rotativos.
- Checklist de publicación.
- Prueba de dominio real.

---

# 🔴 5. Orden de cierre

1. Completar maquinaria y mantenciones.
2. Completar rendimientos y margen de producción.
3. Completar filtros funcionales del dashboard.
4. Completar informes XLSX/PDF.
5. Completar edición y anulación controlada de registros.
6. Completar importación desde Excel.
7. Completar recuperación de contraseña y perfil.
8. Ejecutar pruebas de instalación limpia.
9. Ejecutar pruebas de actualización de migraciones.
10. Validar cálculos contra Excel.
11. Validar permisos con perfiles distintos.
12. Revisar seguridad y rendimiento.
13. Preparar despliegue final en cPanel.

# 🔴 6. Criterio de finalización

El sistema se considerará terminado cuando:

- Una agrícola pueda instalarse desde cero mediante el wizard.
- Un administrador pueda configurar la empresa, logo, usuarios y roles.
- Los usuarios solo puedan acceder a módulos autorizados.
- Fundos, temporadas, especies y cuarteles estén correctamente relacionados.
- Costos, mano de obra, producción e inventario puedan registrarse sin Excel.
- Los informes reproduzcan los totales esperados de las planillas actuales.
- El dashboard use datos reales y filtros funcionales.
- Toda operación importante quede auditada.
- La instalación pueda actualizarse mediante migraciones.
- El sistema funcione en WAMP64 y pueda publicarse en cPanel.

---

## 🔴 Módulo adicional — Compras y proveedores

Estado: En desarrollo.

Elementos existentes:

- 🟢 Migración `004_procurement_schema.sql`.
- 🟢 Tablas `suppliers`, `purchase_orders` y `purchase_order_items`.
- 🟢 Índices y claves foráneas.
- 🟢 Servicio `ProcurementManagement`.
- 🟢 Controlador `ProcurementController`.
- 🟢 Ruta `?module=procurement`.
- 🟢 Vista de proveedores y órdenes.
- 🟢 Permiso mediante `costs.manage`.
- 🟢 Auditoría de proveedores y órdenes.
- 🟢 Validaciones de empresa y referencias.

Elementos faltantes:

- 🔴 Entidades y repositorios dedicados.
- 🔴 Middleware dedicado.
- 🔴 Edición, recepción y anulación de órdenes.
- 🔴 Detalle de productos y recepción contra inventario.
- 🔴 Documentación funcional específica.

## 🔴 Módulo adicional — Presupuestos

Estado: En desarrollo.

Elementos existentes:

- 🟢 Migración `005_budget_schema.sql`.
- 🟢 Tabla `budgets`.
- 🟢 Índices y claves foráneas.
- 🟢 Servicio `BudgetManagement`.
- 🟢 Controlador `BudgetController`.
- 🟢 Ruta `?module=budgets`.
- 🟢 Vista de creación y comparación contra costos ejecutados.
- 🟢 Auditoría de creación.
- 🟢 Validaciones de período, monto y pertenencia empresarial.

Elementos faltantes:

- 🔴 Entidades y repositorios dedicados.
- 🔴 Middleware dedicado.
- 🔴 Estados y flujo de aprobación.
- 🔴 Edición, cierre y reapertura.
- 🔴 Alertas de desviación.
- 🔴 Documentación funcional específica.
