# Migración desde el sistema anterior

La primera versión del sistema (PHP sin framework, carpeta `legacy/`) usaba una base MySQL llamada `UTA`
(esquema en `legacy/sql/uta db.sql`). El comando `legado:importar` copia sus datos al nuevo esquema.

## Preparación

1. Cargar la base anterior en MySQL:

   ```bash
   mysql -u root -p -e "CREATE DATABASE UTA CHARACTER SET utf8mb4"
   mysql -u root -p UTA < "legacy/sql/uta db.sql"
   ```

2. Configurar la conexión en `.env`:

   ```dotenv
   LEGADO_DB_HOST=127.0.0.1      # o el nombre del contenedor si se usa Docker
   LEGADO_DB_PORT=3306
   LEGADO_DB_DATABASE=UTA
   LEGADO_DB_USERNAME=root
   LEGADO_DB_PASSWORD=
   ```

3. Migrar y sembrar la base nueva: `php artisan migrate --seed`.

## Uso

```bash
php artisan legado:importar --uploads=legacy --costo=0
```

| Opción | Descripción |
|--------|-------------|
| `--uploads=` | Carpeta raíz del sistema anterior (la que contiene `uploads/`). Sin ella se importan los datos pero no los archivos |
| `--costo=` | Costo asignado a los eventos importados; el sistema anterior no lo guardaba (por defecto `0`) |

Todo se ejecuta en una transacción: si un paso falla, la base queda como estaba. Los archivos ya copiados al disco
no se revierten.

El comando es **idempotente**: omite usuarios (por correo o cédula), eventos (por nombre y fecha de inicio),
inscripciones (por usuario y evento), solicitudes (por usuario y título) y contenido ya existentes, por lo que puede
ejecutarse varias veces.

## Correspondencia de datos

| Sistema anterior | Sistema nuevo | Notas |
|------------------|---------------|-------|
| `categorias_evento` | `categorias` | La categoría "Publico" no se crea: se convierte en `eventos.es_publico` |
| `tipos_evento` | `tipos_evento` | Si falta el tipo de un evento se usa "Curso" |
| `estudiantes` | `users` + `documentos` | Los hashes `password_hash` se copian tal cual (compatibles). `carrera` puede ser id o nombre |
| `eventos` + `evento_categoria` | `eventos` + `categoria_evento` | Estado `cerrado` pasa a *Finalizado*; fechas de inscripción posteriores al inicio se recortan |
| `requisitos_evento` | `requisitos` | `ruta_cedula`, `ruta_papeleta`, `ruta_matricula` pasan a requisitos de documento |
| `inscripciones` | `inscripciones` + `comprobantes` | Nota y asistencia se ajustan a sus rangos válidos |
| `requisitos_inscripcion` | `respuestas_requisito` | Solo respuestas de texto |
| `info_fisei` | `contenidos` | Solo si el sistema nuevo no tiene contenido propio. Los tipos `evento` e `imagen` se descartan |
| `solicitudes` + `resoluciones` | `solicitudes_cambio` + `resoluciones` | Se toma la última resolución de cada solicitud |

## Archivos

Solo se copian archivos ubicados dentro de la carpeta indicada en `--uploads` y de tipo PDF, JPEG, PNG o WebP.
Cualquier otro archivo (por ejemplo `.php`) o ruta que salga de esa carpeta se omite y se informa al final.
