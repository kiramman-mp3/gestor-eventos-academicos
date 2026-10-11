<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/** Solicitud de cambio sobre el sistema (gestión de la configuración del software). */
#[Table('solicitudes_cambio')]
#[Fillable(['titulo', 'tipo', 'descripcion', 'justificacion', 'contexto'])]
class SolicitudCambio extends Model
{
    public const TIPOS = ['Mejora', 'Corrección de error', 'Nueva funcionalidad', 'Cambio de contenido', 'Otro'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function resolucion(): HasOne
    {
        return $this->hasOne(Resolucion::class);
    }
}
