<?php

namespace App\Http\Requests\Director;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GenerateReporteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tipo' => ['required', Rule::in([
                'CURSOS', 'DOCENTES', 'ESTUDIANTES', 'HORARIOS', 'NOTAS',
                'PORTAFOLIO', 'CONSOLIDADO', 'IA_PREDICTIVA',
            ])],
            'formato' => ['required', Rule::in(['PDF', 'EXCEL', 'CSV'])],
        ];
    }
}
