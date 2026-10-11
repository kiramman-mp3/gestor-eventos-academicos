<?php

namespace App\Http\Requests;

use App\Enums\TipoDocumento;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Documento personal: `mimes` valida el tipo real del contenido (finfo), no la extensión enviada.
 */
class SubirDocumentoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'tipo' => ['required', Rule::enum(TipoDocumento::class)],
            'archivo' => [
                'required',
                'file',
                'mimes:'.implode(',', config('fisei.uploads.documento_mimes')),
                'max:'.config('fisei.uploads.documento_max_kb'),
            ],
        ];
    }

    public function attributes(): array
    {
        return ['archivo' => 'documento'];
    }
}
