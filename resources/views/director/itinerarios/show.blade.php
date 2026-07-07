@extends('layouts.app', ['title' => 'Itinerario formativo', 'subtitle' => $itinerario->programa->nombre])

@section('content')
    <div class="dir-iti-shell">
        @include('director.itinerarios.partials.doc-head', ['itinerario' => $itinerario])
        @include('director.itinerarios.partials.export-toolbar', ['itinerario' => $itinerario, 'modo' => 'show'])

        @if (session('status'))
            <div class="dir-iti-alert is-success"><i class="bi bi-check-circle"></i> {{ session('status') }}</div>
        @endif

        <div class="dir-iti-body-grid">
            <div class="dir-iti-table-wrap">
                @include('director.itinerarios.partials.editor-table', ['itinerario' => $itinerario, 'editable' => false])
            </div>

            <aside class="dir-iti-side-panel">
                <section class="dir-iti-panel-card">
                    <div class="dir-iti-panel-header"><h4>Datos generales</h4></div>
                    <div class="dir-iti-panel-body">
                        <dl class="dir-iti-datos">
                            <div><dt>Código</dt><dd>{{ $itinerario->codigo }}</dd></div>
                            <div><dt>Versión</dt><dd>{{ $itinerario->version }}</dd></div>
                            <div><dt>Duración</dt><dd>{{ $itinerario->duracion_ciclos }} ciclos</dd></div>
                            <div><dt>Créditos</dt><dd>{{ $itinerario->total_creditos }}</dd></div>
                            <div><dt>Horas</dt><dd>{{ number_format($itinerario->total_horas) }}</dd></div>
                            <div><dt>Módulos</dt><dd>{{ $itinerario->modulos->count() }}</dd></div>
                            <div><dt>Aprobación</dt><dd>{{ $itinerario->fecha_aprobacion?->format('d/m/Y') ?? '—' }}</dd></div>
                        </dl>
                        @if ($itinerario->descripcion)
                            <p class="dir-iti-descripcion">{{ $itinerario->descripcion }}</p>
                        @endif
                    </div>
                </section>

                @include('director.itinerarios.partials.validation-panel', ['validaciones' => $validaciones])

                <div class="dir-iti-leyenda">
                    <span><i class="dir-iti-dot" style="--dot-color:#FFFFFF"></i> Especialidad técnica</span>
                    <span><i class="dir-iti-dot" style="--dot-color:#DCEEFA"></i> Empleabilidad grupo 1</span>
                    <span><i class="dir-iti-dot" style="--dot-color:#DFF3E3"></i> Empleabilidad grupo 2</span>
                    <span><i class="dir-iti-dot" style="--dot-color:#BFE3F5"></i> ESRT</span>
                </div>
            </aside>
        </div>

        <footer class="dir-iti-status-bar">
            <span>Última modificación: {{ $itinerario->updated_at?->format('d/m/Y H:i') ?? '—' }}</span>
            <span>Versión: {{ $itinerario->version }}</span>
            <span>Estado: {{ $itinerario->estado }}</span>
        </footer>
    </div>

    <div class="dir-iti-toast" id="dir-iti-toast" role="status" aria-live="polite"></div>
@endsection

@push('scripts')
    @vite('resources/js/director/itinerarios.js')
@endpush
