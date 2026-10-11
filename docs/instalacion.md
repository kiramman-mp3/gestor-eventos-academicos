# Instalación

## Requisitos

| Herramienta | Versión |
|-------------|---------|
| PHP | 8.4 o superior, con `bcmath`, `fileinfo`, `gd`, `intl`, `mbstring`, `pdo_mysql` / `pdo_sqlite`, `zip` |
| Composer | 2.x |
| Node.js | 20 o superior (para compilar los assets con Vite) |
| Base de datos | SQLite (por defecto) o MySQL 8.0.16+ / MariaDB 10.2+ |

> Con MySQL/MariaDB las migraciones agregan restricciones `CHECK` (fechas coherentes, notas entre 0 y 10,
> asistencia entre 0 y 100, costo no negativo). Con SQLite esas reglas solo se validan en la aplicación.

## Instalación nativa

```bash
git clone https://github.com/kiramman-mp3/fisei-gestion-eventos-academicos2.0.git
cd fisei-gestion-eventos-academicos2.0

composer install
cp .env.example .env
php artisan key:generate

touch database/database.sqlite      # solo si se usa SQLite
php artisan migrate --seed
php artisan storage:link

npm install
npm run build

php artisan serve
```

Abrir <http://localhost:8000>.

El atajo `composer run setup` ejecuta `composer install`, copia `.env`, genera la clave, migra y compila los assets
(no ejecuta el seeder ni `storage:link`).

### Administrador inicial

`php artisan db:seed` crea las carreras de la FISEI, los tipos de evento, el contenido base de la página de inicio
y un administrador con el correo `ADMIN_EMAIL`. Si `ADMIN_PASSWORD` está vacío se genera una contraseña aleatoria
que se muestra **una sola vez** en la consola:

```
Administrador creado: admin@uta.edu.ec / <contraseña> (cámbiala tras el primer ingreso).
```

El seeder es idempotente: puede ejecutarse varias veces sin duplicar datos.

## Instalación con Docker

Útil cuando la versión de PHP del equipo es menor a 8.4 (por ejemplo, XAMPP con PHP 8.2).

### 1. Imagen de PHP

```dockerfile
# Dockerfile
FROM php:8.4-cli-alpine
RUN apk add --no-cache git unzip libpng-dev libjpeg-turbo-dev freetype-dev icu-dev libzip-dev \
 && docker-php-ext-configure gd --with-jpeg --with-freetype \
 && docker-php-ext-install -j4 gd intl zip pdo_mysql bcmath
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /app
```

```bash
docker build -t fisei-php .
```

### 2. MySQL

```bash
docker network create fisei-net
docker run -d --name fisei-mysql --network fisei-net \
  -e MYSQL_ROOT_PASSWORD=secret -e MYSQL_DATABASE=fisei_eventos \
  mysql:8.4
```

En `.env`:

```dotenv
DB_CONNECTION=mysql
DB_HOST=fisei-mysql
DB_PORT=3306
DB_DATABASE=fisei_eventos
DB_USERNAME=root
DB_PASSWORD=secret
```

`DB_HOST` es el nombre del contenedor, no `127.0.0.1`: dentro de un contenedor `127.0.0.1` es el propio contenedor.

### 3. Comandos

En Git Bash (Windows) conviene desactivar la conversión de rutas y definir un alias:

```bash
export MSYS_NO_PATHCONV=1
alias dphp='docker run --rm -it --network fisei-net -v "$(pwd):/app" -w /app fisei-php'

dphp composer install
dphp php artisan key:generate
dphp php artisan migrate --seed
dphp php artisan storage:link
npm install && npm run build
```

Levantar el servidor publicando el puerto:

```bash
docker run --rm -it --network fisei-net -p 8000:8000 -v "$(pwd):/app" -w /app \
  fisei-php php artisan serve --host=0.0.0.0 --port=8000
```

## Variables de entorno

Además de las estándar de Laravel:

| Variable | Uso |
|----------|-----|
| `APP_TIMEZONE` | Zona horaria (`America/Guayaquil`) usada para periodos de inscripción |
| `APP_LOCALE` | Idioma de la interfaz y de los mensajes de validación (`es`) |
| `ADMIN_EMAIL`, `ADMIN_PASSWORD` | Administrador que crea el seeder |
| `MAIL_*` | Envío de verificación de correo y recuperación de contraseña. Con `MAIL_MAILER=log` los correos se escriben en `storage/logs/laravel.log` |
| `LEGADO_DB_*` | Conexión de solo lectura a la base del sistema anterior, usada por `legado:importar` |

Los límites de archivos, la edad mínima de registro y los mínimos por defecto de nota/asistencia están en
`config/fisei.php`.

## Comandos habituales

| Comando | Descripción |
|---------|-------------|
| `php artisan serve` | Servidor de desarrollo |
| `npm run dev` | Vite con recarga en caliente |
| `npm run build` | Compila CSS/JS para producción |
| `php artisan test` | Ejecuta las pruebas (SQLite en memoria, no toca la base local) |
| `vendor/bin/pint` | Formatea el código PHP |
| `php artisan migrate:fresh --seed` | Reconstruye la base de datos (**borra todos los datos**) |
| `php artisan legado:importar` | Importa datos del sistema anterior, ver [migración](migracion-legado.md) |

## Producción

- `APP_ENV=production`, `APP_DEBUG=false` y una `APP_KEY` propia.
- Servir `public/` como raíz del sitio web; nunca la raíz del repositorio.
- Configurar un servidor SMTP real en `MAIL_*`: sin él los usuarios no pueden verificar su correo.
- Ejecutar `php artisan migrate --force`, `php artisan storage:link` y `php artisan optimize` en cada despliegue.
- Respaldar `storage/app/private` (documentos y comprobantes) junto con la base de datos.
