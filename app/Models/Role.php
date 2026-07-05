<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $table = 'roles';
    protected $primaryKey = 'id_rol';
    public $timestamps = false;

    /**
     * El Director administra usuarios institucionales operativos, pero no a
     * otros Directores: esa cuenta solo se crea por seeder o intervencion
     * directa en base de datos, nunca desde el modulo de gestion de usuarios.
     */
    public const CODIGOS_ASIGNABLES_POR_DIRECTOR = ['jua', 'coordinador', 'docente'];

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'estado',
    ];

    protected $casts = [
        'fecha_creacion' => 'datetime',
    ];

    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class, 'id_rol');
    }
}
