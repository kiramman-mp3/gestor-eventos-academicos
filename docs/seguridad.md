# Seguridad

## Autenticación

- Contraseñas con `bcrypt` (`BCRYPT_ROUNDS`).
- Verificación obligatoria del correo antes de acceder a las rutas protegidas (`verified`).
- Limitación de intentos en inicio de sesión, registro, recuperación de contraseña, verificación de certificados
  y envío de solicitudes (`throttle`).
- Regeneración de la sesión al iniciar y cerrar sesión.
- Cuentas desactivadas: `EnsureUserIsActive` cierra la sesión del usuario.

## Autorización

- Middleware `role:` para separar áreas (estudiante, docente, administrador).
- Policies por recurso: `EventoPolicy`, `InscripcionPolicy`, `DocumentoPolicy`, `CertificadoPolicy`,
  `SolicitudCambioPolicy`. Ningún controlador confía en identificadores enviados por el cliente para decidir el dueño
  de un recurso: el usuario sale siempre de la sesión.

## Archivos

- Documentos, comprobantes y capturas se guardan en el disco privado y solo se sirven a través de rutas autorizadas.
- Se valida el tipo MIME real del archivo (no solo la extensión) y el tamaño máximo (`config/fisei.php`).
- Los archivos se guardan con nombre aleatorio; el nombre original solo se conserva como dato.

## Datos

- Protección CSRF en todos los formularios.
- Asignación masiva limitada (`fillable`); campos sensibles como `rol` o `estado` se asignan explícitamente.
- Transacciones y bloqueos de fila en operaciones concurrentes (cupos, revisión de comprobantes).
- Restricciones `CHECK` en MySQL/MariaDB como segunda barrera para fechas, notas, asistencia y costos.

## Reportar una vulnerabilidad

No abras un issue público. Escribe a los mantenedores del repositorio con la descripción y los pasos para
reproducirla.
