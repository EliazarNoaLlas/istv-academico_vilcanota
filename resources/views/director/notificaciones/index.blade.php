@extends('layouts.app', ['title' => 'Notificaciones', 'subtitle' => 'Centro de notificaciones'])

@section('content')
    <div class="dir-shell">
        <div class="c-panel">
            <div class="c-panel-header">
                <i class="bi bi-bell"></i><h3>Centro de Notificaciones</h3>
                <button type="button" id="dir-notificaciones-marcar-todas" class="c-btn c-btn-outline c-btn-sm">Marcar todas como leídas</button>
            </div>
            <div class="c-panel-body" id="dir-notificaciones-lista">Cargando…</div>
        </div>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/director/notificaciones.js')
@endpush
