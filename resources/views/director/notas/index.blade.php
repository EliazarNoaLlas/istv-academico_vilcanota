@extends('layouts.app', ['title' => 'Notas', 'subtitle' => 'Supervisión de calificaciones'])

@section('content')
    <div class="dir-shell">
        <div class="dir-hero">
            <div>
                <small>DIRECCIÓN ACADÉMICA</small>
                <h2>Supervisión de Notas</h2>
                <p>Vista institucional de solo lectura. La edición de notas la realiza el Coordinador.</p>
            </div>
        </div>

        <div class="dir-kpis" id="dir-notas-kpis">
            <div class="c-stat-card teal">
                <i class="bi bi-clipboard-data c-stat-icon"></i>
                <div class="c-stat-label">Total registros</div>
                <div class="c-stat-value" data-kpi="total">—</div>
            </div>
            <div class="c-stat-card teal">
                <i class="bi bi-person-check c-stat-icon"></i>
                <div class="c-stat-label">Aprobados</div>
                <div class="c-stat-value" data-kpi="aprobados">—</div>
            </div>
            <div class="c-stat-card red">
                <i class="bi bi-person-x c-stat-icon"></i>
                <div class="c-stat-label">Desaprobados</div>
                <div class="c-stat-value" data-kpi="desaprobados">—</div>
            </div>
            <div class="c-stat-card gold">
                <i class="bi bi-graph-up c-stat-icon"></i>
                <div class="c-stat-label">Promedio general</div>
                <div class="c-stat-value" data-kpi="promedio">—</div>
            </div>
        </div>

        <div class="c-panel">
            <div class="c-panel-header"><i class="bi bi-funnel"></i><h3>Filtros</h3></div>
            <div class="c-panel-body">
                <div class="coord-filter-grid">
                    <div class="coord-filter-field">
                        <label>Curso</label>
                        <select id="dir-notas-filtro-curso" class="input-inline">
                            <option value="">Todos</option>
                            @foreach ($cursos as $curso)
                                <option value="{{ $curso->id_curso }}">{{ $curso->nombre_curso }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="coord-filter-field">
                        <label>Unidad</label>
                        <select id="dir-notas-filtro-unidad" class="input-inline">
                            <option value="">Todas</option>
                            <option value="I">I</option>
                            <option value="II">II</option>
                            <option value="III">III</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="c-panel">
            <div class="c-panel-header"><i class="bi bi-clipboard-data"></i><h3>Registro de notas</h3></div>
            <div class="c-panel-body" style="padding-top:0;overflow-x:auto">
                <table class="c-table">
                    <thead>
                        <tr>
                            <th>Estudiante</th>
                            <th>Curso</th>
                            <th>Unidad</th>
                            <th>Práctica (20%)</th>
                            <th>Teoría (30%)</th>
                            <th>Examen (50%)</th>
                            <th>Promedio</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody id="dir-notas-tbody">
                        <tr><td colspan="8" class="c-table-empty">Cargando notas…</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/director/notas.js')
@endpush
