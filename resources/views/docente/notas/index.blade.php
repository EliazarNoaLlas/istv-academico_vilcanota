@extends('layouts.app', ['title' => 'Registro de Notas', 'subtitle' => auth()->user()->rol?->nombre])

@section('content')
    <div class="doc-shell">
        <div class="doc-notas-toolbar">
            <div class="doc-notas-field">
                <label>Curso</label>
                <select id="doc-notas-curso" class="input-inline">
                    <option value="">Selecciona un curso…</option>
                </select>
            </div>
            <div class="doc-notas-field">
                <label>Unidad</label>
                <select id="doc-notas-unidad" class="input-inline">
                    <option value="I">Unidad I</option>
                    <option value="II">Unidad II</option>
                    <option value="III">Unidad III</option>
                </select>
            </div>
            <div class="doc-notas-acta-estado" id="doc-notas-acta-estado" hidden></div>
        </div>

        <div id="doc-notas-sin-curso" class="c-card doc-empty-state">
            <i class="bi bi-clipboard-data"></i>
            <h3>Selecciona un curso</h3>
            <p>Elige uno de tus cursos asignados para ver y registrar las notas de sus estudiantes.</p>
        </div>

        <div id="doc-notas-contenido" hidden>
            <div class="c-stat-grid" id="doc-notas-kpis">
                <div class="c-stat-card navy">
                    <div class="c-stat-label">Estudiantes</div>
                    <div class="c-stat-value" data-kpi="total">—</div>
                </div>
                <div class="c-stat-card teal">
                    <div class="c-stat-label">Aprobados</div>
                    <div class="c-stat-value" data-kpi="aprobados">—</div>
                </div>
                <div class="c-stat-card red">
                    <div class="c-stat-label">Desaprobados</div>
                    <div class="c-stat-value" data-kpi="desaprobados">—</div>
                </div>
                <div class="c-stat-card gold">
                    <div class="c-stat-label">Promedio General</div>
                    <div class="c-stat-value" data-kpi="promedio">—</div>
                </div>
            </div>

            <div class="c-panel">
                <div class="c-panel-header">
                    <i class="bi bi-clipboard-data"></i><h3>Estudiantes matriculados</h3>
                    <button type="button" id="doc-notas-guardar" class="c-btn c-btn-primary c-btn-sm">
                        <i class="bi bi-save"></i> Guardar borrador
                    </button>
                    <button type="button" id="doc-notas-cerrar" class="c-btn c-btn-danger c-btn-sm">
                        <i class="bi bi-lock"></i> Cerrar acta
                    </button>
                </div>
                <div class="c-panel-body" style="padding-top:0;overflow-x:auto">
                    <table class="c-table">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>DNI</th>
                                <th>Apellidos y Nombres</th>
                                <th>Práctica (20%)</th>
                                <th>Teoría (30%)</th>
                                <th>Examen (50%)</th>
                                <th>Promedio</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody id="doc-notas-tbody"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <div id="doc-notas-empty" class="c-card doc-empty-state" hidden>
            <i class="bi bi-people"></i>
            <h3>Sin estudiantes matriculados</h3>
            <p>Este curso todavía no tiene estudiantes matriculados en el periodo activo.</p>
        </div>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/docente/notas.js')
@endpush
