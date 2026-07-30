# Sistema de Gestión Agrícola PCCURICO — arquitectura inicial

## Objetivo

Sistema de Gestión Agrícola PCCURICO será un sistema web PHP 8.2 + MySQL 8 para administrar una agrícola chilena configurable desde un wizard de instalación. La primera instalación no asume el nombre una empresa agrícola: la empresa, logo, RUT, datos de contacto y estructura productiva se registran durante la configuración.

## Estructura

```text
app/
├── Core/                 # Servicios técnicos compartidos
├── Controllers/          # Acciones HTTP por módulo
├── Models/               # Acceso a datos y reglas de dominio
├── Services/             # Casos de uso
└── Views/                # Plantillas HTML
config/
├── config.example.php    # Plantilla sin secretos
└── config.php            # Archivo local ignorado y creado en instalación
database/
├── migrations/           # Cambios versionados del esquema
└── seeds/                # Catálogos y permisos iniciales
docs/                     # Documentación funcional y técnica
public/
├── assets/               # CSS, JavaScript e imágenes públicas
└── index.php             # Único punto de entrada web
storage/
├── logs/                 # Errores y auditoría técnica
└── uploads/              # Logos y archivos cargados
```

## Módulos

1. **Wizard inicial:** empresa, logo, usuario administrador, fundos, especies, temporadas, cuarteles y centros de costo.
2. **Acceso y permisos:** usuarios, roles, permisos por módulo, recuperación y registro de actividad.
3. **Maestros agrícolas:** fundos, sectores, cuarteles, especies, variedades, temporadas y unidades.
4. **Costos:** administración, mano de obra, inversiones, servicios/gastos y asignación a fundo, cuartel y temporada.
5. **Mano de obra:** trabajadores, jornales, labores, tarifas y liquidación operativa.
6. **Bodega:** insumos, maquinaria, herramientas, entradas, salidas, ajustes y stock valorizado.
7. **Dashboard e informes:** filtros por temporada/fundo/especie/cuartel, comparativos, costo por hectárea, exportación y reportes.

## Reglas de implementación

- Todas las consultas usan PDO con parámetros preparados.
- Las contraseñas se almacenan con `password_hash()`.
- Las fechas y horas se guardan en UTC cuando corresponda; la interfaz usa `America/Santiago`.
- Toda tabla transaccional debe tener `created_at`, `updated_at` y referencia a la empresa.
- Los movimientos nunca se eliminan físicamente; se anulan o corrigen mediante trazabilidad.
- Los datos de una empresa no se exponen a otra consulta o usuario.
