<?php

namespace App\Http\Requests;

use App\Enums\TipoRequisito;
use App\Models\Evento;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Exige una respuesta por cada requisito de texto del evento.
 */
class InscribirseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('inscribirse', $this->evento());
    }

    public function rules(): array
    {
        $rules = ['respuestas' => ['array']];

        foreach ($this->evento()->requisitos->where('tipo', TipoRequisito::Texto) as $requisito) {
            $rules["respuestas.{$requisito->id}"] = ['required', 'string', 'min:3', 'max:2000'];
        }

        return $rules;
    }

    public function attributes(): array
    {
        return $this->evento()->requisitos
            ->mapWithKeys(fn ($requisito) => ["respuestas.{$requisito->id}" => mb_strtolower($requisito->descripcion)])
            ->all();
    }

    public function evento(): Evento
    {
        return $this->route('evento')->loadMissing(['requisitos', 'categorias']);
    }
}
