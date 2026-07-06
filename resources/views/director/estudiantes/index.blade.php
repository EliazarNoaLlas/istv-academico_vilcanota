@extends('layouts.app', ['title' => 'Estudiantes', 'subtitle' => 'Matrícula y seguimiento institucional'])

@section('content')
    <div class="dir-shell">
        <div class="dir-hero">
            <div>
                <small>DIRECCIÓN ACADÉMICA</small>
                <h2>Estudiantes</h2>
                <p>Matrícula y seguimiento académico institucional.</p>
            </div>
        </div>

        <div class="dir-kpis" id="dir-estudiantes-kpis">
            <div class="c-stat-card teal">
                <i class="bi bi-mortarboard c-stat-icon"></i>
                <div class="c-stat-label">Matriculados</div>
                <div class="c-stat-value" data-kpi="total">—</div>
            </div>
            <div class="c-stat-card navy">
                <i class="bi bi-graph-up c-stat-icon"></i>
                <div class="c-stat-label">Promedio general</div>
                <div class="c-stat-value" data-kpi="promedio">—</div>
            </div>
            <div class="c-stat-card gold">
                <i class="bi bi-exclamation-circle c-stat-icon"></i>
                <div class="c-stat-label">Observados</div>
                <div class="c-stat-value" data-kpi="observados">—</div>
            </div>
            <div class="c-stat-card red">
                <i class="bi bi-exclamation-triangle c-stat-icon"></i>
                <div class="c-stat-label">En riesgo</div>
                <div class="c-stat-value" data-kpi="riesgo">—</div>
            </div>
        </div>

        <div class="c-panel">
            <div class="c-panel-header"><i class="bi bi-funnel"></i><h3>Filtros de estudiantes</h3></div>
            <div class="c-panel-body">
                <div class="coord-filter-grid">
                    <div class="coord-filter-field">
                        <label>Programa</label>
                        <select id="dir-estudiantes-filtro-programa" class="input-inline">
                            <option value="">Todos los programas</option>
                            @foreach ($programas as $programa)
                                <option value="{{ $programa->id_programa }}">{{ $programa->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="coord-filter-field">
                        <label>Ciclo</label>
                        <select id="dir-estudiantes-filtro-ciclo" class="input-inline">
                            <option value="">Todos</option>
                            @foreach (['I', 'II', 'III', 'IV', 'V', 'VI'] as $ciclo)
                                <option value="{{ $ciclo }}">Semestre {{ $ciclo }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="c-panel">
            <div class="c-panel-header">
                <i class="bi bi-people"></i><h3>Relación de estudiantes</h3>
                <button type="button" id="dir-estudiantes-nuevo" class="c-btn c-btn-primary c-btn-sm">
                    <i class="bi bi-plus-lg"></i> Nuevo estudiante
                </button>
            </div>
            <div class="c-panel-body" style="padding-top:0">
                <table class="c-table">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Estudiante</th>
                            <th>Programa</th>
                            <th>Ciclo</th>
                            <th>Promedio</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody id="dir-estudiantes-tbody">
                        <tr><td colspan="6" class="c-table-empty">Cargando estudiantes…</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="dir-usuarios-modal-backdrop" id="dir-estudiantes-modal">
        <div class="dir-usuarios-modal">
            <div class="dir-usuarios-modal-head">
                <h2>Nuevo estudiante</h2>
                <p class="dir-usuarios-modal-subtitle">Complete los datos del estudiante. Los campos marcados con * son obligatorios.</p>
            </div>

            <div class="dir-usuarios-form-error" id="dir-estudiantes-form-error"></div>

            <form id="dir-estudiantes-form" novalidate>
                <div class="dir-usuarios-modal-grid">
                    <div class="form-group">
                        <label>Nombres *</label>
                        <input type="text" name="nombres" required>
                        <small class="dir-usuarios-field-error" data-error-for="nombres"></small>
                    </div>
                    <div class="form-group">
                        <label>Apellido paterno *</label>
                        <input type="text" name="apellido_paterno" required>
                        <small class="dir-usuarios-field-error" data-error-for="apellido_paterno"></small>
                    </div>
                    <div class="form-group">
                        <label>Apellido materno</label>
                        <input type="text" name="apellido_materno">
                        <small class="dir-usuarios-field-error" data-error-for="apellido_materno"></small>
                    </div>
                    <div class="form-group">
                        <label>DNI *</label>
                        <input type="text" name="dni" maxlength="8" required>
                        <small class="dir-usuarios-field-error" data-error-for="dni"></small>
                    </div>
                    <div class="form-group">
                        <label>Teléfono</label>
                        <input type="text" name="telefono">
                        <small class="dir-usuarios-field-error" data-error-for="telefono"></small>
                    </div>
                    <div class="form-group">
                        <label>Programa de estudios *</label>
                        <select name="id_programa" required>
                            <option value="">Seleccione programa</option>
                            @foreach ($programas as $programa)
                                <option value="{{ $programa->id_programa }}">{{ $programa->nombre }}</option>
                            @endforeach
                        </select>
                        <small class="dir-usuarios-field-error" data-error-for="id_programa"></small>
                    </div>
                    <div class="form-group">
                        <label>Semestre *</label>
                        <select name="ciclo" required>
                            @foreach (['I', 'II', 'III', 'IV', 'V', 'VI'] as $ciclo)
                                <option value="{{ $ciclo }}">Semestre {{ $ciclo }}</option>
                            @endforeach
                        </select>
                        <small class="dir-usuarios-field-error" data-error-for="ciclo"></small>
                    </div>
                </div>

                <div class="dir-usuarios-modal-actions">
                    <button type="button" class="c-btn c-btn-outline" id="dir-estudiantes-modal-cerrar">Cancelar</button>
                    <button type="submit" class="c-btn c-btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/director/estudiantes.js')
@endpush
