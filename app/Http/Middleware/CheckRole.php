<?php

namespace App\Http\Middleware;

use App\Enums\RolUsuario;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Verifica que el usuario autenticado tenga alguno de los roles indicados.
 *
 * Uso en rutas: ->middleware('role:administrador') o ->middleware('role:administrador,docente')
 */
class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->guest(route('login'));
        }

        $permitidos = array_map(fn (string $rol) => RolUsuario::from($rol), $roles);

        abort_unless($user->tieneRol(...$permitidos), Response::HTTP_FORBIDDEN, 'No tienes permiso para acceder a esta sección.');

        return $next($request);
    }
}
