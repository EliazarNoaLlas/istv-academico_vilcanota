<?php

namespace App\Services\Director;

use App\Models\AlertaAcademica;
use App\Models\AuditoriaSistema;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class AlertaAdminService
{
    public function listar(?string $estado = null, ?string $severidad = null): Collection
    {
        return AlertaAcademica::query()
            ->when($estado, fn ($q) => $q->where('estado', $estado))
            ->when($severidad, fn ($q) => $q->where('severidad', $severidad))
            ->with(['estudiante', 'docente.usuario', 'curso'])
            ->orderByDesc('fecha_creacion')
            ->get();
    }

    public function gestionar(AlertaAcademica $alerta, string $estado, User $usuario): AlertaAcademica
    {
        $alerta->update([
            'estado' => $estado,
            'fecha_cierre' => $estado === 'CERRADA' ? now() : null,
        ]);

        AuditoriaSistema::create([
            'id_usuario' => $usuario->id_usuario,
            'accion' => 'ALERTA_GESTIONADA',
            'tabla_afectada' => 'alertas_academicas',
            'registro_id' => (string) $alerta->id_alerta,
            'detalle' => "Alerta '{$alerta->titulo}' marcada como {$estado}",
        ]);

        return $alerta->fresh(['estudiante', 'docente.usuario', 'curso']);
    }
}
