<?php

namespace App\Policies;

use App\Enums\RolUsuario;
use App\Models\Evento;
use App\Models\User;

class EventoPolicy
{
    /** Catálogo: eventos visibles y dirigidos al usuario (público o su carrera). */
    public function view(?User $user, Evento $evento): bool
    {
        if ($user?->esAdmin() || ($user && $evento->docente_id === $user->id)) {
            return true;
        }

        return $evento->estado->esVisible()
            && ($evento->es_publico || ($user?->categoria_id && $evento->categorias->contains('id', $user->categoria_id)));
    }

    /** Solo estudiantes verificados, en periodo de inscripción y dentro de los destinatarios. */
    public function inscribirse(User $user, Evento $evento): bool
    {
        return $user->rol === RolUsuario::Estudiante
            && $user->hasVerifiedEmail()
            && $evento->inscripcionAbierta()
            && $evento->admiteA($user);
    }

    /** Registrar notas y asistencia: administradores o el docente asignado. */
    public function calificar(User $user, Evento $evento): bool
    {
        return $user->esAdmin() || ($user->esDocente() && $evento->docente_id === $user->id);
    }
}
