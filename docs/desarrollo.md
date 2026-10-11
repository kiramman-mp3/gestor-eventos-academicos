# Guía de desarrollo

## Flujo de ramas

```
main                versiones estables
└── develop         desarrollo actual
     ├── feature/*  nuevas funcionalidades
     ├── fix/*      correcciones
     ├── refactor/* refactorizaciones
     └── chore/*    mantenimiento, configuración, documentación
```

```bash
git checkout develop
git pull
git checkout -b feature/nombre-en-kebab-case
# ... commits pequeños ...
git checkout develop
git merge --no-ff feature/nombre-en-kebab-case
git branch -d feature/nombre-en-kebab-case
```

`main` solo recibe merges de `develop` cuando la versión está probada. No se hacen commits directos en `main` ni
`push --force` sobre `main` o `develop`.

## Commits

[Conventional Commits](https://www.conventionalcommits.org/es/): `tipo: descripción`.

Tipos: `feat`, `fix`, `refactor`, `style`, `docs`, `test`, `chore`, `perf`, `build`, `ci`.

```
feat: add certificate verification page
fix: prevent double enrollment on concurrent requests
test: cover payment receipt review
```

Commits pequeños y coherentes: una funcionalidad o corrección por commit, con su prueba en un commit aparte cuando
sea razonable.

## Antes de integrar

```bash
php artisan test        # pruebas
vendor/bin/pint --test  # estilo (vendor/bin/pint para corregir)
npm run build           # build de assets
```

## Pruebas

- `tests/Feature`: flujos HTTP completos por módulo (autenticación, catálogo, inscripción, comprobantes, notas,
  certificados, administración, importación del sistema anterior).
- `tests/Unit`: reglas aisladas (validación de cédula).
- Se ejecutan sobre SQLite en memoria (`phpunit.xml`) con `RefreshDatabase`; los discos se simulan con
  `Storage::fake`.
- Los factories están en `database/factories`.

## Convenciones de código

- Dominio en español (modelos, tablas, rutas, vistas); código de infraestructura de Laravel en inglés.
- Controladores delgados: validación en Form Requests, autorización en Policies, lógica en Services.
- Estados y catálogos fijos como Enums respaldados por string, con `label()` para la interfaz.
- Formato con Laravel Pint (configuración por defecto, preset `laravel`).
