@extends('layouts.app', ['title' => 'Sesiones de Aprendizaje', 'subtitle' => auth()->user()->rol?->nombre])

@section('content')
    <div class="doc-shell">
        <div class="doc-sesiones-toolbar">
            <div class="doc-sesiones-field">
                <label>Curso</label>
                <select id="doc-sesiones-curso" class="input-inline">
                    <option value="">Selecciona un curso…</option>
                </select>
            </div>
            <button type="button" id="doc-sesiones-nueva" class="c-btn c-btn-primary c-btn-sm" disabled>
                <i class="bi bi-upload"></i> Subir sesión
            </button>
        </div>

        <div id="doc-sesiones-sin-curso" class="c-card doc-empty-state">
            <i class="bi bi-file-earmark-text"></i>
            <h3>Selecciona un curso</h3>
            <p>Elige uno de tus cursos asignados para ver o subir sus sesiones de aprendizaje.</p>
        </div>

        <div class="c-panel" id="doc-sesiones-panel" hidden>
            <div class="c-panel-header"><i class="bi bi-file-earmark-text"></i><h3>Sesiones subidas</h3></div>
            <div class="c-panel-body" style="padding-top:0;overflow-x:auto">
                <table class="c-table">
                    <thead>
                        <tr>
                            <th>N.º</th>
                            <th>Título</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="doc-sesiones-tbody"></tbody>
                </table>
            </div>
        </div>

        <div id="doc-sesiones-empty" class="c-card doc-empty-state" hidden>
            <i class="bi bi-file-earmark-x"></i>
            <h3>Sin sesiones subidas</h3>
            <p>Todavía no has subido sesiones de aprendizaje para este curso.</p>
        </div>
    </div>

    <div class="doc-portafolio-modal-backdrop" id="doc-sesiones-modal">
        <div class="doc-portafolio-modal">
            <h2>Subir sesión de aprendizaje</h2>
            <div class="doc-portafolio-form-error" id="doc-sesiones-form-error"></div>
            <form id="doc-sesiones-form">
                <input type="hidden" name="id_curso">
                <div class="form-group">
                    <label>Título</label>
                    <input type="text" name="titulo" required maxlength="255">
                </div>
                <div class="form-group">
                    <label>N.º de sesión</label>
                    <input type="number" name="numero_sesion" min="1">
                </div>
                <div class="form-group">
                    <label>Archivo (máx. 10MB)</label>
                    <input type="file" name="archivo" required accept=".pdf,.doc,.docx,.xls,.xlsx,.txt,.csv,.md,.json,.html">
                </div>
                <div class="doc-portafolio-modal-actions">
                    <button type="button" class="c-btn c-btn-outline" id="doc-sesiones-modal-cerrar">Cancelar</button>
                    <button type="submit" class="c-btn c-btn-primary">Subir</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/docente/sesiones.js')
@endpush
