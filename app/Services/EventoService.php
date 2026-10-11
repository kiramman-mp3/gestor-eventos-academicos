<?php

namespace App\Services;

use App\Enums\EstadoEvento;
use App\Exceptions\InscripcionException;
use App\Models\Evento;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Persistencia de eventos con sus carreras, requisitos e imagen en una sola transacción.
 */
class EventoService
{
    public const DIRECTORIO_IMAGENES = 'eventos';

    /**
     * @param  array<string, mixed>  $datos
     * @param  list<int>  $categorias
     * @param  list<array{tipo: string, tipo_documento?: ?string, descripcion: string}>  $requisitos
     */
    public function guardar(Evento $evento, array $datos, array $categorias, array $requisitos, ?UploadedFile $imagen = null): Evento
    {
        $imagenAnterior = $evento->imagen_path;
        $imagenNueva = $imagen?->store(self::DIRECTORIO_IMAGENES, 'public');

        try {
            DB::transaction(function () use ($evento, $datos, $categorias, $requisitos, $imagenNueva) {
                $evento->fill($datos);
                if ($imagenNueva) {
                    $evento->imagen_path = $imagenNueva;
                }
                $evento->save();

                $evento->categorias()->sync($categorias);

                // Con inscripciones existentes los requisitos se congelan: cambiarlos
                // alteraría las condiciones aceptadas por quienes ya se inscribieron.
                if (! $evento->inscripciones()->exists()) {
                    $evento->requisitos()->delete();
                    foreach ($requisitos as $requisito) {
                        $evento->requisitos()->create([
                            'tipo' => $requisito['tipo'],
                            'tipo_documento' => $requisito['tipo'] === 'documento' ? $requisito['tipo_documento'] : null,
                            'descripcion' => $requisito['descripcion'],
                        ]);
                    }
                }
            });
        } catch (Throwable $e) {
            if ($imagenNueva) {
                Storage::disk('public')->delete($imagenNueva);
            }

            throw $e;
        }

        if ($imagenNueva && $imagenAnterior) {
            Storage::disk('public')->delete($imagenAnterior);
        }

        return $evento;
    }

    public function cambiarEstado(Evento $evento, EstadoEvento $estado): void
    {
        $evento->estado = $estado;
        $evento->save();
    }

    /** @throws InscripcionException */
    public function eliminar(Evento $evento): void
    {
        if ($evento->inscripciones()->exists()) {
            throw new InscripcionException('No se puede eliminar un evento con inscripciones. Cancélalo en su lugar.');
        }

        $evento->delete();
    }
}
