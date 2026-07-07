<?php

namespace App\Services\Notificaciones;

use App\Models\Curso;
use App\Models\Docente;
use App\Models\Notificacion;
use App\Models\PortafolioDocumento;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class NotificacionService
{
    private const ETIQUETAS_TIPO_DOCUMENTO = [
        'SILABO' => 'sílabo',
        'PLAN_SESION' => 'sesión de aprendizaje',
        'ASISTENCIA' => 'registro de asistencia',
        'NOTAS' => 'registro de notas',
        'ACTA' => 'acta',
        'EVIDENCIA' => 'evidencia de aprendizaje',
    ];

    public function paraUsuario(int $idUsuario, int $limite = 20): Collection
    {
        return Notificacion::query()
            ->where('id_usuario', $idUsuario)
            ->orderByDesc('fecha_creacion')
            ->limit($limite)
            ->get();
    }

    /** Avisa a los coordinadores del programa del curso que hay un documento nuevo por revisar. */
    public function notificarNuevoDocumentoPortafolio(PortafolioDocumento $documento, Curso $curso, Docente $docente): void
    {
        $etiqueta = self::ETIQUETAS_TIPO_DOCUMENTO[$documento->tipo] ?? strtolower($documento->tipo);
        $nombreDocente = trim("{$docente->usuario->nombres} {$docente->usuario->apellidos}");

        $coordinadores = User::whereHas('rol', fn ($q) => $q->where('codigo', 'coordinador'))
            ->where('id_programa', $curso->id_programa)
            ->where('estado', 'ACTIVO')
            ->where('id_usuario', '!=', $docente->id_usuario)
            ->get();

        foreach ($coordinadores as $coordinador) {
            Notificacion::create([
                'id_usuario' => $coordinador->id_usuario,
                'tipo' => 'PORTAFOLIO_DOCUMENTO',
                'titulo' => 'Nuevo documento de portafolio para revisar',
                'detalle' => "{$nombreDocente} subió un {$etiqueta} en el curso {$curso->nombre_curso}.",
                'url_destino' => '/coordinador/portafolio',
                'leido' => false,
            ]);
        }
    }

    public function marcarLeida(Notificacion $notificacion): Notificacion
    {
        $notificacion->update(['leido' => true]);

        return $notificacion;
    }

    public function marcarTodasLeidas(int $idUsuario): int
    {
        return Notificacion::where('id_usuario', $idUsuario)
            ->where('leido', false)
            ->update(['leido' => true]);
    }
}
