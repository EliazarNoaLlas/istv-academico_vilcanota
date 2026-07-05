<?php

namespace App\Services\Academic;

use App\Models\Docente;
use Illuminate\Database\Eloquent\Collection;

class DocenteService
{
    public function listar(): Collection
    {
        return Docente::query()
            ->where('estado_academico', 'ACTIVO')
            ->with('usuario')
            ->join('usuarios', 'usuarios.id_usuario', '=', 'docentes.id_usuario')
            ->orderBy('usuarios.nombres')
            ->select('docentes.*')
            ->get();
    }

    /** Docentes activos con cursos asignados y carga horaria real, para el panel de coordinador. */
    public function listarConCarga(): Collection
    {
        return Docente::query()
            ->where('estado_academico', 'ACTIVO')
            ->withCount('cursos')
            ->with(['cursos:id_curso,id_docente,total_horas', 'usuario'])
            ->join('usuarios', 'usuarios.id_usuario', '=', 'docentes.id_usuario')
            ->orderBy('usuarios.nombres')
            ->select('docentes.*')
            ->get()
            ->map(function ($docente) {
                $docente->carga_horaria = $docente->cursos->sum('total_horas');

                return $docente;
            });
    }

    public function cursosDe(int $idDocente): Collection
    {
        $docente = Docente::with(['cursos' => function ($q) {
            $q->withCount('sesionesAprendizaje')->orderBy('nombre_curso');
        }])->findOrFail($idDocente);

        return $docente->cursos;
    }

    public function porUsuario(string $nombreUsuario): ?Docente
    {
        $usuario = \App\Models\User::where('usuario', $nombreUsuario)->first();

        return $usuario?->docente;
    }
}
