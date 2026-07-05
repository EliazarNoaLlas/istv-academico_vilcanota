<?php

namespace App\Http\Requests\Cursos;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCursoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre_curso' => ['sometimes', 'string', 'max:150'],
            'modulo' => ['sometimes', 'string', 'max:100'],
            'semestre' => ['sometimes', 'string', 'max:10'],
            'creditos' => ['sometimes', 'integer', 'min:0'],
            'horas_teoria' => ['sometimes', 'integer', 'min:0'],
            'horas_practica' => ['sometimes', 'integer', 'min:0'],
            'horas_ud' => ['sometimes', 'integer', 'min:0'],
            'total_teoria' => ['sometimes', 'integer', 'min:0'],
            'total_practica' => ['sometimes', 'integer', 'min:0'],
            'total_horas' => ['sometimes', 'integer', 'min:0'],
            'id_docente' => ['nullable', Rule::exists('docentes', 'id_docente')],
            'id_programa' => ['nullable', Rule::exists('programas_estudio', 'id_programa')],
            'estado' => ['sometimes', 'string', Rule::in(['ACTIVO', 'INACTIVO', 'ARCHIVADO'])],
        ];
    }
}
