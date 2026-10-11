<?php

namespace App\Models;

use App\Enums\TipoDocumento;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Documento personal del usuario. El archivo vive en el disco privado. */
#[Fillable(['tipo', 'path', 'nombre_original', 'mime', 'tamano'])]
class Documento extends Model
{
    public const DISK = 'local';

    public const DIRECTORIO = 'documentos';

    protected function casts(): array
    {
        return [
            'tipo' => TipoDocumento::class,
            'tamano' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
