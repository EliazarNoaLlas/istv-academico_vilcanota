@extends('layouts.app', ['title' => 'Analítica Docente', 'subtitle' => auth()->user()->rol?->nombre])

@section('content')
    <div class="doc-shell">
        <div class="c-stat-grid" id="doc-analitica-kpis">
            <div class="c-stat-card teal">
                <div class="c-stat-label">Aprobados</div>
                <div class="c-stat-value" data-kpi="aprobados">—</div>
            </div>
            <div class="c-stat-card red">
                <div class="c-stat-label">Desaprobados</div>
                <div class="c-stat-value" data-kpi="desaprobados">—</div>
            </div>
            <div class="c-stat-card gold">
                <div class="c-stat-label">Estudiantes en Riesgo</div>
                <div class="c-stat-value" data-kpi="en_riesgo">—</div>
            </div>
        </div>

        <div class="doc-grid-2">
            <div class="c-panel">
                <div class="c-panel-header"><i class="bi bi-bar-chart"></i><h3>Rendimiento por Curso</h3></div>
                <div class="c-panel-body">
                    <div class="doc-chart-wrap"><canvas id="doc-analitica-chart-rendimiento"></canvas></div>
                    <p class="doc-empty-msg" id="doc-analitica-chart-rendimiento-empty" hidden>Todavía no hay notas registradas en tus cursos.</p>
                </div>
            </div>
            <div class="c-panel">
                <div class="c-panel-header"><i class="bi bi-pie-chart"></i><h3>Distribución de Notas</h3></div>
                <div class="c-panel-body">
                    <div class="doc-chart-wrap doc-chart-small"><canvas id="doc-analitica-chart-distribucion"></canvas></div>
                    <p class="doc-empty-msg" id="doc-analitica-chart-distribucion-empty" hidden>Todavía no hay notas registradas en tus cursos.</p>
                </div>
            </div>
        </div>

        <div class="doc-grid-2">
            <div class="c-panel">
                <div class="c-panel-header"><i class="bi bi-clipboard-check"></i><h3>Asistencia por Curso</h3></div>
                <div class="c-panel-body">
                    <div class="doc-chart-wrap"><canvas id="doc-analitica-chart-asistencia"></canvas></div>
                    <p class="doc-empty-msg" id="doc-analitica-chart-asistencia-empty" hidden>Todavía no hay asistencia registrada en tus cursos.</p>
                </div>
            </div>
            <div class="c-panel">
                <div class="c-panel-header"><i class="bi bi-graph-up-arrow"></i><h3>Evolución por Unidad</h3></div>
                <div class="c-panel-body">
                    <div class="doc-chart-wrap"><canvas id="doc-analitica-chart-evolucion"></canvas></div>
                    <p class="doc-empty-msg" id="doc-analitica-chart-evolucion-empty" hidden>Todavía no hay suficientes unidades registradas para mostrar una evolución.</p>
                </div>
            </div>
        </div>

        <div class="c-panel">
            <div class="c-panel-header"><i class="bi bi-exclamation-triangle"></i><h3>Estudiantes en Riesgo</h3></div>
            <div class="c-panel-body" style="padding-top:0;overflow-x:auto">
                <table class="c-table">
                    <thead>
                        <tr>
                            <th>Estudiante</th>
                            <th>Curso</th>
                            <th>Promedio</th>
                            <th>Asistencia</th>
                            <th>Motivo</th>
                        </tr>
                    </thead>
                    <tbody id="doc-analitica-riesgo-tbody"></tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/docente/analitica.js')
@endpush
