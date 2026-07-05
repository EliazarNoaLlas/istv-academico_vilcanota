<?php

namespace App\Http\Requests\Horarios;

use Illuminate\Foundation\Http\FormRequest;

class ClearHorarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'filtro_docente' => ['nullable', 'integer', 'exists:docentes,id_docente'],
            'filtro_semestre' => ['nullable', 'string', 'in:I,II,III,IV,V,VI'],
            'filtro_programa' => ['nullable', 'integer', 'exists:programas_estudio,id_programa'],
            'confirmar' => ['required', 'accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'confirmar.accepted' => 'Debe confirmar la limpieza del horario antes de continuar.',
        ];
    }
}
