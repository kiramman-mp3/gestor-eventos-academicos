<?php

namespace App\Models;

use App\Enums\EstadoComprobante;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Comprobante de pago subido por el estudiante. El archivo vive en el disco privado. */
#[Fillable(['path', 'nombre_original', 'mime', 'tamano'])]
class Comprobante extends Model
{
    public const DISK = 'local';

    public const DIRECTORIO = 'comprobantes';

    protected $attributes = [
        'estado' => 'pendiente',
    ];

    protected function casts(): array
    {
        return [
            'estado' => EstadoComprobante::class,
            'revisado_at' => 'datetime',
            'tamano' => 'integer',
        ];
    }

    public function inscripcion(): BelongsTo
    {
        return $this->belongsTo(Inscripcion::class);
    }

    public function revisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revisado_por');
    }
}
