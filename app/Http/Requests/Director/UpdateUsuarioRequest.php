<?php

namespace App\Http\Requests\Director;

use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        // El Director no administra a otros Directores desde este modulo.
        return $this->route('usuario')?->rol?->codigo !== 'director';
    }

    public function rules(): array
    {
        $usuario = $this->route('usuario');
        $esCoordinador = $this->rolSeleccionado()?->codigo === 'coordinador';

        return [
            'nombres' => ['required', 'string', 'max:120'],
            'apellidos' => ['nullable', 'string', 'max:120'],
            'usuario' => ['required', 'string', 'max:80', Rule::unique('usuarios', 'usuario')->ignore($usuario->id_usuario, 'id_usuario')],
            'correo' => ['required', 'email', 'max:150', Rule::unique('usuarios', 'correo')->ignore($usuario->id_usuario, 'id_usuario')],
            'id_rol' => ['required', Rule::exists('roles', 'id_rol')],
            'dni' => ['nullable', 'string', 'size:8', Rule::unique('usuarios', 'dni')->ignore($usuario->id_usuario, 'id_usuario')],
            'telefono' => ['nullable', 'string', 'max:20'],
            'id_programa' => [$esCoordinador ? 'required' : 'nullable', Rule::exists('programas_estudio', 'id_programa')],
        ];
    }

    private function rolSeleccionado(): ?Role
    {
        return $this->filled('id_rol') ? Role::find($this->input('id_rol')) : null;
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->filled('id_rol') && Role::find($this->input('id_rol'))?->codigo === 'director') {
                $validator->errors()->add('id_rol', 'No está permitido asignar el rol Director desde este módulo.');
            }
        });
    }
}
