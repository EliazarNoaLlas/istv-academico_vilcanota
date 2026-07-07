@php
    /** @var \App\Models\ItinerarioFormativo $itinerario */
    $editable = $editable ?? false;
    $ciclos = ['I', 'II', 'III', 'IV', 'V', 'VI'];
    // Color de fila: usa el color_hex del bloque; si es blanco/vacío, aplica el color oficial por tipo.
    $coloresPorTipo = [
        'ESPECIALIDAD' => '#FFFFFF',
        'EMPLEABILIDAD' => '#DCEEFA',
        'TRANSVERSAL' => '#DFF3E3',
        'ESRT' => '#BFE3F5',
        'OTRO' => '#F4F7FB',
    ];
@endphp
<div class="dir-iti-table-scroll">
    <table class="dir-iti-table {{ $editable ? 'is-editable' : '' }}" id="dir-iti-editor-table">
        <thead>
            <tr>
                <th class="dir-iti-col-modulo" rowspan="2">Módulo</th>
                <th class="dir-iti-col-unidad" rowspan="2">Unidades didácticas</th>
                <th colspan="6">Ciclo</th>
                <th rowspan="2">Teóricos</th>
                <th rowspan="2">Prácticos</th>
                <th rowspan="2">Créditos</th>
                <th rowspan="2">Créditos<br>módulo</th>
                <th rowspan="2">De<br>teoría</th>
                <th rowspan="2">De<br>práctica</th>
                <th rowspan="2">Horas<br>U.D.</th>
                <th rowspan="2">Total<br>horas</th>
            </tr>
            <tr>
                @foreach ($ciclos as $ciclo)
                    <th class="dir-iti-col-num">{{ $ciclo }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($itinerario->modulos as $modulo)
                @php
                    $filasModulo = $modulo->bloques->sum(fn ($b) => max($b->unidades->count(), 1));
                    $primeraFilaModulo = true;
                @endphp

                @foreach ($modulo->bloques as $bloque)
                    @php
                        $colorHex = strtoupper((string) $bloque->color_hex);
                        $colorFila = ($colorHex && $colorHex !== '#FFFFFF')
                            ? $bloque->color_hex
                            : ($coloresPorTipo[$bloque->tipo_bloque] ?? '#FFFFFF');
                        $filasBloque = max($bloque->unidades->count(), 1);
                    @endphp

                    @forelse ($bloque->unidades as $unidad)
                        <tr class="dir-iti-row-unidad {{ $bloque->tipo_bloque === 'ESRT' ? 'dir-iti-tipo-esrt' : '' }}"
                            style="--iti-row: {{ $colorFila }}"
                            @if ($editable)
                                data-unidad-id="{{ $unidad->id_unidad }}"
                                data-modulo="{{ $modulo->id_modulo }}"
                                data-nombre="{{ $unidad->nombre }}"
                                data-codigo="{{ $unidad->codigo }}"
                                data-ciclo="{{ $unidad->ciclo }}"
                                data-teoricas="{{ $unidad->horas_teoricas_semanales }}"
                                data-practicas="{{ $unidad->horas_practicas_semanales }}"
                                data-bloque="{{ $unidad->id_bloque }}"
                                data-bloque-nombre="Módulo {{ $modulo->numero_modulo }} — {{ $bloque->nombre }}"
                                data-estado="{{ $unidad->estado }}"
                                data-observacion="{{ $unidad->observacion }}"
                                tabindex="0"
                            @endif>
                            @if ($primeraFilaModulo)
                                @php $primeraFilaModulo = false; @endphp
                                <td class="dir-iti-cell-modulo" rowspan="{{ $filasModulo }}">
                                    <span class="dir-iti-modulo-nombre">MÓDULO {{ $modulo->numero_modulo }}</span>
                                    @if ($modulo->competencia)
                                        <span class="dir-iti-modulo-competencia">{{ $modulo->competencia }}</span>
                                    @endif
                                </td>
                            @endif
                            <td class="dir-iti-cell-nombre">
                                @if ($editable)
                                    <span class="dir-iti-editable" contenteditable="true" spellcheck="false" data-field="nombre">{{ $unidad->nombre }}</span>
                                @else
                                    {{ $unidad->nombre }}
                                @endif
                            </td>
                            @foreach ($ciclos as $ciclo)
                                <td class="dir-iti-cell-num dir-iti-cell-ciclo {{ $unidad->ciclo === $ciclo ? 'is-activo' : 'is-vacio' }}"
                                    data-col="ciclo-{{ $ciclo }}">{{ $unidad->ciclo === $ciclo ? $unidad->horas_ciclo : '—' }}</td>
                            @endforeach
                            <td class="dir-iti-cell-num" data-col="teoricas">
                                @if ($editable)
                                    <span class="dir-iti-editable" contenteditable="true" spellcheck="false" data-field="horas_teoricas_semanales">{{ $unidad->horas_teoricas_semanales }}</span>
                                @else
                                    {{ $unidad->horas_teoricas_semanales }}
                                @endif
                            </td>
                            <td class="dir-iti-cell-num" data-col="practicas">
                                @if ($editable)
                                    <span class="dir-iti-editable" contenteditable="true" spellcheck="false" data-field="horas_practicas_semanales">{{ $unidad->horas_practicas_semanales }}</span>
                                @else
                                    {{ $unidad->horas_practicas_semanales }}
                                @endif
                            </td>
                            <td class="dir-iti-cell-num dir-iti-cell-calc" data-col="creditos">{{ $unidad->creditos }}</td>
                            @if ($loop->first)
                                <td class="dir-iti-cell-num dir-iti-cell-calc" rowspan="{{ $filasBloque }}" data-bloque-cred="{{ $bloque->id_bloque }}">{{ $bloque->creditos_bloque }}</td>
                            @endif
                            <td class="dir-iti-cell-num dir-iti-cell-calc" data-col="total-teoria">{{ $unidad->total_horas_teoria }}</td>
                            <td class="dir-iti-cell-num dir-iti-cell-calc" data-col="total-practica">{{ $unidad->total_horas_practica }}</td>
                            <td class="dir-iti-cell-num dir-iti-cell-calc" data-col="horas-ud">{{ $unidad->horas_ud }}</td>
                            @if ($loop->first)
                                <td class="dir-iti-cell-num dir-iti-cell-calc" rowspan="{{ $filasBloque }}" data-bloque-horas="{{ $bloque->id_bloque }}">{{ $bloque->horas_bloque }}</td>
                            @endif
                        </tr>
                    @empty
                        <tr class="dir-iti-row-unidad" style="--iti-row: {{ $colorFila }}">
                            @if ($primeraFilaModulo)
                                @php $primeraFilaModulo = false; @endphp
                                <td class="dir-iti-cell-modulo" rowspan="{{ $filasModulo }}">
                                    <span class="dir-iti-modulo-nombre">MÓDULO {{ $modulo->numero_modulo }}</span>
                                    @if ($modulo->competencia)
                                        <span class="dir-iti-modulo-competencia">{{ $modulo->competencia }}</span>
                                    @endif
                                </td>
                            @endif
                            <td class="dir-iti-cell-vacio" colspan="9">{{ $bloque->nombre }}: sin unidades didácticas registradas.</td>
                            <td class="dir-iti-cell-num dir-iti-cell-calc" data-bloque-cred="{{ $bloque->id_bloque }}">{{ $bloque->creditos_bloque }}</td>
                            <td class="dir-iti-cell-vacio" colspan="3"></td>
                            <td class="dir-iti-cell-num dir-iti-cell-calc" data-bloque-horas="{{ $bloque->id_bloque }}">{{ $bloque->horas_bloque }}</td>
                        </tr>
                    @endforelse
                @endforeach

                @php $unidadesModulo = $modulo->bloques->flatMap->unidades; @endphp
                <tr class="dir-iti-row-total" data-modulo-total="{{ $modulo->id_modulo }}">
                    <td colspan="2">TOTALES MÓDULO {{ $modulo->numero_modulo }}</td>
                    <td colspan="6"></td>
                    <td class="dir-iti-cell-num" data-sum="teoricas">{{ $unidadesModulo->sum('horas_teoricas_semanales') }}</td>
                    <td class="dir-iti-cell-num" data-sum="practicas">{{ $unidadesModulo->sum('horas_practicas_semanales') }}</td>
                    <td class="dir-iti-cell-num" data-sum="creditos">{{ $unidadesModulo->sum('creditos') }}</td>
                    <td class="dir-iti-cell-num" data-total="creditos">{{ $modulo->total_creditos }}</td>
                    <td class="dir-iti-cell-num" data-sum="teoria">{{ $unidadesModulo->sum('total_horas_teoria') }}</td>
                    <td class="dir-iti-cell-num" data-sum="practica">{{ $unidadesModulo->sum('total_horas_practica') }}</td>
                    <td class="dir-iti-cell-num" data-sum="ud">{{ $unidadesModulo->sum('horas_ud') }}</td>
                    <td class="dir-iti-cell-num" data-total="horas">{{ $modulo->total_horas }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="16" class="dir-iti-cell-vacio">Este itinerario aún no tiene módulos registrados.</td>
                </tr>
            @endforelse

            @if ($itinerario->modulos->isNotEmpty())
                <tr class="dir-iti-row-total dir-iti-row-total-general" data-itinerario-total>
                    <td colspan="11">TOTAL ITINERARIO ({{ $itinerario->duracion_ciclos }} CICLOS)</td>
                    <td class="dir-iti-cell-num" data-total="creditos">{{ $itinerario->total_creditos }}</td>
                    <td colspan="3"></td>
                    <td class="dir-iti-cell-num" data-total="horas">{{ number_format($itinerario->total_horas) }}</td>
                </tr>
            @endif
        </tbody>
    </table>
</div>
