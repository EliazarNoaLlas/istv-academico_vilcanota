<?php

namespace App\Http\Requests\Director;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEstudianteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombres' => ['required', 'string', 'max:120'],
            'apellido_paterno' => ['required', 'string', 'max:80'],
            'apellido_materno' => ['nullable', 'string', 'max:80'],
            'dni' => ['required', 'string', 'size:8', 'unique:estudiantes,dni'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'id_programa' => ['required', 'integer', Rule::exists('programas_estudio', 'id_programa')],
            'ciclo' => ['required', Rule::in(['I', 'II', 'III', 'IV', 'V', 'VI'])],
        ];
    }
}
