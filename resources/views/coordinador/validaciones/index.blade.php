@extends('layouts.app', ['title' => 'Validaciones', 'subtitle' => 'Control de pendientes académicos'])

@section('content')
    <div class="coord-shell">
        <div class="coord-hero">
            <div>
                <small>COORDINADOR ACADÉMICO</small>
                <h2>Control y Validaciones</h2>
                <p>Detección de pendientes académicos y administrativos, calculados desde la base de datos.</p>
            </div>
        </div>

        <div class="coord-kpis" id="coord-validaciones-kpis">
            <div class="c-stat-card red">
                <i class="bi bi-person-x c-stat-icon"></i>
                <div class="c-stat-label">Sin docente</div>
                <div class="c-stat-value" data-kpi="sin_docente">—</div>
            </div>
            <div class="c-stat-card gold">
                <i class="bi bi-calendar-x c-stat-icon"></i>
                <div class="c-stat-label">Sin horario</div>
                <div class="c-stat-value" data-kpi="sin_horario">—</div>
            </div>
            <div class="c-stat-card navy">
                <i class="bi bi-diagram-3 c-stat-icon"></i>
                <div class="c-stat-label">Sin programa</div>
                <div class="c-stat-value" data-kpi="sin_programa">—</div>
            </div>
            <div class="c-stat-card gold">
                <i class="bi bi-folder-x c-stat-icon"></i>
                <div class="c-stat-label">Portafolio incompleto</div>
                <div class="c-stat-value" data-kpi="portafolio_incompleto">—</div>
            </div>
            <div class="c-stat-card red">
                <i class="bi bi-clipboard-x c-stat-icon"></i>
                <div class="c-stat-label">Actas pendientes</div>
                <div class="c-stat-value" data-kpi="actas_pendientes">—</div>
            </div>
        </div>

        <div class="c-panel">
            <div class="c-panel-header"><i class="bi bi-check2-square"></i><h3>Alertas de control</h3></div>
            <div class="c-panel-body" id="coord-validaciones-alertas">Cargando…</div>
        </div>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/coordinador/validaciones.js')
@endpush
