<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TipoContenido;
use App\Http\Controllers\Controller;
use App\Http\Requests\GuardarContenidoRequest;
use App\Models\Contenido;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/** Editor de los bloques de la página de inicio (antes administrar_pagina/). */
class ContenidoController extends Controller
{
    private const DIRECTORIO = 'contenidos';

    public function index(): View
    {
        return view('admin.contenidos.index', [
            'tipos' => TipoContenido::cases(),
            'contenidos' => Contenido::query()->orderBy('orden')->orderBy('id')->get()->groupBy(fn (Contenido $c) => $c->tipo->value),
        ]);
    }

    public function store(GuardarContenidoRequest $request): RedirectResponse
    {
        $tipo = TipoContenido::from($request->validated('tipo'));

        abort_if($tipo->esUnico() && Contenido::query()->where('tipo', $tipo)->exists(), 422, "{$tipo->label()} ya existe: edítala.");

        $contenido = new Contenido($request->safe()->except('imagen'));
        if ($request->hasFile('imagen')) {
            $contenido->imagen_path = $request->file('imagen')->store(self::DIRECTORIO, 'public');
        }
        $contenido->save();

        return back()->with('status', "{$tipo->label()}: elemento agregado.");
    }

    public function update(GuardarContenidoRequest $request, Contenido $contenido): RedirectResponse
    {
        $anterior = $contenido->imagen_path;

        $contenido->fill($request->safe()->except('imagen'));
        if ($request->hasFile('imagen')) {
            $contenido->imagen_path = $request->file('imagen')->store(self::DIRECTORIO, 'public');
        }
        $contenido->save();

        if ($anterior && $anterior !== $contenido->imagen_path) {
            Storage::disk('public')->delete($anterior);
        }

        return back()->with('status', 'Contenido actualizado.');
    }

    public function destroy(Contenido $contenido): RedirectResponse
    {
        $contenido->delete();

        if ($contenido->imagen_path) {
            Storage::disk('public')->delete($contenido->imagen_path);
        }

        return back()->with('status', 'Contenido eliminado.');
    }
}
