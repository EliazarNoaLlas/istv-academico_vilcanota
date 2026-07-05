<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AsistenciaDetalle extends Model
{
    protected $table = 'asistencia_detalle';
    protected $primaryKey = 'id_asistencia';
    public $timestamps = false;

    protected $fillable = [
        'id_sesion',
        'id_estudiante',
        'estado',
        'observacion',
    ];

    protected $casts = [
        'fecha_registro' => 'datetime',
    ];

    public function sesion(): BelongsTo
    {
        return $this->belongsTo(AsistenciaSesion::class, 'id_sesion');
    }

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class, 'id_estudiante');
    }
}
