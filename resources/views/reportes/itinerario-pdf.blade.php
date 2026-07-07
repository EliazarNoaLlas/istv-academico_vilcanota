<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Itinerario Formativo - {{ $itinerario->programa->nombre }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 8px; color: #0B1C3A; }
        .encabezado { text-align: center; margin-bottom: 10px; }
        .encabezado h1 { font-size: 12px; }
        .encabezado h2 { font-size: 10px; font-weight: normal; margin-top: 2px; }
        .encabezado h3 { font-size: 11px; margin-top: 4px; }
        .encabezado p { font-size: 8px; margin-top: 2px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 0.6px solid #000; padding: 2.5px 3px; }
        thead th { background: #0B1C3A; color: #fff; text-align: center; font-size: 7.5px; }
        td.num { text-align: center; }
        tr.modulo td { background: #1a3160; color: #fff; font-weight: bold; font-size: 8.5px; }
        tr.modulo small { font-weight: normal; font-size: 7px; }
        tr.subtotal td { background: #EBF0F8; font-weight: bold; }
        tr.total-modulo td { background: #122347; color: #fff; font-weight: bold; }
        tr.total-general td { background: #0B1C3A; color: #fff; font-weight: bold; font-size: 9px; }
        td.bloque { font-size: 7.5px; }
        td.bloque small { display: block; color: #4A5E7A; font-size: 6.5px; }
        .pie { margin-top: 10px; font-size: 7.5px; color: #4A5E7A; width: 100%; }
        .pie td { border: none; padding: 1px 0; }
    </style>
</head>
<body>
    <div class="encabezado">
        <h1>INSTITUTO DE EDUCACIÓN SUPERIOR TECNOLÓGICO PÚBLICO "VILCANOTA"</h1>
        <h2>ITINERARIO FORMATIVO DEL PROGRAMA DE ESTUDIOS</h2>
        <h3>{{ mb_strtoupper($itinerario->programa->nombre) }}</h3>
        <p>{{ $itinerario->resolucion_oficio ?: 'SIN OFICIO / RESOLUCIÓN REGISTRADA' }} &nbsp;·&nbsp; Versión {{ $itinerario->version }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:11%">Módulos</th>
                <th style="width:20%">Unidades didácticas</th>
                @foreach ($ciclos as $ciclo)
                    <th style="width:3.5%">{{ $ciclo }}</th>
                @endforeach
                <th style="width:5%">Teóricos</th>
                <th style="width:5%">Prácticos</th>
                <th style="width:5%">Créditos</th>
                <th style="width:6%">Créditos módulo</th>
                <th style="width:5.5%">De teoría</th>
                <th style="width:5.5%">De práctica</th>
                <th style="width:5.5%">Horas U.D.</th>
                <th style="width:6%">Total horas</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($itinerario->modulos as $modulo)
                <tr class="modulo">
                    <td colspan="16">
                        MÓDULO {{ $modulo->numero_modulo }}: {{ mb_strtoupper($modulo->nombre) }}
                        @if ($modulo->competencia)
                            <br><small>{{ $modulo->competencia }}</small>
                        @endif
                    </td>
                </tr>
                @foreach ($modulo->bloques as $bloque)
                    @php $color = $bloque->color_hex && $bloque->color_hex !== '#FFFFFF' ? $bloque->color_hex : null; @endphp
                    @foreach ($bloque->unidades as $unidad)
                        <tr @if ($color) style="background: {{ $color }}" @endif>
                            @if ($loop->first)
                                <td class="bloque" rowspan="{{ $bloque->unidades->count() }}">
                                    {{ $bloque->nombre }}
                                    <small>{{ $bloque->tipo_bloque }}</small>
                                </td>
                            @endif
                            <td>{{ $unidad->nombre }}</td>
                            @foreach ($ciclos as $ciclo)
                                <td class="num">{{ $unidad->ciclo === $ciclo ? $unidad->horas_ciclo : '' }}</td>
                            @endforeach
                            <td class="num">{{ $unidad->horas_teoricas_semanales }}</td>
                            <td class="num">{{ $unidad->horas_practicas_semanales }}</td>
                            <td class="num">{{ $unidad->creditos }}</td>
                            <td class="num"></td>
                            <td class="num">{{ $unidad->total_horas_teoria }}</td>
                            <td class="num">{{ $unidad->total_horas_practica }}</td>
                            <td class="num">{{ $unidad->horas_ud }}</td>
                            <td class="num"></td>
                        </tr>
                    @endforeach
                    <tr class="subtotal">
                        <td colspan="11">TOTAL {{ mb_strtoupper($bloque->nombre) }}</td>
                        <td class="num">{{ $bloque->creditos_bloque }}</td>
                        <td colspan="3"></td>
                        <td class="num">{{ $bloque->horas_bloque }}</td>
                    </tr>
                @endforeach
                <tr class="total-modulo">
                    <td colspan="11">TOTAL MÓDULO {{ $modulo->numero_modulo }}</td>
                    <td class="num">{{ $modulo->total_creditos }}</td>
                    <td colspan="3"></td>
                    <td class="num">{{ $modulo->total_horas }}</td>
                </tr>
            @endforeach
            <tr class="total-general">
                <td colspan="11">TOTAL ITINERARIO ({{ $itinerario->duracion_ciclos }} CICLOS)</td>
                <td class="num">{{ $itinerario->total_creditos }}</td>
                <td colspan="3"></td>
                <td class="num">{{ number_format($itinerario->total_horas) }}</td>
            </tr>
        </tbody>
    </table>

    <table class="pie">
        <tr>
            <td>Generado el {{ $generadoEn->format('d/m/Y H:i') }}@if ($generadoPor) por {{ $generadoPor }}@endif · Sistema Académico ISTV Vilcanota</td>
            <td style="text-align:right">Estado: {{ $itinerario->estado }} · Código: {{ $itinerario->codigo }}</td>
        </tr>
    </table>
</body>
</html>
