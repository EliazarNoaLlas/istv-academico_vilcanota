<?php

namespace App\Http\Requests\Horarios;

use App\Models\Aula;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreHorarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'horarios' => ['required', 'array'],
            'horarios.*.id_curso' => ['required', 'integer', 'exists:cursos,id_curso'],
            'horarios.*.id_docente' => ['required', 'integer', 'exists:docentes,id_docente'],
            'horarios.*.dia' => ['required', 'string', 'in:Lunes,Martes,Miércoles,Jueves,Viernes,Sábado'],
            'horarios.*.hora_inicio' => ['required', 'date_format:H:i'],
            'horarios.*.hora_fin' => ['required', 'date_format:H:i', 'after:horarios.*.hora_inicio'],
            'horarios.*.aula' => ['required', 'string', Rule::in(Aula::pluck('codigo'))],
            'horarios.*.estado' => ['nullable', 'string', 'max:30'],
            'horarios.*.fuente' => ['nullable', 'string', 'in:MANUAL,IA'],

            // Alcance del guardado: si vienen vacios, se reemplaza el
            // horario completo (vista sin filtro); si vienen, solo se
            // reemplaza ese subconjunto (ver HorarioPersistenceService).
            'filtro_docente' => ['nullable', 'integer', 'exists:docentes,id_docente'],
            'filtro_semestre' => ['nullable', 'string', 'in:I,II,III,IV,V,VI'],
            'filtro_programa' => ['nullable', 'integer', 'exists:programas_estudio,id_programa'],
        ];
    }
}
