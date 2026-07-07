<?php

namespace App\Services\Academic;

use App\Models\SesionAprendizaje;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SesionAprendizajeService
{

    public function listarPorCurso(int $idCurso, int $idDocente): Collection
    {
        return SesionAprendizaje::where('id_curso', $idCurso)
            ->where('id_docente', $idDocente)
            ->orderByDesc('fecha_subida')
            ->get();
    }

    public function subir(UploadedFile $archivo, int $idCurso, int $idDocente, string $titulo, ?int $numeroSesion): SesionAprendizaje
    {
        try {
            $nombreArchivo = bin2hex(random_bytes(8)) . '.' . $archivo->getClientOriginalExtension();
            $ruta = $archivo->storeAs('sesiones', $nombreArchivo, 'local');

            return SesionAprendizaje::create([
                'id_curso' => $idCurso,
                'id_docente' => $idDocente,
                'titulo' => $titulo,
                'archivo' => $ruta,
                'numero_sesion' => $numeroSesion,
                'estado' => 'PENDIENTE',
            ]);
        } catch (\Throwable $e) {
            Log::error('Error al subir sesion de aprendizaje', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function eliminar(SesionAprendizaje $sesion, int $idDocente): bool
    {
        if ($sesion->id_docente !== $idDocente) {
            return false;
        }

        abort_if($sesion->estado === 'APROBADO', 422, 'No se puede eliminar una sesión ya aprobada.');

        $archivo = $sesion->archivo;
        $sesion->delete();

        if ($archivo) {
            $disk = Storage::disk('local');
            $baseDir = realpath($disk->path('sesiones'));
            $rutaAbsoluta = realpath($disk->path($archivo));

            if ($baseDir && $rutaAbsoluta && str_starts_with($rutaAbsoluta, $baseDir)) {
                $disk->delete($archivo);
            } else {
                Log::error('Intento de eliminar archivo de sesion fuera del directorio permitido', ['ruta' => $archivo]);
            }
        }

        return true;
    }
}
