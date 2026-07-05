@extends('layouts.app', ['title' => 'Docentes', 'subtitle' => 'Carga académica y asignaciones'])

@section('content')
    <div class="coord-shell">
        <div class="coord-hero">
            <div>
                <small>COORDINADOR ACADÉMICO</small>
                <h2>Docentes</h2>
                <p>Carga horaria y cursos asignados por docente.</p>
            </div>
        </div>

        <div class="coord-kpis" id="coord-docentes-kpis">
            <div class="c-stat-card teal">
                <i class="bi bi-person-video3 c-stat-icon"></i>
                <div class="c-stat-label">Total docentes</div>
                <div class="c-stat-value" data-kpi="total">—</div>
            </div>
            <div class="c-stat-card gold">
                <i class="bi bi-clock c-stat-icon"></i>
                <div class="c-stat-label">Carga promedio</div>
                <div class="c-stat-value" data-kpi="carga-promedio">—</div>
            </div>
            <div class="c-stat-card navy">
                <i class="bi bi-journal-bookmark c-stat-icon"></i>
                <div class="c-stat-label">Cursos asignados</div>
                <div class="c-stat-value" data-kpi="cursos-asignados">—</div>
            </div>
            <div class="c-stat-card red">
                <i class="bi bi-exclamation-triangle c-stat-icon"></i>
                <div class="c-stat-label">Sobrecarga (&gt;20h)</div>
                <div class="c-stat-value" data-kpi="sobrecarga">—</div>
            </div>
        </div>

        <div class="c-panel">
            <div class="c-panel-header"><i class="bi bi-person-video3"></i><h3>Registro de docentes</h3></div>
            <div class="c-panel-body" style="padding-top:0">
                <table class="c-table">
                    <thead>
                        <tr>
                            <th>Docente</th>
                            <th>Especialidad</th>
                            <th>Cursos asignados</th>
                            <th>Carga horaria</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody id="coord-docentes-tbody">
                        <tr><td colspan="5" class="coord-portafolio-empty">Cargando docentes…</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/coordinador/docentes.js')
@endpush
