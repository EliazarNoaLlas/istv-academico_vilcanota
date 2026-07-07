@extends('layouts.app', ['title' => 'Itinerarios Formativos', 'subtitle' => 'Mallas curriculares oficiales por programa de estudio'])

@section('content')
    <div class="dir-shell dir-iti-shell">
        @include('director.itinerarios.partials.header')

        @if (session('status'))
            <div class="dir-iti-alert is-success"><i class="bi bi-check-circle"></i> {{ session('status') }}</div>
        @endif

        @include('director.itinerarios.partials.kpis')
        @include('director.itinerarios.partials.filters')

        @if ($itinerarios->isEmpty())
            <div class="c-panel dir-iti-empty">
                <i class="bi bi-layers"></i>
                <h3>No hay itinerarios formativos registrados</h3>
                <p>Crea el primer itinerario para representar la malla curricular oficial de un programa de estudio.</p>
                <button type="button" class="c-btn c-btn-primary" data-iti-modal-open>
                    <i class="bi bi-plus-lg"></i> Crear primer itinerario
                </button>
            </div>
        @else
            <div class="dir-iti-grid">
                @foreach ($itinerarios as $itinerario)
                    @include('director.itinerarios.partials.card', ['itinerario' => $itinerario])
                @endforeach
            </div>
        @endif
    </div>

    {{-- Modal: nuevo itinerario --}}
    <div class="dir-iti-modal-overlay {{ $errors->any() ? 'is-open' : '' }}" id="dir-iti-modal">
        <div class="dir-iti-modal" role="dialog" aria-modal="true" aria-labelledby="dir-iti-modal-title">
            <div class="dir-iti-modal-head">
                <h3 id="dir-iti-modal-title"><i class="bi bi-layers"></i> Nuevo itinerario formativo</h3>
                <button type="button" class="dir-iti-modal-close" data-iti-modal-close aria-label="Cerrar">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

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

            <form method="POST" action="{{ route('director.itinerarios.store') }}" class="dir-iti-modal-form">
                @csrf
                <div class="form-group">
                    <label>Programa de estudio *</label>
                    <select name="id_programa" class="input-inline" required>
                        <option value="">Seleccione un programa</option>
                        @foreach ($programas as $programa)
                            <option value="{{ $programa->id_programa }}" @selected(old('id_programa') == $programa->id_programa)>
                                {{ $programa->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="dir-iti-modal-row">
                    <div class="form-group">
                        <label>Código *</label>
                        <input type="text" name="codigo" class="input-inline" value="{{ old('codigo') }}" placeholder="ITI-DSI-2026" required>
                    </div>
                    <div class="form-group">
                        <label>Versión</label>
                        <input type="text" name="version" class="input-inline" value="{{ old('version', '2026') }}">
                    </div>
                </div>
                <div class="form-group">
                    <label>Nombre del itinerario *</label>
                    <input type="text" name="nombre" class="input-inline" value="{{ old('nombre') }}"
                           placeholder="Itinerario Formativo de Desarrollo de Sistemas de Información" required>
                </div>
                <div class="form-group">
                    <label>Oficio / Resolución</label>
                    <input type="text" name="resolucion_oficio" class="input-inline" value="{{ old('resolucion_oficio') }}"
                           placeholder="OFICIO 00883-2020-MINEDU/VMGP-DIGESUTPA-DISERTPA">
                </div>
                <div class="dir-iti-modal-row">
                    <div class="form-group">
                        <label>Duración (ciclos)</label>
                        <input type="number" name="duracion_ciclos" class="input-inline" min="1" max="10" value="{{ old('duracion_ciclos', 6) }}">
                    </div>
                    <div class="form-group">
                        <label>Estado</label>
                        <select name="estado" class="input-inline">
                            @foreach (['BORRADOR', 'ACTIVO'] as $estado)
                                <option value="{{ $estado }}" @selected(old('estado', 'BORRADOR') === $estado)>{{ $estado }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Descripción</label>
                    <textarea name="descripcion" class="input-inline" rows="2">{{ old('descripcion') }}</textarea>
                </div>
                <div class="dir-iti-modal-actions">
                    <button type="button" class="c-btn" data-iti-modal-close>Cancelar</button>
                    <button type="submit" class="c-btn c-btn-primary"><i class="bi bi-check-lg"></i> Crear itinerario</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/director/itinerarios.js')
@endpush
