<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HorarioIaGenerado extends Model
{
    protected $table = 'horarios_ia_generados';
    protected $primaryKey = 'id_generacion';
    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'id_periodo',
        'programa',
        'modelo',
        'prompt_resumen',
        'resultado_json',
        'metadata_json',
        'errores_json',
        'estado',
    ];

    protected $casts = [
        'resultado_json' => 'array',
        'metadata_json' => 'array',
        'errores_json' => 'array',
        'fecha_generacion' => 'datetime',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    public function periodo(): BelongsTo
    {
        return $this->belongsTo(PeriodoAcademico::class, 'id_periodo');
    }
}
