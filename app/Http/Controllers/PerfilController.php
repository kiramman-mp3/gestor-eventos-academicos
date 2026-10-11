<?php

namespace App\Http\Controllers;

use App\Enums\TipoDocumento;
use App\Http\Requests\ActualizarPerfilRequest;
use App\Models\Categoria;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class PerfilController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user()->load(['categoria', 'documentos']);

        return view('perfil.edit', [
            'user' => $user,
            'categorias' => Categoria::query()->orderBy('nombre')->pluck('nombre', 'id'),
            'tiposDocumento' => TipoDocumento::cases(),
        ]);
    }

    public function update(ActualizarPerfilRequest $request): RedirectResponse
    {
        $request->user()->update($request->validated());

        return back()->with('status', 'Perfil actualizado.');
    }

    public function actualizarPassword(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('password', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $request->user()->update(['password' => $validated['password']]);

        return back()->with('status', 'Contraseña actualizada.');
    }
}
