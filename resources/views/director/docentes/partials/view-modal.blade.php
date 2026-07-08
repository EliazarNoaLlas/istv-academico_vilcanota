<div class="dir-docentes-modal-backdrop" id="dir-docentes-view-modal">
    <div class="dir-docentes-modal dir-docentes-modal-xl">
        <div class="dir-docentes-modal-head dir-docentes-modal-head-row">
            <div class="dir-docentes-view-head">
                <div class="c-avatar-sm dir-docentes-avatar-lg" id="dv-avatar">—</div>
                <div>
                    <div class="dir-docentes-view-name" id="dv-nombre">—</div>
                    <div class="dir-docentes-modal-subtitle">Detalle de carga académica y cursos asignados</div>
                </div>
            </div>
            <button type="button" class="dir-docentes-modal-x" id="dir-docentes-view-cerrar-x">✕</button>
        </div>

        <div class="dir-docentes-view-meta">
            <div class="dir-docentes-view-meta-item">
                <div class="dir-docentes-view-meta-label">Especialidad</div>
                <div class="dir-docentes-view-meta-value" id="dv-especialidad">—</div>
            </div>
            <div class="dir-docentes-view-meta-item">
                <div class="dir-docentes-view-meta-label">Tipo de docente</div>
                <div class="dir-docentes-view-meta-value" id="dv-tipo">—</div>
            </div>
            <div class="dir-docentes-view-meta-item">
                <div class="dir-docentes-view-meta-label">Estado</div>
                <span class="c-badge dir-docentes-badge" id="dv-estado">—</span>
            </div>
        </div>

        <div class="dir-docentes-mini-stats">
            <div class="dir-docentes-mini-stat">
                <i class="bi bi-journal-bookmark"></i>
                <div>
                    <div class="dir-docentes-mini-stat-label">Cursos asignados</div>
                    <div class="dir-docentes-mini-stat-value" id="dv-cursos">—</div>
                </div>
            </div>
            <div class="dir-docentes-mini-stat">
                <i class="bi bi-clock"></i>
                <div>
                    <div class="dir-docentes-mini-stat-label">Carga semanal total</div>
                    <div class="dir-docentes-mini-stat-value" id="dv-carga">—</div>
                </div>
            </div>
            <div class="dir-docentes-mini-stat">
                <i class="bi bi-shield-check"></i>
                <div>
                    <div class="dir-docentes-mini-stat-label">Horas máximas permitidas</div>
                    <div class="dir-docentes-mini-stat-value" id="dv-limite">—</div>
                </div>
            </div>
            <div class="dir-docentes-mini-stat">
                <i class="bi bi-signpost-split" id="dv-disp-icon"></i>
                <div>
                    <div class="dir-docentes-mini-stat-label" id="dv-disp-label">Disponibilidad restante</div>
                    <div class="dir-docentes-mini-stat-value" id="dv-disponible">—</div>
                </div>
            </div>
        </div>

        <div class="dir-docentes-modal-section">
            <div class="dir-docentes-section-title"><i class="bi bi-journal-check"></i> Cursos asignados</div>
            <div class="dir-docentes-table-scroll">
                <table class="c-table dir-docentes-view-table">
                    <thead>
                        <tr>
                            <th>Curso</th>
                            <th>Programa</th>
                            <th>Ciclo</th>
                            <th>Horas/sem.</th>
                            <th>Periodo</th>
                            <th>Aula</th>
                        </tr>
                    </thead>
                    <tbody id="dv-cursos-tbody"></tbody>
                    <tfoot>
                        <tr class="dir-docentes-total-row">
                            <td>Total</td><td></td><td></td><td id="dv-cursos-total"></td><td></td><td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="dir-docentes-modal-two-col">
            <div>
                <div class="dir-docentes-section-title"><i class="bi bi-calendar-week"></i> Distribución semanal (horas por día)</div>
                <div class="dir-docentes-dist-grid" id="dv-dist-grid"></div>
            </div>
            <div>
                <div class="dir-docentes-section-title"><i class="bi bi-clipboard-check"></i> Observaciones y validación</div>
                <div id="dv-observaciones"></div>
            </div>
        </div>

        <div class="dir-docentes-modal-actions">
            <button type="button" class="c-btn c-btn-primary" id="dir-docentes-view-reasignar">
                <i class="bi bi-journal-plus"></i> Asignar / reasignar cursos
            </button>
            <button type="button" class="c-btn c-btn-outline" id="dir-docentes-view-cerrar">Cerrar</button>
        </div>
    </div>
</div>
