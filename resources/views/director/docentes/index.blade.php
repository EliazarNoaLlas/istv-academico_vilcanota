@extends('layouts.app', ['title' => 'Docentes', 'subtitle' => 'Carga académica institucional'])

@section('content')
    <div class="dir-shell">
        <div class="dir-hero">
            <div>
                <small>DIRECCIÓN ACADÉMICA</small>
                <h2>Docentes</h2>
                <p>Carga horaria y cursos asignados por docente, a nivel institucional.</p>
            </div>
            <div class="dir-docentes-hero-actions">
                <button type="button" class="c-btn c-btn-primary c-btn-sm" id="dir-docentes-btn-asignar">
                    <i class="bi bi-person-plus"></i> Asignar cursos
                </button>
                <a href="{{ route('director.docentes.export.excel') }}" class="c-btn c-btn-outline c-btn-sm dir-docentes-btn-light">
                    <i class="bi bi-file-earmark-excel"></i> Exportar Excel
                </a>
                <a href="{{ route('director.docentes.export.pdf') }}" class="c-btn c-btn-outline c-btn-sm dir-docentes-btn-light">
                    <i class="bi bi-file-earmark-pdf"></i> Exportar PDF
                </a>
                <button type="button" class="c-btn c-btn-outline c-btn-sm dir-docentes-btn-light" onclick="window.print()">
                    <i class="bi bi-printer"></i> Imprimir
                </button>
            </div>
        </div>

        <div class="dir-kpis" id="dir-docentes-kpis">
            <div class="c-stat-card teal">
                <i class="bi bi-person-video3 c-stat-icon"></i>
                <div class="c-stat-label">Total docentes</div>
                <div class="c-stat-value" data-kpi="total">—</div>
                <div class="c-stat-sub">Docentes registrados</div>
            </div>
            <div class="c-stat-card gold">
                <i class="bi bi-clock c-stat-icon"></i>
                <div class="c-stat-label">Carga promedio</div>
                <div class="c-stat-value" data-kpi="carga-promedio">—</div>
                <div class="c-stat-sub">Horas semanales promedio</div>
            </div>
            <div class="c-stat-card navy">
                <i class="bi bi-journal-bookmark c-stat-icon"></i>
                <div class="c-stat-label">Cursos asignados</div>
                <div class="c-stat-value" data-kpi="cursos-asignados">—</div>
                <div class="c-stat-sub">Cursos en total</div>
            </div>
            <div class="c-stat-card red">
                <i class="bi bi-exclamation-triangle c-stat-icon"></i>
                <div class="c-stat-label">Sobrecarga (&gt;40h)</div>
                <div class="c-stat-value" data-kpi="sobrecarga">—</div>
                <div class="c-stat-sub">Docentes sobrecargados</div>
            </div>
        </div>

        <div class="c-panel">
            <div class="c-panel-header"><i class="bi bi-funnel"></i><h3>Filtros de docentes</h3></div>
            <div class="c-panel-body dir-docentes-toolbar">
                <div class="form-group">
                    <label>Programa</label>
                    <select id="dir-docentes-filtro-programa" class="input-inline">
                        <option value="">Todos los programas</option>
                        @foreach ($programas as $programa)
                            <option value="{{ $programa->id_programa }}">{{ $programa->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Especialidad</label>
                    <select id="dir-docentes-filtro-especialidad" class="input-inline">
                        <option value="">Todas las especialidades</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Estado de carga</label>
                    <select id="dir-docentes-filtro-estado" class="input-inline">
                        <option value="">Todos</option>
                        <option value="SIN_CARGA">Sin carga</option>
                        <option value="NORMAL">Carga normal</option>
                        <option value="MODERADA">Carga moderada</option>
                        <option value="ALTA">Carga alta</option>
                        <option value="SOBRECARGA">Sobrecarga</option>
                    </select>
                </div>
                <div class="form-group dir-docentes-search">
                    <label>Buscar docente</label>
                    <input type="text" id="dir-docentes-search" class="input-inline" placeholder="Buscar por nombre...">
                </div>
                <button type="button" class="c-btn c-btn-outline c-btn-sm dir-docentes-btn-clear" id="dir-docentes-limpiar">
                    <i class="bi bi-arrow-counterclockwise"></i> Limpiar
                </button>
            </div>
        </div>

        <div class="dir-docentes-grid">
            <div class="c-panel">
                <div class="c-panel-header"><i class="bi bi-person-video3"></i><h3>Registro de docentes</h3></div>
                <div class="c-panel-body" style="padding-top:0">
                    <div class="dir-docentes-table-scroll">
                        <table class="c-table dir-docentes-table">
                            <thead>
                                <tr>
                                    <th>Docente</th>
                                    <th>Especialidad</th>
                                    <th>Tipo docente</th>
                                    <th>Cursos</th>
                                    <th colspan="7">Carga semanal (horas)</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="dir-docentes-tbody">
                                <tr><td colspan="13" class="c-table-empty">Cargando docentes…</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="dir-docentes-footer">
                        <div id="dir-docentes-resumen">Mostrando 0 de 0 docentes</div>
                        <div class="dir-docentes-footer-right">
                            Mostrar
                            <select id="dir-docentes-page-size" class="input-inline">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                            </select>
                            registros
                        </div>
                        <div class="dir-docentes-pagination" id="dir-docentes-pagination"></div>
                    </div>
                </div>
            </div>

            <div>
                <div class="c-panel">
                    <div class="c-panel-header"><i class="bi bi-diagram-3"></i><h3>Resumen por especialidad</h3></div>
                    <div class="c-panel-body" id="dir-docentes-especialidades"></div>
                </div>

                <div class="c-panel">
                    <div class="c-panel-header"><i class="bi bi-people"></i><h3>Docentes disponibles</h3></div>
                    <div class="c-panel-body">
                        <div class="dir-docentes-disp-row">
                            <div>
                                <div class="dir-docentes-disp-num" data-kpi="sin-carga">—</div>
                                <div class="dir-docentes-disp-label">Sin carga asignada</div>
                            </div>
                            <i class="bi bi-people dir-docentes-disp-icon"></i>
                        </div>
                    </div>
                </div>

                <div class="c-panel">
                    <div class="c-panel-header"><i class="bi bi-info-circle"></i><h3>Leyenda de carga semanal</h3></div>
                    <div class="c-panel-body dir-docentes-legend">
                        <div class="dir-docentes-legend-row"><span class="dir-docentes-legend-sq sin"></span>0 h<span class="dir-docentes-legend-desc">Sin carga</span></div>
                        <div class="dir-docentes-legend-row"><span class="dir-docentes-legend-sq normal"></span>1 - 20 h<span class="dir-docentes-legend-desc">Carga normal</span></div>
                        <div class="dir-docentes-legend-row"><span class="dir-docentes-legend-sq moderada"></span>21 - 30 h<span class="dir-docentes-legend-desc">Carga moderada</span></div>
                        <div class="dir-docentes-legend-row"><span class="dir-docentes-legend-sq alta"></span>31 - 40 h<span class="dir-docentes-legend-desc">Carga alta</span></div>
                        <div class="dir-docentes-legend-row"><span class="dir-docentes-legend-sq sobrecarga"></span>+40 h<span class="dir-docentes-legend-desc">Sobrecarga</span></div>
                    </div>
                </div>

                <div class="c-panel">
                    <div class="c-panel-header"><i class="bi bi-clock-history"></i><h3>Información de reporte</h3></div>
                    <div class="c-panel-body dir-docentes-info">
                        <div class="dir-docentes-info-row"><i class="bi bi-calendar3"></i><div><b>Última actualización:</b> <span id="dir-docentes-fecha"></span></div></div>
                        <div class="dir-docentes-info-row"><i class="bi bi-person-badge"></i><div><b>Generado por:</b> {{ auth()->user()->nombres }} {{ auth()->user()->apellidos }}</div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('director.docentes.partials.view-modal')
    @include('director.docentes.partials.assign-modal')
@endsection

@push('scripts')
    @vite('resources/js/director/docentes.js')
@endpush
