<?php

namespace App\Http\Requests\Docente;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CerrarActaRequest extends FormRequest
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
        ];
    }
}
