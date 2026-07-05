<?php

namespace App\Services\Director;

use App\Models\AuditoriaSistema;
use App\Models\ConfiguracionSistema;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class ConfiguracionAdminService
{
    public function listar(): Collection
    {
        return ConfiguracionSistema::orderBy('clave')->get();
    }

    /** @param array<string, string> $valores */
    public function actualizar(array $valores, User $usuario): Collection
    {
        foreach ($valores as $clave => $valor) {
            $config = ConfiguracionSistema::where('clave', $clave)->first();

            if (! $config || $config->valor === $valor) {
                continue;
            }

            $anterior = $config->valor;
            $config->update(['valor' => $valor, 'fecha_actualizacion' => now()]);

            AuditoriaSistema::create([
                'id_usuario' => $usuario->id_usuario,
                'accion' => 'CONFIGURACION_ACTUALIZADA',
                'tabla_afectada' => 'configuracion_sistema',
                'registro_id' => $config->id_configuracion,
                'detalle' => "{$clave}: '{$anterior}' -> '{$valor}'",
            ]);
        }

        return $this->listar();
    }
}
