<?php

namespace App\Policies;

use App\Models\SolicitudCambio;
use App\Models\User;

class SolicitudCambioPolicy
{
    public function view(User $user, SolicitudCambio $solicitud): bool
    {
        return $user->id === $solicitud->user_id || $user->esAdmin();
    }

    public function resolver(User $user, SolicitudCambio $solicitud): bool
    {
        return $user->esAdmin();
    }
}
