@extends('layouts.app', ['title' => 'Horarios', 'subtitle' => 'Supervisión de horarios académicos'])

@section('content')
    <div class="dir-shell">
        <div class="dir-hero">
            <div>
                <small>DIRECCIÓN ACADÉMICA</small>
                <h2>Horarios Académicos</h2>
                <p>Vista institucional de solo lectura · Periodo:
                    <strong>{{ $periodos->firstWhere('estado', 'ACTIVO')?->codigo ?? '—' }}</strong></p>
            </div>
        </div>

        <div class="c-panel">
            <div class="c-panel-header"><i class="bi bi-funnel"></i>
                <h3>Filtros</h3></div>
            <div class="c-panel-body">
                <div class="coord-filter-grid">
                    <div class="coord-filter-field">
                        <label>Programa</label>
                        <select id="dir-horarios-filtro-programa">
                            <option value="">Todos los programas</option>
                            @foreach ($programas as $programa)
                                <option value="{{ $programa->id_programa }}">{{ $programa->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="coord-filter-field">
                        <label>Docente</label>
                        <select id="dir-horarios-filtro-docente">
                            <option value="">Todos los docentes</option>
                            @foreach ($docentes as $docente)
                                <option
                                    value="{{ $docente->id_docente }}">{{ $docente->usuario->nombres }} {{ $docente->usuario->apellidos }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="coord-filter-field">
                        <label>Semestre</label>
                        <select id="dir-horarios-filtro-semestre">
                            <option value="">Todos</option>
                            @foreach (['I', 'II', 'III', 'IV', 'V', 'VI'] as $ciclo)
                                <option value="{{ $ciclo }}">Semestre {{ $ciclo }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="c-panel">
            <div class="c-panel-header"><i class="bi bi-calendar-week"></i>
                <h3>Grilla semanal</h3></div>
            <div class="c-panel-body" style="overflow-x:auto">
                <table class="academic-schedule-table">
                    <thead>
                    <tr>
                        <th>HORAS</th>
                        @foreach (['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'] as $dia)
                            <th>{{ mb_strtoupper($dia) }}</th>
                        @endforeach
                    </tr>
                    </thead>
                    <tbody id="dir-horarios-tbody">
                    @foreach ($bloques_horario as $bloque)
                        @if (! empty($bloque['receso']))
                            <tr class="academic-break-row">
                                <td class="academic-hour">{{ $bloque['inicio'] }}-{{ $bloque['fin'] }}</td>
                                <td colspan="5">RECESO</td>
                            </tr>
                        @else
                            <tr>
                                <td class="academic-hour">{{ $bloque['inicio'] }}-{{ $bloque['fin'] }}</td>
                                @foreach (['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'] as $dia)
                                    <td class="academic-slot" data-day="{{ $dia }}"
                                        data-start="{{ $bloque['inicio'] }}"></td>
                                @endforeach
                            </tr>
                        @endif
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/director/horarios.js')
@endpush
