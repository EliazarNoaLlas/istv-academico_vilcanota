{{-- La tabla se llena por fetch (coordinador/cursos.js) contra
     /api/coordinador/cursos, nunca con datos escritos aqui. --}}
<div class="c-panel-body" style="padding-top:0">
    <table class="c-table">
        <thead>
            <tr>
                <th>Curso</th>
                <th>Programa</th>
                <th>Módulo</th>
                <th>Semestre</th>
                <th>Docente</th>
                <th>Horas</th>
                <th>Estado</th>
                <th></th>
            </tr>
        </thead>
        <tbody id="coord-cursos-tbody">
            <tr><td colspan="8" class="coord-cursos-empty">Cargando cursos…</td></tr>
        </tbody>
    </table>
</div>
