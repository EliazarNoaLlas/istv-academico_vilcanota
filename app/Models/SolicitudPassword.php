<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SolicitudPassword extends Model
{
    protected $table = 'solicitudes_password';
    protected $primaryKey = 'id_solicitud';
    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'id_usuario_atiende',
        'motivo',
        'estado',
        'motivo_rechazo',
        'ip_solicitud',
        'fecha_atencion',
    ];

    protected $casts = [
        'fecha_solicitud' => 'datetime',
        'fecha_atencion' => 'datetime',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    public function atendidaPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario_atiende');
    }
}
