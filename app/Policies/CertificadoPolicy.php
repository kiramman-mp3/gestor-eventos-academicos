<?php

namespace App\Policies;

use App\Enums\EstadoEvento;
use App\Models\Inscripcion;
use App\Models\User;

/**
 * Autoriza la descarga del certificado de una inscripción.
 * Exige: dueño (o administrador), pago aprobado, nota y asistencia mínimas cumplidas
 * y el evento finalizado por la administración.
 */
class CertificadoPolicy
{
    public function descargar(User $user, Inscripcion $inscripcion): bool
    {
        if ($user->id !== $inscripcion->user_id && ! $user->esAdmin()) {
            return false;
        }

        return $inscripcion->evento->estado === EstadoEvento::Finalizado
            && $inscripcion->aptaParaCertificado();
    }
}
