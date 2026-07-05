<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfiguracionSistema extends Model
{
    protected $table = 'configuracion_sistema';
    protected $primaryKey = 'id_configuracion';
    public $timestamps = false;

    protected $fillable = [
        'clave',
        'valor',
        'descripcion',
    ];

    protected $casts = [
        'fecha_actualizacion' => 'datetime',
    ];
}
