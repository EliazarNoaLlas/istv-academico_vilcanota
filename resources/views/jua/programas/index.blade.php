@extends('layouts.app', ['title' => 'Programas de Estudio', 'subtitle' => auth()->user()->rol?->nombre])

@section('content')
    <div class="jua-shell">
        <div class="jua-hero">
            <div>
                <small>GESTIÓN ACADÉMICA</small>
                <h2>Programas de Estudio</h2>
                <p>Programas de estudio oficiales del instituto, con sus cursos, docentes y estudiantes reales.</p>
            </div>
        </div>

        <div class="jua-programas-grid">
            @foreach ($programas as $programa)
                @php $itinerario = $programa->itinerarios->first(); @endphp
                <div class="c-panel jua-programa-card">
                    <div class="c-panel-body">
                        <div class="jua-programa-head">
                            <div class="jua-programa-icono"><i class="bi bi-mortarboard"></i></div>
                            <div>
                                <strong>{{ $programa->nombre }}</strong>
                                <small>{{ $programa->codigo }} · {{ $programa->familia_profesional }}</small>
                            </div>
                        </div>

                        <div class="jua-programa-stats">
                            <div>
                                <strong>{{ $programa->duracion_ciclos }}</strong>
                                <span>Ciclos</span>
                            </div>
                            <div>
                                <strong>{{ $programa->cursos_count }}</strong>
                                <span>Cursos</span>
                            </div>
                            <div>
                                <strong>{{ $programa->docentes_count }}</strong>
                                <span>Docentes</span>
                            </div>
                            <div>
                                <strong>{{ $programa->estudiantes_count }}</strong>
                                <span>Estudiantes</span>
                            </div>
                        </div>

                        <div class="jua-programa-footer">
                            <span class="c-badge {{ $programa->estado === 'ACTIVO' ? 'c-badge-green' : 'c-badge-navy' }}">{{ $programa->estado }}</span>
                            @if ($itinerario)
                                <span class="c-badge c-badge-gold"><i class="bi bi-layers"></i> Itinerario v{{ $itinerario->version }}</span>
                            @else
                                <span class="c-badge c-badge-red"><i class="bi bi-exclamation-triangle-fill"></i> Sin itinerario</span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
