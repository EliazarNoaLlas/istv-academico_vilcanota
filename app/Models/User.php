<?php

namespace App\Models;

use App\Models\Scopes\CoordinadorDocenteProgramaScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Modelo de autenticacion de Laravel mapeado sobre la tabla legacy `usuarios`.
 * Sustituye al modelo Usuario que existia en la Fase 3: no deben coexistir dos
 * modelos autenticables para la misma tabla.
 */
class User extends Authenticatable
{
    use Notifiable, SoftDeletes;

    protected $table = 'usuarios';
    protected $primaryKey = 'id_usuario';

    const CREATED_AT = 'fecha_creacion';
    const UPDATED_AT = 'fecha_actualizacion';

    protected $fillable = [
        'id_rol',
        'id_programa',
        'usuario',
        'correo',
        'nombres',
        'apellidos',
        'dni',
        'telefono',
        'estado',
        'ultimo_acceso',
        'otp_verified_at',
        'otp_last_verified_ip',
        'otp_last_verified_user_agent',
        'cambio_password_obligatorio',
    ];

    protected $hidden = [
        'password_hash',
    ];

    protected $casts = [
        'ultimo_acceso' => 'datetime',
        'otp_verified_at' => 'datetime',
        'cambio_password_obligatorio' => 'boolean',
    ];

    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    public function hasVerifiedOtp(): bool
    {
        return ! is_null($this->otp_verified_at);
    }

    public function debeCambiarPassword(): bool
    {
        return (bool) $this->cambio_password_obligatorio;
    }

    public function rol(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'id_rol');
    }

    public function docente(): HasOne
    {
        return $this->hasOne(Docente::class, 'id_usuario');
    }

    /**
     * Perfil docente propio (un coordinador puede ademas dictar cursos),
     * ignorando el scope de aislamiento por programa: ese scope protege el
     * acceso a OTROS docentes, no debe poder ocultarle a alguien su propia
     * cuenta aunque su docente aun no tenga un docente_programa asignado.
     */
    public function miDocentePropio(): ?Docente
    {
        return Docente::withoutGlobalScope(CoordinadorDocenteProgramaScope::class)
            ->where('id_usuario', $this->id_usuario)
            ->first();
    }

    public function programa(): BelongsTo
    {
        return $this->belongsTo(ProgramaEstudio::class, 'id_programa');
    }

    public function notificaciones(): HasMany
    {
        return $this->hasMany(Notificacion::class, 'id_usuario');
    }

    public function mensajesEnviados(): HasMany
    {
        return $this->hasMany(Mensaje::class, 'id_remitente');
    }

    public function mensajesRecibidos(): HasMany
    {
        return $this->hasMany(Mensaje::class, 'id_destinatario');
    }

    public function reportesGenerados(): HasMany
    {
        return $this->hasMany(ReporteGenerado::class, 'id_usuario');
    }

    public function horariosIaGenerados(): HasMany
    {
        return $this->hasMany(HorarioIaGenerado::class, 'id_usuario');
    }

    public function auditorias(): HasMany
    {
        return $this->hasMany(AuditoriaSistema::class, 'id_usuario');
    }

    public function solicitudesPassword(): HasMany
    {
        return $this->hasMany(SolicitudPassword::class, 'id_usuario');
    }
}
