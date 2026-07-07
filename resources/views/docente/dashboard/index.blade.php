@extends('layouts.app', ['title' => 'Panel Docente', 'subtitle' => auth()->user()->rol?->nombre])

@section('content')
    <div class="doc-shell">
        <div class="doc-hero" id="doc-dashboard-hero">
            <div>
                <small>PANEL DOCENTE</small>
                <h2>{{ auth()->user()->nombres }} {{ auth()->user()->apellidos }}</h2>
                <p data-hero="detalle">Cargando información del docente…</p>
            </div>
        </div>

        <div class="c-stat-grid" id="doc-dashboard-kpis">
            <div class="c-stat-card navy">
                <i class="bi bi-journal-bookmark c-stat-icon"></i>
                <div class="c-stat-label">Cursos Asignados</div>
                <div class="c-stat-value" data-kpi="cursos_asignados">—</div>
            </div>
            <div class="c-stat-card teal">
                <i class="bi bi-mortarboard c-stat-icon"></i>
                <div class="c-stat-label">Total Estudiantes</div>
                <div class="c-stat-value" data-kpi="total_estudiantes">—</div>
            </div>
            <div class="c-stat-card gold">
                <i class="bi bi-clock-history c-stat-icon"></i>
                <div class="c-stat-label">Horas Semanales</div>
                <div class="c-stat-value" data-kpi="horas_semanales">—</div>
            </div>
            <div class="c-stat-card teal">
                <i class="bi bi-graph-up c-stat-icon"></i>
                <div class="c-stat-label">Promedio General</div>
                <div class="c-stat-value" data-kpi="promedio_general">—</div>
            </div>
            <div class="c-stat-card gold">
                <i class="bi bi-check2-square c-stat-icon"></i>
                <div class="c-stat-label">Asistencia Promedio</div>
                <div class="c-stat-value" data-kpi="asistencia_promedio">—</div>
            </div>
        </div>

        <div class="doc-grid-2">
            <div class="c-panel">
                <div class="c-panel-header"><i class="bi bi-calendar-day"></i><h3>Mis Clases de Hoy</h3></div>
                <div class="c-panel-body" id="doc-dashboard-clases">Cargando…</div>
            </div>
            <div class="c-panel">
                <div class="c-panel-header"><i class="bi bi-exclamation-circle"></i><h3>Pendientes Académicos</h3></div>
                <div class="c-panel-body" id="doc-dashboard-pendientes">Cargando…</div>
            </div>
        </div>

        <div class="doc-grid-2">
            <div class="c-panel">
                <div class="c-panel-header"><i class="bi bi-bar-chart"></i><h3>Rendimiento por Curso</h3></div>
                <div class="c-panel-body">
                    <div class="doc-chart-wrap"><canvas id="doc-chart-rendimiento"></canvas></div>
                    <p class="doc-empty-msg" id="doc-chart-rendimiento-empty" hidden>Todavía no hay notas registradas en tus cursos.</p>
                </div>
            </div>
            <div class="c-panel">
                <div class="c-panel-header"><i class="bi bi-clipboard-check"></i><h3>Asistencia por Curso</h3></div>
                <div class="c-panel-body">
                    <div class="doc-chart-wrap"><canvas id="doc-chart-asistencia"></canvas></div>
                    <p class="doc-empty-msg" id="doc-chart-asistencia-empty" hidden>Todavía no hay asistencia registrada en tus cursos.</p>
                </div>
            </div>
        </div>

        <div class="doc-grid-2">
            <div class="c-panel">
                <div class="c-panel-header"><i class="bi bi-pie-chart"></i><h3>Estado del Portafolio</h3></div>
                <div class="c-panel-body">
                    <div class="doc-chart-wrap doc-chart-small"><canvas id="doc-chart-portafolio"></canvas></div>
                    <p class="doc-empty-msg" id="doc-chart-portafolio-empty" hidden>Todavía no tienes cursos con portafolio iniciado.</p>
                </div>
            </div>
            <div class="c-panel">
                <div class="c-panel-header"><i class="bi bi-file-earmark-text"></i><h3>Últimas Sesiones Subidas</h3></div>
                <div class="c-panel-body" id="doc-dashboard-sesiones">Cargando…</div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/docente/dashboard.js')
@endpush
