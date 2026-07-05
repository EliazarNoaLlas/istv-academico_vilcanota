@extends('layouts.app', ['title' => 'Usuarios y Roles', 'subtitle' => 'Gestión de cuentas institucionales'])

@section('content')
    <div class="dir-shell">
        <div class="dir-hero">
            <div>
                <small>DIRECCIÓN ACADÉMICA</small>
                <h2>Gestión de Usuarios y Roles</h2>
                <p>Cuentas institucionales: director, JUA, coordinador y docentes.</p>
            </div>
        </div>

        <div class="c-panel" id="dir-usuarios-solicitudes-panel" style="display:none">
            <div class="c-panel-header"><i class="bi bi-envelope-exclamation"></i><h3>Solicitudes de restablecimiento pendientes</h3></div>
            <div class="c-panel-body" id="dir-usuarios-solicitudes-lista"></div>
        </div>

        <div class="c-panel">
            <div class="c-panel-header">
                <i class="bi bi-people"></i><h3>Usuarios registrados</h3>
                <button type="button" id="dir-usuarios-nuevo" class="c-btn c-btn-primary c-btn-sm">
                    <i class="bi bi-plus-lg"></i> Nuevo usuario
                </button>
            </div>
            <div class="c-panel-body">
                <div class="dir-usuarios-toolbar">
                    <input type="text" id="dir-usuarios-search" class="input-inline" placeholder="Buscar usuario...">
                    <select id="dir-usuarios-filtro-rol">
                        <option value="">Todos los roles</option>
                        @foreach ($roles as $rol)
                            <option value="{{ $rol->id_rol }}">{{ $rol->nombre }}</option>
                        @endforeach
                    </select>
                    <select id="dir-usuarios-filtro-estado">
                        <option value="">Todos los estados</option>
                        <option value="ACTIVO">Activo</option>
                        <option value="INACTIVO">Inactivo</option>
                        <option value="BLOQUEADO">Bloqueado</option>
                    </select>
                </div>
            </div>
            @include('director.usuarios.partials.table')
        </div>
    </div>

    @include('director.usuarios.partials.form-modal')
@endsection

@push('scripts')
    @vite('resources/js/director/usuarios.js')
@endpush
