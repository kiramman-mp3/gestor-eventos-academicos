<?php

namespace App\Models;

use App\Enums\TipoContenido;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/** Bloque editable de la página de inicio (carrusel, autoridades, reseñas, misión...). */
#[Fillable(['tipo', 'titulo', 'subtitulo', 'cuerpo', 'orden'])]
class Contenido extends Model
{
    protected function casts(): array
    {
        return [
            'tipo' => TipoContenido::class,
            'orden' => 'integer',
        ];
    }

    #[Scope]
    protected function deTipo(Builder $query, TipoContenido $tipo): void
    {
        $query->where('tipo', $tipo)->orderBy('orden')->orderBy('id');
    }

    protected function imagenUrl(): Attribute
    {
        return Attribute::get(fn () => $this->imagen_path ? Storage::disk('public')->url($this->imagen_path) : null);
    }
}
