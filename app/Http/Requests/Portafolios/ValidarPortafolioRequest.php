<?php

namespace App\Http\Requests\Portafolios;

use Illuminate\Foundation\Http\FormRequest;

class ValidarPortafolioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'estado' => ['required', 'string', 'in:APROBADO,OBSERVADO,EN_REVISION'],
            'observacion' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
