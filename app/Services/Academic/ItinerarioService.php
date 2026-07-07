<?php

namespace App\Services\Academic;

use App\Models\ItinerarioFormativo;
use App\Models\ItinerarioUnidadDidactica;
use App\Models\ProgramaEstudio;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ItinerarioService
{
    public function __construct(private readonly ItinerarioCalculoService $calculo) {}

    /**
     * Itinerarios con relaciones cargadas + KPIs para el dashboard del director.
     */
    public function listarParaDirector(array $filters = []): array
    {
        $itinerarios = ItinerarioFormativo::query()
            ->with([
                'programa',
                'modulos' => fn ($q) => $q->orderBy('orden'),
                'modulos.bloques' => fn ($q) => $q->orderBy('orden'),
                'modulos.bloques.unidades' => fn ($q) => $q->orderBy('orden'),
            ])
            ->when($filters['q'] ?? null, function (Builder $query, string $q) {
                $query->where(function (Builder $sub) use ($q) {
                    $sub->where('nombre', 'like', "%{$q}%")
                        ->orWhere('codigo', 'like', "%{$q}%")
                        ->orWhere('resolucion_oficio', 'like', "%{$q}%")
                        ->orWhereHas('programa', fn (Builder $p) => $p->where('nombre', 'like', "%{$q}%"));
                });
            })
            ->when($filters['id_programa'] ?? null, fn (Builder $q, $id) => $q->where('id_programa', (int) $id))
            ->when($filters['estado'] ?? null, fn (Builder $q, $estado) => $q->where('estado', $estado))
            ->when($filters['version'] ?? null, fn (Builder $q, $version) => $q->where('version', $version))
            ->orderByDesc('updated_at')
            ->get();

        $alertas = $itinerarios->sum(fn (ItinerarioFormativo $i) => count($this->calculo->validarTotales($i)));
        $activos = $itinerarios->where('estado', 'ACTIVO');

        return [
            'itinerarios' => $itinerarios,
            'kpis' => [
                'programas_activos' => ProgramaEstudio::where('estado', 'ACTIVO')->count(),
                'itinerarios_activos' => ItinerarioFormativo::where('estado', 'ACTIVO')->count(),
                'creditos_totales' => (int) $activos->sum('total_creditos'),
                'horas_totales' => (int) $activos->sum('total_horas'),
                'alertas_validacion' => $alertas,
            ],
            'versiones' => ItinerarioFormativo::query()->distinct()->orderBy('version')->pluck('version'),
        ];
    }

    public function obtenerDetalle(int $id): ItinerarioFormativo
    {
        return ItinerarioFormativo::with([
            'programa',
            'totales',
            'modulos' => fn ($q) => $q->orderBy('orden'),
            'modulos.bloques' => fn ($q) => $q->orderBy('orden'),
            'modulos.bloques.unidades' => fn ($q) => $q->orderBy('ciclo')->orderBy('orden'),
            'modulos.bloques.unidades.curso',
        ])->findOrFail($id);
    }

    public function obtenerParaEditor(int $id): array
    {
        $itinerario = $this->obtenerDetalle($id);

        $bloques = $itinerario->modulos->flatMap(
            fn ($modulo) => $modulo->bloques->map(fn ($bloque) => [
                'id_bloque' => $bloque->id_bloque,
                'nombre' => "Módulo {$modulo->numero_modulo} · {$bloque->nombre}",
                'tipo_bloque' => $bloque->tipo_bloque,
            ])
        )->values();

        return [
            'itinerario' => $itinerario,
            'bloques' => $bloques,
            'validaciones' => $this->calculo->validarTotales($itinerario),
        ];
    }

    public function crearItinerario(array $data): ItinerarioFormativo
    {
        $data['version'] = $data['version'] ?? '2026';
        $data['estado'] = $data['estado'] ?? 'BORRADOR';
        $data['duracion_ciclos'] = $data['duracion_ciclos'] ?? 6;

        return ItinerarioFormativo::create($data);
    }

    public function actualizarItinerario(ItinerarioFormativo $itinerario, array $data): ItinerarioFormativo
    {
        $itinerario->update($data);

        return $itinerario->refresh();
    }

    /**
     * Actualiza una unidad, aplica las fórmulas y recalcula la cascada de totales.
     */
    public function actualizarUnidad(ItinerarioFormativo $itinerario, ItinerarioUnidadDidactica $unidad, array $data): array
    {
        $this->asegurarPertenencia($itinerario, $unidad);

        if (! empty($data['id_bloque'])) {
            $pertenece = $itinerario->modulos()
                ->whereHas('bloques', fn ($q) => $q->where('itinerario_bloques.id_bloque', (int) $data['id_bloque']))
                ->exists();

            if (! $pertenece) {
                throw new NotFoundHttpException('El bloque indicado no pertenece a este itinerario.');
            }
        } else {
            unset($data['id_bloque']);
        }

        return DB::transaction(function () use ($itinerario, $unidad, $data) {
            $unidad->fill($data);
            $unidad->save();
            $this->calculo->aplicarCalculosUnidad($unidad);

            $totales = $this->calculo->recalcularTotales($itinerario);

            return [
                'unidad' => $unidad->refresh()->load('bloque.modulo'),
                'totales' => $totales,
                'validaciones' => $this->calculo->validarTotales($itinerario->refresh()),
            ];
        });
    }

    /**
     * Copia profunda del itinerario (módulos, bloques y unidades) como BORRADOR.
     */
    public function duplicarItinerario(ItinerarioFormativo $itinerario): ItinerarioFormativo
    {
        return DB::transaction(function () use ($itinerario) {
            $itinerario->loadMissing('modulos.bloques.unidades');

            $copia = $itinerario->replicate(['total_creditos', 'total_horas']);
            $copia->version = $this->versionDisponible($itinerario);
            $copia->estado = 'BORRADOR';
            $copia->fecha_aprobacion = null;
            $copia->total_creditos = $itinerario->total_creditos;
            $copia->total_horas = $itinerario->total_horas;
            $copia->save();

            foreach ($itinerario->modulos as $modulo) {
                $moduloCopia = $modulo->replicate();
                $moduloCopia->id_itinerario = $copia->id_itinerario;
                $moduloCopia->save();

                foreach ($modulo->bloques as $bloque) {
                    $bloqueCopia = $bloque->replicate();
                    $bloqueCopia->id_modulo = $moduloCopia->id_modulo;
                    $bloqueCopia->save();

                    foreach ($bloque->unidades as $unidad) {
                        $unidadCopia = $unidad->replicate(['id_curso']);
                        $unidadCopia->id_bloque = $bloqueCopia->id_bloque;
                        $unidadCopia->save();
                    }
                }
            }

            return $copia;
        });
    }

    /**
     * Activa el itinerario y archiva cualquier otro itinerario ACTIVO del
     * mismo programa (solo puede haber una malla vigente por programa).
     */
    public function activarItinerario(ItinerarioFormativo $itinerario): ItinerarioFormativo
    {
        return DB::transaction(function () use ($itinerario) {
            ItinerarioFormativo::where('id_programa', $itinerario->id_programa)
                ->where('id_itinerario', '!=', $itinerario->id_itinerario)
                ->where('estado', 'ACTIVO')
                ->update(['estado' => 'ARCHIVADO']);

            $itinerario->update(['estado' => 'ACTIVO']);

            return $itinerario->refresh();
        });
    }

    public function archivarItinerario(ItinerarioFormativo $itinerario): ItinerarioFormativo
    {
        $itinerario->update(['estado' => 'ARCHIVADO']);

        return $itinerario->refresh();
    }

    private function asegurarPertenencia(ItinerarioFormativo $itinerario, ItinerarioUnidadDidactica $unidad): void
    {
        $perteneceAlItinerario = $unidad->bloque()
            ->whereHas('modulo', fn ($q) => $q->where('id_itinerario', $itinerario->id_itinerario))
            ->exists();

        if (! $perteneceAlItinerario) {
            throw new NotFoundHttpException('La unidad didáctica no pertenece a este itinerario.');
        }
    }

    private function versionDisponible(ItinerarioFormativo $itinerario): string
    {
        $base = mb_substr("{$itinerario->version}-COPIA", 0, 30);
        $version = $base;
        $n = 2;

        while (ItinerarioFormativo::where('id_programa', $itinerario->id_programa)
            ->where('codigo', $itinerario->codigo)
            ->where('version', $version)
            ->exists()) {
            $version = mb_substr($base, 0, 30 - strlen("-{$n}"))."-{$n}";
            $n++;
        }

        return $version;
    }
}
