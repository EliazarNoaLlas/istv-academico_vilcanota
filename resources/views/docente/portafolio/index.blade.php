@extends('layouts.app', ['title' => 'Portafolio', 'subtitle' => auth()->user()->rol?->nombre])

@section('content')
    <div class="doc-shell">
        <div id="doc-portafolio-grid-wrap">
            <div class="c-panel">
                <div class="c-panel-body">
                    <div class="coord-portafolio-toolbar">
                        <select id="doc-portafolio-curso" data-id-docente="{{ $miDocente->id_docente }}" data-id-periodo="{{ $periodoActivo?->id_periodo }}" data-periodo-codigo="{{ $periodoActivo?->codigo }}">
                            @forelse ($miDocente->cursos as $curso)
                                <option value="{{ $curso->id_curso }}" data-semestre="{{ $curso->semestre }}">{{ $curso->nombre_curso }} ({{ $curso->semestre }})</option>
                            @empty
                                <option value="">Aún no tienes cursos asignados</option>
                            @endforelse
                        </select>
                    </div>
                    <div class="coord-portafolio-form-error" id="doc-portafolio-error"></div>
                </div>
            </div>

            <div class="coord-mi-portafolio-grid" id="doc-portafolio-grid"></div>
        </div>

        <div class="coord-portafolio-modal-backdrop" id="doc-portafolio-upload-modal">
            <div class="coord-portafolio-modal">
                <h2><i class="bi bi-cloud-arrow-up"></i> Subir documento</h2>

                <div class="form-group">
                    <label>Documento</label>
                    <input type="file" id="doc-portafolio-upload-archivo">
                </div>

                <div class="form-group">
                    <label>Curso asignado</label>
                    <select id="doc-portafolio-upload-curso"></select>
                </div>

                <div class="form-group">
                    <label>Semestre asignado</label>
                    <select id="doc-portafolio-upload-semestre" disabled></select>
                </div>

                <div class="coord-portafolio-form-error" id="doc-portafolio-upload-error"></div>

                <div class="coord-portafolio-modal-actions">
                    <button type="button" class="c-btn c-btn-outline" id="doc-portafolio-upload-cancelar">Cancelar</button>
                    <button type="button" class="c-btn c-btn-primary" id="doc-portafolio-upload-confirmar">
                        <i class="bi bi-cloud-upload"></i> Subir archivo
                    </button>
                </div>
            </div>
        </div>

        <div class="c-panel coord-sesiones-manager" id="doc-sesiones-manager" style="display:none" data-id-docente="{{ $miDocente->id_docente }}">
            <div class="c-panel-header">
                <i class="bi bi-journal-text"></i><h3>Sesiones de aprendizaje</h3>
                <button type="button" class="c-btn c-btn-outline c-btn-sm" id="doc-sesiones-volver">Volver</button>
            </div>
            <div class="c-panel-body">
                <div class="coord-sesiones-grid">
                    <div class="coord-sesiones-col">
                        <h4>Acciones</h4>
                        <div class="coord-sesiones-docente-card">
                            <div class="c-avatar-sm">{{ strtoupper(substr(auth()->user()->nombres, 0, 1) . substr(auth()->user()->apellidos ?? '', 0, 1)) }}</div>
                            <div>
                                <div>{{ auth()->user()->nombres }} {{ auth()->user()->apellidos }}</div>
                                <small>{{ $miDocente->cursos->count() }} cursos asignados</small>
                            </div>
                        </div>

                        <div class="coord-sesiones-seleccionado" id="doc-sesiones-seleccionado" style="display:none">
                            <small>SELECCIONADO</small>
                            <div>Curso: <strong id="doc-sesiones-curso-actual"></strong></div>
                        </div>

                        <button type="button" class="c-btn c-btn-primary" id="doc-sesiones-subir" disabled>
                            <i class="bi bi-cloud-upload"></i> Subir sesión
                        </button>
                        <button type="button" class="c-btn c-btn-outline coord-sesiones-btn-eliminar" id="doc-sesiones-eliminar" disabled>
                            <i class="bi bi-trash"></i> Eliminar
                        </button>
                        <p class="coord-sesiones-hint">Elige una sesión de la lista de la derecha para poder eliminarla.</p>
                        <div class="coord-portafolio-form-error" id="doc-sesiones-error"></div>
                    </div>

                    <div class="coord-sesiones-col">
                        <h4><i class="bi bi-journal-bookmark"></i> Cursos</h4>
                        <p class="coord-sesiones-hint">Filtra las sesiones por curso</p>
                        <div id="doc-sesiones-lista-cursos"></div>
                    </div>

                    <div class="coord-sesiones-col coord-sesiones-col-lista">
                        <div class="coord-sesiones-lista-header">
                            <h4 id="doc-sesiones-lista-titulo"><i class="bi bi-list"></i> Seleccione un curso</h4>
                            <input type="text" id="doc-sesiones-buscar" placeholder="Buscar sesión...">
                        </div>
                        <div id="doc-sesiones-lista-items"></div>
                    </div>
                </div>
            </div>
        </div>
        <input type="file" id="doc-sesiones-input-archivo" style="display:none">

        {{-- Gestor de Asistencia: mismo bloque/ids que /docente/asistencia, reutiliza ese JS ya existente. --}}
        <div id="doc-portafolio-asistencia-manager" style="display:none">
            <div class="coord-sesiones-manager" style="border-bottom:none;margin-bottom:0">
                <div class="c-panel-header">
                    <i class="bi bi-check2-square"></i><h3>Registro de Asistencia</h3>
                    <button type="button" class="c-btn c-btn-outline c-btn-sm" id="doc-portafolio-asistencia-volver">Volver</button>
                </div>
            </div>

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

        {{-- Gestor de Notas: mismo bloque/ids que /docente/notas, reutiliza ese JS ya existente. --}}
        <div id="doc-portafolio-notas-manager" style="display:none">
            <div class="coord-sesiones-manager" style="border-bottom:none;margin-bottom:0">
                <div class="c-panel-header">
                    <i class="bi bi-clipboard-data"></i><h3>Registro de Notas</h3>
                    <button type="button" class="c-btn c-btn-outline c-btn-sm" id="doc-portafolio-notas-volver">Volver</button>
                </div>
            </div>

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
    </div>
@endsection

@push('scripts')
    @vite(['resources/js/docente/portafolio.js', 'resources/js/docente/asistencia.js', 'resources/js/docente/notas.js'])
@endpush
