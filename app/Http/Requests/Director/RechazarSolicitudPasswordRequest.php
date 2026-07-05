<?php

namespace App\Http\Requests\Director;

use Illuminate\Foundation\Http\FormRequest;

class RechazarSolicitudPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'motivo_rechazo' => ['required', 'string', 'min:5', 'max:255'],
        ];
    }
}
