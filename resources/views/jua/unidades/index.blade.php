@extends('layouts.app', ['title' => 'Unidades Didácticas', 'subtitle' => auth()->user()->rol?->nombre])

@section('content')
    <div class="jua-shell">
        <div class="jua-hero">
            <div>
                <small>GESTIÓN ACADÉMICA</small>
                <h2>Unidades Didácticas</h2>
                <p>Unidades didácticas reales de los itinerarios formativos vigentes, con sus créditos y horas oficiales.</p>
            </div>
        </div>

        <div class="c-panel">
            <div class="c-panel-header"><i class="bi bi-funnel"></i><h3>Filtrar por programa</h3></div>
            <div class="c-panel-body">
                <form method="GET" class="coord-portafolio-toolbar">
                    <select name="id_programa" onchange="this.form.submit()">
                        <option value="">Todos los programas</option>
                        @foreach ($programas as $programa)
                            <option value="{{ $programa->id_programa }}" {{ (int) $idProgramaActivo === $programa->id_programa ? 'selected' : '' }}>{{ $programa->nombre }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
        </div>

        <div class="c-panel">
            <div class="c-panel-header"><i class="bi bi-journal-text"></i><h3>{{ $unidades->count() }} unidad(es) didáctica(s)</h3></div>
            <div class="c-panel-body" style="padding-top:0;overflow-x:auto">
                <table class="c-table">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Unidad Didáctica</th>
                            <th>Programa</th>
                            <th>Ciclo</th>
                            <th>Créditos</th>
                            <th>H. Teoría</th>
                            <th>H. Práctica</th>
                            <th>H. UD Total</th>
                            <th>Curso vinculado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($unidades as $unidad)
                            <tr>
                                <td>{{ $unidad->codigo }}</td>
                                <td>{{ $unidad->nombre }}</td>
                                <td>{{ $unidad->bloque->modulo->itinerario->programa->nombre ?? '—' }}</td>
                                <td><span class="c-badge c-badge-navy">Ciclo {{ $unidad->ciclo }}</span></td>
                                <td>{{ $unidad->creditos }}</td>
                                <td>{{ $unidad->total_horas_teoria }}</td>
                                <td>{{ $unidad->total_horas_practica }}</td>
                                <td><strong>{{ $unidad->horas_ud }}</strong></td>
                                <td>{{ $unidad->curso?->nombre_curso ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="coord-portafolio-empty">No hay unidades didácticas para este filtro.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
