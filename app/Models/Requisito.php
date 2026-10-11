<?php

namespace App\Models;

use App\Enums\TipoDocumento;
use App\Enums\TipoRequisito;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Requisito que debe cumplir un usuario para inscribirse en un evento. */
#[Fillable(['tipo', 'tipo_documento', 'descripcion'])]
class Requisito extends Model
{
    protected function casts(): array
    {
        return [
            'tipo' => TipoRequisito::class,
            'tipo_documento' => TipoDocumento::class,
        ];
    }

    public function evento(): BelongsTo
    {
        return $this->belongsTo(Evento::class);
    }

    public function cumplidoPor(User $user): bool
    {
        return $this->tipo !== TipoRequisito::Documento || $user->tieneDocumento($this->tipo_documento);
    }
}
