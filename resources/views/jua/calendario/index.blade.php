@extends('layouts.app', ['title' => 'Calendario Académico', 'subtitle' => auth()->user()->rol?->nombre])

@section('content')
    <div class="jua-cal-shell">
        <div class="jua-cal-header">
            <div class="jua-cal-header-icon"><i class="bi bi-calendar3"></i></div>
            <div class="jua-cal-header-text">
                <h2>Calendario Académico</h2>
                <p>Gestiona las fechas clave del semestre para todos los docentes</p>
            </div>
            <button type="button" class="c-btn c-btn-primary jua-cal-btn-nuevo" id="jua-cal-nuevo-evento">
                <i class="bi bi-plus-lg"></i> Nuevo evento
            </button>
        </div>

        <div class="jua-cal-kpis">
            <div class="jua-cal-kpi">
                <div class="jua-cal-kpi-icon navy"><i class="bi bi-calendar3"></i></div>
                <div>
                    <strong data-kpi="eventos_semestre">{{ $kpis['eventos_semestre'] }}</strong>
                    <span>Eventos este semestre</span>
                </div>
            </div>
            <div class="jua-cal-kpi">
                <div class="jua-cal-kpi-icon blue"><i class="bi bi-clock"></i></div>
                <div>
                    <strong data-kpi="evaluaciones">{{ $kpis['evaluaciones'] }}</strong>
                    <span>Evaluaciones</span>
                </div>
            </div>
            <div class="jua-cal-kpi">
                <div class="jua-cal-kpi-icon gold"><i class="bi bi-file-earmark"></i></div>
                <div>
                    <strong data-kpi="plazos_administrativos">{{ $kpis['plazos_administrativos'] }}</strong>
                    <span>Plazos administrativos</span>
                </div>
            </div>
            <div class="jua-cal-kpi">
                <div class="jua-cal-kpi-icon red"><i class="bi bi-clock-history"></i></div>
                <div>
                    <strong data-kpi="proximos_7_dias">{{ $kpis['proximos_7_dias'] }}</strong>
                    <span>Próximos 7 días</span>
                </div>
            </div>
        </div>

        <div class="jua-cal-body">
            <div class="c-panel jua-cal-main">
                <div class="c-panel-body">
                    <div class="jua-cal-nav">
                        <div class="jua-cal-nav-left">
                            <button type="button" class="c-btn c-btn-outline c-btn-sm" id="jua-cal-mes-anterior"><i class="bi bi-chevron-left"></i></button>
                            <button type="button" class="c-btn c-btn-outline c-btn-sm" id="jua-cal-mes-siguiente"><i class="bi bi-chevron-right"></i></button>
                            <h3 id="jua-cal-mes-titulo">—</h3>
                        </div>
                        <button type="button" class="c-btn c-btn-outline c-btn-sm" id="jua-cal-hoy">Hoy</button>
                    </div>

                    <div class="jua-cal-leyenda" id="jua-cal-leyenda">
                        @foreach ($tipos as $clave => $info)
                            <button type="button" class="jua-cal-chip active" data-tipo="{{ $clave }}">
                                <span class="jua-cal-dot {{ $info['color'] }}"></span> {{ $info['etiqueta'] }}
                            </button>
                        @endforeach
                    </div>

                    <div class="jua-cal-grid-dias">
                        <div>DOM</div><div>LUN</div><div>MAR</div><div>MIÉ</div><div>JUE</div><div>VIE</div><div>SÁB</div>
                    </div>
                    <div class="jua-cal-grid" id="jua-cal-grid"></div>
                </div>
            </div>

            <div class="c-panel jua-cal-sidebar">
                <div class="c-panel-header"><i class="bi bi-clock"></i><h3>Próximos eventos</h3></div>
                <p class="jua-cal-sidebar-hint">Toca un evento para editarlo</p>
                <div id="jua-cal-proximos"></div>
            </div>
        </div>
    </div>

    <div class="coord-portafolio-modal-backdrop" id="jua-cal-modal">
        <div class="coord-portafolio-modal">
            <h2 id="jua-cal-modal-titulo">Nuevo evento</h2>

            <div class="form-group">
                <label>Título</label>
                <input type="text" id="jua-cal-form-titulo" maxlength="180">
            </div>

            <div class="form-group">
                <label>Tipo de evento</label>
                <select id="jua-cal-form-tipo">
                    @foreach ($tipos as $clave => $info)
                        <option value="{{ $clave }}">{{ $info['etiqueta'] }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Fecha</label>
                <input type="date" id="jua-cal-form-fecha">
            </div>

            <div class="form-group">
                <label>Descripción (opcional)</label>
                <textarea id="jua-cal-form-descripcion" rows="3" style="width:100%;padding:10px 12px;border:1.5px solid var(--border);border-radius:var(--radius-sm);font-family:inherit;font-size:13px;resize:vertical"></textarea>
            </div>

            <div class="coord-portafolio-form-error" id="jua-cal-form-error"></div>

            <div class="coord-portafolio-modal-actions">
                <button type="button" class="c-btn c-btn-danger" id="jua-cal-eliminar" style="display:none;margin-right:auto">
                    <i class="bi bi-trash"></i> Eliminar
                </button>
                <button type="button" class="c-btn c-btn-outline" id="jua-cal-cancelar">Cancelar</button>
                <button type="button" class="c-btn c-btn-primary" id="jua-cal-guardar">Guardar</button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/jua/calendario.js')
@endpush
