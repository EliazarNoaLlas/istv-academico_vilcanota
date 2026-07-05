<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SilaboEstructura extends Model
{
    protected $table = 'silabo_estructuras';
    protected $primaryKey = 'id_estructura';

    const CREATED_AT = 'fecha_creacion';
    const UPDATED_AT = 'fecha_actualizacion';

    protected $fillable = [
        'codigo',
        'nombre',
        'version',
        'descripcion',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function criterios(): HasMany
    {
        return $this->hasMany(SilaboEstructuraCriterio::class, 'id_estructura');
    }
}
