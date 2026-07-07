<?php

namespace App\Http\Requests\Director;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateItinerarioUnidadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:180'],
            'codigo' => ['nullable', 'string', 'max:50'],
            'ciclo' => ['required', Rule::in(['I', 'II', 'III', 'IV', 'V', 'VI'])],
            'horas_teoricas_semanales' => ['required', 'integer', 'min:0', 'max:20'],
            'horas_practicas_semanales' => ['required', 'integer', 'min:0', 'max:20'],
            'id_bloque' => ['nullable', 'integer', Rule::exists('itinerario_bloques', 'id_bloque')],
            'observacion' => ['nullable', 'string'],
            'estado' => ['nullable', Rule::in(['ACTIVO', 'INACTIVO'])],
        ];
    }
}
