<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Comprobante de pago: PDF o imagen, validado por su contenido real (finfo), no por la extensión.
 */
class SubirComprobanteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('subirComprobante', $this->route('inscripcion'));
    }

    public function rules(): array
    {
        return [
            'comprobante' => [
                'required',
                'file',
                'mimes:'.implode(',', config('fisei.uploads.documento_mimes')),
                'max:'.config('fisei.uploads.comprobante_max_kb'),
            ],
        ];
    }
}
