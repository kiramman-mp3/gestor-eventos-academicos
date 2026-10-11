<?php

namespace App\Models;

use App\Enums\EstadoSolicitud;
use App\Enums\Prioridad;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('resoluciones')]
#[Fillable(['prioridad', 'estado', 'comentario'])]
class Resolucion extends Model
{
    protected function casts(): array
    {
        return [
            'prioridad' => Prioridad::class,
            'estado' => EstadoSolicitud::class,
        ];
    }

    public function solicitud(): BelongsTo
    {
        return $this->belongsTo(SolicitudCambio::class, 'solicitud_cambio_id');
    }

    public function resolutor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resuelto_por');
    }
}
