<?php

namespace App\Services\Academic;

use App\Models\ProgramaEstudio;
use Illuminate\Database\Eloquent\Collection;

class ProgramaService
{
    public function listarConResumen(): Collection
    {
        return ProgramaEstudio::withCount(['estudiantes', 'cursos'])
            ->orderBy('nombre')
            ->get();
    }
}
