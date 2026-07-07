<?php

namespace App\Http\Requests\Docente;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubirPortafolioDocenteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_curso' => ['required', 'integer', 'exists:cursos,id_curso'],
            'tipo' => ['required', 'string', Rule::in(['SILABO', 'PLAN_SESION', 'EVIDENCIA'])],
            'titulo' => ['required', 'string', 'max:180'],
            'documento' => [
                'required',
                'file',
                'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png',
                'max:10240',
            ],
        ];
    }
}
