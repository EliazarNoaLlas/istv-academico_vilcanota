@extends('layouts.app', ['title' => 'Panel JUA', 'subtitle' => auth()->user()->rol?->nombre])

@section('content')
    <div id="jua-dashboard-kpis" class="c-stat-grid jua-dashboard-kpis">
        <div class="c-stat-card navy">
            <div class="c-stat-label">Docentes</div>
            <div class="c-stat-value" data-kpi="docentes">—</div>
            <i class="bi bi-people c-stat-icon"></i>
        </div>
        <div class="c-stat-card teal">
            <div class="c-stat-label">Cursos</div>
            <div class="c-stat-value" data-kpi="cursos">—</div>
            <i class="bi bi-journal-bookmark c-stat-icon"></i>
        </div>
        <div class="c-stat-card gold">
            <div class="c-stat-label">Estudiantes</div>
            <div class="c-stat-value" data-kpi="estudiantes">—</div>
            <i class="bi bi-mortarboard c-stat-icon"></i>
        </div>
    </div>

    <div class="c-card">
        <p style="color:var(--text-muted);font-size:13px">
            Programación y validaciones académicas se conectan a esta misma vista en la Fase 7.
        </p>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/jua/dashboard.js')
@endpush
