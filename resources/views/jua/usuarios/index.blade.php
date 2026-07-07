@extends('layouts.app', ['title' => 'Usuarios', 'subtitle' => auth()->user()->rol?->nombre])

@section('content')
    <div class="jua-shell">
        <div class="jua-hero">
            <div>
                <small>CONFIGURACIÓN</small>
                <h2>Usuarios</h2>
                <p>Cuentas reales del sistema. La gestión completa (crear, editar, restablecer contraseña) sigue a cargo de Dirección Académica.</p>
            </div>
        </div>

        <div class="c-panel">
            <div class="c-panel-header"><i class="bi bi-people"></i><h3>{{ $usuarios->count() }} usuario(s)</h3></div>
            <div class="c-panel-body" style="padding-top:0">
                <table class="c-table">
                    <thead>
                        <tr>
                            <th>Usuario</th>
                            <th>Nombre completo</th>
                            <th>Rol</th>
                            <th>Programa</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($usuarios as $usuario)
                            <tr>
                                <td>{{ $usuario->usuario }}</td>
                                <td>{{ $usuario->nombres }} {{ $usuario->apellidos }}</td>
                                <td><span class="c-badge c-badge-navy">{{ $usuario->rol?->nombre ?? '—' }}</span></td>
                                <td>{{ $usuario->programa?->nombre ?? '—' }}</td>
                                <td>
                                    <span class="c-badge {{ $usuario->estado === 'ACTIVO' ? 'c-badge-green' : 'c-badge-red' }}">{{ $usuario->estado }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
