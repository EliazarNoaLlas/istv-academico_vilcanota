<?php

namespace App\Http\Requests\Docente;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GuardarNotaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_curso' => ['required', 'integer', 'exists:cursos,id_curso'],
            'unidad' => ['required', 'string', Rule::in(['I', 'II', 'III'])],
            'notas' => ['required', 'array', 'min:1'],
            'notas.*.id_matricula_curso' => ['required', 'integer', 'exists:matricula_cursos,id_matricula_curso'],
            'notas.*.practica' => ['nullable', 'numeric', 'between:0,20'],
            'notas.*.teoria' => ['nullable', 'numeric', 'between:0,20'],
            'notas.*.examen' => ['nullable', 'numeric', 'between:0,20'],
        ];
    }
}
