@extends('layouts.app', ['title' => 'Panel Director', 'subtitle' => 'Vista general institucional · Instituto Superior Tecnológico Vilcanota'])

@section('content')
    <div class="dir-shell">
        <div class="dir-hero">
            <div>
                <small>DIRECCIÓN ACADÉMICA</small>
                <h2>Panel Institucional</h2>
                <p>
                    Director: <strong>{{ auth()->user()->nombres }} {{ auth()->user()->apellidos }}</strong>
                </p>
            </div>
        </div>

        <div class="dir-kpis" id="dir-dashboard-kpis">
            <div class="c-stat-card teal">
                <i class="bi bi-mortarboard c-stat-icon"></i>
                <div class="c-stat-label">Total Estudiantes</div>
                <div class="c-stat-value" data-kpi="total_estudiantes">—</div>
            </div>
            <div class="c-stat-card gold">
                <i class="bi bi-person-video3 c-stat-icon"></i>
                <div class="c-stat-label">Docentes Activos</div>
                <div class="c-stat-value" data-kpi="docentes_activos">—</div>
            </div>
            <div class="c-stat-card navy">
                <i class="bi bi-journal-bookmark c-stat-icon"></i>
                <div class="c-stat-label">Cursos Activos</div>
                <div class="c-stat-value" data-kpi="cursos_activos">—</div>
            </div>
            <div class="c-stat-card red">
                <i class="bi bi-exclamation-triangle c-stat-icon"></i>
                <div class="c-stat-label">Alertas Tempranas</div>
                <div class="c-stat-value" data-kpi="alertas_abiertas">—</div>
            </div>
        </div>

        <div class="c-panel">
            <div class="c-panel-header"><i class="bi bi-mortarboard"></i><h3>Programas de Estudio</h3></div>
            <div class="c-panel-body">
                <div class="dir-kpis" id="dir-dashboard-programas">
                    <div class="c-stat-card teal"><div class="c-stat-label">Cargando…</div></div>
                </div>
            </div>
        </div>

        <div class="dir-grid-2">
            <div class="c-panel">
                <div class="c-panel-header"><i class="bi bi-bar-chart"></i><h3>Rendimiento Académico por Programa de Estudio</h3></div>
                <div class="c-panel-body">
                    <div class="c-mini-chart" id="dir-dashboard-rendimiento">Cargando…</div>
                </div>
            </div>
            <div class="c-panel">
                <div class="c-panel-header"><i class="bi bi-pie-chart"></i><h3>Estado del Portafolio Docente</h3></div>
                <div class="c-panel-body">
                    <div class="dir-donut-wrap" id="dir-dashboard-portafolio">Cargando…</div>
                </div>
            </div>
        </div>

        <div class="dir-grid-2">
            <div class="c-panel">
                <div class="c-panel-header"><i class="bi bi-exclamation-circle"></i><h3>Alertas Tempranas Recientes</h3></div>
                <div class="c-panel-body" id="dir-dashboard-alertas">Cargando…</div>
            </div>
            <div class="c-panel">
                <div class="c-panel-header"><i class="bi bi-clock-history"></i><h3>Actividad Reciente del Sistema</h3></div>
                <div class="c-panel-body" id="dir-dashboard-actividad">Cargando…</div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/director/dashboard.js')
@endpush
