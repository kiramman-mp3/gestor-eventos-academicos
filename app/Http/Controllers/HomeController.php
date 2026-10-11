<?php

namespace App\Http\Controllers;

use App\Enums\EstadoEvento;
use App\Enums\TipoContenido;
use App\Models\Contenido;
use App\Models\Evento;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(Request $request): View
    {
        $contenidos = Contenido::query()->orderBy('orden')->orderBy('id')->get()->groupBy(fn (Contenido $c) => $c->tipo->value);

        $proximos = Evento::query()
            ->visiblesPara($request->user())
            ->where('estado', EstadoEvento::Abierto)
            ->with('tipoEvento')
            ->conCuposOcupados()
            ->orderBy('fecha_inicio')
            ->limit(3)
            ->get();

        return view('home', [
            'carrusel' => $contenidos->get(TipoContenido::Carrusel->value, collect()),
            'nosotros' => $contenidos->get(TipoContenido::Nosotros->value, collect()),
            'autoridades' => $contenidos->get(TipoContenido::Autoridad->value, collect()),
            'resenas' => $contenidos->get(TipoContenido::Resena->value, collect()),
            'mision' => $contenidos->get(TipoContenido::Mision->value)?->first(),
            'vision' => $contenidos->get(TipoContenido::Vision->value)?->first(),
            'proximos' => $proximos,
        ]);
    }
}
