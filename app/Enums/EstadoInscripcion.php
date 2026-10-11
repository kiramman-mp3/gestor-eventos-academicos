<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

/**
 * Ciclo de vida de una inscripción.
 *
 *  EnEsperaPago --(sube comprobante)--> PendienteAprobacion --(admin aprueba)--> Pagado
 *        ^                                       |
 *        +------------(admin rechaza)------------+
 *
 *  Un evento gratuito pasa directo de la inscripción a Pagado.
 *  Error = inscripción anulada (no ocupa cupo).
 */
enum EstadoInscripcion: string
{
    use HasValues;

    case EnEsperaPago = 'en_espera_pago';
    case PendienteAprobacion = 'pendiente_aprobacion';
    case Pagado = 'pagado';
    case Error = 'error';

    public function label(): string
    {
        return match ($this) {
            self::EnEsperaPago => 'En espera de pago',
            self::PendienteAprobacion => 'Pendiente de aprobación',
            self::Pagado => 'Pagado',
            self::Error => 'Anulada',
        };
    }

    /**
     * Estados que consumen un cupo del evento. Única fuente de verdad para el conteo de cupos.
     *
     * @return list<self>
     */
    public static function ocupanCupo(): array
    {
        return [self::EnEsperaPago, self::PendienteAprobacion, self::Pagado];
    }

    public function ocupaCupo(): bool
    {
        return in_array($this, self::ocupanCupo(), true);
    }

    public function puedeSubirComprobante(): bool
    {
        return $this === self::EnEsperaPago;
    }

    public function puedeTransicionarA(self $nuevo): bool
    {
        return match ($this) {
            self::EnEsperaPago => in_array($nuevo, [self::PendienteAprobacion, self::Pagado, self::Error], true),
            self::PendienteAprobacion => in_array($nuevo, [self::Pagado, self::EnEsperaPago, self::Error], true),
            self::Pagado, self::Error => false,
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::EnEsperaPago => 'amber',
            self::PendienteAprobacion => 'sky',
            self::Pagado => 'emerald',
            self::Error => 'rose',
        };
    }
}
