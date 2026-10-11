<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RolUsuario;
use App\Enums\TipoUsuario;
use App\Http\Controllers\Controller;
use App\Http\Requests\CrearUsuarioRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UsuarioController extends Controller
{
    public function index(Request $request): View
    {
        $rol = RolUsuario::tryFrom((string) $request->query('rol'));

        $usuarios = User::query()
            ->with('categoria')
            ->withCount('inscripciones')
            ->when($rol, fn ($q) => $q->where('rol', $rol))
            ->when($request->query('q'), fn ($q, $texto) => $q->where(fn ($w) => $w
                ->where('nombre', 'like', "%{$texto}%")
                ->orWhere('apellido', 'like', "%{$texto}%")
                ->orWhere('email', 'like', "%{$texto}%")
                ->orWhere('cedula', 'like', "%{$texto}%")))
            ->orderBy('apellido')
            ->paginate(20)
            ->withQueryString();

        return view('admin.usuarios.index', ['usuarios' => $usuarios, 'rol' => $rol]);
    }

    public function create(): View
    {
        return view('admin.usuarios.create');
    }

    public function store(CrearUsuarioRequest $request): RedirectResponse
    {
        $datos = $request->safe()->except('rol');

        $user = new User([...$datos, 'tipo' => TipoUsuario::desdeEmail($datos['email'])]);
        $user->rol = RolUsuario::from($request->validated('rol'));
        $user->email_verified_at = now();
        $user->save();

        return redirect()->route('admin.usuarios.index')->with('status', "{$user->rol->label()} {$user->nombre_completo} creado.");
    }

    public function cambiarRol(Request $request, User $usuario): RedirectResponse
    {
        $rol = RolUsuario::from($request->validate(['rol' => ['required', Rule::enum(RolUsuario::class)]])['rol']);

        $this->protegerCuentaPropiaYUltimoAdmin($request->user(), $usuario, $rol !== RolUsuario::Administrador);

        $usuario->rol = $rol;
        $usuario->save();

        return back()->with('status', "Rol de {$usuario->nombre_completo} actualizado a {$rol->label()}.");
    }

    public function alternarActivo(Request $request, User $usuario): RedirectResponse
    {
        $this->protegerCuentaPropiaYUltimoAdmin($request->user(), $usuario, $usuario->activo);

        $usuario->activo = ! $usuario->activo;
        $usuario->save();

        return back()->with('status', $usuario->activo ? 'Cuenta activada.' : 'Cuenta desactivada: se cerrarán sus sesiones.');
    }

    public function destroy(Request $request, User $usuario): RedirectResponse
    {
        $this->protegerCuentaPropiaYUltimoAdmin($request->user(), $usuario, true);

        $usuario->delete();

        return back()->with('status', 'Usuario eliminado.');
    }

    /** Nadie puede quitarse privilegios a sí mismo ni dejar el sistema sin administradores activos. */
    private function protegerCuentaPropiaYUltimoAdmin(User $actual, User $objetivo, bool $quitaAcceso): void
    {
        abort_if($actual->is($objetivo), 403, 'No puedes modificar tu propia cuenta desde aquí.');

        $esUltimoAdmin = $objetivo->esAdmin()
            && User::query()->where('rol', RolUsuario::Administrador)->where('activo', true)->count() <= 1;

        abort_if($quitaAcceso && $esUltimoAdmin, 403, 'Debe existir al menos un administrador activo.');
    }
}
