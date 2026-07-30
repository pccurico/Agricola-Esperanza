# Despliegue en cPanel

## Requisitos

- PHP 8.2 con extensiones PDO, pdo_mysql, mbstring, openssl, fileinfo y json.
- MySQL 8 o MariaDB compatible con claves foráneas.
- Apache con `mod_rewrite` habilitado.
- HTTPS activo para proteger sesiones y credenciales.

## Instalación local con WAMP64

La configuración de desarrollo usa PHP 8.2.29 en `C:\wamp64\bin\php\php8.2.29\php.exe` y el host local `http://pccurico.local`.

1. Crear la carpeta del proyecto dentro del document root de Apache de WAMP64.
2. Registrar `pccurico.local` en el archivo `C:\Windows\System32\drivers\etc\hosts` apuntando a `127.0.0.1`.
3. Configurar un VirtualHost de Apache para que `http://pccurico.local` apunte a la carpeta `public/` del proyecto.
4. Crear una base de datos MySQL local, copiar `config/config.example.php` como `config/config.php` y completar las credenciales.
5. Importar las migraciones en orden desde phpMyAdmin.
6. Abrir `http://pccurico.local` para iniciar el wizard.

## Instalación inicial

1. Crear una base de datos y un usuario MySQL desde **MySQL Databases**.
2. Importar las migraciones en orden desde **phpMyAdmin** o consola.
3. Copiar `config/config.example.php` como `config/config.php` y completar las credenciales.
4. Apuntar el document root del dominio a `public/`. Si el hosting obliga a usar `public_html`, copiar solo el contenido de `public/` allí y ajustar la ruta de bootstrap.
5. Crear `storage/logs` y `storage/uploads`, con permisos de escritura limitados al usuario de PHP.
6. Abrir el dominio para iniciar el wizard.
7. Eliminar o bloquear archivos de instalación después de completar el wizard.

Nunca subir `config/config.php`, respaldos SQL ni archivos `.env` a un repositorio público.
