<?php

namespace App\Services\Director;

use App\Models\AuditoriaSistema;
use App\Models\Notificacion;
use App\Models\SolicitudPassword;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class SolicitudPasswordService
{
    public function __construct(private readonly UsuarioAdminService $usuarios) {}

    /**
     * Registra la solicitud si el usuario existe y no tiene ya una pendiente.
     * No revela al solicitante si el usuario existe o no: siempre debe
     * mostrarse el mismo mensaje genérico en el formulario publico.
     */
    public function solicitar(string $usuarioOCorreo, ?string $motivo, string $ip): void
    {
        $usuario = User::where('usuario', $usuarioOCorreo)->orWhere('correo', $usuarioOCorreo)->first();

        if (! $usuario) {
            return;
        }

        $yaTienePendiente = SolicitudPassword::where('id_usuario', $usuario->id_usuario)
            ->where('estado', 'PENDIENTE')
            ->exists();

        if ($yaTienePendiente) {
            return;
        }

        $solicitud = SolicitudPassword::create([
            'id_usuario' => $usuario->id_usuario,
            'motivo' => $motivo,
            'estado' => 'PENDIENTE',
            'ip_solicitud' => $ip,
        ]);

        $this->notificarDirectores($usuario, $solicitud);
    }

    public function pendientes(): Collection
    {
        return SolicitudPassword::where('estado', 'PENDIENTE')
            ->with('usuario.rol')
            ->orderBy('fecha_solicitud')
            ->get();
    }

    public function aprobar(SolicitudPassword $solicitud, User $director): SolicitudPassword
    {
        return DB::transaction(function () use ($solicitud, $director) {
            $this->usuarios->resetPassword($solicitud->usuario, $director);

            $solicitud->update([
                'estado' => 'APROBADA',
                'id_usuario_atiende' => $director->id_usuario,
                'fecha_atencion' => now(),
            ]);

            AuditoriaSistema::create([
                'id_usuario' => $director->id_usuario,
                'accion' => 'SOLICITUD_PASSWORD_APROBADA',
                'tabla_afectada' => 'solicitudes_password',
                'registro_id' => (string) $solicitud->id_solicitud,
                'detalle' => "Solicitud de {$solicitud->usuario->usuario} aprobada",
            ]);

            return $solicitud->fresh(['usuario.rol', 'atendidaPor']);
        });
    }

    public function rechazar(SolicitudPassword $solicitud, string $motivoRechazo, User $director): SolicitudPassword
    {
        $solicitud->update([
            'estado' => 'RECHAZADA',
            'motivo_rechazo' => $motivoRechazo,
            'id_usuario_atiende' => $director->id_usuario,
            'fecha_atencion' => now(),
        ]);

        AuditoriaSistema::create([
            'id_usuario' => $director->id_usuario,
            'accion' => 'SOLICITUD_PASSWORD_RECHAZADA',
            'tabla_afectada' => 'solicitudes_password',
            'registro_id' => (string) $solicitud->id_solicitud,
            'detalle' => "Solicitud de {$solicitud->usuario->usuario} rechazada: {$motivoRechazo}",
        ]);

        return $solicitud->fresh(['usuario.rol', 'atendidaPor']);
    }

    private function notificarDirectores(User $usuario, SolicitudPassword $solicitud): void
    {
        $directores = User::whereHas('rol', fn ($q) => $q->where('codigo', 'director'))
            ->where('estado', 'ACTIVO')
            ->get();

        foreach ($directores as $director) {
            Notificacion::create([
                'id_usuario' => $director->id_usuario,
                'tipo' => 'SOLICITUD_PASSWORD',
                'titulo' => 'Solicitud de restablecimiento de contraseña',
                'detalle' => "{$usuario->nombres} {$usuario->apellidos} ({$usuario->usuario}) solicitó recuperar su contraseña.",
                'url_destino' => '/director/usuarios',
                'leido' => false,
            ]);
        }
    }
}
