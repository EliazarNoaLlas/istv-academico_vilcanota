<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocentePrograma extends Model
{
    protected $table = 'docente_programa';
    protected $primaryKey = 'id_docente_programa';

    protected $fillable = [
        'id_docente',
        'id_programa',
        'tipo_asignacion',
        'es_principal',
        'estado',
        'fecha_inicio',
        'fecha_fin',
        'observacion',
    ];

    protected $casts = [
        'es_principal' => 'boolean',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];

    public function docente(): BelongsTo
    {
        return $this->belongsTo(Docente::class, 'id_docente');
    }

    public function programa(): BelongsTo
    {
        return $this->belongsTo(ProgramaEstudio::class, 'id_programa');
    }
}
