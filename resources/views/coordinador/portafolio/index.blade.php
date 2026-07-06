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

        @if ($miDocente)
            <div class="c-tabs">
                <button type="button" class="c-tab-btn active" data-tab="revision">Revisión de docentes</button>
                <button type="button" class="c-tab-btn" data-tab="mio">Mi portafolio</button>
            </div>
        @endif

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

        @if ($miDocente)
            <div class="c-tab-pane" id="tab-mio">
                <div class="c-panel">
                    <div class="c-panel-header"><i class="bi bi-upload"></i><h3>Subir documento</h3></div>
                    <div class="c-panel-body">
                        <form id="coord-mi-portafolio-form" class="coord-portafolio-toolbar" enctype="multipart/form-data" data-id-docente="{{ $miDocente->id_docente }}">
                            <select name="id_curso" required>
                                <option value="">Seleccione curso</option>
                                @foreach ($miDocente->cursos as $curso)
                                    <option value="{{ $curso->id_curso }}">{{ $curso->nombre_curso }} ({{ $curso->semestre }})</option>
                                @endforeach
                            </select>

                            <select name="tipo" required>
                                <option value="SILABO">Sílabo</option>
                                <option value="PLAN_SESION">Plan de sesión</option>
                                <option value="EVALUACION">Evaluación</option>
                                <option value="INSTRUMENTO">Instrumento</option>
                                <option value="ASISTENCIA">Asistencia</option>
                                <option value="NOTAS">Notas</option>
                                <option value="EVIDENCIA">Evidencia</option>
                                <option value="ACTA">Acta</option>
                                <option value="OTRO">Otro</option>
                            </select>

                            <input type="text" name="titulo" placeholder="Título del documento" required>
                            <input type="hidden" name="id_periodo" value="{{ $periodoActivo?->id_periodo }}">
                            <input type="file" name="documento" required>

                            <button type="submit" class="c-btn c-btn-primary c-btn-sm">
                                <i class="bi bi-upload"></i> Subir
                            </button>
                        </form>
                        <div class="coord-portafolio-form-error" id="coord-mi-portafolio-error"></div>
                    </div>
                </div>

                <div class="c-panel">
                    <div class="c-panel-header"><i class="bi bi-folder2-open"></i><h3>Mis documentos</h3></div>
                    <div class="c-panel-body" style="padding-top:0">
                        <table class="c-table">
                            <thead>
                                <tr>
                                    <th>Documento</th>
                                    <th>Curso</th>
                                    <th>Tipo</th>
                                    <th>Estado</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="coord-mi-portafolio-tbody">
                                <tr><td colspan="5" class="coord-portafolio-empty">Cargando documentos…</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
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
