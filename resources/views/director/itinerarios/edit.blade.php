@extends('layouts.app', ['title' => 'Editar itinerario', 'subtitle' => $itinerario->programa->nombre])

@section('content')
    <div class="dir-iti-shell dir-iti-editor-page"
         id="dir-iti-editor"
         data-unidad-url="{{ route('director.itinerarios.unidades.update', [$itinerario, '__UNIDAD__']) }}"
         data-validar-url="{{ route('director.itinerarios.validar-totales', $itinerario) }}"
         data-recalcular-url="{{ route('director.itinerarios.recalcular-totales', $itinerario) }}">

        @include('director.itinerarios.partials.doc-head', ['itinerario' => $itinerario])
        @include('director.itinerarios.partials.export-toolbar', ['itinerario' => $itinerario, 'modo' => 'edit'])

        @if (session('status'))
            <div class="dir-iti-alert is-success"><i class="bi bi-check-circle"></i> {{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="dir-iti-alert is-error">
                <i class="bi bi-exclamation-triangle"></i>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="dir-iti-panel-card">
            <div class="dir-iti-panel-header">
                <h4>Datos del itinerario</h4>
                <button type="button" class="dir-iti-icon-btn" data-iti-collapse="dir-iti-meta-body" title="Colapsar">–</button>
            </div>
            <div class="dir-iti-panel-body" id="dir-iti-meta-body">
                <form method="POST" action="{{ route('director.itinerarios.update', $itinerario) }}"
                      id="dir-iti-meta-form" class="dir-iti-meta-grid">
                    @csrf
                    @method('PUT')
                    <div class="dir-iti-field">
                        <label>Código</label>
                        <input type="text" name="codigo" value="{{ old('codigo', $itinerario->codigo) }}" required>
                    </div>
                    <div class="dir-iti-field">
                        <label>Versión</label>
                        <input type="text" name="version" value="{{ old('version', $itinerario->version) }}">
                    </div>
                    <div class="dir-iti-field dir-iti-meta-wide">
                        <label>Nombre</label>
                        <input type="text" name="nombre" value="{{ old('nombre', $itinerario->nombre) }}" required>
                    </div>
                    <div class="dir-iti-field dir-iti-meta-wide">
                        <label>Oficio / Resolución</label>
                        <input type="text" name="resolucion_oficio" value="{{ old('resolucion_oficio', $itinerario->resolucion_oficio) }}">
                    </div>
                    <div class="dir-iti-field">
                        <label>Estado</label>
                        <select name="estado">
                            @foreach (['BORRADOR', 'ACTIVO', 'ARCHIVADO'] as $estado)
                                <option value="{{ $estado }}" @selected(old('estado', $itinerario->estado) === $estado)>{{ $estado }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="dir-iti-field">
                        <label>Fecha de aprobación</label>
                        <input type="date" name="fecha_aprobacion" value="{{ old('fecha_aprobacion', $itinerario->fecha_aprobacion?->format('Y-m-d')) }}">
                    </div>
                    <div class="dir-iti-field dir-iti-meta-wide">
                        <label>Descripción</label>
                        <textarea name="descripcion" rows="2">{{ old('descripcion', $itinerario->descripcion) }}</textarea>
                    </div>
                </form>
            </div>
        </section>

        <div class="dir-iti-body-grid">
            <div class="dir-iti-table-wrap">
                @include('director.itinerarios.partials.editor-table', ['itinerario' => $itinerario, 'editable' => true])
            </div>

            <aside class="dir-iti-side-panel">
                @include('director.itinerarios.partials.properties-panel', ['bloques' => $bloques])
                @include('director.itinerarios.partials.validation-panel', ['validaciones' => $validaciones, 'conNavegacion' => true])

                <div class="dir-iti-leyenda">
                    <span><i class="dir-iti-dot" style="--dot-color:#FFFFFF"></i> Especialidad técnica</span>
                    <span><i class="dir-iti-dot" style="--dot-color:#DCEEFA"></i> Empleabilidad grupo 1</span>
                    <span><i class="dir-iti-dot" style="--dot-color:#DFF3E3"></i> Empleabilidad grupo 2</span>
                    <span><i class="dir-iti-dot" style="--dot-color:#BFE3F5"></i> ESRT</span>
                </div>
            </aside>
        </div>

        <footer class="dir-iti-status-bar">
            <span id="dir-iti-ultima-modificacion">Última modificación: {{ $itinerario->updated_at?->format('d/m/Y H:i') ?? '—' }}</span>
            <span>Versión: {{ $itinerario->version }}</span>
            <span class="dir-iti-status-pill saved" id="dir-iti-status-pill">Sin cambios pendientes</span>
            <span id="dir-iti-autosave-tag">Los cambios de unidades se guardan al confirmar cada fila.</span>
        </footer>
    </div>

    <div class="dir-iti-toast" id="dir-iti-toast" role="status" aria-live="polite"></div>
@endsection

@push('scripts')
    @vite('resources/js/director/itinerarios.js')
@endpush
