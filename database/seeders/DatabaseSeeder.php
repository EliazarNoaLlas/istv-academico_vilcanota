<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Orden obligatorio: catalogos base primero, cuentas institucionales al final
     * porque UsuarioBaseSeeder depende de que roles ya exista.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            ProgramaEstudioSeeder::class,
            PeriodoAcademicoSeeder::class,
            AulaSeeder::class,
            ConfiguracionSistemaSeeder::class,
            CursoSeeder::class,
            ItinerarioDsiSeeder::class,
            ItinerarioCcSeeder::class,
            ItinerarioContSeeder::class,
            ItinerarioPaSeeder::class,
            ItinerarioEnfSeeder::class,
            CorreccionCursosDsiSeeder::class,
            BackfillHorariosDsiSeeder::class,
            NormalizarAulasHorariosDsiSeeder::class,
            ReasignarGestionServiciosTiSeeder::class,
            UsuarioBaseSeeder::class,
        ]);
    }
}
