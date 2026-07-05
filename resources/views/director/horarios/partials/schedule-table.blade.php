@php
    $diasTabla = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];
@endphp
<div class="scheduler-workspace">
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
