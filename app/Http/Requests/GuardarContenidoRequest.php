<?php

namespace App\Http\Requests;

use App\Enums\TipoContenido;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GuardarContenidoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->esAdmin();
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['orden' => (int) $this->input('orden', 0)]);
    }

    public function rules(): array
    {
        $tipo = $this->route('contenido')?->tipo ?? TipoContenido::tryFrom((string) $this->input('tipo'));
        $campos = $tipo?->campos() ?? [];

        return [
            'tipo' => [$this->route('contenido') ? 'prohibited' : 'required', Rule::enum(TipoContenido::class)],
            'titulo' => [isset($campos['titulo']) ? 'required' : 'prohibited', 'nullable', 'string', 'max:255'],
            'subtitulo' => [isset($campos['subtitulo']) ? 'required' : 'prohibited', 'nullable', 'string', 'max:255'],
            'cuerpo' => [isset($campos['cuerpo']) ? 'required' : 'prohibited', 'nullable', 'string', 'max:3000'],
            'orden' => ['nullable', 'integer', 'min:0', 'max:999'],
            'imagen' => [
                $tipo?->admiteImagen() ? 'nullable' : 'prohibited',
                'image',
                'mimes:'.implode(',', config('fisei.uploads.imagen_mimes')),
                'max:'.config('fisei.uploads.imagen_max_kb'),
            ],
        ];
    }
}
