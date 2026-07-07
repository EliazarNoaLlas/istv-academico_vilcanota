@extends('layouts.app', ['title' => 'Mi Horario', 'subtitle' => auth()->user()->rol?->nombre])

@section('content')
    <div class="doc-shell">
        <div class="doc-horario-toolbar">
            <select id="doc-horario-periodo"></select>
            <button type="button" id="doc-horario-imprimir" class="c-btn c-btn-outline c-btn-sm">
                <i class="bi bi-printer"></i> Imprimir
            </button>
        </div>

        <div id="doc-horario-grid" class="doc-horario-grid"></div>

        <div id="doc-horario-empty" class="c-card doc-empty-state" hidden>
            <i class="bi bi-calendar-x"></i>
            <h3>Todavía no tienes horario asignado</h3>
            <p>No se encontraron clases programadas para el periodo seleccionado. Comunícate con coordinación académica.</p>
        </div>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/docente/horario.js')
@endpush
