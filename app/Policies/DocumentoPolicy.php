<?php

namespace App\Policies;

use App\Models\Documento;
use App\Models\User;

class DocumentoPolicy
{
    /** Descargar: el dueño o un administrador (para revisar requisitos). */
    public function view(User $user, Documento $documento): bool
    {
        return $user->id === $documento->user_id || $user->esAdmin();
    }

    public function delete(User $user, Documento $documento): bool
    {
        return $user->id === $documento->user_id;
    }
}
