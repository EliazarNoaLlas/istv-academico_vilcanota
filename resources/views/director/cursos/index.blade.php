@extends('layouts.app', ['title' => 'Cursos', 'subtitle' => 'Oferta académica institucional'])

@section('content')
    <div class="dir-shell">
        <div class="dir-hero">
            <div>
                <small>DIRECCIÓN ACADÉMICA</small>
                <h2>Cursos</h2>
                <p>Registro institucional de cursos, docente asignado y programa.</p>
            </div>
        </div>

        <div class="dir-kpis" id="dir-cursos-kpis">
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
            <div class="c-panel-header"><i class="bi bi-funnel"></i><h3>Filtros de cursos</h3></div>
            <div class="c-panel-body">
                <div class="coord-filter-grid">
                    <div class="coord-filter-field">
                        <label>Buscar</label>
                        <input type="text" id="dir-cursos-search" class="input-inline" placeholder="Nombre de curso...">
                    </div>
                    <div class="coord-filter-field">
                        <label>Programa</label>
                        <select id="dir-cursos-filtro-programa" class="input-inline">
                            <option value="">Todos los programas</option>
                            @foreach ($programas as $programa)
                                <option value="{{ $programa->id_programa }}">{{ $programa->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="coord-filter-field">
                        <label>Semestre</label>
                        <select id="dir-cursos-filtro-semestre" class="input-inline">
                            <option value="">Todos los semestres</option>
                            @foreach (['I', 'II', 'III', 'IV', 'V', 'VI'] as $ciclo)
                                <option value="{{ $ciclo }}">Semestre {{ $ciclo }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="c-panel">
            <div class="c-panel-header"><i class="bi bi-table"></i><h3>Registro de cursos</h3></div>
            <div class="c-panel-body" style="padding-top:0">
                <table class="c-table">
                    <thead>
                        <tr>
                            <th>Curso</th>
                            <th>Programa</th>
                            <th>Semestre</th>
                            <th>Horas</th>
                            <th>Docente</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody id="dir-cursos-tbody">
                        <tr><td colspan="6" class="c-table-empty">Cargando cursos…</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/director/cursos.js')
@endpush
