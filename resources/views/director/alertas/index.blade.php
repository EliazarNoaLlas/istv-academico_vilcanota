@extends('layouts.app', ['title' => 'Alertas Tempranas', 'subtitle' => 'Sistema de alertas académicas'])

@section('content')
    <div class="dir-shell">
        <div class="dir-hero">
            <div>
                <small>DIRECCIÓN ACADÉMICA</small>
                <h2>Sistema de Alertas Tempranas</h2>
                <p>Alertas generadas por reglas académicas reales, gestionables desde este panel.</p>
            </div>
        </div>

        <div class="c-panel">
            <div class="c-panel-header">
                <i class="bi bi-exclamation-triangle"></i><h3>Alertas</h3>
                <select id="dir-alertas-filtro-estado" class="input-inline" style="margin-left:auto">
                    <option value="">Todos los estados</option>
                    <option value="ABIERTA">Abiertas</option>
                    <option value="EN_SEGUIMIENTO">En seguimiento</option>
                    <option value="CERRADA">Cerradas</option>
                </select>
            </div>
            <div class="c-panel-body" id="dir-alertas-lista">Cargando…</div>
        </div>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/director/alertas.js')
@endpush
