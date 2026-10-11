# Reglas de negocio

## Usuarios

- **Roles**: `administrador`, `docente`, `estudiante` (rol por defecto al registrarse).
- **Tipo**: `institucional` si el correo termina en `@uta.edu.ec`; `publico` en cualquier otro caso.
- El registro exige cédula ecuatoriana válida (dígito verificador), edad mínima de 17 años y verificación del correo
  antes de usar el sistema.
- Un usuario desactivado por la administración no puede iniciar sesión.

## Eventos

- Cada evento tiene un **tipo** (Curso, Webinar, Congreso, Taller, Evento FISEI, ...), modalidad
  (presencial, virtual, híbrida), horas, cupos y costo (`0` = gratuito).
- **Destinatarios**: un evento es público (`es_publico`) o se dirige a una o varias **carreras**. Un usuario solo ve
  y puede inscribirse en eventos públicos o de su carrera.
- **Fechas**: `inicio de inscripciones ≤ fin de inscripciones ≤ inicio del evento ≤ fin del evento`.
- **Aprobación**: un evento puede exigir nota mínima (0-10) y/o asistencia mínima (0-100 %). Si se marca como
  obligatoria sin fijar el valor, se usan 7.0 y 70 % (`config/fisei.php`).
- **Requisitos**: documentos del perfil (cédula, papeleta, matrícula) o preguntas de texto que el estudiante responde
  al inscribirse. Una vez que existen inscripciones, los requisitos ya no se pueden modificar.
- Un evento con inscripciones no se elimina; se cancela.

### Estados del evento

| Estado | Visible en el catálogo | Editable | Acepta inscripciones |
|--------|:----:|:----:|:----:|
| Borrador | No | Sí | No |
| Abierto | Sí | Sí | Sí, dentro del periodo de inscripción |
| En ejecución | Sí | No | No |
| Finalizado | Sí | No | No (habilita certificados) |
| Cancelado | No | No | No |

## Inscripciones

1. El estudiante se inscribe si el evento está **abierto**, hoy está dentro del periodo de inscripción, el evento se
   dirige a él, cumple los requisitos y quedan cupos.
2. Los cupos se controlan dentro de una transacción con bloqueo de fila (`SELECT ... FOR UPDATE`), por lo que dos
   inscripciones simultáneas nunca sobrepasan el límite. El índice único `(user_id, evento_id)` impide inscribirse
   dos veces.
3. Ocupan cupo las inscripciones en estado *En espera de pago*, *Pendiente de aprobación* y *Pagado*.

### Estados de la inscripción

```
                  subir comprobante              aprobar
En espera de pago ──────────────────> Pendiente de aprobación ──────> Pagado
        ▲                                      │
        └──────────── rechazar (con motivo) ───┘
Cualquier estado no final ──> Error
```

- Los eventos gratuitos pasan directamente a *Pagado*.
- *Pagado* y *Error* son estados finales.
- Cada comprobante queda registrado con su revisor, fecha y motivo de rechazo.

## Calificaciones

- Las registran los administradores o el **docente asignado** al evento.
- Nota entre 0 y 10, asistencia entre 0 y 100 %.

## Certificados

Se puede descargar el certificado de una inscripción cuando:

- quien lo pide es el dueño de la inscripción o un administrador,
- la inscripción está *Pagado*,
- se cumplen la nota y la asistencia mínimas exigidas por el evento,
- el evento está *Finalizado*.

Cada certificado lleva un código único verificable públicamente en `/certificados/verificar/{codigo}`.

## Reportes

El administrador puede descargar en PDF el reporte de un evento con inscritos, estado de pago, notas, asistencia y
resultado (aprobado / no aprobado).

## Solicitudes de cambio

Cualquier usuario autenticado puede registrar una solicitud de cambio (mejora o corrección) con captura opcional.
El administrador la resuelve indicando prioridad (baja, media, alta, crítica), estado (pendiente, en progreso,
terminado, rechazado) y un comentario.
