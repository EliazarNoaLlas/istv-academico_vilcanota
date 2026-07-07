@extends('layouts.app', ['title' => 'Portafolio', 'subtitle' => 'Revisión de documentos docentes'])

@section('content')
    <div class="coord-shell">
        <div class="coord-hero">
            <div>
                <small>COORDINADOR ACADÉMICO</small>
                <h2>Portafolio Docente</h2>
                <p>Revisión, aprobación y observación de documentos subidos por los docentes.</p>
            </div>
        </div>

        <div class="c-tabs">
            <button type="button" class="c-tab-btn active" data-tab="revision">Revisar portafolios</button>
            <button type="button" class="c-tab-btn" data-tab="mio">Mi portafolio</button>
        </div>

        <div class="c-tab-pane active" id="tab-revision">
            <div class="coord-kpis" id="coord-portafolio-kpis">
                <div class="c-stat-card teal">
                    <i class="bi bi-archive c-stat-icon"></i>
                    <div class="c-stat-label">Total documentos</div>
                    <div class="c-stat-value" data-kpi="total">—</div>
                </div>
                <div class="c-stat-card gold">
                    <i class="bi bi-check-circle c-stat-icon"></i>
                    <div class="c-stat-label">Aprobados</div>
                    <div class="c-stat-value" data-kpi="aprobados">—</div>
                </div>
                <div class="c-stat-card red">
                    <i class="bi bi-exclamation-circle c-stat-icon"></i>
                    <div class="c-stat-label">Observados</div>
                    <div class="c-stat-value" data-kpi="observados">—</div>
                </div>
                <div class="c-stat-card navy">
                    <i class="bi bi-clock-history c-stat-icon"></i>
                    <div class="c-stat-label">Pendientes / en revisión</div>
                    <div class="c-stat-value" data-kpi="pendientes">—</div>
                </div>
            </div>

            <div class="c-panel">
                <div class="c-panel-header"><i class="bi bi-funnel"></i><h3>Filtros de portafolio</h3></div>
                <div class="c-panel-body">
                    <div class="coord-portafolio-toolbar">
                        <select id="coord-portafolio-filtro-docente">
                            <option value="">Todos los docentes</option>
                            @foreach ($docentes as $docente)
                                <option value="{{ $docente->id_docente }}">{{ $docente->usuario->nombres }} {{ $docente->usuario->apellidos }}</option>
                            @endforeach
                        </select>

                        <select id="coord-portafolio-filtro-curso">
                            <option value="">Todos los cursos</option>
                            @foreach ($cursos as $curso)
                                <option value="{{ $curso->id_curso }}" data-id-docente="{{ $curso->id_docente }}" data-semestre="{{ $curso->semestre }}">{{ $curso->nombre_curso }}</option>
                            @endforeach
                        </select>

                        <select id="coord-portafolio-filtro-estado">
                            <option value="">Todos los estados</option>
                            <option value="PENDIENTE">Pendiente</option>
                            <option value="SUBIDO">Subido</option>
                            <option value="APROBADO">Aprobado</option>
                            <option value="OBSERVADO">Observado</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="c-panel">
                <div class="c-panel-header"><i class="bi bi-table"></i><h3>Documentos del portafolio</h3></div>
                <div class="c-panel-body" style="padding-top:0">
                    <table class="c-table">
                        <thead>
                            <tr>
                                <th>Documento</th>
                                <th>Docente</th>
                                <th>Curso</th>
                                <th>Tipo</th>
                                <th>Estado</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="coord-portafolio-tbody">
                            <tr><td colspan="6" class="coord-portafolio-empty">Cargando documentos…</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="c-tab-pane" id="tab-mio">
            @if ($miDocente)
                <div class="c-panel">
                    <div class="c-panel-body">
                        <div class="coord-portafolio-toolbar">
                            <select id="coord-mi-portafolio-curso" data-id-docente="{{ $miDocente->id_docente }}" data-id-periodo="{{ $periodoActivo?->id_periodo }}">
                                @forelse ($miDocente->cursos as $curso)
                                    <option value="{{ $curso->id_curso }}" data-semestre="{{ $curso->semestre }}">{{ $curso->nombre_curso }} ({{ $curso->semestre }})</option>
                                @empty
                                    <option value="">Aún no tienes cursos asignados</option>
                                @endforelse
                            </select>
                        </div>
                        <div class="coord-portafolio-form-error" id="coord-mi-portafolio-error"></div>
                    </div>
                </div>

                <div class="coord-mi-portafolio-grid" id="coord-mi-portafolio-grid"></div>

                <div class="coord-portafolio-modal-backdrop" id="coord-mi-portafolio-upload-modal">
                    <div class="coord-portafolio-modal">
                        <h2><i class="bi bi-cloud-arrow-up"></i> Subir documento</h2>

                        <div class="form-group">
                            <label>Documento</label>
                            <input type="file" id="coord-mi-portafolio-upload-archivo">
                        </div>

                        <div class="form-group">
                            <label>Curso asignado</label>
                            <select id="coord-mi-portafolio-upload-curso"></select>
                        </div>

                        <div class="form-group">
                            <label>Semestre asignado</label>
                            <select id="coord-mi-portafolio-upload-semestre" disabled></select>
                        </div>

                        <div class="coord-portafolio-form-error" id="coord-mi-portafolio-upload-error"></div>

                        <div class="coord-portafolio-modal-actions">
                            <button type="button" class="c-btn c-btn-outline" id="coord-mi-portafolio-upload-cancelar">Cancelar</button>
                            <button type="button" class="c-btn c-btn-primary" id="coord-mi-portafolio-upload-confirmar">
                                <i class="bi bi-cloud-upload"></i> Subir archivo
                            </button>
                        </div>
                    </div>
                </div>

                <div class="c-panel coord-sesiones-manager" id="coord-sesiones-manager" style="display:none" data-id-docente="{{ $miDocente->id_docente }}">
                    <div class="c-panel-header">
                        <i class="bi bi-journal-text"></i><h3>Sesiones de aprendizaje</h3>
                        <button type="button" class="c-btn c-btn-outline c-btn-sm" id="coord-sesiones-volver">Volver</button>
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

                                <div class="coord-sesiones-seleccionado" id="coord-sesiones-seleccionado" style="display:none">
                                    <small>SELECCIONADO</small>
                                    <div>Curso: <strong id="coord-sesiones-curso-actual"></strong></div>
                                </div>

                                <button type="button" class="c-btn c-btn-primary" id="coord-sesiones-subir" disabled>
                                    <i class="bi bi-cloud-upload"></i> Subir sesión
                                </button>
                                <button type="button" class="c-btn c-btn-outline coord-sesiones-btn-eliminar" id="coord-sesiones-eliminar" disabled>
                                    <i class="bi bi-trash"></i> Eliminar
                                </button>
                                <p class="coord-sesiones-hint">Elige una sesión de la lista de la derecha para poder eliminarla.</p>
                                <div class="coord-portafolio-form-error" id="coord-sesiones-error"></div>
                            </div>

                            <div class="coord-sesiones-col">
                                <h4><i class="bi bi-journal-bookmark"></i> Cursos</h4>
                                <p class="coord-sesiones-hint">Filtra las sesiones por curso</p>
                                <div id="coord-sesiones-lista-cursos"></div>
                            </div>

                            <div class="coord-sesiones-col coord-sesiones-col-lista">
                                <div class="coord-sesiones-lista-header">
                                    <h4 id="coord-sesiones-lista-titulo"><i class="bi bi-list"></i> Seleccione un curso</h4>
                                    <input type="text" id="coord-sesiones-buscar" placeholder="Buscar sesión...">
                                </div>
                                <div id="coord-sesiones-lista-items"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <input type="file" id="coord-sesiones-input-archivo" style="display:none">

                {{-- Reutiliza las clases coord-sesiones-* (mismo layout de 3 columnas). --}}
                <div class="c-panel coord-sesiones-manager" id="coord-notas-manager" style="display:none" data-id-docente="{{ $miDocente->id_docente }}">
                    <div class="c-panel-header">
                        <i class="bi bi-clipboard-data"></i><h3>Ingresar Notas</h3>
                        <button type="button" class="c-btn c-btn-outline c-btn-sm" id="coord-notas-volver">Volver</button>
                    </div>
                    <div class="c-panel-body">
                        <div class="coord-sesiones-grid">
                            <div class="coord-sesiones-col">
                                <h4>Acciones</h4>
                                <div class="coord-sesiones-docente-card">
                                    <div class="c-avatar-sm">{{ strtoupper(substr(auth()->user()->nombres, 0, 1) . substr(auth()->user()->apellidos ?? '', 0, 1)) }}</div>
                                    <div>{{ auth()->user()->nombres }} {{ auth()->user()->apellidos }}</div>
                                </div>

                                <div class="coord-sesiones-seleccionado" id="coord-notas-seleccionado" style="display:none">
                                    <small>SELECCIONADO</small>
                                    <div>Curso: <strong id="coord-notas-curso-actual"></strong></div>
                                </div>

                                <label class="coord-sesiones-hint" style="display:block;font-weight:600;color:var(--navy)">Unidad</label>
                                <select id="coord-notas-unidad" class="input-inline" style="width:100%;margin-bottom:14px">
                                    @foreach (['I', 'II', 'III', 'IV', 'V', 'VI'] as $unidad)
                                        <option value="{{ $unidad }}">{{ $unidad }}</option>
                                    @endforeach
                                </select>
                                <div class="coord-portafolio-form-error" id="coord-notas-error"></div>
                            </div>

                            <div class="coord-sesiones-col">
                                <h4><i class="bi bi-journal-bookmark"></i> Cursos</h4>
                                <p class="coord-sesiones-hint">Filtra los estudiantes por curso</p>
                                <div id="coord-notas-lista-cursos"></div>
                            </div>

                            <div class="coord-sesiones-col coord-sesiones-col-lista">
                                <div class="coord-sesiones-lista-header">
                                    <h4 id="coord-notas-lista-titulo"><i class="bi bi-people"></i> Seleccione un curso</h4>
                                </div>
                                <div id="coord-notas-lista-estudiantes"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="c-panel coord-sesiones-manager" id="coord-asistencia-manager" style="display:none" data-id-docente="{{ $miDocente->id_docente }}">
                    <div class="c-panel-header">
                        <i class="bi bi-calendar-check"></i><h3>Ingresar Asistencia</h3>
                        <button type="button" class="c-btn c-btn-outline c-btn-sm" id="coord-asistencia-volver">Volver</button>
                    </div>
                    <div class="c-panel-body">
                        <div class="coord-sesiones-grid">
                            <div class="coord-sesiones-col">
                                <h4>Acciones</h4>
                                <div class="coord-sesiones-docente-card">
                                    <div class="c-avatar-sm">{{ strtoupper(substr(auth()->user()->nombres, 0, 1) . substr(auth()->user()->apellidos ?? '', 0, 1)) }}</div>
                                    <div>{{ auth()->user()->nombres }} {{ auth()->user()->apellidos }}</div>
                                </div>

                                <div class="coord-sesiones-seleccionado" id="coord-asistencia-seleccionado" style="display:none">
                                    <small>SELECCIONADO</small>
                                    <div>Curso: <strong id="coord-asistencia-curso-actual"></strong></div>
                                </div>

                                <label class="coord-sesiones-hint" style="display:block;font-weight:600;color:var(--navy)">Mes</label>
                                <div class="coord-asistencia-mes-nav">
                                    <button type="button" class="c-btn c-btn-outline c-btn-sm" id="coord-asistencia-mes-anterior"><i class="bi bi-chevron-left"></i></button>
                                    <span class="coord-asistencia-mes-actual" id="coord-asistencia-mes-actual">—</span>
                                    <button type="button" class="c-btn c-btn-outline c-btn-sm" id="coord-asistencia-mes-siguiente"><i class="bi bi-chevron-right"></i></button>
                                </div>

                                <button type="button" class="c-btn c-btn-primary" id="coord-asistencia-guardar" disabled>
                                    <i class="bi bi-save"></i> Guardar cambios
                                </button>
                                <p class="coord-asistencia-pendientes" id="coord-asistencia-pendientes">Sin cambios pendientes</p>
                                <div class="coord-portafolio-form-error" id="coord-asistencia-error"></div>
                            </div>

                            <div class="coord-sesiones-col">
                                <h4><i class="bi bi-journal-bookmark"></i> Cursos</h4>
                                <p class="coord-sesiones-hint">Filtra la asistencia por curso</p>
                                <div id="coord-asistencia-lista-cursos"></div>
                            </div>

                            <div class="coord-sesiones-col coord-sesiones-col-lista">
                                <div class="coord-sesiones-lista-header">
                                    <h4 id="coord-asistencia-lista-titulo"><i class="bi bi-table"></i> Seleccione un curso</h4>
                                </div>
                                <div class="coord-asistencia-leyenda">
                                    <span><i style="background:rgba(26,191,160,.5)"></i> Presente (clic para cambiar)</span>
                                    <span><i style="background:rgba(212,160,23,.5)"></i> Tardanza</span>
                                    <span><i style="background:rgba(224,80,80,.5)"></i> Ausente</span>
                                    <span><i style="background:rgba(11,28,58,.3)"></i> Justificado</span>
                                    <span><i style="background:var(--surface);border:1px solid var(--border)"></i> Sin sesión (clic para tomarla)</span>
                                </div>
                                <div class="coord-asistencia-matriz-scroll">
                                    <table class="coord-asistencia-matriz" id="coord-asistencia-tabla"></table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="c-panel">
                    <div class="c-panel-body">
                        <p class="coord-portafolio-empty">Tu cuenta no tiene un perfil docente asociado, así que no dictas cursos propios para tener un portafolio. Si deberías dictar clases, contacta a Dirección Académica.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="coord-portafolio-modal-backdrop" id="coord-portafolio-modal">
        <div class="coord-portafolio-modal">
            <h2 id="coord-portafolio-modal-title">Revisar documento</h2>
            <p id="coord-portafolio-modal-sub"></p>

            <span class="coord-portafolio-ia-nota" id="coord-portafolio-ia-resultado"></span>

            <textarea id="coord-portafolio-observacion" placeholder="Observaciones (opcional si apruebas)"></textarea>

            <div class="coord-portafolio-modal-actions">
                <button type="button" class="c-btn c-btn-outline" id="coord-portafolio-analizar-ia">
                    <i class="bi bi-stars"></i> Analizar con IA
                </button>
                <button type="button" class="c-btn c-btn-outline" id="coord-portafolio-cerrar">Cancelar</button>
                <button type="button" class="c-btn c-btn-danger" id="coord-portafolio-observar">Observar</button>
                <button type="button" class="c-btn c-btn-primary" id="coord-portafolio-aprobar">Aprobar</button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/coordinador/portafolio.js')
@endpush
