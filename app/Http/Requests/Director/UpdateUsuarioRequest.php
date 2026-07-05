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

        return [
            'nombres' => ['required', 'string', 'max:120'],
            'apellidos' => ['nullable', 'string', 'max:120'],
            'usuario' => ['required', 'string', 'max:80', Rule::unique('usuarios', 'usuario')->ignore($usuario->id_usuario, 'id_usuario')],
            'correo' => ['required', 'email', 'max:150', Rule::unique('usuarios', 'correo')->ignore($usuario->id_usuario, 'id_usuario')],
            'id_rol' => ['required', Rule::exists('roles', 'id_rol')],
            'dni' => ['nullable', 'string', 'size:8', Rule::unique('usuarios', 'dni')->ignore($usuario->id_usuario, 'id_usuario')],
            'telefono' => ['nullable', 'string', 'max:20'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombres.required' => 'El nombre es obligatorio.',
            'usuario.required' => 'El usuario es obligatorio.',
            'usuario.unique' => 'El usuario ya ha sido registrado.',
            'correo.required' => 'El correo institucional es obligatorio.',
            'correo.email' => 'Ingrese un correo institucional valido.',
            'correo.unique' => 'El correo ya ha sido registrado.',
            'id_rol.required' => 'Seleccione un rol.',
            'id_rol.exists' => 'El rol seleccionado no es valido.',
            'dni.size' => 'El DNI debe tener exactamente 8 digitos.',
            'dni.unique' => 'El DNI ya ha sido registrado.',
        ];
    }

    public function attributes(): array
    {
        return [
            'nombres' => 'nombres',
            'apellidos' => 'apellidos',
            'usuario' => 'usuario',
            'correo' => 'correo institucional',
            'id_rol' => 'rol',
            'dni' => 'DNI',
            'telefono' => 'telefono',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->filled('id_rol') && Role::find($this->input('id_rol'))?->codigo === 'director') {
                $validator->errors()->add('id_rol', 'No esta permitido asignar el rol Director desde este modulo.');
            }
        });
    }
}
