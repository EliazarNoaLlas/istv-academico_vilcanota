<?php

namespace App\Http\Requests\Docente;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GuardarAsistenciaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_curso' => ['required', 'integer', 'exists:cursos,id_curso'],
            'fecha' => ['required', 'date'],
            'tema' => ['nullable', 'string', 'max:180'],
            'registros' => ['required', 'array', 'min:1'],
            'registros.*.id_estudiante' => ['required', 'integer', 'exists:estudiantes,id_estudiante'],
            'registros.*.estado' => ['required', 'string', Rule::in(['PRESENTE', 'TARDANZA', 'AUSENTE', 'JUSTIFICADO'])],
            'registros.*.observacion' => ['nullable', 'string', 'max:255'],
        ];
    }
}
