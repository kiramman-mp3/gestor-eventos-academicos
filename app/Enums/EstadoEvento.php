<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum EstadoEvento: string
{
    use HasValues;

    case Borrador = 'borrador';
    case Abierto = 'abierto';
    case EnEjecucion = 'en_ejecucion';
    case Finalizado = 'finalizado';
    case Cancelado = 'cancelado';

    public function label(): string
    {
        return match ($this) {
            self::Borrador => 'Borrador',
            self::Abierto => 'Abierto',
            self::EnEjecucion => 'En ejecución',
            self::Finalizado => 'Finalizado',
            self::Cancelado => 'Cancelado',
        };
    }

    /** Estados en los que el evento aparece en el catálogo público. */
    public function esVisible(): bool
    {
        return in_array($this, [self::Abierto, self::EnEjecucion, self::Finalizado], true);
    }

    public function color(): string
    {
        return match ($this) {
            self::Borrador => 'zinc',
            self::Abierto => 'emerald',
            self::EnEjecucion => 'sky',
            self::Finalizado => 'indigo',
            self::Cancelado => 'rose',
        };
    }
}
