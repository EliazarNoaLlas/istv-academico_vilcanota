<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlertaAcademica extends Model
{
    protected $table = 'alertas_academicas';
    protected $primaryKey = 'id_alerta';
    public $timestamps = false;

    protected $fillable = [
        'id_estudiante',
        'id_docente',
        'id_curso',
        'tipo',
        'severidad',
        'titulo',
        'detalle',
        'estado',
        'fecha_cierre',
    ];

    protected $casts = [
        'fecha_creacion' => 'datetime',
        'fecha_cierre' => 'datetime',
    ];

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class, 'id_estudiante');
    }

    public function docente(): BelongsTo
    {
        return $this->belongsTo(Docente::class, 'id_docente');
    }

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class, 'id_curso');
    }
}
