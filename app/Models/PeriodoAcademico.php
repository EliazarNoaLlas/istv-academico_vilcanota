<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PeriodoAcademico extends Model
{
    protected $table = 'periodos_academicos';
    protected $primaryKey = 'id_periodo';
    public $timestamps = false;

    protected $fillable = [
        'codigo',
        'nombre',
        'fecha_inicio',
        'fecha_fin',
        'estado',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];

    public function matriculas(): HasMany
    {
        return $this->hasMany(Matricula::class, 'id_periodo');
    }

    public function portafolios(): HasMany
    {
        return $this->hasMany(PortafolioDocente::class, 'id_periodo');
    }

    public function asistenciaSesiones(): HasMany
    {
        return $this->hasMany(AsistenciaSesion::class, 'id_periodo');
    }

    public function horariosIaGenerados(): HasMany
    {
        return $this->hasMany(HorarioIaGenerado::class, 'id_periodo');
    }

    public function iaPredicciones(): HasMany
    {
        return $this->hasMany(IaPrediccion::class, 'id_periodo');
    }

    public function horarios(): HasMany
    {
        return $this->hasMany(Horario::class, 'id_periodo');
    }
}
