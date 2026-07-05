<?php

namespace App\Http\Requests\Cursos;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCursoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre_curso' => ['required', 'string', 'max:150'],
            'modulo' => ['required', 'string', 'max:100'],
            'semestre' => ['required', 'string', 'max:10'],
            'creditos' => ['required', 'integer', 'min:0'],
            'horas_teoria' => ['required', 'integer', 'min:0'],
            'horas_practica' => ['required', 'integer', 'min:0'],
            'horas_ud' => ['required', 'integer', 'min:0'],
            'total_teoria' => ['required', 'integer', 'min:0'],
            'total_practica' => ['required', 'integer', 'min:0'],
            'total_horas' => ['required', 'integer', 'min:0'],
            'id_docente' => ['nullable', Rule::exists('docentes', 'id_docente')],
            'id_programa' => ['nullable', Rule::exists('programas_estudio', 'id_programa')],
            'estado' => ['required', 'string', Rule::in(['ACTIVO', 'INACTIVO', 'ARCHIVADO'])],
        ];
    }
}
