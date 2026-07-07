@extends('layouts.app', ['title' => 'Registro de Asistencia', 'subtitle' => auth()->user()->rol?->nombre])

@section('content')
    <div class="doc-shell">
        <div class="doc-asistencia-toolbar">
            <div class="doc-asistencia-field">
                <label>Curso</label>
                <select id="doc-asistencia-curso" class="input-inline">
                    <option value="">Selecciona un curso…</option>
                </select>
            </div>
            <div class="doc-asistencia-field">
                <label>Fecha</label>
                <input type="date" id="doc-asistencia-fecha" class="input-inline">
            </div>
            <div class="doc-asistencia-estado" id="doc-asistencia-estado" hidden></div>
        </div>

        <div id="doc-asistencia-sin-curso" class="c-card doc-empty-state">
            <i class="bi bi-check2-square"></i>
            <h3>Selecciona un curso y una fecha</h3>
            <p>Elige uno de tus cursos asignados y una fecha para ver o registrar la asistencia de sus estudiantes.</p>
        </div>

        <div id="doc-asistencia-contenido" hidden>
            <div class="c-stat-grid" id="doc-asistencia-kpis">
                <div class="c-stat-card teal">
                    <div class="c-stat-label">Presentes</div>
                    <div class="c-stat-value" data-kpi="presentes">—</div>
                </div>
                <div class="c-stat-card red">
                    <div class="c-stat-label">Ausentes</div>
                    <div class="c-stat-value" data-kpi="ausentes">—</div>
                </div>
                <div class="c-stat-card gold">
                    <div class="c-stat-label">Tardanzas</div>
                    <div class="c-stat-value" data-kpi="tardanzas">—</div>
                </div>
                <div class="c-stat-card navy">
                    <div class="c-stat-label">Justificados</div>
                    <div class="c-stat-value" data-kpi="justificados">—</div>
                </div>
                <div class="c-stat-card teal">
                    <div class="c-stat-label">% Asistencia del día</div>
                    <div class="c-stat-value" data-kpi="porcentaje">—</div>
                </div>
            </div>

            <div id="doc-asistencia-alerta" class="c-panel" hidden>
                <div class="c-panel-header"><i class="bi bi-exclamation-triangle"></i><h3>Estudiantes con asistencia menor a 70%</h3></div>
                <div class="c-panel-body" id="doc-asistencia-alerta-lista"></div>
            </div>

            <div class="c-panel">
                <div class="c-panel-header">
                    <i class="bi bi-check2-square"></i><h3>Estudiantes matriculados</h3>
                    <button type="button" id="doc-asistencia-guardar" class="c-btn c-btn-primary c-btn-sm">
                        <i class="bi bi-save"></i> Guardar asistencia
                    </button>
                </div>
                <div class="c-panel-body" style="padding-top:0;overflow-x:auto">
                    <table class="c-table">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>DNI</th>
                                <th>Apellidos y Nombres</th>
                                <th>Estado</th>
                                <th>Observación</th>
                            </tr>
                        </thead>
                        <tbody id="doc-asistencia-tbody"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <div id="doc-asistencia-empty" class="c-card doc-empty-state" hidden>
            <i class="bi bi-people"></i>
            <h3>Sin estudiantes matriculados</h3>
            <p>Este curso todavía no tiene estudiantes matriculados en el periodo activo.</p>
        </div>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/docente/asistencia.js')
@endpush
