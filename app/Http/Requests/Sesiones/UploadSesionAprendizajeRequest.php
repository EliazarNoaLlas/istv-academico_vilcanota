<?php

namespace App\Http\Requests\Sesiones;

use Illuminate\Foundation\Http\FormRequest;

class UploadSesionAprendizajeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_curso' => ['required', 'integer', 'exists:cursos,id_curso'],
            'titulo' => ['required', 'string', 'max:255'],
            'numero_sesion' => ['nullable', 'integer', 'min:1'],
            'archivo' => [
                'required',
                'file',
                'mimes:pdf,doc,docx,xls,xlsx,txt,csv,md,json,html',
                'max:10240',
            ],
        ];
    }
}
