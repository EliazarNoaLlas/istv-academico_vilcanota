@extends('layouts.app', ['title' => 'Panel Docente', 'subtitle' => auth()->user()->rol?->nombre])

@section('content')
    <div id="doc-dashboard-kpis" class="c-stat-grid doc-dashboard-kpis">
        <div class="c-stat-card navy">
            <div class="c-stat-label">Mis cursos</div>
            <div class="c-stat-value" data-kpi="mis-cursos">—</div>
            <i class="bi bi-journal-bookmark c-stat-icon"></i>
        </div>
        <div class="c-stat-card teal">
            <div class="c-stat-label">Sesiones registradas</div>
            <div class="c-stat-value" data-kpi="sesiones-registradas">—</div>
            <i class="bi bi-file-earmark-text c-stat-icon"></i>
        </div>
    </div>

    <div class="c-card">
        <p style="color:var(--text-muted);font-size:13px">
            Los módulos de notas, asistencia y portafolio se conectan a esta misma vista en la Fase 7.
        </p>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/docente/dashboard.js')
@endpush
