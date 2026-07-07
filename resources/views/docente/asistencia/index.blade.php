@extends('layouts.app', ['title' => 'Registro de Asistencia', 'subtitle' => auth()->user()->rol?->nombre])

@section('content')
    <div class="doc-shell">
        <div class="doc-asistencia-header">
            <div class="doc-asistencia-header-icon"><i class="bi bi-check2-square"></i></div>
            <div>
                <h2>Registro de Asistencia</h2>
                <p>Marca la asistencia de tus estudiantes por fecha</p>
            </div>
        </div>

        <div id="doc-asistencia-sin-curso" class="c-card doc-empty-state">
            <i class="bi bi-check2-square"></i>
            <h3>Selecciona un curso</h3>
            <p>Elige uno de tus cursos asignados para ver o registrar la asistencia de sus estudiantes.</p>
            <select id="doc-asistencia-curso-vacio" class="input-inline" style="max-width:320px;margin:14px auto 0">
                <option value="">Selecciona un curso…</option>
            </select>
        </div>

        <div id="doc-asistencia-contenido" hidden>
            <div class="doc-asistencia-card-curso">
                <div class="doc-asistencia-curso-info">
                    <div class="doc-asistencia-curso-icono"><i class="bi bi-code-slash"></i></div>
                    <div>
                        <small>CURSO SELECCIONADO</small>
                        <div class="doc-asistencia-curso-select-wrap">
                            <select id="doc-asistencia-curso" class="doc-asistencia-curso-select"></select>
                            <i class="bi bi-chevron-down"></i>
                        </div>
                        <div class="doc-asistencia-curso-sub"><i class="bi bi-people"></i> <span id="doc-asistencia-total-matriculados">0</span> estudiantes matriculados</div>
                    </div>
                </div>
                <div class="doc-asistencia-curso-derecha">
                    <div class="doc-asistencia-semestre">
                        <small>SEMESTRE</small>
                        <div id="doc-asistencia-semestre-valor">—</div>
                    </div>
                    <div class="doc-asistencia-stat">
                        <div class="doc-asistencia-stat-valor teal" id="doc-asistencia-stat-pct">—</div>
                        <small>ASISTENCIA</small>
                    </div>
                    <div class="doc-asistencia-stat">
                        <div class="doc-asistencia-stat-valor red" id="doc-asistencia-stat-ausentes">—</div>
                        <small>AUSENTES HOY</small>
                    </div>
                </div>
            </div>

            <div class="doc-asistencia-card-fecha">
                <div class="doc-asistencia-fecha-info">
                    <i class="bi bi-calendar-week"></i>
                    <div>
                        <small>FECHA DE LA SESIÓN</small>
                        <div class="doc-asistencia-fecha-nav">
                            <strong id="doc-asistencia-fecha-texto">—</strong>
                            <button type="button" id="doc-asistencia-fecha-anterior" class="c-btn c-btn-outline c-btn-sm"><i class="bi bi-chevron-left"></i></button>
                            <button type="button" id="doc-asistencia-fecha-siguiente" class="c-btn c-btn-outline c-btn-sm"><i class="bi bi-chevron-right"></i></button>
                            <button type="button" id="doc-asistencia-fecha-hoy" class="c-btn c-btn-outline c-btn-sm">Hoy</button>
                            <input type="date" id="doc-asistencia-fecha" style="display:none">
                        </div>
                    </div>
                </div>
                <button type="button" id="doc-asistencia-guardar" class="c-btn c-btn-primary">
                    <i class="bi bi-save"></i> Guardar asistencia
                </button>
            </div>

            <div id="doc-asistencia-alerta" class="c-panel" hidden>
                <div class="c-panel-header"><i class="bi bi-exclamation-triangle"></i><h3>Estudiantes con asistencia menor a 70%</h3></div>
                <div class="c-panel-body" id="doc-asistencia-alerta-lista"></div>
            </div>

            <div class="doc-asistencia-filtros">
                <div class="doc-asistencia-chips">
                    <button type="button" class="doc-asistencia-chip" data-filtro="PRESENTE">
                        <span class="doc-asistencia-dot teal"></span> Presentes <strong id="doc-asistencia-chip-presentes">0</strong>
                    </button>
                    <button type="button" class="doc-asistencia-chip" data-filtro="TARDANZA">
                        <span class="doc-asistencia-dot gold"></span> Tardanzas <strong id="doc-asistencia-chip-tardanzas">0</strong>
                    </button>
                    <button type="button" class="doc-asistencia-chip" data-filtro="AUSENTE">
                        <span class="doc-asistencia-dot red"></span> Ausentes <strong id="doc-asistencia-chip-ausentes">0</strong>
                    </button>
                </div>
                <div class="doc-asistencia-acciones-lista">
                    <input type="text" id="doc-asistencia-buscar" placeholder="Buscar estudiante…">
                    <button type="button" id="doc-asistencia-marcar-todos" class="c-btn c-btn-outline c-btn-sm">
                        <i class="bi bi-check2-all"></i> Marcar todos presentes
                    </button>
                </div>
            </div>

            <div id="doc-asistencia-lista"></div>
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
