@extends('layouts.app', ['title' => 'Portafolio Docente', 'subtitle' => 'Seguimiento institucional'])

@section('content')
    <div class="dir-shell">
        <div class="dir-hero">
            <div>
                <small>DIRECCIÓN ACADÉMICA</small>
                <h2>Seguimiento de Portafolio Docente</h2>
                <p>Vista institucional de solo lectura de los documentos subidos por los docentes.</p>
            </div>
        </div>

        <div class="c-panel">
            <div class="c-panel-header"><i class="bi bi-funnel"></i><h3>Filtros</h3></div>
            <div class="c-panel-body">
                <div class="coord-filter-grid">
                    <div class="coord-filter-field">
                        <label>Docente</label>
                        <select id="dir-portafolio-filtro-docente" class="input-inline">
                            <option value="">Todos los docentes</option>
                            @foreach ($docentes as $docente)
                                <option value="{{ $docente->id_docente }}">{{ $docente->usuario->nombres }} {{ $docente->usuario->apellidos }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="coord-filter-field">
                        <label>Curso</label>
                        <select id="dir-portafolio-filtro-curso" class="input-inline">
                            <option value="">Todos los cursos</option>
                            @foreach ($cursos as $curso)
                                <option value="{{ $curso->id_curso }}">{{ $curso->nombre_curso }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="coord-filter-field">
                        <label>Estado</label>
                        <select id="dir-portafolio-filtro-estado" class="input-inline">
                            <option value="">Todos</option>
                            <option value="PENDIENTE">Pendiente</option>
                            <option value="SUBIDO">Subido</option>
                            <option value="APROBADO">Aprobado</option>
                            <option value="OBSERVADO">Observado</option>
                        </select>
                    </div>
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
                        </tr>
                    </thead>
                    <tbody id="dir-portafolio-tbody">
                        <tr><td colspan="5" class="c-table-empty">Cargando documentos…</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/director/portafolio.js')
@endpush
