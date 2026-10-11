<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum Prioridad: string
{
    use HasValues;

    case Baja = 'baja';
    case Media = 'media';
    case Alta = 'alta';
    case Critica = 'critica';

    public function label(): string
    {
        return match ($this) {
            self::Baja => 'Baja',
            self::Media => 'Media',
            self::Alta => 'Alta',
            self::Critica => 'Crítica',
        };
    }
}
