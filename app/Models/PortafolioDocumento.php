<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PortafolioDocumento extends Model
{
    use SoftDeletes;

    protected $table = 'portafolio_documentos';
    protected $primaryKey = 'id_documento';
    public $timestamps = false;

    protected $fillable = [
        'id_portafolio',
        'tipo',
        'titulo',
        'archivo',
        'estado',
        'observacion',
    ];

    protected $casts = [
        'fecha_subida' => 'datetime',
    ];

    public function portafolio(): BelongsTo
    {
        return $this->belongsTo(PortafolioDocente::class, 'id_portafolio');
    }
}
