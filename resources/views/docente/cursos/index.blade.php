@extends('layouts.app', ['title' => 'Mis Cursos', 'subtitle' => auth()->user()->rol?->nombre])

@section('content')
    <div class="doc-shell">
        <div class="doc-cursos-toolbar">
            <div class="doc-cursos-periodo" id="doc-cursos-periodo">Cargando periodo académico…</div>
            <input type="text" id="doc-cursos-buscar" class="input-inline doc-cursos-search" placeholder="Buscar curso…">
        </div>

        <div id="doc-cursos-grid" class="doc-cursos-grid"></div>

        <div id="doc-cursos-empty" class="c-card doc-empty-state" hidden>
            <i class="bi bi-journal-x"></i>
            <h3>Todavía no tienes cursos asignados</h3>
            <p>No tienes cursos asignados para el periodo activo. Comunícate con coordinación académica.</p>
        </div>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/docente/cursos.js')
@endpush
