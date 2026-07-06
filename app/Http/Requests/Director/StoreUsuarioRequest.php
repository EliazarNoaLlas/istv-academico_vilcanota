<?php

namespace App\Http\Requests\Director;

use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $esDocente = $this->rolSeleccionado()?->codigo === 'docente';
        $esCoordinador = $this->rolSeleccionado()?->codigo === 'coordinador';
        $esGeneral = $this->input('tipo_docente') === 'GENERAL';

        return [
            'nombres' => ['required', 'string', 'max:120'],
            'apellidos' => ['nullable', 'string', 'max:120'],
            'usuario' => ['required', 'string', 'max:80', 'unique:usuarios,usuario'],
            'correo' => ['required', 'email', 'max:150', 'unique:usuarios,correo'],
            'id_rol' => ['required', Rule::exists('roles', 'id_rol')],
            'dni' => ['nullable', 'string', 'size:8', 'unique:usuarios,dni'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'estado' => ['required', Rule::in(['ACTIVO', 'INACTIVO', 'BLOQUEADO'])],

            'especialidad' => [$esDocente ? 'required' : 'nullable', 'string', 'max:100'],
            'tipo_docente' => [$esDocente ? 'required' : 'nullable', Rule::in(['ESPECIFICO', 'GENERAL'])],
            'programas' => [
                $esDocente ? 'required' : 'nullable',
                'array',
                $esGeneral ? 'min:1' : 'size:1',
            ],
            'programas.*' => ['integer', Rule::exists('programas_estudio', 'id_programa')],

            'id_programa' => [$esCoordinador ? 'required' : 'nullable', Rule::exists('programas_estudio', 'id_programa')],
        ];
    }

    public function messages(): array
    {
        return [
            'programas.size' => 'Un docente específico solo puede tener un programa asignado.',
            'programas.min' => 'Seleccione al menos un programa.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->rolSeleccionado()?->codigo === 'director') {
                $validator->errors()->add('id_rol', 'No está permitido crear usuarios con rol Director desde este módulo.');
            }
        });
    }

    private function rolSeleccionado(): ?Role
    {
        return $this->filled('id_rol') ? Role::find($this->input('id_rol')) : null;
    }
}
