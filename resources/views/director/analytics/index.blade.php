@extends('layouts.app', ['title' => 'Analytics', 'subtitle' => 'Indicadores institucionales'])

@section('content')
    <div class="dir-shell">
        <div class="dir-hero">
            <div>
                <small>DIRECCIÓN ACADÉMICA</small>
                <h2>Analytics Institucional</h2>
                <p>Indicadores calculados en tiempo real desde la base de datos.</p>
            </div>
        </div>

        <div class="dir-analytics-matrix">
            <div class="c-panel">
                <div class="c-panel-header"><i class="bi bi-bar-chart"></i><h3>Rendimiento Académico por Programa</h3></div>
                <div class="c-panel-body dir-analytics-chart-box">
                    <canvas id="dir-chart-rendimiento"></canvas>
                </div>
            </div>
            <div class="c-panel">
                <div class="c-panel-header"><i class="bi bi-folder-check"></i><h3>Entrega de Portafolio</h3></div>
                <div class="c-panel-body dir-analytics-chart-box">
                    <canvas id="dir-chart-portafolio"></canvas>
                </div>
            </div>
            <div class="c-panel">
                <div class="c-panel-header"><i class="bi bi-journal-check"></i><h3>Cumplimiento del Sílabo por Ciclo</h3></div>
                <div class="c-panel-body dir-analytics-chart-box">
                    <canvas id="dir-chart-silabo"></canvas>
                </div>
            </div>
            <div class="c-panel">
                <div class="c-panel-header"><i class="bi bi-exclamation-diamond"></i><h3>Riesgo Académico vs Asistencia</h3></div>
                <div class="c-panel-body dir-analytics-chart-box">
                    <canvas id="dir-chart-riesgo"></canvas>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/director/analytics.js')
@endpush
