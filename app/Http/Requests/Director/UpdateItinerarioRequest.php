<?php

namespace App\Http\Requests\Director;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateItinerarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'codigo' => ['sometimes', 'required', 'string', 'max:50'],
            'nombre' => ['sometimes', 'required', 'string', 'max:180'],
            'resolucion_oficio' => ['nullable', 'string', 'max:180'],
            'descripcion' => ['nullable', 'string'],
            'duracion_ciclos' => ['nullable', 'integer', 'min:1', 'max:10'],
            'version' => ['nullable', 'string', 'max:30'],
            'estado' => ['nullable', Rule::in(['BORRADOR', 'ACTIVO', 'ARCHIVADO'])],
            'fecha_aprobacion' => ['nullable', 'date'],
        ];
    }
}
