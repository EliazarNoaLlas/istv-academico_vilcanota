@extends('layouts.app', ['title' => 'Consolidado', 'subtitle' => 'Cierre de actas y riesgo académico'])

@section('content')
    <div class="coord-shell">
        <div class="coord-hero">
            <div>
                <small>COORDINADOR ACADÉMICO</small>
                <h2>Consolidado de Notas</h2>
                <p>Control final de actas y promedios por curso, con riesgo académico calculado por reglas.</p>
            </div>
        </div>

        <div class="coord-kpis" id="coord-consolidado-kpis">
            <div class="c-stat-card red">
                <i class="bi bi-person-exclamation c-stat-icon"></i>
                <div class="c-stat-label">Estudiantes en riesgo</div>
                <div class="c-stat-value" data-kpi="riesgo">—</div>
                <div class="c-stat-sub">IA predictiva por reglas</div>
            </div>
            <div class="c-stat-card gold">
                <i class="bi bi-graph-down-arrow c-stat-icon"></i>
                <div class="c-stat-label">Cursos baja aprobación</div>
                <div class="c-stat-value" data-kpi="baja-aprobacion">—</div>
                <div class="c-stat-sub">aprobación menor a 60%</div>
            </div>
            <div class="c-stat-card teal">
                <i class="bi bi-cpu c-stat-icon"></i>
                <div class="c-stat-label">Modelo</div>
                <div class="c-stat-value" style="font-size:16px" data-kpi="modelo">—</div>
                <div class="c-stat-sub">notas + asistencia + histórico</div>
            </div>
        </div>

        <div class="c-panel">
            <div class="c-panel-header"><i class="bi bi-person-exclamation"></i><h3>Estudiantes en riesgo</h3></div>
            <div class="c-panel-body" id="coord-consolidado-riesgo">Cargando…</div>
        </div>

        <div class="c-panel">
            <div class="c-panel-header"><i class="bi bi-file-text"></i><h3>Consolidado por curso</h3></div>
            <div class="c-panel-body" style="padding-top:0;overflow-x:auto">
                <table class="c-table">
                    <thead>
                        <tr>
                            <th>Curso</th>
                            <th>Ciclo</th>
                            <th>Docente</th>
                            <th>Promedio</th>
                            <th>Aprobados</th>
                            <th>Desaprobados</th>
                            <th>Actas</th>
                        </tr>
                    </thead>
                    <tbody id="coord-consolidado-tbody">
                        <tr><td colspan="7" class="coord-portafolio-empty">Cargando consolidado…</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/coordinador/consolidado.js')
@endpush
