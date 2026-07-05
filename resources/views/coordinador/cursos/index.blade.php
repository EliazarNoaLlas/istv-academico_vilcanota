@extends('layouts.app', ['title' => 'Cursos', 'subtitle' => 'Gestión académica de cursos'])

@section('content')
    <div class="coord-shell">
        <div class="coord-hero">
            <div>
                <small>COORDINADOR ACADÉMICO</small>
                <h2>Cursos</h2>
                <p>Registro de cursos, docente asignado, programa y estado académico.</p>
            </div>
        </div>

        <div class="coord-kpis" id="coord-cursos-kpis">
            <div class="c-stat-card teal">
                <i class="bi bi-journal-bookmark c-stat-icon"></i>
                <div class="c-stat-label">Total cursos</div>
                <div class="c-stat-value" data-kpi="total">—</div>
            </div>
            <div class="c-stat-card gold">
                <i class="bi bi-check-circle c-stat-icon"></i>
                <div class="c-stat-label">Activos</div>
                <div class="c-stat-value" data-kpi="activos">—</div>
            </div>
            <div class="c-stat-card red">
                <i class="bi bi-person-x c-stat-icon"></i>
                <div class="c-stat-label">Sin docente</div>
                <div class="c-stat-value" data-kpi="sin-docente">—</div>
            </div>
            <div class="c-stat-card navy">
                <i class="bi bi-diagram-3 c-stat-icon"></i>
                <div class="c-stat-label">Sin programa</div>
                <div class="c-stat-value" data-kpi="sin-programa">—</div>
            </div>
        </div>

        <div class="c-panel">
            <div class="c-panel-header">
                <i class="bi bi-funnel"></i><h3>Filtros de cursos</h3>
                <button type="button" id="coord-cursos-nuevo" class="c-btn c-btn-primary c-btn-sm">
                    <i class="bi bi-plus-lg"></i> Nuevo curso
                </button>
            </div>
            <div class="c-panel-body">
                <div class="coord-cursos-toolbar">
                    <input type="text" id="coord-cursos-search" class="input-inline coord-cursos-search"
                           placeholder="Buscar por nombre de curso...">

                    <select id="coord-cursos-filtro-programa">
                        <option value="">Todos los programas</option>
                        @foreach ($programas as $programa)
                            <option value="{{ $programa->id_programa }}">{{ $programa->nombre }}</option>
                        @endforeach
                    </select>

                    <select id="coord-cursos-filtro-semestre">
                        <option value="">Todos los semestres</option>
                        @foreach (['I', 'II', 'III', 'IV', 'V', 'VI'] as $ciclo)
                            <option value="{{ $ciclo }}">Semestre {{ $ciclo }}</option>
                        @endforeach
                    </select>

                    <select id="coord-cursos-filtro-modulo">
                        <option value="">Todos los módulos</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="c-panel">
            <div class="c-panel-header"><i class="bi bi-table"></i><h3>Registro de cursos</h3></div>
            @include('coordinador.cursos.partials.table')
        </div>
    </div>

    @include('coordinador.cursos.partials.form-modal')
@endsection

@push('scripts')
    @vite('resources/js/coordinador/cursos.js')
@endpush
