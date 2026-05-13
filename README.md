# Regresa a Casa — UABC

Plataforma para gestión de eventos de egresados de la Universidad Autónoma de Baja California.

Incluye:
- Sitio público para consultar eventos y registrarse.
- Envío de confirmación por correo con código QR.
- Panel administrativo para gestionar eventos, asistentes y preguntas frecuentes.

## Tecnologías

- **Frontend:** HTML5, CSS3, JavaScript (Vanilla)
- **Backend:** PHP
- **Base de datos:** MySQL (PDO)
- **Dependencias (Composer):**
  - `vlucas/phpdotenv`
  - `phpmailer/phpmailer`
  - `endroid/qr-code`

## Requisitos

- PHP 8.x
- Composer
- MySQL
- Servidor local (Apache, Nginx o `php -S`)

## Configuración rápida

1. Clona el repositorio.
2. Instala dependencias:

   ```bash
   composer install
   ```

3. Crea un archivo `.env` en la raíz del proyecto con:

   ```env
   DB_HOST=localhost
   DB_NAME=nombre_bd
   DB_USER=usuario_bd
   DB_PASS=clave_bd

   MAIL_HOST=smtp.tu-servidor.com
   MAIL_USER=tu_correo@dominio.com
   MAIL_PASS=tu_password_o_app_password
   MAIL_PORT=465
   ```

4. Prepara la base de datos.
   - Este repositorio no incluye migraciones ni script SQL de inicialización.
   - Crea al menos las tablas usadas por el código:
     - `usuario`
     - `evento`
     - `registro_asistente`
     - `faq`
     - `campus`
     - `facultad`
     - `carrera`
   - Inserta al menos un usuario administrador en `usuario` con contraseña hasheada (`password_hash`).

5. Levanta el proyecto en tu servidor local y abre:
   - Sitio público: `index.html`
   - Admin login: `admin/login.html`

## Estructura del proyecto

```text
regresoacasauabc/
├── index.html
├── admin/
│   ├── admin.php
│   ├── login.html
│   ├── participantes.php
│   └── php/
│       ├── conexion.php
│       ├── validar_login.php
│       ├── get_eventos.php
│       ├── get_asistentes.php
│       ├── exportar_asistentes.php
│       └── ...
├── php/
│   ├── procesar_registro.php
│   ├── get_facultades.php
│   └── get_carreras.php
├── assets/
│   ├── css/
│   ├── js/
│   └── images/
├── composer.json
└── README.md
```

## Funcionalidades principales

- Listado de eventos disponibles.
- Registro de asistentes.
- Generación de código QR único por registro.
- Envío de QR por correo electrónico.
- Panel admin con:
  - Dashboard y métricas.
  - Gestión de eventos.
  - Gestión de asistentes.
  - Gestión de FAQ.
  - Validación de QR.
  - Exportación a Excel.

## Notas

- Este repositorio actualmente no define scripts de test/lint en `composer.json`.
- Las credenciales sensibles deben mantenerse en `.env` (no versionar).

## Autor

- [RodolfoHubster](https://github.com/RodolfoHubster)
