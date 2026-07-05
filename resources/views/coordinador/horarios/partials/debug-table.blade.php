@if (app()->environment('local') || config('app.debug'))
    <div class="c-panel coord-horarios-debug">
        <div class="c-panel-header">
            <i class="bi bi-bug"></i><h3>Panel técnico — registros MySQL (solo entorno local/debug)</h3>
        </div>
        <div class="c-panel-body" style="padding-top:0;overflow-x:auto">
            <table class="c-table">
                <thead>
                    <tr>
                        <th>ID</th><th>Curso</th><th>Docente</th><th>Día</th><th>Inicio</th><th>Fin</th><th>Aula</th><th>Estado</th><th>Fuente</th>
                    </tr>
                </thead>
                <tbody id="coord-horarios-debug-tbody">
                    <tr><td colspan="9" class="coord-portafolio-empty">Cargando…</td></tr>
                </tbody>
            </table>
        </div>
    </div>
@endif
