<?php

namespace App\Http\Requests\Director;

use App\Models\ConfiguracionSistema;
use Illuminate\Foundation\Http\FormRequest;

class UpdateConfiguracionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'valores' => ['required', 'array'],
            'valores.*' => ['required', 'string', 'max:255'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $clavesValidas = ConfiguracionSistema::pluck('clave')->all();

            foreach (array_keys($this->input('valores', [])) as $clave) {
                if (! in_array($clave, $clavesValidas, true)) {
                    $validator->errors()->add("valores.{$clave}", 'La clave de configuración no existe.');
                }
            }
        });
    }
}
