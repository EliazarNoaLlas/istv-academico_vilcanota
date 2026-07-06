<?php

namespace App\Http\Requests\Portafolios;

use Illuminate\Foundation\Http\FormRequest;

class UploadPortafolioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'documento' => [
                'required',
                'file',
                'mimes:pdf,doc,docx,xls,xlsx,txt,csv,md,json,html',
                'max:10240',
            ],
            'id_curso' => ['required', 'integer', 'exists:cursos,id_curso'],
            'id_periodo' => ['required', 'integer', 'exists:periodos_academicos,id_periodo'],
            'tipo' => [
                'required',
                'string',
                'in:SILABO,PLAN_SESION,EVALUACION,INSTRUMENTO,ASISTENCIA,NOTAS,EVIDENCIA,ACTA,OTRO',
            ],
            'titulo' => ['required', 'string', 'max:180'],
        ];
    }
}
