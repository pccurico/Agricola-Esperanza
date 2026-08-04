# Infraestructura centralizada de librerías

Este directorio concentra las librerías del ERP sin duplicar archivos ni depender de copias dispersas en public/assets, vendor, plugins o módulos.

## Estructura base

- charts/
- pdf/
- excel/
- barcode/
- qrcode/
- images/
- mail/
- reports/
- import/
- export/
- documents/
- validation/
- security/
- cache/
- storage/
- maps/
- api/
- helpers/
- integrations/

## Reglas

- No se instalarán librerías duplicadas.
- Las dependencias de terceros deben ir detrás de adaptadores internos.
- El sistema debe consumir servicios del ERP, no bibliotecas de terceros directamente.
- Si una librería ya existe, se reutiliza y se reubica antes de instalar otra.
- Se ejecuta validación antes de eliminar copias antiguas.

## Estado actual

El proyecto aún no tenía dependencias Composer instaladas y sólo había una copia manual de Chart.js en public/assets/js/chart.min.js.

Se mantiene una estructura centralizada para futuras librerías, sin romper rutas actuales ni eliminar activos sin validar referencias.
