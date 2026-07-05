<?php

namespace App\Http\Requests\Horarios;

use Illuminate\Foundation\Http\FormRequest;

class GenerateHorarioIaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_programa' => ['required', 'integer', 'exists:programas_estudio,id_programa'],
            'id_periodo' => ['required', 'integer', 'exists:periodos_academicos,id_periodo'],
            'semestre' => ['nullable', 'string', 'in:I,II,III,IV,V,VI'],
            'provider' => ['nullable', 'string', 'in:gemini,grok,fake'],
            'modo' => ['nullable', 'string', 'in:borrador,guardar_si_valido'],
            'max_intentos_reparacion' => ['nullable', 'integer', 'min:0', 'max:100'],
        ];
    }
}
