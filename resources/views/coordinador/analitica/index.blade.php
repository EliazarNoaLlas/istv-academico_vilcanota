@extends('layouts.app', ['title' => 'Analítica', 'subtitle' => 'Indicadores de tu programa'])

@section('content')
    <div class="coord-shell">
        <div class="coord-hero">
            <div>
                <small>COORDINADOR ACADÉMICO</small>
                <h2>Analítica del Programa</h2>
                <p>Indicadores calculados en tiempo real, acotados a tu programa de estudios.</p>
            </div>
        </div>

        <div class="coord-analitica-matrix">
            <div class="c-panel">
                <div class="c-panel-header"><i class="bi bi-bar-chart"></i><h3>Rendimiento Académico por Curso</h3></div>
                <div class="c-panel-body coord-analitica-chart-box">
                    <canvas id="coord-chart-rendimiento"></canvas>
                </div>
            </div>
            <div class="c-panel">
                <div class="c-panel-header"><i class="bi bi-folder-check"></i><h3>Entrega de Portafolio</h3></div>
                <div class="c-panel-body coord-analitica-chart-box">
                    <canvas id="coord-chart-portafolio"></canvas>
                </div>
            </div>
            <div class="c-panel">
                <div class="c-panel-header"><i class="bi bi-journal-check"></i><h3>Cumplimiento del Sílabo por Ciclo</h3></div>
                <div class="c-panel-body coord-analitica-chart-box">
                    <canvas id="coord-chart-silabo"></canvas>
                </div>
            </div>
            <div class="c-panel">
                <div class="c-panel-header"><i class="bi bi-exclamation-diamond"></i><h3>Riesgo Académico vs Asistencia por Ciclo</h3></div>
                <div class="c-panel-body coord-analitica-chart-box">
                    <canvas id="coord-chart-riesgo"></canvas>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/coordinador/analytics.js')
@endpush
