<?php

namespace App\Http\Requests\Director;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUsuarioEstadoRequest extends FormRequest
{
    public function authorize(): bool
    {
        // El Director no activa, desactiva ni bloquea a otros Directores desde este modulo.
        return $this->route('usuario')?->rol?->codigo !== 'director';
    }

    public function rules(): array
    {
        return [
            'estado' => ['required', Rule::in(['ACTIVO', 'INACTIVO', 'BLOQUEADO'])],
            'motivo' => ['required', 'string', 'min:5', 'max:255'],
        ];
    }
}
