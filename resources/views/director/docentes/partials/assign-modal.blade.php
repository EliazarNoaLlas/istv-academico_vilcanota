<div class="dir-docentes-modal-backdrop" id="dir-docentes-modal">
    <div class="dir-docentes-modal dir-docentes-modal-xl">
        <div class="dir-docentes-modal-head dir-docentes-modal-head-row">
            <div>
                <h2>Asignar cursos</h2>
                <p class="dir-docentes-modal-subtitle">Asignación de cursos al docente por periodo académico</p>
            </div>
            <button type="button" class="dir-docentes-modal-x" id="dir-docentes-modal-cerrar-x">✕</button>
        </div>

        <div class="dir-docentes-form-error" id="dir-docentes-modal-error"></div>

        <div class="dir-docentes-assign-head" id="dir-docentes-modal-picker">
            <div class="form-group" style="margin-bottom:0">
                <label>Seleccione un docente</label>
                <select id="dir-docentes-modal-select-docente" class="input-inline">
                    <option value="">Elija un docente…</option>
                </select>
            </div>
        </div>

        <div id="dir-docentes-modal-body" style="display:none">
            <div class="dir-docentes-assign-head">
                <div class="dir-docentes-view-head">
                    <div class="c-avatar-sm dir-docentes-avatar-lg" id="am-avatar">—</div>
                    <div>
                        <div class="dir-docentes-view-name" id="am-nombre">—</div>
                        <div class="dir-docentes-modal-subtitle" id="am-especialidad">—</div>
                    </div>
                </div>
                <div class="dir-docentes-assign-head-meta">
                    <div class="dir-docentes-assign-head-item">
                        <div class="dir-docentes-view-meta-label">Tipo</div>
                        <div class="dir-docentes-view-meta-value" id="am-tipo">—</div>
                    </div>
                    <div class="dir-docentes-assign-head-item">
                        <div class="dir-docentes-view-meta-label">Estado</div>
                        <span class="c-badge dir-docentes-badge" id="am-estado">—</span>
                    </div>
                    <div class="dir-docentes-assign-head-item">
                        <div class="dir-docentes-view-meta-label">Carga actual</div>
                        <div class="dir-docentes-view-meta-value" id="am-carga-actual">—</div>
                    </div>
                    <div class="dir-docentes-overload-flag" id="am-overload-flag" style="display:none"></div>
                </div>
            </div>

            <div class="dir-docentes-assign-columns">
                <div>
                    <div class="dir-docentes-col-title">Cursos disponibles</div>
                    <input type="text" id="dir-docentes-modal-search" class="input-inline dir-docentes-assign-search" placeholder="Buscar curso...">
                    <div class="dir-docentes-filter-row">
                        <select id="dir-docentes-modal-filtro-programa" class="input-inline">
                            <option value="">Todos los programas</option>
                            @foreach ($programas as $programa)
                                <option value="{{ $programa->id_programa }}">{{ $programa->nombre }}</option>
                            @endforeach
                        </select>
                        <select id="dir-docentes-modal-filtro-ciclo" class="input-inline">
                            <option value="">Todos los ciclos</option>
                            @foreach (['I', 'II', 'III', 'IV', 'V', 'VI'] as $ciclo)
                                <option value="{{ $ciclo }}">Ciclo {{ $ciclo }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="dir-docentes-lista dir-docentes-lista-lg" id="dir-docentes-modal-cursos"></div>
                </div>

                <div>
                    <div class="dir-docentes-col-title">Cursos seleccionados</div>
                    <div class="dir-docentes-table-scroll dir-docentes-lista-lg">
                        <table class="c-table dir-docentes-selected-table">
                            <thead>
                                <tr><th>Curso</th><th>Ciclo</th><th>Horas</th><th></th></tr>
                            </thead>
                            <tbody id="dir-docentes-modal-seleccionados">
                                <tr><td colspan="4" class="c-table-empty">Sin cursos seleccionados.</td></tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="dir-docentes-summary-bar">
                        <div class="dir-docentes-summary-cell">
                            <i class="bi bi-journal-plus"></i>
                            <div>
                                <div class="dir-docentes-summary-label">Cursos seleccionados</div>
                                <div class="dir-docentes-summary-value" id="am-sum-cursos">0</div>
                            </div>
                        </div>
                        <div class="dir-docentes-summary-cell">
                            <i class="bi bi-clock-history"></i>
                            <div>
                                <div class="dir-docentes-summary-label">Horas agregadas</div>
                                <div class="dir-docentes-summary-value" id="am-sum-horas">0h</div>
                            </div>
                        </div>
                        <div class="dir-docentes-summary-cell">
                            <i class="bi bi-signpost-split"></i>
                            <div>
                                <div class="dir-docentes-summary-label">Carga proyectada</div>
                                <div class="dir-docentes-summary-value" id="am-sum-proyectada">0h</div>
                            </div>
                        </div>
                    </div>

                    <div class="dir-docentes-note"><i class="bi bi-info-circle"></i> La asignación se guarda de inmediato; el horario y el aula se definen luego en el módulo de Horarios.</div>
                </div>
            </div>
        </div>

        <div class="dir-docentes-modal-actions">
            <button type="button" class="c-btn c-btn-outline" id="dir-docentes-modal-cancelar">Cancelar</button>
            <button type="button" class="c-btn c-btn-primary" id="dir-docentes-modal-guardar">Confirmar asignación</button>
        </div>
    </div>
</div>
