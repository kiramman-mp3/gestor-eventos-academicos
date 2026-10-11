<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TipoEvento;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TipoEventoController extends Controller
{
    public function index(): View
    {
        return view('admin.catalogos.index', [
            'titulo' => 'Tipos de evento',
            'descripcion' => 'Clasificación de los eventos: curso, webinar, congreso, taller…',
            'ruta' => 'admin.tipos-evento',
            'items' => TipoEvento::query()->withCount('eventos')->orderBy('nombre')->get()
                ->map(fn (TipoEvento $t) => [$t, "{$t->eventos_count} eventos", $t->eventos_count === 0]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        TipoEvento::create($this->validar($request));

        return back()->with('status', 'Tipo de evento creado.');
    }

    public function update(Request $request, TipoEvento $tipoEvento): RedirectResponse
    {
        $tipoEvento->update($this->validar($request, $tipoEvento));

        return back()->with('status', 'Tipo de evento actualizado.');
    }

    public function destroy(TipoEvento $tipoEvento): RedirectResponse
    {
        if ($tipoEvento->eventos()->withTrashed()->exists()) {
            return back()->with('error', 'No se puede eliminar: hay eventos de este tipo.');
        }

        $tipoEvento->delete();

        return back()->with('status', 'Tipo de evento eliminado.');
    }

    private function validar(Request $request, ?TipoEvento $tipo = null): array
    {
        return $request->validate([
            'nombre' => ['required', 'string', 'max:50', Rule::unique('tipos_evento')->ignore($tipo)],
        ]);
    }
}
