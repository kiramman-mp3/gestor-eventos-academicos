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

    /**
     * Valida si es admisible transicionar hacia el nuevo estado.
     */
    public function puedeTransicionarA(self $nuevo): bool
    {
        if ($this === $nuevo) {
            return true;
        }

        return array_key_exists($nuevo->value, $this->transicionesValidas());
    }

    /**
     * Transiciones disponibles desde este estado: [estado_destino_value => etiqueta_accion]
     *
     * @return array<string, string>
     */
    public function transicionesValidas(): array
    {
        return match ($this) {
            self::Borrador => [
                self::Abierto->value => 'Publicar',
                self::Cancelado->value => 'Cancelar',
            ],
            self::Abierto => [
                self::EnEjecucion->value => 'Marcar en ejecución',
                self::Finalizado->value => 'Finalizar evento',
                self::Borrador->value => 'Volver a borrador',
                self::Cancelado->value => 'Cancelar',
            ],
            self::EnEjecucion => [
                self::Finalizado->value => 'Finalizar evento',
                self::Cancelado->value => 'Cancelar',
            ],
            self::Finalizado, self::Cancelado => [],
        };
    }
}
