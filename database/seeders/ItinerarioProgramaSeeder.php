<?php

namespace Database\Seeders;

use App\Models\Curso;
use App\Models\ItinerarioFormativo;
use App\Models\ItinerarioTotal;
use App\Models\ProgramaEstudio;
use App\Services\Academic\ItinerarioCalculoService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Base para poblar el itinerario formativo oficial de un programa de estudio.
 *
 * Los campos derivados de cada unidad (horas_ciclo, creditos, totales) se
 * calculan con ItinerarioCalculoService para garantizar consistencia con las
 * fórmulas del sistema. Idempotente: al re-ejecutarse reemplaza el itinerario
 * completo y re-vincula los cursos por nombre.
 */
abstract class ItinerarioProgramaSeeder extends Seeder
{
    /** ['codigo', 'codigos_anteriores' => [], 'nombre', 'familia_profesional', 'duracion_ciclos'] */
    abstract protected function programa(): array;

    /** ['codigo', 'nombre', 'resolucion_oficio', 'descripcion', 'version'] */
    abstract protected function itinerario(): array;

    /** Módulos -> bloques -> unidades ([nombre, codigo, ciclo, teóricas, prácticas]). */
    abstract protected function estructura(): array;

    public function run(): void
    {
        $calculo = app(ItinerarioCalculoService::class);

        DB::transaction(function () use ($calculo) {
            $programa = $this->obtenerPrograma();
            $config = $this->itinerario();

            // Reemplazar el itinerario anterior (cascade elimina módulos,
            // bloques, unidades y totales; cursos quedan con FK en NULL).
            ItinerarioFormativo::where('id_programa', $programa->id_programa)
                ->where('codigo', $config['codigo'])
                ->get()
                ->each
                ->delete();

            // Solo puede haber una malla vigente por programa.
            ItinerarioFormativo::where('id_programa', $programa->id_programa)
                ->where('estado', 'ACTIVO')
                ->update(['estado' => 'ARCHIVADO']);

            $itinerario = ItinerarioFormativo::create([
                'id_programa' => $programa->id_programa,
                'codigo' => $config['codigo'],
                'nombre' => $config['nombre'],
                'resolucion_oficio' => $config['resolucion_oficio'],
                'descripcion' => $config['descripcion'],
                'duracion_ciclos' => $this->programa()['duracion_ciclos'] ?? 6,
                'version' => $config['version'] ?? '2026',
                'estado' => 'ACTIVO',
                'fecha_aprobacion' => now()->toDateString(),
            ]);

            $this->crearEstructura($itinerario, $calculo);
            $calculo->recalcularTotales($itinerario);
            $this->generarTotales($itinerario->refresh());
            $this->sincronizarCursos($itinerario, $programa);
        });

        $modulos = count($this->estructura());
        $bloques = collect($this->estructura())->sum(fn ($m) => count($m['bloques']));
        $unidades = collect($this->estructura())->sum(
            fn ($m) => collect($m['bloques'])->sum(fn ($b) => count($b['unidades']))
        );

        $this->command?->info(sprintf(
            'Itinerario %s poblado: %d módulos, %d bloques, %d unidades didácticas, totales y cursos vinculados.',
            $this->itinerario()['codigo'],
            $modulos,
            $bloques,
            $unidades,
        ));
    }

    /**
     * Ubica el programa por su código nuevo, por códigos anteriores del
     * catálogo o por nombre (colación insensible a tildes), y lo normaliza.
     */
    private function obtenerPrograma(): ProgramaEstudio
    {
        $datos = $this->programa();
        $codigos = array_merge([$datos['codigo']], $datos['codigos_anteriores'] ?? []);

        $programa = ProgramaEstudio::whereIn('codigo', $codigos)->orderBy('id_programa')->first()
            ?? ProgramaEstudio::where('nombre', $datos['nombre'])->orderBy('id_programa')->first()
            ?? new ProgramaEstudio();

        $programa->fill([
            'codigo' => $datos['codigo'],
            'nombre' => $datos['nombre'],
            'familia_profesional' => $datos['familia_profesional'],
            'duracion_ciclos' => $datos['duracion_ciclos'] ?? 6,
            'estado' => 'ACTIVO',
        ]);
        $programa->save();

        return $programa;
    }

    private function crearEstructura(ItinerarioFormativo $itinerario, ItinerarioCalculoService $calculo): void
    {
        foreach ($this->estructura() as $indice => $datosModulo) {
            $modulo = $itinerario->modulos()->create([
                'numero_modulo' => $datosModulo['numero'] ?? $indice + 1,
                'codigo' => $datosModulo['codigo'],
                'nombre' => $datosModulo['nombre'],
                'competencia' => $datosModulo['competencia'],
                'descripcion' => $datosModulo['descripcion'] ?? $datosModulo['competencia'],
                'orden' => $indice + 1,
                'color_hex' => '#0EA5D9',
            ]);

            $ordenUnidad = 1;
            foreach ($datosModulo['bloques'] as $datosBloque) {
                $bloque = $modulo->bloques()->create([
                    'nombre' => $datosBloque['nombre'],
                    'tipo_bloque' => $datosBloque['tipo'],
                    'color_hex' => $datosBloque['color'],
                    'orden' => $datosBloque['orden'],
                    'descripcion' => $datosBloque['descripcion'] ?? $datosBloque['nombre'],
                ]);

                foreach ($datosBloque['unidades'] as $unidad) {
                    [$nombre, $codigo, $ciclo, $teoricas, $practicas] = $unidad;

                    $bloque->unidades()->create(array_merge(
                        [
                            'nombre' => $nombre,
                            'codigo' => $codigo,
                            'ciclo' => $ciclo,
                            'horas_teoricas_semanales' => $teoricas,
                            'horas_practicas_semanales' => $practicas,
                            'orden' => $ordenUnidad++,
                            'es_editable' => true,
                            'estado' => 'ACTIVO',
                        ],
                        $calculo->calcularCamposUnidad($teoricas, $practicas),
                    ));
                }
            }
        }
    }

    private function generarTotales(ItinerarioFormativo $itinerario): void
    {
        ItinerarioTotal::where('id_itinerario', $itinerario->id_itinerario)->delete();

        $itinerario->load('modulos.bloques.unidades');
        $todas = $itinerario->modulos->flatMap->bloques->flatMap->unidades;

        $filaTotales = fn ($unidades) => [
            'total_creditos' => (int) $unidades->sum('creditos'),
            'total_horas_teoria' => (int) $unidades->sum('total_horas_teoria'),
            'total_horas_practica' => (int) $unidades->sum('total_horas_practica'),
            'total_horas_ud' => (int) $unidades->sum('horas_ud'),
        ];

        foreach ($itinerario->modulos as $modulo) {
            foreach ($modulo->bloques as $bloque) {
                ItinerarioTotal::create(array_merge([
                    'id_itinerario' => $itinerario->id_itinerario,
                    'id_modulo' => $modulo->id_modulo,
                    'id_bloque' => $bloque->id_bloque,
                    'tipo_total' => 'POR_BLOQUE',
                ], $filaTotales($bloque->unidades)));
            }

            ItinerarioTotal::create(array_merge([
                'id_itinerario' => $itinerario->id_itinerario,
                'id_modulo' => $modulo->id_modulo,
                'tipo_total' => 'POR_MODULO',
            ], $filaTotales($modulo->bloques->flatMap->unidades)));
        }

        foreach (['I', 'II', 'III', 'IV', 'V', 'VI'] as $ciclo) {
            $delCiclo = $todas->where('ciclo', $ciclo);
            if ($delCiclo->isEmpty()) {
                continue;
            }

            ItinerarioTotal::create(array_merge([
                'id_itinerario' => $itinerario->id_itinerario,
                'tipo_total' => 'POR_CICLO',
                'ciclo' => $ciclo,
            ], $filaTotales($delCiclo)));
        }

        ItinerarioTotal::create(array_merge([
            'id_itinerario' => $itinerario->id_itinerario,
            'tipo_total' => 'GENERAL',
        ], $filaTotales($todas)));
    }

    private function sincronizarCursos(ItinerarioFormativo $itinerario, ProgramaEstudio $programa): void
    {
        foreach ($itinerario->modulos as $modulo) {
            foreach ($modulo->bloques as $bloque) {
                foreach ($bloque->unidades as $unidad) {
                    $curso = Curso::withoutGlobalScopes()
                        ->withTrashed()
                        ->where('id_programa', $programa->id_programa)
                        ->whereRaw('LOWER(TRIM(nombre_curso)) = ?', [mb_strtolower(trim($unidad->nombre))])
                        ->first();

                    if (! $curso) {
                        $curso = new Curso(['nombre_curso' => $unidad->nombre]);
                        $curso->id_programa = $programa->id_programa;
                    } elseif ($curso->trashed()) {
                        $curso->restore();
                    }

                    $curso->fill([
                        'id_unidad_itinerario' => $unidad->id_unidad,
                        'tipo_curso' => $bloque->tipo_bloque === 'ESPECIALIDAD' ? 'ESPECIFICO' : 'TRANSVERSAL',
                        'tipo_formacion' => $bloque->tipo_bloque,
                        'modulo' => $modulo->codigo,
                        'semestre' => $unidad->ciclo,
                        'ciclo' => $unidad->ciclo,
                        'creditos' => $unidad->creditos,
                        'horas_teoria' => $unidad->horas_teoricas_semanales,
                        'horas_practica' => $unidad->horas_practicas_semanales,
                        'horas_ud' => $unidad->horas_ciclo,
                        'total_teoria' => $unidad->total_horas_teoria,
                        'total_practica' => $unidad->total_horas_practica,
                        'total_horas' => $unidad->horas_ud,
                        'color_hex' => $bloque->color_hex,
                        'orden_malla' => $unidad->orden,
                        'descripcion' => "Unidad didáctica del {$modulo->nombre} - {$bloque->nombre}",
                        'estado' => 'ACTIVO',
                    ]);
                    $curso->save();

                    $unidad->update(['id_curso' => $curso->id_curso]);
                }
            }
        }
    }
}
