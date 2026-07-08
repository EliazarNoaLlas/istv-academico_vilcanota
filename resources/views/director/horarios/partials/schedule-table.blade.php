@php
    $diasTabla = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];
@endphp
<div class="c-panel dir-iti-empty" id="dir-horarios-estado-vacio" style="display:none;">
    <i class="bi bi-calendar-x"></i>
    <h3>Este semestre tiene cursos, pero aún no tiene horario generado.</h3>
    <p id="dir-horarios-estado-vacio-texto"></p>
    <button type="button" class="c-btn c-btn-primary" id="dir-horarios-estado-generar">
        <i class="bi bi-magic"></i> Generar horario del semestre
    </button>
</div>

<div class="c-panel dir-iti-empty" id="dir-horarios-estado-sin-docente" style="display:none;">
    <i class="bi bi-person-x"></i>
    <h3>No se puede generar porque hay cursos sin docente</h3>
    <ul id="dir-horarios-estado-sin-docente-lista" class="dir-horarios-cursos-sin-docente"></ul>
    <a href="{{ route('director.cursos.index') }}" class="c-btn c-btn-primary">
        <i class="bi bi-person-plus"></i> Ir a asignar docentes
    </a>
</div>

<div class="scheduler-workspace" id="dir-horarios-schedule-wrapper">
    <div class="scheduler-board-panel">
        <div class="scheduler-board-head">
            <div class="scheduler-legend">
                <span><i class="dot blue"></i> Curso</span>
                <span><i class="dot red"></i> Pendiente/Conflicto</span>
            </div>
        </div>

        <div class="academic-schedule">
            <div class="academic-schedule-title" id="dir-horarios-schedule-title">TODOS LOS PROGRAMAS</div>
            <div class="academic-schedule-subtitle" id="dir-horarios-schedule-subtitle">{{ $periodos->firstWhere('estado', 'ACTIVO')?->codigo ?? '' }}</div>

            <table class="academic-schedule-table">
                <thead>
                    <tr>
                        <th>HORAS</th>
                        @foreach ($diasTabla as $dia)
                            <th>{{ mb_strtoupper($dia) }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody id="dir-horarios-tbody">
                    @foreach ($bloques_horario as $bloque)
                        @if (! empty($bloque['receso']))
                            <tr class="academic-break-row">
                                <td class="academic-hour">{{ $bloque['inicio'] }}-{{ $bloque['fin'] }}</td>
                                <td colspan="{{ count($diasTabla) }}">RECESO</td>
                            </tr>
                        @else
                            <tr>
                                <td class="academic-hour">{{ $bloque['inicio'] }}-{{ $bloque['fin'] }}</td>
                                @foreach ($diasTabla as $dia)
                                    <td class="academic-slot scheduler-slot" data-day="{{ $dia }}" data-start="{{ $bloque['inicio'] }}" data-end="{{ $bloque['fin'] }}"></td>
                                @endforeach
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
