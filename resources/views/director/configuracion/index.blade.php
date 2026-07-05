@extends('layouts.app', ['title' => 'Configuración', 'subtitle' => 'Parámetros institucionales'])

@section('content')
    <div class="dir-shell">
        <div class="dir-hero">
            <div>
                <small>DIRECCIÓN ACADÉMICA</small>
                <h2>Configuración del Sistema</h2>
                <p>Parámetros institucionales reales, editables solo por Dirección.</p>
            </div>
        </div>

        <div class="c-panel">
            <div class="c-panel-header"><i class="bi bi-sliders"></i><h3>Parámetros</h3></div>
            <div class="c-panel-body">
                <form id="dir-configuracion-form">
                    <div id="dir-configuracion-campos">Cargando…</div>
                    <div style="padding-top:16px;border-top:1px solid var(--border);display:flex;gap:10px;margin-top:8px">
                        <button type="submit" class="c-btn c-btn-primary"><i class="bi bi-check-lg"></i> Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/director/configuracion.js')
@endpush
