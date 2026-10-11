# Arquitectura

## Stack

| Capa | Tecnología |
|------|------------|
| Backend | Laravel 13 (PHP 8.4) |
| Vistas | Blade + componentes anónimos |
| Estilos e interacción | Tailwind CSS 4, Alpine.js |
| Build de assets | Vite |
| PDF | `barryvdh/laravel-dompdf` (certificados y reportes) |
| Base de datos | SQLite o MySQL/MariaDB |
| Pruebas | PHPUnit 12 |

## Estructura

```
app/
├── Console/Commands/     Comando legado:importar
├── Enums/                Estados, roles y tipos (respaldados por string)
├── Exceptions/           InscripcionException (errores de negocio con mensaje para el usuario)
├── Http/
│   ├── Controllers/      Controladores públicos y de estudiante
│   │   ├── Admin/        Panel de administración y calificaciones
│   │   └── Auth/         Registro, login, verificación de correo y recuperación de contraseña
│   ├── Middleware/       CheckRole (role:...), EnsureUserIsActive
│   └── Requests/         Validación de formularios
├── Models/               Modelos Eloquent
├── Policies/             Autorización por recurso
├── Rules/                Reglas de validación propias (cédula ecuatoriana)
└── Services/             Lógica transaccional (eventos, inscripciones, certificados)
config/fisei.php          Parámetros propios del sistema
database/
├── migrations/           Esquema
└── seeders/              Datos iniciales (carreras, tipos de evento, admin, contenido)
resources/views/          Vistas Blade (admin, auth, eventos, inscripciones, pdf, ...)
routes/web.php            Rutas de la aplicación; routes/auth.php para autenticación
legacy/                   Código del sistema PHP anterior (solo referencia y fuente de importación)
docs/                     Esta documentación
tests/                    Pruebas Feature y Unit
```

## Capas

- **Controladores**: delgados. Validan con Form Requests, autorizan con policies y delegan en servicios.
- **Servicios** (`app/Services`): concentran las operaciones que tocan varias tablas o archivos dentro de una
  transacción.
  - `EventoService`: guarda el evento con sus carreras, requisitos e imagen; congela los requisitos cuando ya hay
    inscripciones; impide eliminar eventos con inscripciones.
  - `InscripcionService`: inscripción con control de cupos (`lockForUpdate`), subida y revisión de comprobantes.
  - `CertificadoPdfService`: genera el certificado PDF con su código de verificación.
- **Policies**: deciden quién puede ver, inscribirse, calificar, descargar certificados o archivos.
- **Enums**: definen los estados válidos y sus transiciones (por ejemplo `EstadoInscripcion::puedeTransicionarA`).

## Modelo de datos

```
users ──< documentos                 (cédula, papeleta, matrícula; uno por tipo)
users ──< inscripciones >── eventos
            │                 ├──< requisitos
            ├──< comprobantes │
            └──< respuestas_requisito >── requisitos
eventos >──< categorias       (tabla categoria_evento: carreras a las que se dirige)
eventos >── tipos_evento
eventos >── users (docente_id, docente asignado)
users ──< solicitudes_cambio ──1 resoluciones
contenidos                    (carrusel, nosotros, autoridades, reseñas, misión, visión)
configuraciones               (clave/valor: nombre de la institución, contacto, dirección)
```

Restricciones relevantes:

- `inscripciones`: `UNIQUE(user_id, evento_id)`, índice `(evento_id, estado)` para contar cupos.
- `documentos`: `UNIQUE(user_id, tipo)`.
- `resoluciones`: una por solicitud (`UNIQUE(solicitud_cambio_id)`).
- `users` y `eventos` usan borrado lógico (`deleted_at`).

## Almacenamiento de archivos

| Archivo | Disco | Acceso |
|---------|-------|--------|
| Documentos personales | `local` (`storage/app/private/documentos`) | Dueño y administradores, vía `DocumentoPolicy` |
| Comprobantes de pago | `local` (`storage/app/private/comprobantes`) | Dueño y administradores, vía `InscripcionPolicy` |
| Capturas de solicitudes | `local` | Solicitante y administradores |
| Imágenes de eventos y contenido | `public` (`storage/app/public`) | Públicas, servidas por `public/storage` |

Los archivos se guardan con nombre aleatorio y se valida su tipo MIME real.

## Rutas principales

| Ámbito | Prefijo / ruta | Middleware |
|--------|----------------|------------|
| Público | `/`, `/eventos`, `/eventos/{evento}`, `/creditos`, `/certificados/verificar/{codigo}` | — |
| Autenticado | `/inicio`, `/perfil`, `/documentos`, `/solicitudes`, `/inscripciones/{id}/certificado` | `auth`, `verified` |
| Estudiante | `/mis-inscripciones`, `/eventos/{evento}/inscripcion`, `/inscripciones/{id}/comprobante` | `role:estudiante` |
| Calificaciones | `/gestion/...` | `role:administrador,docente` |
| Administración | `/admin/...` | `role:administrador` |

Lista completa: `php artisan route:list`.
