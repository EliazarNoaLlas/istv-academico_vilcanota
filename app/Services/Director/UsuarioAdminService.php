<?php

namespace App\Services\Director;

use App\Mail\CredencialesTemporalesMail;
use App\Models\AuditoriaSistema;
use App\Models\Docente;
use App\Models\DocentePrograma;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class UsuarioAdminService
{
    public function listar(?string $q = null, ?int $idRol = null, ?string $estado = null): Collection
    {
        return User::query()
            ->when($q, fn ($query) => $query->where(fn ($qq) => $qq
                ->where('nombres', 'like', "%{$q}%")
                ->orWhere('apellidos', 'like', "%{$q}%")
                ->orWhere('usuario', 'like', "%{$q}%")
                ->orWhere('correo', 'like', "%{$q}%")))
            ->when($idRol, fn ($query) => $query->where('id_rol', $idRol))
            ->when($estado, fn ($query) => $query->where('estado', $estado))
            ->with(['rol', 'docente.programas'])
            ->orderBy('nombres')
            ->get();
    }

    public function catalogoEspecialidades(): Collection
    {
        return Docente::query()
            ->whereNotNull('especialidad')
            ->where('especialidad', '!=', '')
            ->select('especialidad')
            ->distinct()
            ->orderBy('especialidad')
            ->pluck('especialidad');
    }

    public function siguienteCodigoDocente(): string
    {
        $ultimoCodigo = Docente::query()
            ->where('codigo_docente', 'like', 'doc%')
            ->orderByDesc('codigo_docente')
            ->value('codigo_docente');

        $siguiente = 1;

        if ($ultimoCodigo && preg_match('/^doc(\d+)$/i', $ultimoCodigo, $matches)) {
            $siguiente = ((int) $matches[1]) + 1;
        }

        return sprintf('doc%03d', $siguiente);
    }

    /** Crea el usuario y, si el rol es docente, tambien su perfil academico. */
    public function crear(array $datos, User $creadoPor): User
    {
        ['datosUsuario' => $datosUsuario, 'datosDocente' => $datosDocente] = $this->separarDatosDocente($datos);

        return DB::transaction(function () use ($datosUsuario, $datosDocente, $creadoPor) {
            $passwordTemporal = Str::password(12);

            $usuario = new User($datosUsuario);
            $usuario->forceFill([
                'password_hash' => Hash::make($passwordTemporal),
                'password_algoritmo' => 'bcrypt',
                'cambio_password_obligatorio' => true,
            ])->save();

            if ($datosDocente !== null) {
                $this->crearPerfilDocente($usuario, $datosDocente);
            }

            $this->auditar($creadoPor, 'USUARIO_CREADO', $usuario, "Cuenta creada con rol {$usuario->id_rol}");
            $this->enviarCredenciales($usuario, $passwordTemporal, 'Se creo su cuenta institucional en el Sistema Academico ISTV.');

            return $usuario->load(['rol', 'docente.programas']);
        });
    }

    private function separarDatosDocente(array $datos): array
    {
        $camposDocente = ['codigo_docente', 'especialidad', 'tipo_docente', 'programas'];

        $datosDocente = isset($datos['tipo_docente']) ? [
            'codigo_docente' => $this->siguienteCodigoDocente(),
            'especialidad' => $datos['especialidad'] ?? null,
            'tipo_docente' => $datos['tipo_docente'],
            'programas' => $datos['programas'] ?? [],
        ] : null;

        $datosUsuario = array_diff_key($datos, array_flip($camposDocente));

        return ['datosUsuario' => $datosUsuario, 'datosDocente' => $datosDocente];
    }

    private function crearPerfilDocente(User $usuario, array $datosDocente): void
    {
        $docente = Docente::create([
            'id_usuario' => $usuario->id_usuario,
            'codigo_docente' => $datosDocente['codigo_docente'],
            'especialidad' => $datosDocente['especialidad'],
            'tipo_docente' => $datosDocente['tipo_docente'],
            'estado_academico' => $usuario->estado === 'ACTIVO' ? 'ACTIVO' : 'INACTIVO',
        ]);

        foreach ($datosDocente['programas'] as $indice => $idPrograma) {
            DocentePrograma::create([
                'id_docente' => $docente->id_docente,
                'id_programa' => $idPrograma,
                'tipo_asignacion' => $datosDocente['tipo_docente'],
                'es_principal' => $indice === 0,
                'estado' => 'ACTIVO',
            ]);
        }
    }

    public function actualizar(User $usuario, array $datos, User $actorPor): User
    {
        $usuario->update($datos);

        $this->auditar($actorPor, 'USUARIO_ACTUALIZADO', $usuario, 'Datos de la cuenta actualizados');

        return $usuario->fresh(['rol', 'docente.programas']);
    }

    public function cambiarEstado(User $usuario, string $estado, string $motivo, User $actorPor): User
    {
        $anterior = $usuario->estado;
        $usuario->update(['estado' => $estado]);

        $this->auditar($actorPor, 'USUARIO_ESTADO_CAMBIADO', $usuario, "Estado {$anterior} -> {$estado}. Motivo: {$motivo}");

        return $usuario->fresh('rol');
    }

    public function resetPassword(User $usuario, User $actorPor): void
    {
        $passwordTemporal = Str::password(12);

        $usuario->forceFill([
            'password_hash' => Hash::make($passwordTemporal),
            'password_algoritmo' => 'bcrypt',
            'cambio_password_obligatorio' => true,
        ])->save();

        $this->auditar($actorPor, 'PASSWORD_RESTABLECIDA', $usuario, 'Contrasena restablecida por Direccion');
        $this->enviarCredenciales($usuario, $passwordTemporal, 'Direccion Academica restablecio la contrasena de su cuenta.');
    }

    private function enviarCredenciales(User $usuario, string $passwordTemporal, string $motivo): void
    {
        $destinatario = config('mail.otp_test_recipient') ?: $usuario->correo;

        Mail::to($destinatario)->send(new CredencialesTemporalesMail($usuario, $passwordTemporal, $motivo));
    }

    private function auditar(User $actor, string $accion, User $usuarioAfectado, string $detalle): void
    {
        AuditoriaSistema::create([
            'id_usuario' => $actor->id_usuario,
            'accion' => $accion,
            'tabla_afectada' => 'usuarios',
            'registro_id' => (string) $usuarioAfectado->id_usuario,
            'detalle' => $detalle,
        ]);
    }
}
