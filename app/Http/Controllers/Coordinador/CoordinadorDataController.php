<?php

namespace App\Http\Controllers\Coordinador;

use App\Http\Controllers\Controller;
use App\Models\AlertaAcademica;
use App\Models\Curso;
use App\Models\Docente;
use App\Models\Estudiante;
use App\Models\Horario;
use App\Models\PeriodoAcademico;
use App\Models\PortafolioDocente;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class CoordinadorDataController extends Controller
{
    /**
     * Agregador de datos del panel de coordinador. Reemplaza a
     * coordinador_tablas.php: todo viene de Eloquent, nada hardcodeado.
     */
    public function index(): JsonResponse
    {
        try {
            return response()->json([
                'ok' => true,
                'cursos' => Curso::with('docente.usuario')->orderBy('nombre_curso')->get(),
                'docentes' => Docente::where('estado_academico', 'ACTIVO')
                    ->with('usuario')
                    ->join('usuarios', 'usuarios.id_usuario', '=', 'docentes.id_usuario')
                    ->orderBy('usuarios.nombres')
                    ->select('docentes.*')
                    ->get(),
                'estudiantes' => Estudiante::all(),
                'horarios' => Horario::with(['curso', 'docente.usuario'])->get(),
                'portafolios' => PortafolioDocente::with(['docente.usuario', 'curso', 'periodo'])->get(),
                'alertas' => AlertaAcademica::where('estado', 'ABIERTA')->with(['estudiante', 'docente.usuario', 'curso'])->get(),
                'periodo_activo' => PeriodoAcademico::where('estado', 'ACTIVO')->first(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Error al cargar datos del panel de coordinador', ['error' => $e->getMessage()]);

            return response()->json(['ok' => false, 'mensaje' => 'No se pudo cargar la informacion.'], 500);
        }
    }
}
