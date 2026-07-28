# Especificación Maestra de Arquitectura y Desarrollo del ERP Agrícola v1.0

**Estado:** documento rector obligatorio del proyecto  
**Ámbito:** arquitectura, datos, seguridad, desarrollo, operación y evolución  
**Plataforma objetivo:** PHP 8.2 · MySQL 8/MariaDB · Apache · WAMP64 · cPanel

Este documento reemplaza instrucciones dispersas y gobierna todas las decisiones técnicas y funcionales del ERP. Ningún cambio de código, base de datos, configuración o documentación puede contradecirlo. `docs/entity-catalog.md`, `database/schema.sql`, las migraciones y el roadmap deben mantenerse sincronizados con esta especificación.

## 1. Filosofía del proyecto

El ERP debe reemplazar planillas Excel mediante una fuente única, trazable y multiempresa para la operación agrícola, administrativa, financiera, logística y productiva. La prioridad es la consistencia del sistema completo, no la velocidad de incorporar pantallas.

Principios rectores:

- diseño arquitectónico antes de código;
- configuración y datos administrables antes que listas fijas en PHP;
- trazabilidad desde el origen de una operación hasta sus efectos;
- preservación de información crítica mediante bajas lógicas y anulaciones;
- seguridad, auditoría e integridad desde el primer diseño;
- módulos integrados como un único ERP, no como aplicaciones aisladas;
- extensibilidad sin modificar innecesariamente el núcleo.

## 2. Objetivos

El sistema debe centralizar:

- identidad y configuración de una o más empresas;
- usuarios, roles, permisos y actividad;
- fundos, cuarteles, especies, variedades, temporadas y centros de costo;
- costos, inversiones, servicios, gastos y presupuestos;
- proveedores, clientes, compras y recepciones;
- inventario, bodegas, ubicaciones, lotes, transferencias y solicitudes;
- producción, trabajadores, contratistas, cuadrillas y labores;
- maquinaria, mantenciones y combustible;
- documentos, adjuntos, tareas, calendario y notificaciones;
- dashboard, reportes, exportaciones, API, backup, restauración y diagnóstico.

## 3. Alcance

El alcance incluye análisis funcional, diseño relacional, instalación, autenticación, RBAC, operación multiempresa, módulos agrícolas y transversales, reportes, exportaciones, auditoría, documentación y puntos de extensión para integraciones futuras.

No se conectarán servicios externos sin requisitos, credenciales y autorización explícitos; sí deben quedar contratos, interfaces y estructuras preparadas.

## 4. Requisitos funcionales

Cada funcionalidad debe permitir, según corresponda, crear, consultar, editar, anular, aprobar, rechazar, exportar, auditar y consultar historial. Los flujos que impacten otros módulos deben propagar sus efectos de forma consistente.

Módulos obligatorios:

1. Dashboard y dashboard ejecutivo.
2. Wizard de instalación.
3. Usuarios, roles, permisos y perfil.
4. Configuración de empresa, parámetros, monedas, unidades, correlativos y configuración regional/tributaria.
5. Maestros agrícolas.
6. Costos, inversiones, servicios/gastos y presupuestos.
7. Proveedores, clientes, compras, órdenes y recepciones.
8. Producción y mano de obra.
9. Maquinaria, mantenciones y combustible.
10. Inventario, bodegas, ubicaciones, lotes, vencimientos, herramientas e insumos.
11. Documentos y adjuntos.
12. Calendario, tareas y notificaciones.
13. Reportes, exportaciones e importación Excel.
14. Auditoría y logs.
15. API REST preparada.
16. Backup, restauración, mantenimiento, diagnóstico, ayuda y manual integrado.

## 5. Requisitos no funcionales

- PHP 8.2 compatible con WAMP64 y cPanel.
- MySQL 8 o MariaDB compatible.
- Arquitectura MVC con bajo acoplamiento.
- PDO y consultas preparadas.
- Interfaz responsive sin dependencia obligatoria de Bootstrap.
- Integridad referencial, índices y restricciones verificables.
- Soporte para crecimiento de datos y futuras integraciones.
- Configuración de zona horaria, idioma y formatos sin duplicación.
- Errores controlados sin exponer secretos ni detalles internos.

## 6. Arquitectura general

`public/index.php` es el único punto de entrada web. La aplicación se organiza en `app/Core`, `app/Controllers`, `app/Models`, `app/Repositories`, `app/Services` y `app/Views`; la configuración vive en `config`; la base en `database`; los archivos no públicos en `storage`; y la documentación en `docs`.

El flujo obligatorio es:

```text
HTTP -> Front Controller -> Middleware/Autorización -> Controller -> Service
    -> Repository/PDO -> Base de datos
    -> AuditLog/Eventos -> View, API o Exportador
```

Los controladores reciben la solicitud, validan el contexto HTTP y delegan. Las reglas de negocio viven en Services. El acceso a datos se concentra en repositorios o componentes de persistencia. Las vistas no consultan tablas directamente.

## 7. Arquitectura MVC

- **Models/Entities:** representan datos y sus invariantes.
- **Repositories:** encapsulan consultas y persistencia.
- **Services:** implementan casos de uso, transacciones, reglas y propagación de efectos.
- **Controllers:** coordinan HTTP, autorización, validación de entrada y respuesta.
- **Views:** presentan datos preparados, escapan la salida y no contienen reglas de negocio.
- **Middleware/Core:** sesión, CSRF, autorización, configuración, errores, logging y utilidades transversales.

## 8. Principios SOLID y PSR

Aplicar responsabilidad única, inversión de dependencias, interfaces pequeñas, bajo acoplamiento y sustitución segura. Mantener convenciones PSR de nombres, namespaces, carga de clases, formato y separación. No crear clases vacías, duplicadas o abstractions sin consumidor real.

## 9. Organización del código

```text
app/
├── Core/ Controllers/ Models/ Repositories/ Services/ Views/
config/
database/
├── migrations/ seeds/ schema.sql
docs/
public/
├── assets/ index.php
storage/
├── logs/ uploads/ backups/
```

`config/config.example.php` no contiene secretos. `config/config.php` es local y generado durante la instalación. Las rutas públicas no deben exponer `config`, `database` ni `storage`.

## 10. Diseño completo de la base de datos

El catálogo maestro de entidades está en `docs/entity-catalog.md` y es obligatorio para analizar cualquier cambio. Toda entidad debe documentar propósito, módulo, dependencias, consumidores, claves foráneas, restricciones, permisos, auditoría, dashboard, reportes, exportación y API.

Las entidades principales incluyen empresas, configuración, usuarios, roles, permisos, auditoría, logs, fundos, bloques, especies, temporadas, centros de costo, proveedores, clientes, compras, documentos, adjuntos, inventario, bodegas, ubicaciones, lotes, movimientos, solicitudes, trabajadores, contratistas, cuadrillas, labores, producción, maquinaria, mantenciones, combustible, costos, presupuestos, notificaciones, calendario, tareas, tokens API, backups y restauraciones.

## 11. `schema.sql` como fuente oficial

`database/schema.sql` representa el estado completo y actual de una instalación nueva. No puede ser parcial ni diferir del modelo aprobado. Toda modificación estructural debe actualizarlo inmediatamente junto con el catálogo de entidades y la documentación afectada.

La revisión estática debe comprobar tablas, columnas, tipos, índices, claves foráneas, restricciones, nombres y dependencias. No se aceptan tablas creadas solamente para resolver una pantalla aislada.

## 12. Migraciones

Las migraciones existen para actualizar instalaciones existentes y deben ser ordenadas, identificables e idempotentes cuando la operación lo requiera. Una instalación nueva parte de `schema.sql`, carga Seeds, registra la versión y ejecuta solo migraciones posteriores necesarias.

Nunca se deben ejecutar todas las migraciones históricas sobre una instalación nueva si eso contradice el flujo de instalación. Toda migración debe tener impacto documentado y correspondencia en `schema.sql`.

## 13. Seeds

Los Seeds contienen únicamente datos iniciales del sistema: permisos, catálogos base, monedas, regiones, comunas, unidades, parámetros y configuraciones técnicas. No deben incluir empresas, usuarios, personas, proveedores, clientes, temporadas, fundos ni datos ficticios de negocio.

Deben ser repetibles sin duplicar registros y no deben sustituir la configuración solicitada por el Wizard.

## 14. Wizard de instalación

El Wizard debe solicitar empresa, razón social, nombre comercial, RUT, logo, contacto, dirección, región, comuna, primer fundo, temporada, administrador, contraseña y centros de costo iniciales.

Debe utilizar `schema.sql` y Seeds, crear la configuración inicial, guardar archivos de forma segura y generar `config/config.php`. No debe crear tablas ni alterar la estructura. No puede contener nombres, correos, RUT, fundos o temporadas hardcodeados.

## 15. Configuración inicial

Toda configuración por empresa debe almacenarse en `company_settings` o entidades parametrizables con tipo, valor, validación, vigencia y auditoría. Incluye formatos regionales, moneda, zona horaria, numeración, impuestos, notificaciones, estados permitidos y preferencias de reportes.

La lógica configurable no debe esconderse en constantes PHP cuando pueda ser administrada como dato.

## 16. Multiempresa

Toda entidad operativa perteneciente a una empresa debe incluir `company_id` y toda consulta debe filtrarlo. Los Services deben verificar pertenencia de relaciones cruzadas: empresa, usuario, rol, fundo, temporada, centro, proveedor, bodega y documento.

No se permite mezclar datos entre empresas ni confiar únicamente en filtros enviados por el navegador. Las claves, índices y autorizaciones deben reforzar el aislamiento.

## 17. Seguridad

Implementar CSRF, escape de salida contra XSS, consultas preparadas contra SQL injection, `password_hash`, regeneración de sesión, cookies `HttpOnly`, `SameSite`, `Secure` en producción, cabeceras de seguridad, autorización por permiso, validación de subida de archivos, protección de directorios y control de errores.

Nunca registrar contraseñas, tokens planos, secretos, archivos privados ni datos sensibles innecesarios. Las descargas de adjuntos, logos, backups y exportaciones deben pasar por autorización.

## 18. RBAC (roles y permisos)

Cada módulo tiene permisos propios y granulares, como `view`, `create`, `update`, `delete`, `approve`, `export` y `manage` cuando corresponda. No se deben reutilizar permisos de otro módulo para resolver accesos incorrectos.

El middleware y el router deben aplicar permisos en servidor. Los menús solo reflejan permisos; nunca sustituyen la autorización. Los cambios de roles, permisos y usuarios son auditables.

## 19. Catálogo maestro de entidades

`docs/entity-catalog.md` es el inventario arquitectónico obligatorio. Ninguna entidad puede implementarse aislada. Antes de modificar una entidad se actualizan sus consumidores, dependencias, reglas, permisos, auditoría, dashboard, reportes, exportaciones, API y documentación.

La cadena mínima de trazabilidad debe permitir seguir una operación desde su origen hasta sus efectos: por ejemplo, solicitud interna → orden de compra → recepción → lote/movimiento de inventario → costo → reporte y auditoría.

## 20. Catálogos parametrizables

Estados, tipos, categorías, unidades, monedas, bancos, formas de pago, tipos de maquinaria, combustibles, labores, cultivos, calidades, motivos, prioridades, regiones y comunas deben almacenarse como catálogos administrables.

No se permiten listas de negocio hardcodeadas en controladores, Services, vistas o JavaScript. Los Seeds pueden entregar valores iniciales del sistema; la empresa debe poder administrar los valores autorizados cuando corresponda.

## 21. Integración entre módulos

Cada operación que impacta otros módulos debe ejecutarse mediante un Service transaccional y propagar efectos consistentes. Se deben definir eventos de dominio o mecanismos equivalentes para estados como aprobación, recepción, anulación, cierre y restauración.

Ejemplos obligatorios:

- compras → proveedores, documentos, inventario, costos, dashboard y reportes;
- producción → inventario, costos, mano de obra, dashboard y reportes;
- maquinaria → mantenciones, combustible, costos, calendario, alertas y auditoría;
- presupuestos → costos reales, desviaciones, aprobaciones y notificaciones.

## 22. Flujo obligatorio de desarrollo

El orden no puede invertirse:

1. análisis funcional;
2. arquitectura y dependencias;
3. modelo de datos y relaciones;
4. reglas de negocio y estados;
5. permisos y roles;
6. auditoría, logs y eventos;
7. impacto en integraciones;
8. actualización de `schema.sql`, catálogo y roadmap;
9. migraciones y Seeds;
10. entidades y modelos;
11. repositorios;
12. Services;
13. middleware, rutas y controladores;
14. vistas y componentes;
15. dashboard, reportes, exportaciones y API;
16. documentación funcional y técnica;
17. auditoría estática global.

No se deben implementar controladores, vistas o Services mientras existan entidades principales sin definir.

## 23. Checklist obligatorio por módulo

Un módulo solo puede marcarse **Completado** cuando todos estos elementos existen y son coherentes:

- análisis y arquitectura;
- migración, tabla, índices, claves foráneas y restricciones;
- Seeds necesarios;
- entidad/modelo y repositorio;
- Service con reglas y transacciones;
- middleware, rutas y controlador;
- vistas, formularios y componentes;
- validaciones de servidor y cliente cuando corresponda;
- permisos y roles;
- auditoría, logs y manejo de errores;
- mensajes al usuario;
- configuración y catálogos;
- integración con módulos consumidores;
- dashboard, reportes, exportaciones y API aplicables;
- documentación técnica y funcional;
- auditoría estática sin inconsistencias.

Si falta un elemento, el estado es **En desarrollo**.

## 24. Auditoría continua

Después de cada cambio se debe revisar el impacto global en base de datos, relaciones, Services, controladores, vistas, dashboard, reportes, API, exportaciones, permisos, auditoría, navegación, configuración y documentación.

Las operaciones importantes registran creación, edición, aprobación, anulación, exportación, importación, login, logout, cambios de configuración y administración de permisos. La información crítica no se elimina físicamente cuando debe preservarse trazabilidad.

## 25. Documentación obligatoria

Cada módulo debe documentar propósito, actores, flujo, reglas, estados, entidades, permisos, auditoría, integraciones, reportes, exportaciones, API, instalación, mantenimiento y pruebas pendientes. El catálogo, la especificación, el roadmap y `schema.sql` deben permanecer sincronizados.

## 26. Reportes de avance

Los reportes deben contener únicamente información verificable por inspección estática. Para cada módulo indicar `No iniciado`, `En desarrollo` o `Completado`, checklist satisfecho, elementos faltantes y módulos anteriores modificados.

No usar porcentajes estimados ni expresiones como “casi terminado”, “prácticamente listo” o “implementado parcialmente”. No afirmar completitud sin checklist completo.

## 27. Verificaciones estáticas

En este flujo no se ejecutan PHPUnit, pruebas manuales, pruebas funcionales, pruebas de carga ni validaciones que requieran entorno de ejecución. La verificación se realiza mediante lectura y búsqueda estructural.

Debe comprobarse consistencia de namespaces, referencias, clases, métodos, servicios, rutas, vistas, permisos, migraciones, schema, dependencias, índices, archivos huérfanos, código muerto, nombres duplicados, documentación y aislamiento multiempresa. Las pruebas runtime pendientes deben quedar documentadas, sin bloquear el desarrollo.

## 28. Datos de ejemplo y prohibición de hardcodear

Toda información observada en planillas, capturas o conversaciones es referencia funcional. Está prohibido dejar hardcodeados nombres de empresas, personas, correos, RUT, direcciones, temporadas, fundos, proveedores, clientes, cantidades o listas de negocio.

Los datos operativos se crean mediante Wizard, formularios, importaciones autorizadas o API. Los Seeds solo contienen datos iniciales del sistema definidos en la sección 13.

## 29. Calidad del código

No crear código duplicado, archivos innecesarios, clases vacías, métodos sin uso, código muerto, controladores pesados ni consultas directas desde vistas. Mantener cohesión, bajo acoplamiento, nombres claros, transacciones en operaciones compuestas y errores controlados.

## 30. Rendimiento

Usar índices para filtros multiempresa, fechas, estados y relaciones frecuentes. Evitar N+1, consultas sin límites, cargas innecesarias de adjuntos y cálculos repetidos. Los reportes deben paginar o agrupar adecuadamente y reutilizar consultas de repositorios/Services.

## 31. Escalabilidad

Diseñar para múltiples empresas, temporadas, fundos, usuarios, bodegas y grandes volúmenes transaccionales. Las integraciones y exportadores deben consumir Services estables. Las operaciones largas deben poder evolucionar a procesos controlados sin acoplar la interfaz al almacenamiento.

## 32. Internacionalización

No fijar textos, monedas, fechas, números, zona horaria o separadores en lógica de negocio. La configuración regional se administra por empresa y permite comenzar con Chile (`America/Santiago`, CLP, RUT, regiones y comunas) sin impedir futuras localizaciones.

## 33. API

Preparar una API REST versionable con autenticación por tokens almacenados como hash, expiración, revocación, permisos, límites y auditoría. La API reutiliza Services y reglas del sistema; no accede directamente a tablas ni permite saltarse multiempresa.

## 34. Integraciones futuras

Dejar puntos de extensión para sensores, balanzas, estaciones meteorológicas, GPS, facturación electrónica, bancos, correo, almacenamiento externo y otros servicios. Las integraciones deben aislarse detrás de contratos/adaptadores y registrar errores y trazabilidad sin contaminar el núcleo.

## 35. Mantenimiento

Incluir diagnóstico de configuración, salud de dependencias, logs técnicos, limpieza controlada, gestión de archivos, rotación de logs, ayuda contextual y manual integrado. Las acciones administrativas requieren permisos y auditoría.

## 36. Backup y restauración

Los backups deben registrar empresa, usuario, fecha, versión, ruta segura, checksum, tamaño y estado. Las restauraciones deben registrar origen, operador, resultado, errores y efectos. Nunca exponer rutas ni archivos privados; toda restauración crítica requiere trazabilidad y controles de seguridad.

## 37. Criterios de finalización del proyecto

El proyecto finaliza únicamente cuando:

- el modelo arquitectónico y el catálogo están completos;
- `schema.sql`, migraciones y Seeds son coherentes;
- todos los módulos tienen checklist completo o una decisión explícita documentada;
- no existen integraciones críticas pendientes;
- no hay datos operativos hardcodeados;
- los permisos son granulares y aplicados en servidor;
- la trazabilidad y auditoría cubren operaciones críticas;
- dashboard, reportes, exportaciones y API consumen datos reales;
- backup, restauración, mantenimiento y documentación están preparados;
- la auditoría estática global no detecta deuda crítica.

La ausencia de pruebas runtime no autoriza a declarar producción lista; esas pruebas se registran como validaciones humanas pendientes. Tampoco bloquea el trabajo estructural ni permite afirmar que un módulo está completado sin su checklist.

# Reglas operativas obligatorias

- Trabajar de forma autónoma y continuar por dependencias, sin solicitar confirmaciones entre módulos.
- No suponer que existe una tabla, clase, vista, ruta, permiso o migración: verificarlo.
- No agregar parches aislados si la solución requiere corregir la arquitectura.
- Si una decisión deja de ser adecuada, refactorizar el modelo completo antes de añadir funcionalidades.
- Al modificar un módulo anterior, actualizar inmediatamente su código, documentación, catálogo, roadmap y dependencias.
- No informar “se implementó”, “se agregó”, “se creó” o “se completó” sin verificación estática del checklist correspondiente.
- La arquitectura tiene prioridad sobre la velocidad y la consistencia global tiene prioridad sobre el avance aparente.
