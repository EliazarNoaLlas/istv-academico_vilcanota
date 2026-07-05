<?php

namespace App\Services\Horarios;

use App\Models\Aula;
use App\Models\Curso;
use App\Models\Docente;
use App\Models\PeriodoAcademico;
use App\Models\ProgramaEstudio;

class HorarioCatalogService
{
    /** Catalogos reales para poblar selects del editor de horarios (nunca hardcodeados). */
    public function obtener(): array
    {
        return [
            'cursos' => Curso::with('docente.usuario')->orderBy('nombre_curso')->get(),
            'docentes' => Docente::where('estado_academico', 'ACTIVO')
                ->with('usuario')
                ->join('usuarios', 'usuarios.id_usuario', '=', 'docentes.id_usuario')
                ->orderBy('usuarios.nombres')
                ->select('docentes.*')
                ->get(),
            'aulas' => Aula::where('estado', 'DISPONIBLE')->orderBy('codigo')->get(),
            'periodos' => PeriodoAcademico::orderByDesc('codigo')->get(),
            'programas' => ProgramaEstudio::orderBy('nombre')->get(),
            'dias' => ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
            // Bloques de 45 min del horario institucional, igual a los que
            // ya existen en los 60 horarios reales (verificado en Fase 7).
            'bloques_horario' => [
                ['inicio' => '08:00', 'fin' => '08:45'],
                ['inicio' => '08:45', 'fin' => '09:30'],
                ['inicio' => '09:30', 'fin' => '10:15'],
                ['inicio' => '10:15', 'fin' => '11:00'],
                ['receso' => true, 'inicio' => '11:00', 'fin' => '11:15'],
                ['inicio' => '11:15', 'fin' => '12:00'],
                ['inicio' => '12:00', 'fin' => '12:45'],
            ],
        ];
    }
}
