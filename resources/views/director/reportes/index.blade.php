@extends('layouts.app', ['title' => 'Reportes', 'subtitle' => 'Generación de reportes institucionales'])

@section('content')
    <div class="dir-shell">
        <div class="dir-hero">
            <div>
                <small>DIRECCIÓN ACADÉMICA</small>
                <h2>Reportes Institucionales</h2>
                <p>Generados en el servidor a partir de datos reales, en PDF, Excel o CSV.</p>
            </div>
        </div>

        <div class="c-panel">
            <div class="c-panel-header"><i class="bi bi-file-earmark-text"></i><h3>Generar reporte</h3></div>
            <div class="c-panel-body">
                <form id="dir-reportes-form" class="dir-reportes-form">
                    <div class="form-group">
                        <label>Tipo de reporte</label>
                        <select name="tipo" required>
                            <option value="CURSOS">Consolidado de Cursos</option>
                            <option value="DOCENTES">Informe de Docentes</option>
                            <option value="ESTUDIANTES">Informe de Estudiantes</option>
                            <option value="HORARIOS">Reporte de Horarios</option>
                            <option value="NOTAS">Consolidado de Notas</option>
                            <option value="PORTAFOLIO">Reporte de Portafolio Docente</option>
                            <option value="CONSOLIDADO">Consolidado Académico Institucional</option>
                            <option value="IA_PREDICTIVA">Reporte de IA Predictiva</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Formato</label>
                        <select name="formato" required>
                            <option value="PDF">PDF</option>
                            <option value="EXCEL">Excel</option>
                            <option value="CSV">CSV</option>
                        </select>
                    </div>
                    <button type="submit" class="c-btn c-btn-primary" id="dir-reportes-generar">
                        <i class="bi bi-download"></i> Generar y descargar
                    </button>
                </form>
            </div>
        </div>

        <div class="c-panel">
            <div class="c-panel-header"><i class="bi bi-clock-history"></i><h3>Historial de reportes</h3></div>
            <div class="c-panel-body" style="padding-top:0">
                <table class="c-table">
                    <thead>
                        <tr>
                            <th>Reporte</th>
                            <th>Formato</th>
                            <th>Generado por</th>
                            <th>Fecha</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="dir-reportes-tbody">
                        <tr><td colspan="5" class="c-table-empty">Cargando historial…</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/director/reportes.js')
@endpush
