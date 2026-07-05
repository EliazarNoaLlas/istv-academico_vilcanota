@extends('layouts.app', ['title' => 'Panel Principal', 'subtitle' => 'Vista general del módulo · Instituto Superior Tecnológico Vilcanota'])

@section('content')
    <div class="coord-shell">
        <div class="coord-hero">
            <div>
                <small>COORDINADOR ACADÉMICO</small>
                <h2>Dashboard</h2>
                <p>
                    Coordinador: <strong>{{ auth()->user()->nombres }} {{ auth()->user()->apellidos }}</strong>
                    <span id="coord-dashboard-periodo"></span>
                </p>
            </div>
        </div>

        <div class="coord-kpis" id="coord-dashboard-kpis">
            <div class="c-stat-card teal">
                <i class="bi bi-person-video3 c-stat-icon"></i>
                <div class="c-stat-label">Docentes</div>
                <div class="c-stat-value" data-kpi="docentes">—</div>
            </div>
            <div class="c-stat-card gold">
                <i class="bi bi-journal-bookmark c-stat-icon"></i>
                <div class="c-stat-label">Cursos</div>
                <div class="c-stat-value" data-kpi="cursos">—</div>
            </div>
            <div class="c-stat-card navy">
                <i class="bi bi-mortarboard c-stat-icon"></i>
                <div class="c-stat-label">Estudiantes</div>
                <div class="c-stat-value" data-kpi="estudiantes">—</div>
            </div>
            <div class="c-stat-card red">
                <i class="bi bi-folder2-open c-stat-icon"></i>
                <div class="c-stat-label">Portafolios pendientes</div>
                <div class="c-stat-value" data-kpi="portafolios-pendientes">—</div>
            </div>
            <div class="c-stat-card teal">
                <i class="bi bi-calendar-week c-stat-icon"></i>
                <div class="c-stat-label">Bloques de horario</div>
                <div class="c-stat-value" data-kpi="horarios">—</div>
            </div>
        </div>

        <div class="coord-grid-2">
            <div class="c-panel">
                <div class="c-panel-header"><i class="bi bi-exclamation-circle"></i><h3>Alertas del programa</h3></div>
                <div class="c-panel-body" id="coord-dashboard-alertas">Cargando…</div>
            </div>
            <div class="c-panel">
                <div class="c-panel-header"><i class="bi bi-bar-chart"></i><h3>Cursos por ciclo</h3></div>
                <div class="c-panel-body">
                    <div class="c-mini-chart" id="coord-dashboard-ciclos">Cargando…</div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/coordinador/dashboard.js')
@endpush
