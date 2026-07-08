<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Reporte de Carga Académica Docente</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        @page { margin: 132px 22px 60px 22px; }

        body { font-family: DejaVu Sans, sans-serif; font-size: 8.5px; color: #0B1C3A; }

        header {
            position: fixed;
            top: -132px;
            left: 0;
            right: 0;
            height: 132px;
        }

        header .logo-izq { position: absolute; top: 6px; left: 0; height: 46px; }
        header .logo-der { position: absolute; top: 6px; right: 0; height: 46px; }

        header .titulos { text-align: center; padding: 0 60px; }
        header .titulos h1 { font-size: 13px; color: #0B1C3A; }
        header .titulos h2 { font-size: 19px; color: #0B1C3A; margin-top: 8px; }
        header .titulos p { font-size: 10px; color: #4A5E7A; font-style: italic; margin-top: 6px; }

        header .regla { border-bottom: 1.4px solid #0B1C3A; margin-top: 10px; }

        footer {
            position: fixed;
            bottom: -60px;
            left: 0;
            right: 0;
            height: 60px;
        }

        footer .regla { border-top: 0.8px solid #D8E3F0; margin-bottom: 6px; }
        footer table { width: 100%; font-size: 8.5px; color: #4A5E7A; }
        footer td.der { text-align: right; }

        table.datos { width: 100%; border-collapse: collapse; }
        table.datos th, table.datos td { border: 0.6px solid #D8E3F0; padding: 5px 6px; vertical-align: middle; }

        thead th {
            background: #0B1C3A;
            color: #fff;
            text-align: center;
            font-size: 8px;
            text-transform: uppercase;
        }

        td.centro { text-align: center; }
        td.nombre { font-weight: bold; color: #0B1C3A; }
        tr.par td { background: #F7F9FC; }

        .pill {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 10px;
            font-weight: bold;
            font-size: 7.6px;
        }
    </style>
</head>
<body>
    <header>
        @if (is_file(public_path('images/ministerioeducaionlogo.png')))
            <img class="logo-izq" src="{{ public_path('images/ministerioeducaionlogo.png') }}" alt="MINEDU">
        @endif
        @if (is_file(public_path('images/logo_pdf.png')))
            <img class="logo-der" src="{{ public_path('images/logo_pdf.png') }}" alt="ISTV Vilcanota">
        @endif
        <div class="titulos">
            <h1>INSTITUTO DE EDUCACIÓN SUPERIOR TECNOLÓGICO PÚBLICO "VILCANOTA"</h1>
            <h2>REPORTE DE CARGA ACADÉMICA DOCENTE</h2>
            <p>Dirección Académica &nbsp;·&nbsp; Gestión de carga docente por periodo</p>
        </div>
        <div class="regla"></div>
    </header>

    <footer>
        <div class="regla"></div>
        <table>
            <tr>
                <td>Fecha de emisión: {{ $generadoEn->format('d/m/Y H:i') }}</td>
                <td class="der">Generado por: {{ $generadoPor ?? 'Dirección Académica' }} &nbsp;·&nbsp; Total docentes: {{ $docentes->count() }}</td>
            </tr>
        </table>
    </footer>

    <table class="datos">
        <thead>
            <tr>
                <th style="width:3%">N°</th>
                <th style="width:15%">Docente</th>
                <th style="width:13%">Especialidad</th>
                <th style="width:8%">Tipo docente</th>
                <th style="width:7%">Cursos<br>asignados</th>
                <th style="width:34%">Detalle de cursos</th>
                <th style="width:8%">Horas<br>semanales</th>
                <th style="width:12%">Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($docentes as $i => $docente)
                @php
                    $estado = $estados[$docente->estado_carga] ?? $estados['SIN_CARGA'];
                    $detalle = $docente->cursos->isEmpty() ? 'Sin cursos asignados' : $docente->cursos->pluck('nombre_curso')->implode(' · ');
                @endphp
                <tr @if ($i % 2 === 1) class="par" @endif>
                    <td class="centro">{{ $i + 1 }}</td>
                    <td class="nombre">{{ trim("{$docente->usuario?->nombres} {$docente->usuario?->apellidos}") }}</td>
                    <td>{{ $docente->especialidad ?? '—' }}</td>
                    <td>{{ $tiposDocente[$docente->tipo_docente] ?? $docente->tipo_docente }}</td>
                    <td class="centro">{{ $docente->cursos_count }}</td>
                    <td>{{ $detalle }}</td>
                    <td class="centro">{{ $docente->carga_semanal }} h</td>
                    <td class="centro">
                        <span class="pill" style="background:#{{ substr($estado['bg'], 2) }};color:#{{ substr($estado['texto'], 2) }}">{{ $estado['label'] }}</span>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="centro">No hay docentes registrados.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
