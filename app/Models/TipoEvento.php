<?php

namespace App\Models;

use Database\Factories\TipoEventoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Curso, webinar, congreso, etc. */
#[Table('tipos_evento')]
#[Fillable(['nombre'])]
class TipoEvento extends Model
{
    /** @use HasFactory<TipoEventoFactory> */
    use HasFactory;

    public function eventos(): HasMany
    {
        return $this->hasMany(Evento::class);
    }
}
