<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Aula extends Model
{
    protected $table = 'aulas';
    protected $primaryKey = 'id_aula';
    public $timestamps = false;

    protected $fillable = [
        'codigo',
        'nombre',
        'tipo',
        'capacidad',
        'ubicacion',
        'estado',
    ];

    /**
     * horarios.aula sigue existiendo como varchar libre (legado); horarios.id_aula
     * es la FK real agregada para poder validar cruces de aula. No todos los
     * horarios tienen id_aula poblado si su valor de texto no coincidia con
     * ningun codigo/nombre real de esta tabla.
     */
    public function horarios(): HasMany
    {
        return $this->hasMany(Horario::class, 'id_aula');
    }
}
