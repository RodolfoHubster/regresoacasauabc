# Regresa a Casa — UABC

Sistema de registro de eventos para egresados de la Universidad Autónoma de Baja California.

## Estructura del proyecto

```
regresoacasauabc/
├── index.html                  # Página pública: lista de eventos
├── admin/
│   └── index.html              # Panel de administración
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
```

## Funcionalidades previstas

- Listado de eventos disponibles (hasta 3 por año)
- Registro de asistentes con generación y envío de QR
- Panel de administración con gestión de eventos, asistentes y FAQ
- Exportación de estadísticas a Excel
- Envío de correo recordatorio 2 días antes del evento
- Validación de QR desde distintos dispositivos

## Tecnologías

- **Front-end:** HTML5, CSS3, JavaScript (Vanilla)
- **Back-end:** PHP
- **Base de datos:** MySQL
- **Paleta:** Azul marino UABC `#002855` + Dorado UABC `#C8972B`

## Pruebas unitarias

1. Instala dependencias:
   ```bash
   composer install
   ```
2. Ejecuta las pruebas:
   ```bash
   composer test
   ```

## Autor

- [RodolfoHubster](https://github.com/RodolfoHubster)
