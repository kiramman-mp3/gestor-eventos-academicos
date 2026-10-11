# Eventos Académicos FISEI

Sistema web para gestionar cursos, congresos, webinars y talleres de la Facultad de Ingeniería en Sistemas,
Electrónica e Industrial (FISEI) de la Universidad Técnica de Ambato: publicación de eventos, inscripciones con
requisitos y control de cupos, pagos con comprobante, calificaciones y certificados PDF verificables.

![Laravel](https://img.shields.io/badge/Laravel-13-FF2D20?logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?logo=php&logoColor=white)
![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-4-06B6D4?logo=tailwindcss&logoColor=white)
![Licencia](https://img.shields.io/badge/licencia-MIT-green)

## Funcionalidades

- **Catálogo de eventos** con búsqueda y filtros; eventos públicos o dirigidos a carreras específicas.
- **Inscripción** con verificación de requisitos (documentos del perfil y preguntas) y control de cupos sin
  sobreventa.
- **Pagos**: subida de comprobantes y aprobación o rechazo por parte del administrador; eventos gratuitos sin pago.
- **Calificaciones**: nota y asistencia registradas por administradores o el docente asignado.
- **Certificados PDF** con código de verificación público.
- **Reportes PDF** por evento con pagos, notas y resultados.
- **Administración**: eventos, usuarios y roles, carreras, tipos de evento, contenido de la página de inicio y
  configuración general.
- **Solicitudes de cambio** de los usuarios con resolución, prioridad y seguimiento.
- **Migración** de los datos del sistema anterior con `php artisan legado:importar`.

Roles: **administrador**, **docente** y **estudiante**. Detalle en [docs/funcionalidades.md](docs/funcionalidades.md).

## Inicio rápido

Requisitos: PHP 8.4+, Composer 2, Node.js 20+.

```bash
git clone https://github.com/kiramman-mp3/fisei-gestion-eventos-academicos2.0.git
cd fisei-gestion-eventos-academicos2.0

composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed       # muestra la contraseña del administrador inicial
php artisan storage:link
npm install && npm run build

php artisan serve                # http://localhost:8000
```

¿PHP menor a 8.4 o prefieres MySQL? Consulta la [instalación con Docker](docs/instalacion.md#instalación-con-docker).

## Pruebas

```bash
php artisan test
vendor/bin/pint --test
```

## Documentación

| | |
|---|---|
| [Instalación](docs/instalacion.md) | Requisitos, Docker, variables de entorno, producción |
| [Arquitectura](docs/arquitectura.md) | Stack, estructura, modelo de datos, rutas |
| [Reglas de negocio](docs/reglas-de-negocio.md) | Estados de eventos e inscripciones, pagos, certificados |
| [Funcionalidades por rol](docs/funcionalidades.md) | Qué puede hacer cada usuario |
| [Seguridad](docs/seguridad.md) | Autenticación, autorización y manejo de archivos |
| [Migración del sistema anterior](docs/migracion-legado.md) | Comando `legado:importar` |
| [Guía de desarrollo](docs/desarrollo.md) | Flujo Git, commits, pruebas, convenciones |

## Contexto académico

Proyecto de la asignatura **Manejo y Configuración de Software**, carrera de Software, Universidad Técnica de
Ambato. Aplica gestión de la configuración del software: control de versiones con Git y GitHub, flujo de ramas
`main` / `develop` / `feature/*`, Conventional Commits y control de cambios mediante solicitudes registradas en el
propio sistema.

La primera versión (PHP sin framework) se conserva en [`legacy/`](legacy) como referencia y fuente de la migración
de datos.

## Equipo

Pablo Vayas · Alexis López · José Manzano · Johan Rodríguez · Alan Puruncajas

## Licencia

[MIT](LICENSE)
