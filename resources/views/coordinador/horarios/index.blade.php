@extends('layouts.app', ['title' => 'Horarios', 'subtitle' => 'Programación semanal de bloques'])

@section('content')
    <div class="coord-shell coord-scheduler">
        @include('coordinador.horarios.partials.header')
        @include('coordinador.horarios.partials.filters')
        @include('coordinador.horarios.partials.kpis')
        @include('coordinador.horarios.partials.schedule-table')
        @include('coordinador.horarios.partials.debug-table')
    </div>

    @include('coordinador.horarios.partials.block-modal')
    @include('coordinador.horarios.partials.ai-panel')
@endsection

@push('scripts')
    @vite('resources/js/coordinador/horarios.js')
@endpush
