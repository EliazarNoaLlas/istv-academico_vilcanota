<div class="coord-horarios-modal-backdrop" id="dir-horarios-ia-modal">
    <div class="coord-horarios-modal coord-horarios-modal--wide">
        <h2 id="dir-horarios-ia-titulo">Generación de horario con IA</h2>

        <div class="coord-horarios-ia-estado" id="dir-horarios-ia-estado"></div>

        <div class="coord-horarios-form-error" id="dir-horarios-ia-error"></div>

        <div class="coord-horarios-ia-seccion">
            <strong>Bloques propuestos</strong>
            <div style="overflow-x:auto">
                <table class="c-table">
                    <thead>
                        <tr><th>Curso</th><th>Docente</th><th>Aula</th><th>Día</th><th>Inicio</th><th>Fin</th></tr>
                    </thead>
                    <tbody id="dir-horarios-ia-tbody">
                        <tr><td colspan="6" class="coord-portafolio-empty">Sin datos todavía.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="coord-horarios-ia-seccion" id="dir-horarios-ia-observaciones-box" style="display:none">
            <strong>Observaciones</strong>
            <ul id="dir-horarios-ia-observaciones"></ul>
        </div>

        <div class="coord-horarios-ia-seccion" id="dir-horarios-ia-conflictos-box" style="display:none">
            <strong>Conflictos / errores pendientes</strong>
            <ul id="dir-horarios-ia-conflictos"></ul>
        </div>

        <div class="coord-horarios-modal-actions">
            <button type="button" class="c-btn c-btn-outline" id="dir-horarios-ia-cerrar">Cerrar</button>
            <button type="button" class="c-btn c-btn-outline" id="dir-horarios-ia-reparar">
                <i class="bi bi-wrench-adjustable"></i> Reparar
            </button>
            <button type="button" class="c-btn c-btn-outline" id="dir-horarios-ia-descartar">
                <i class="bi bi-x-circle"></i> Descartar
            </button>
            <button type="button" class="c-btn c-btn-primary" id="dir-horarios-ia-aprobar">
                <i class="bi bi-check2-circle"></i> Aprobar y guardar
            </button>
        </div>
    </div>
</div>
