<?php

namespace App\Services;

use App\Enums\EstadoComprobante;
use App\Enums\EstadoEvento;
use App\Enums\EstadoInscripcion;
use App\Enums\TipoRequisito;
use App\Exceptions\InscripcionException;
use App\Models\Comprobante;
use App\Models\Configuracion;
use App\Models\Evento;
use App\Models\Inscripcion;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Lógica transaccional de inscripciones y pagos.
 *
 * Control de cupos: dentro de la transacción se bloquea la fila del evento con
 * SELECT ... FOR UPDATE (lockForUpdate). Dos inscripciones simultáneas al mismo evento
 * se serializan: la segunda espera a que la primera confirme y recién entonces cuenta
 * los cupos ocupados, por lo que nunca se sobrepasa `eventos.cupos`.
 * El índice UNIQUE(user_id, evento_id) es la segunda barrera contra dobles inscripciones.
 */
class InscripcionService
{
    /**
     * @param  array<int|string, string>  $respuestas  Respuestas a requisitos de texto, indexadas por requisito_id
     *
     * @throws InscripcionException
     */
    public function inscribir(User $user, Evento $evento, array $respuestas = []): Inscripcion
    {
        $evento->loadMissing(['requisitos', 'categorias']);
        $user->loadMissing('documentos');

        if (! $evento->admiteA($user)) {
            throw InscripcionException::noDirigido();
        }

        foreach ($evento->requisitos as $requisito) {
            if (! $requisito->cumplidoPor($user)) {
                throw InscripcionException::requisitoPendiente($requisito->descripcion);
            }
        }

        try {
            return DB::transaction(function () use ($user, $evento, $respuestas) {
                $bloqueado = Evento::query()->whereKey($evento->id)->lockForUpdate()->firstOrFail();

                if (! $bloqueado->inscripcionAbierta()) {
                    throw InscripcionException::inscripcionesCerradas();
                }

                if (Inscripcion::query()->whereBelongsTo($user)->whereBelongsTo($bloqueado)->exists()) {
                    throw InscripcionException::yaInscrito();
                }

                $maximo = Configuracion::entero('max_inscripciones_estudiante');
                if ($maximo > 0 && $this->inscripcionesActivas($user) >= $maximo) {
                    throw InscripcionException::limiteAlcanzado($maximo);
                }

                $ocupados = Inscripcion::query()
                    ->whereBelongsTo($bloqueado)
                    ->whereIn('estado', EstadoInscripcion::ocupanCupo())
                    ->count();

                if ($ocupados >= $bloqueado->cupos) {
                    throw InscripcionException::sinCupos();
                }

                $inscripcion = new Inscripcion;
                $inscripcion->user()->associate($user);
                $inscripcion->evento()->associate($bloqueado);
                $inscripcion->estado = $bloqueado->esGratuito() ? EstadoInscripcion::Pagado : EstadoInscripcion::EnEsperaPago;
                $inscripcion->save();

                foreach ($evento->requisitos->where('tipo', TipoRequisito::Texto) as $requisito) {
                    $inscripcion->respuestas()->create([
                        'requisito_id' => $requisito->id,
                        'texto' => trim((string) ($respuestas[$requisito->id] ?? '')),
                    ]);
                }

                return $inscripcion;
            });
        } catch (UniqueConstraintViolationException) {
            throw InscripcionException::yaInscrito();
        }
    }

    /** Inscripciones que ocupan cupo en eventos todavía abiertos o en ejecución. */
    private function inscripcionesActivas(User $user): int
    {
        return Inscripcion::query()
            ->whereBelongsTo($user)
            ->whereIn('estado', EstadoInscripcion::ocupanCupo())
            ->whereHas('evento', fn ($q) => $q->whereIn('estado', [EstadoEvento::Abierto, EstadoEvento::EnEjecucion]))
            ->count();
    }

    /**
     * Guarda el comprobante en el disco privado y pasa la inscripción a revisión.
     *
     * @throws InscripcionException
     */
    public function subirComprobante(Inscripcion $inscripcion, UploadedFile $archivo): Comprobante
    {
        $path = $archivo->store(Comprobante::DIRECTORIO.'/'.$inscripcion->user_id, Comprobante::DISK);

        try {
            return DB::transaction(function () use ($inscripcion, $archivo, $path) {
                $bloqueada = Inscripcion::query()->whereKey($inscripcion->id)->lockForUpdate()->firstOrFail();

                if (! $bloqueada->estado->puedeSubirComprobante()) {
                    throw InscripcionException::estadoInvalido('subir un comprobante');
                }

                $comprobante = $bloqueada->comprobantes()->create([
                    'path' => $path,
                    'nombre_original' => mb_substr($archivo->getClientOriginalName(), 0, 255),
                    'mime' => $archivo->getMimeType(),
                    'tamano' => $archivo->getSize(),
                ]);

                $bloqueada->estado = EstadoInscripcion::PendienteAprobacion;
                $bloqueada->save();

                return $comprobante;
            });
        } catch (Throwable $e) {
            // Si la transacción falla, no queda un archivo huérfano
            Storage::disk(Comprobante::DISK)->delete($path);

            throw $e;
        }
    }

    /** @throws InscripcionException */
    public function aprobarComprobante(Comprobante $comprobante, User $revisor): void
    {
        $this->revisar($comprobante, $revisor, EstadoComprobante::Aprobado, EstadoInscripcion::Pagado);
    }

    /**
     * El estudiante vuelve a "en espera de pago" y puede subir otro comprobante.
     *
     * @throws InscripcionException
     */
    public function rechazarComprobante(Comprobante $comprobante, User $revisor, string $motivo): void
    {
        $this->revisar($comprobante, $revisor, EstadoComprobante::Rechazado, EstadoInscripcion::EnEsperaPago, $motivo);
    }

    private function revisar(
        Comprobante $comprobante,
        User $revisor,
        EstadoComprobante $resultado,
        EstadoInscripcion $nuevoEstado,
        ?string $motivo = null,
    ): void {
        DB::transaction(function () use ($comprobante, $revisor, $resultado, $nuevoEstado, $motivo) {
            $inscripcion = Inscripcion::query()->whereKey($comprobante->inscripcion_id)->lockForUpdate()->firstOrFail();
            $comprobante = Comprobante::query()->whereKey($comprobante->id)->lockForUpdate()->firstOrFail();

            if ($comprobante->estado !== EstadoComprobante::Pendiente || ! $inscripcion->estado->puedeTransicionarA($nuevoEstado)) {
                throw InscripcionException::estadoInvalido('revisar este comprobante');
            }

            $comprobante->estado = $resultado;
            $comprobante->revisor()->associate($revisor);
            $comprobante->revisado_at = now();
            $comprobante->motivo_rechazo = $motivo;
            $comprobante->save();

            $inscripcion->estado = $nuevoEstado;
            $inscripcion->save();
        });
    }
}
