@extends('layouts.app', ['title' => 'Estudiantes', 'subtitle' => 'Matrícula y seguimiento académico'])

@section('content')
    <div class="coord-shell">
        <div class="coord-hero">
            <div>
                <small>COORDINADOR ACADÉMICO</small>
                <h2>Estudiantes</h2>
                <p>Matrícula y seguimiento académico por programa.</p>
            </div>
        </div>

        <div class="coord-kpis" id="coord-estudiantes-kpis">
            <div class="c-stat-card teal">
                <i class="bi bi-mortarboard c-stat-icon"></i>
                <div class="c-stat-label">Matriculados</div>
                <div class="c-stat-value" data-kpi="total">—</div>
            </div>
            <div class="c-stat-card navy">
                <i class="bi bi-graph-up c-stat-icon"></i>
                <div class="c-stat-label">Promedio general</div>
                <div class="c-stat-value" data-kpi="promedio">—</div>
            </div>
            <div class="c-stat-card gold">
                <i class="bi bi-exclamation-circle c-stat-icon"></i>
                <div class="c-stat-label">Observados</div>
                <div class="c-stat-value" data-kpi="observados">—</div>
            </div>
            <div class="c-stat-card red">
                <i class="bi bi-exclamation-triangle c-stat-icon"></i>
                <div class="c-stat-label">En riesgo</div>
                <div class="c-stat-value" data-kpi="riesgo">—</div>
            </div>
        </div>

        <div class="c-panel">
            <div class="c-panel-header"><i class="bi bi-funnel"></i><h3>Filtros de estudiantes</h3></div>
            <div class="c-panel-body">
                <div class="coord-filter-grid">
                    <div class="coord-filter-field">
                        <label>Ciclo</label>
                        <select id="coord-estudiantes-filtro-ciclo" class="input-inline">
                            <option value="">Todos</option>
                            @foreach (['I', 'II', 'III', 'IV', 'V', 'VI'] as $ciclo)
                                <option value="{{ $ciclo }}">Semestre {{ $ciclo }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="c-panel">
            <div class="c-panel-header"><i class="bi bi-people"></i><h3>Relación de estudiantes</h3></div>
            <div class="c-panel-body" style="padding-top:0">
                <table class="c-table">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Estudiante</th>
                            <th>Programa</th>
                            <th>Ciclo</th>
                            <th>Promedio</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody id="coord-estudiantes-tbody">
                        <tr><td colspan="6" class="coord-portafolio-empty">Cargando estudiantes…</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/coordinador/estudiantes.js')
@endpush
