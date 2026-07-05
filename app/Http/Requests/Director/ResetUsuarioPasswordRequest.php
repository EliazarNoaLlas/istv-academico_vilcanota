<?php

namespace App\Http\Requests\Director;

use Illuminate\Foundation\Http\FormRequest;

class ResetUsuarioPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        // El Director no puede restablecer la contraseña de otro Director.
        return $this->route('usuario')?->rol?->codigo !== 'director';
    }

    public function rules(): array
    {
        return [];
    }
}
