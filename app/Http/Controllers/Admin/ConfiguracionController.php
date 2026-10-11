<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Configuracion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConfiguracionController extends Controller
{
    public function edit(): View
    {
        return view('admin.configuracion.edit', [
            'claves' => Configuracion::CLAVES,
            'valores' => Configuracion::query()->pluck('valor', 'clave'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        Configuracion::guardar($request->validate([
            'nombre_institucion' => ['required', 'string', 'max:255'],
            'email_contacto' => ['nullable', 'email', 'max:255'],
            'telefono_contacto' => ['nullable', 'string', 'max:30'],
            'direccion' => ['nullable', 'string', 'max:255'],
        ]));

        return back()->with('status', 'Configuración guardada.');
    }
}
