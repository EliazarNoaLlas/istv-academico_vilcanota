<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SilaboEstructuraCriterio extends Model
{
    protected $table = 'silabo_estructura_criterios';
    protected $primaryKey = 'id_criterio';

    const CREATED_AT = 'fecha_creacion';
    const UPDATED_AT = 'fecha_actualizacion';

    protected $fillable = [
        'id_estructura',
        'orden',
        'seccion',
        'descripcion',
        'campos_json',
        'validaciones_json',
        'peso',
        'obligatorio',
    ];

    protected $casts = [
        'peso' => 'decimal:2',
        'obligatorio' => 'boolean',
    ];

    public function estructura(): BelongsTo
    {
        return $this->belongsTo(SilaboEstructura::class, 'id_estructura');
    }
}
