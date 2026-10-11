<?php

namespace App\Models;

use App\Enums\EstadoComprobante;
use App\Enums\EstadoInscripcion;
use Database\Factories\InscripcionFactory;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Inscripción de un usuario en un evento.
 * Sin atributos asignables en masa: el estado, la nota y el certificado se cambian
 * solo a través de InscripcionService / NotaController.
 */
#[Table('inscripciones')]
class Inscripcion extends Model
{
    /** @use HasFactory<InscripcionFactory> */
    use HasFactory;

    protected $attributes = [
        'estado' => 'en_espera_pago',
        'legalizado' => false,
    ];

    protected function casts(): array
    {
        return [
            'estado' => EstadoInscripcion::class,
            'nota' => 'decimal:2',
            'asistencia' => 'decimal:2',
            'legalizado' => 'boolean',
            'certificado_emitido_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function evento(): BelongsTo
    {
        return $this->belongsTo(Evento::class);
    }

    public function respuestas(): HasMany
    {
        return $this->hasMany(RespuestaRequisito::class);
    }

    public function comprobantes(): HasMany
    {
        return $this->hasMany(Comprobante::class)->latest();
    }

    public function comprobanteVigente(): HasOne
    {
        return $this->hasOne(Comprobante::class)->latestOfMany();
    }

    public function comprobantePendiente(): HasOne
    {
        return $this->hasOne(Comprobante::class)->ofMany(['id' => 'max'], fn ($q) => $q->where('estado', EstadoComprobante::Pendiente));
    }

    public function cumpleNota(): bool
    {
        $evento = $this->evento;

        if (! $evento->requiere_nota) {
            return true;
        }

        $minima = (float) ($evento->nota_minima ?? config('fisei.nota_minima_defecto', 7.0));

        return $this->nota !== null && (float) $this->nota >= $minima;
    }

    public function cumpleAsistencia(): bool
    {
        $evento = $this->evento;

        if (! $evento->requiere_asistencia) {
            return true;
        }

        $minima = (float) ($evento->asistencia_minima ?? config('fisei.asistencia_minima_defecto', 70.0));

        return $this->asistencia !== null && (float) $this->asistencia >= $minima;
    }

    /** Pago aprobado y requisitos académicos cumplidos. La política agrega la comprobación de propiedad. */
    public function aptaParaCertificado(): bool
    {
        return $this->estado === EstadoInscripcion::Pagado
            && $this->cumpleNota()
            && $this->cumpleAsistencia();
    }
}
