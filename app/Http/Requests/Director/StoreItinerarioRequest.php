<?php

namespace App\Http\Requests\Director;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreItinerarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_programa' => ['required', 'integer', Rule::exists('programas_estudio', 'id_programa')],
            'codigo' => ['required', 'string', 'max:50'],
            'nombre' => ['required', 'string', 'max:180'],
            'resolucion_oficio' => ['nullable', 'string', 'max:180'],
            'descripcion' => ['nullable', 'string'],
            'duracion_ciclos' => ['nullable', 'integer', 'min:1', 'max:10'],
            'version' => ['nullable', 'string', 'max:30'],
            'estado' => ['nullable', Rule::in(['BORRADOR', 'ACTIVO', 'ARCHIVADO'])],
        ];
    }
}
