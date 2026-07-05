<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $titulo }}</title>
    <style>
        body { font-family: Helvetica, Arial, sans-serif; font-size: 11px; color: #0B1C3A; }
        h1 { font-size: 16px; margin-bottom: 2px; }
        h2 { font-size: 13px; color: #444; margin-top: 0; font-weight: normal; }
        table { width: 100%; border-collapse: collapse; margin-top: 14px; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
        th { background: #0B1C3A; color: #fff; text-transform: uppercase; font-size: 9px; }
        tr:nth-child(even) td { background: #f7fbff; }
    </style>
</head>
<body>
    <h1>Instituto Superior Tecnológico Vilcanota</h1>
    <h2>{{ $titulo }} — generado {{ now()->format('d/m/Y H:i') }}</h2>

    <table>
        <thead>
            <tr>
                @foreach ($columnas as $columna)
                    <th>{{ $columna }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($filas as $fila)
                <tr>
                    @foreach ($fila as $valor)
                        <td>{{ $valor }}</td>
                    @endforeach
                </tr>
            @empty
                <tr><td colspan="{{ count($columnas) }}">Sin registros.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
