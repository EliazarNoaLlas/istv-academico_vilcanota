@extends('layouts.app', ['title' => 'Horarios', 'subtitle' => 'Programación semanal de bloques'])

@section('content')
    <div class="dir-shell coord-scheduler">
        @include('director.horarios.partials.header')
        @include('director.horarios.partials.filters')
        @include('director.horarios.partials.kpis')
        @include('director.horarios.partials.schedule-table')
    </div>

    @include('director.horarios.partials.block-modal')
    @include('director.horarios.partials.ai-panel')
@endsection

@push('scripts')
    @vite('resources/js/director/horarios.js')
@endpush
