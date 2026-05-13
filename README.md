# Regresa a Casa — UABC

Sistema de registro de eventos para egresados de la Universidad Autónoma de Baja California.
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

```
```text
regresoacasauabc/
├── index.html                  # Página pública: lista de eventos
├── index.html
├── admin/
│   └── index.html              # Panel de administración
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
│   │   ├── base.css            # Reset y estilos fundacionales
│   │   ├── style.css           # Tokens de diseño (paleta UABC, tipografía, espaciado)
│   │   ├── components.css      # Navbar, cards, modals, botones, formularios, footer
│   │   └── admin.css           # Estilos exclusivos del panel admin
│   ├── js/
│   │   ├── main.js             # Inicialización global, tema claro/oscuro, utilidades
│   │   ├── eventos.js          # Carga y renderiza lista de eventos (público)
│   │   ├── registro.js         # Formulario de registro al evento
│   │   ├── admin.js            # Lógica del panel de administración
│   │   └── qr-scanner.js       # Validación de códigos QR
│   └── images/                 # Imágenes del proyecto
│   └── images/
├── composer.json
└── README.md
```

## Funcionalidades previstas
## Funcionalidades principales

- Listado de eventos disponibles (hasta 3 por año)
- Registro de asistentes con generación y envío de QR
- Panel de administración con gestión de eventos, asistentes y FAQ
- Exportación de estadísticas a Excel
- Envío de correo recordatorio 2 días antes del evento
- Validación de QR desde distintos dispositivos
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

## Tecnologías
## Notas

- **Front-end:** HTML5, CSS3, JavaScript (Vanilla)
- **Back-end:** PHP
- **Base de datos:** MySQL
- **Paleta:** Verde UABC `#1A6B2A` + Dorado UABC `#F5C200`
- Este repositorio actualmente no define scripts de test/lint en `composer.json`.
- Las credenciales sensibles deben mantenerse en `.env` (no versionar).

## Autores
- Rodolfo Huitron Leyva
- Juan Carlos Cruz Hernandez
- Josue Fernando Robledo Zuñiga

Estudiantes de UABC Valle de las palmas en la Facultad de Ciencias de la Ingeniería y Tecnología en la carrera de Ingenieria en Software y Tecnologias Emergentes
