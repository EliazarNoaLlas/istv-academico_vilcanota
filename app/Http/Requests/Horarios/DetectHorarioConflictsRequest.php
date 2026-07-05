<?php

namespace App\Http\Requests\Horarios;

use Illuminate\Foundation\Http\FormRequest;

class DetectHorarioConflictsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'horarios' => ['required', 'array'],
            'horarios.*.id_curso' => ['required', 'integer', 'exists:cursos,id_curso'],
            'horarios.*.id_docente' => ['required', 'integer', 'exists:docentes,id_docente'],
            'horarios.*.dia' => ['required', 'string', 'in:Lunes,Martes,Miércoles,Jueves,Viernes,Sábado'],
            'horarios.*.hora_inicio' => ['required', 'date_format:H:i'],
            'horarios.*.hora_fin' => ['required', 'date_format:H:i', 'after:horarios.*.hora_inicio'],
            'horarios.*.aula' => ['nullable', 'string', 'max:80'],
        ];
    }
}
