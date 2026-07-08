<?php

namespace App\Http\Requests\Docentes;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AsignarCursosRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cursos' => ['required', 'array', 'min:1'],
            'cursos.*' => [Rule::exists('cursos', 'id_curso')],
        ];
    }
}
