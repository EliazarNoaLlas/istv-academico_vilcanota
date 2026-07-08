<?php

namespace App\Http\Requests\Horarios;

use Illuminate\Foundation\Http\FormRequest;

class GenerateSemestresPendientesDsiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_programa' => ['required', 'integer', 'exists:programas_estudio,id_programa'],
            'id_periodo' => ['required', 'integer', 'exists:periodos_academicos,id_periodo'],
        ];
    }
}
