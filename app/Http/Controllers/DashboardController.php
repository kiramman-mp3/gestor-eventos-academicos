<?php

namespace App\Http\Controllers;

use App\Enums\RolUsuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** Envía a cada usuario a su página principal según el rol. */
class DashboardController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        return match ($request->user()->rol) {
            RolUsuario::Administrador => redirect()->route('admin.dashboard'),
            RolUsuario::Estudiante => redirect()->route('inscripciones.index'),
            default => redirect()->route('home'),
        };
    }
}
