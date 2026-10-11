<?php

namespace App\Policies;

use App\Models\Inscripcion;
use App\Models\User;

class InscripcionPolicy
{
    public function view(User $user, Inscripcion $inscripcion): bool
    {
        return $user->id === $inscripcion->user_id || $user->esAdmin();
    }

    /** Solo el dueño, y solo mientras la inscripción espera el pago. */
    public function subirComprobante(User $user, Inscripcion $inscripcion): bool
    {
        return $user->id === $inscripcion->user_id && $inscripcion->estado->puedeSubirComprobante();
    }

    /** Descargar comprobantes: el dueño o un administrador. */
    public function verComprobante(User $user, Inscripcion $inscripcion): bool
    {
        return $this->view($user, $inscripcion);
    }
}
