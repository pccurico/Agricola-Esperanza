# Parches de gobierno del ERP Agrícola

## Propósito

Estos parches complementan `docs/documento-maestro-desarrollo.md` y son obligatorios para cualquier cambio posterior.

## Patch 01 — Schema, migraciones, seeds y wizard

- Diseñar el modelo relacional completo antes de considerar módulos terminados.
- Mantener `database/schema.sql` como definición oficial del estado actual.
- Mantener migraciones solo para actualizar instalaciones existentes.
- Ejecutar seeds únicamente con datos iniciales del sistema.
- No insertar empresas, usuarios ni datos de ejemplo en seeds.
- Mantener el wizard sin datos predefinidos y sin creación de tablas.
- Evitar diferencias entre schema, migraciones y documentación.

## Patch 02 — Desarrollo sistémico

Cada módulo debe integrarse con los módulos que consumen sus datos. Compras debe alimentar proveedores, inventario, costos, dashboard, reportes y auditoría. Maquinaria debe integrarse con combustible, mantenciones, costos, dashboard, reportes y auditoría. Producción debe integrarse con inventario, costos, mano de obra, dashboard y reportes.

## Patch 03 — Permisos

Cada módulo debe tener permisos propios. No se permite reutilizar un permiso de otro módulo. Los permisos deben separar, cuando corresponda, operaciones de lectura, creación, actualización, eliminación, aprobación, importación y exportación.

## Patch 04 — Módulos completos

Un módulo solo puede pasar a COMPLETADO cuando tiene migraciones, tablas, índices, claves foráneas, seeds, modelos, entidades, repositorios, servicios, controladores, middleware, rutas, vistas, componentes, formularios, validaciones, manejo de errores, permisos, roles, auditoría, logs, configuración y documentación técnica y funcional.

## Patch 05 — Auditoría continua

Después de cada modificación se debe revisar el impacto en base de datos, relaciones, servicios, controladores, vistas, dashboard, reportes, API, exportaciones, auditoría y documentación. Las inconsistencias deben corregirse antes de continuar.

## Patch 06 — Verificaciones estáticas

No ejecutar PHPUnit, pruebas funcionales, pruebas manuales, pruebas de carga ni validaciones que requieran un entorno de ejecución. Realizar únicamente inspecciones estáticas de referencias, dependencias, rutas, vistas, migraciones, permisos, clases, servicios, archivos huérfanos, código muerto, nombres duplicados y consistencia arquitectónica. Las pruebas de ejecución pendientes deben documentarse.

## Patch 07 — Reportes de avance

Los reportes deben reflejar el estado completo del proyecto. Cada módulo debe indicar estado, checklist verificable, elementos existentes, faltantes y módulos anteriores modificados. No utilizar porcentajes ni expresiones ambiguas.

## Patch 08 — Objetivo final

El proyecto solo termina cuando sea un ERP Agrícola integrado, sin módulos aislados, funcionalidades incompletas, integraciones pendientes, permisos compartidos incorrectamente, datos hardcodeados, diferencias entre schema y migraciones ni deuda técnica crítica detectable por inspección estática.
