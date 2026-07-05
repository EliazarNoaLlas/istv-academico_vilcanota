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

        return [
            'nombres' => ['required', 'string', 'max:120'],
            'apellidos' => ['nullable', 'string', 'max:120'],
            'usuario' => ['required', 'string', 'max:80', 'unique:usuarios,usuario'],
            'correo' => ['required', 'email', 'max:150', 'unique:usuarios,correo'],
            'id_rol' => ['required', Rule::exists('roles', 'id_rol')],
            'dni' => ['nullable', 'string', 'size:8', 'unique:usuarios,dni'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'estado' => ['required', Rule::in(['ACTIVO', 'INACTIVO', 'BLOQUEADO'])],

            'codigo_docente' => ['nullable', 'string', 'max:20'],
            'especialidad' => [$esDocente ? 'required' : 'nullable', 'string', 'max:100'],
            'tipo_docente' => [$esDocente ? 'required' : 'nullable', Rule::in(['ESPECIFICO', 'GENERAL'])],
            'programas' => [$esDocente ? 'required' : 'nullable', 'array'],
            'programas.*' => ['integer', Rule::exists('programas_estudio', 'id_programa')],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->rolSeleccionado()?->codigo === 'director') {
                $validator->errors()->add('id_rol', 'No esta permitido crear usuarios con rol Director desde este modulo.');
            }

            if ($this->rolSeleccionado()?->codigo !== 'docente') {
                return;
            }

            $programas = collect($this->input('programas', []))->filter(fn ($valor) => filled($valor));
            $tipoDocente = $this->input('tipo_docente');

            if ($tipoDocente === 'GENERAL' && $programas->count() < 2) {
                $validator->errors()->add('programas', 'El tipo General requiere seleccionar al menos dos programas.');
            }

            if ($tipoDocente === 'ESPECIFICO' && $programas->count() !== 1) {
                $validator->errors()->add('programas', 'El tipo Especifico solo permite seleccionar un programa.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'nombres.required' => 'El nombre es obligatorio.',
            'nombres.max' => 'El nombre no debe superar :max caracteres.',
            'apellidos.max' => 'Los apellidos no deben superar :max caracteres.',
            'usuario.required' => 'El usuario es obligatorio.',
            'usuario.max' => 'El usuario no debe superar :max caracteres.',
            'usuario.unique' => 'El usuario ya ha sido registrado.',
            'correo.required' => 'El correo institucional es obligatorio.',
            'correo.email' => 'Ingrese un correo institucional valido.',
            'correo.max' => 'El correo no debe superar :max caracteres.',
            'correo.unique' => 'El correo ya ha sido registrado.',
            'id_rol.required' => 'Seleccione un rol.',
            'id_rol.exists' => 'El rol seleccionado no es valido.',
            'dni.size' => 'El DNI debe tener exactamente 8 digitos.',
            'dni.unique' => 'El DNI ya ha sido registrado.',
            'telefono.max' => 'El telefono no debe superar :max caracteres.',
            'estado.required' => 'Seleccione un estado.',
            'estado.in' => 'El estado seleccionado no es valido.',
            'especialidad.required' => 'La especialidad es obligatoria para docentes.',
            'especialidad.max' => 'La especialidad no debe superar :max caracteres.',
            'tipo_docente.required' => 'Seleccione el tipo de docente.',
            'tipo_docente.in' => 'El tipo de docente seleccionado no es valido.',
            'programas.required' => 'Seleccione al menos un programa.',
            'programas.array' => 'La seleccion de programas no es valida.',
            'programas.*.integer' => 'Uno de los programas seleccionados no es valido.',
            'programas.*.exists' => 'Uno de los programas seleccionados no existe.',
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
            'estado' => 'estado',
            'codigo_docente' => 'codigo docente',
            'especialidad' => 'especialidad',
            'tipo_docente' => 'tipo de docente',
            'programas' => 'programas',
        ];
    }

    private function rolSeleccionado(): ?Role
    {
        return $this->filled('id_rol') ? Role::find($this->input('id_rol')) : null;
    }
}
