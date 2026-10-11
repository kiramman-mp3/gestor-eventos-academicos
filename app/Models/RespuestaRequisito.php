<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Respuesta de texto a un requisito, dada al inscribirse. */
#[Table('respuestas_requisito')]
#[Fillable(['requisito_id', 'texto'])]
class RespuestaRequisito extends Model
{
    public function inscripcion(): BelongsTo
    {
        return $this->belongsTo(Inscripcion::class);
    }

    public function requisito(): BelongsTo
    {
        return $this->belongsTo(Requisito::class);
    }
}
