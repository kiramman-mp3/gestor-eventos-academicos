<?php

namespace App\Models;

use Database\Factories\CategoriaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Carrera o área académica de la FISEI. */
#[Fillable(['nombre', 'descripcion'])]
class Categoria extends Model
{
    /** @use HasFactory<CategoriaFactory> */
    use HasFactory;

    public function eventos(): BelongsToMany
    {
        return $this->belongsToMany(Evento::class);
    }

    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
