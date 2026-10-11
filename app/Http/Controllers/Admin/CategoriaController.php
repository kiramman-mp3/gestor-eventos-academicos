<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Carreras / áreas académicas. */
class CategoriaController extends Controller
{
    public function index(): View
    {
        return view('admin.catalogos.index', [
            'titulo' => 'Carreras',
            'descripcion' => 'Carreras de la FISEI: los estudiantes institucionales pertenecen a una y los eventos pueden dirigirse a varias.',
            'ruta' => 'admin.categorias',
            'items' => Categoria::query()->withCount(['eventos', 'usuarios'])->orderBy('nombre')->get()
                ->map(fn (Categoria $c) => [$c, "{$c->eventos_count} eventos · {$c->usuarios_count} usuarios", $c->eventos_count + $c->usuarios_count === 0]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Categoria::create($this->validar($request));

        return back()->with('status', 'Carrera creada.');
    }

    public function update(Request $request, Categoria $categoria): RedirectResponse
    {
        $categoria->update($this->validar($request, $categoria));

        return back()->with('status', 'Carrera actualizada.');
    }

    public function destroy(Categoria $categoria): RedirectResponse
    {
        if ($categoria->eventos()->withTrashed()->exists() || $categoria->usuarios()->withTrashed()->exists()) {
            return back()->with('error', 'No se puede eliminar: hay eventos o usuarios asociados.');
        }

        $categoria->delete();

        return back()->with('status', 'Carrera eliminada.');
    }

    private function validar(Request $request, ?Categoria $categoria = null): array
    {
        return $request->validate([
            'nombre' => ['required', 'string', 'max:100', Rule::unique('categorias')->ignore($categoria)],
        ]);
    }
}
