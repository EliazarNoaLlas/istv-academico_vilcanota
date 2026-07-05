@php
    $rol = auth()->user()->rol?->codigo;
    $dashboardRoute = match ($rol) {
        'director' => 'director.dashboard',
        'jua' => 'jua.dashboard',
        'coordinador' => 'coordinador.dashboard',
        'docente' => 'docente.dashboard',
        default => 'login',
    };
@endphp
<nav id="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-brand">
            <div class="brand-icon">
                <img src="{{ asset('images/logo.jpg') }}" alt="Logo ISTV" class="brand-logo">
            </div>
            <div class="brand-text">
                <h2>ISTV Sistema</h2>
                <span>Plataforma Académica</span>
            </div>
        </div>
    </div>

    <div class="sidebar-user">
        <div class="user-avatar">{{ strtoupper(substr(auth()->user()->nombres, 0, 2)) }}</div>
        <div class="user-info">
            <span>{{ auth()->user()->nombres }} {{ auth()->user()->apellidos }}</span>
            <small>{{ auth()->user()->rol?->nombre }}</small>
            <div class="user-status"><span class="status-dot"></span>En línea</div>
        </div>
    </div>

    <div class="sidebar-nav">
        <div class="nav-section-title">Menú</div>
        <a href="{{ route($dashboardRoute) }}" class="nav-item {{ request()->routeIs($dashboardRoute) ? 'active' : '' }}">
            <i class="bi bi-speedometer2 ni"></i> Panel Principal
        </a>
        @if ($rol === 'director')
            <a href="{{ route('director.usuarios.index') }}" class="nav-item {{ request()->routeIs('director.usuarios.*') ? 'active' : '' }}">
                <i class="bi bi-people ni"></i> Usuarios
            </a>
            <a href="{{ route('director.docentes.index') }}" class="nav-item {{ request()->routeIs('director.docentes.*') ? 'active' : '' }}">
                <i class="bi bi-person-video3 ni"></i> Docentes
            </a>
            <a href="{{ route('director.programas.index') }}" class="nav-item {{ request()->routeIs('director.programas.*') ? 'active' : '' }}">
                <i class="bi bi-mortarboard ni"></i> Programas
            </a>
            <a href="{{ route('director.horarios.index') }}" class="nav-item {{ request()->routeIs('director.horarios.*') ? 'active' : '' }}">
                <i class="bi bi-calendar-week ni"></i> Horarios
            </a>
            <a href="{{ route('director.estudiantes.index') }}" class="nav-item {{ request()->routeIs('director.estudiantes.*') ? 'active' : '' }}">
                <i class="bi bi-people ni"></i> Estudiantes
            </a>
            <a href="{{ route('director.cursos.index') }}" class="nav-item {{ request()->routeIs('director.cursos.*') ? 'active' : '' }}">
                <i class="bi bi-journal-bookmark ni"></i> Cursos
            </a>
            <a href="{{ route('director.notas.index') }}" class="nav-item {{ request()->routeIs('director.notas.*') ? 'active' : '' }}">
                <i class="bi bi-clipboard-data ni"></i> Notas
            </a>
            <a href="{{ route('director.portafolio.index') }}" class="nav-item {{ request()->routeIs('director.portafolio.*') ? 'active' : '' }}">
                <i class="bi bi-folder2-open ni"></i> Portafolio
            </a>
            <a href="{{ route('director.analytics.index') }}" class="nav-item {{ request()->routeIs('director.analytics.*') ? 'active' : '' }}">
                <i class="bi bi-graph-up ni"></i> Analytics
            </a>
            <a href="{{ route('director.alertas.index') }}" class="nav-item {{ request()->routeIs('director.alertas.*') ? 'active' : '' }}">
                <i class="bi bi-exclamation-triangle ni"></i> Alertas
            </a>
            <a href="{{ route('director.notificaciones.index') }}" class="nav-item {{ request()->routeIs('director.notificaciones.*') ? 'active' : '' }}">
                <i class="bi bi-bell ni"></i> Notificaciones
            </a>
            <a href="{{ route('director.reportes.index') }}" class="nav-item {{ request()->routeIs('director.reportes.*') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-text ni"></i> Reportes
            </a>
            <a href="{{ route('director.configuracion.index') }}" class="nav-item {{ request()->routeIs('director.configuracion.*') ? 'active' : '' }}">
                <i class="bi bi-gear ni"></i> Configuración
            </a>
        @endif
        @if ($rol === 'coordinador')
            <a href="{{ route('coordinador.cursos.index') }}" class="nav-item {{ request()->routeIs('coordinador.cursos.*') ? 'active' : '' }}">
                <i class="bi bi-journal-bookmark ni"></i> Cursos
            </a>
            <a href="{{ route('coordinador.horarios.index') }}" class="nav-item {{ request()->routeIs('coordinador.horarios.*') ? 'active' : '' }}">
                <i class="bi bi-calendar-week ni"></i> Horarios
            </a>
            <a href="{{ route('coordinador.portafolio.index') }}" class="nav-item {{ request()->routeIs('coordinador.portafolio.*') ? 'active' : '' }}">
                <i class="bi bi-folder2-open ni"></i> Portafolio
            </a>
            <a href="{{ route('coordinador.docentes.index') }}" class="nav-item {{ request()->routeIs('coordinador.docentes.*') ? 'active' : '' }}">
                <i class="bi bi-person-video3 ni"></i> Docentes
            </a>
            <a href="{{ route('coordinador.estudiantes.index') }}" class="nav-item {{ request()->routeIs('coordinador.estudiantes.*') ? 'active' : '' }}">
                <i class="bi bi-mortarboard ni"></i> Estudiantes
            </a>
            <a href="{{ route('coordinador.notas.index') }}" class="nav-item {{ request()->routeIs('coordinador.notas.*') ? 'active' : '' }}">
                <i class="bi bi-clipboard-data ni"></i> Notas
            </a>
            <a href="{{ route('coordinador.consolidado.index') }}" class="nav-item {{ request()->routeIs('coordinador.consolidado.*') ? 'active' : '' }}">
                <i class="bi bi-file-text ni"></i> Consolidado
            </a>
            <a href="{{ route('coordinador.validaciones.index') }}" class="nav-item {{ request()->routeIs('coordinador.validaciones.*') ? 'active' : '' }}">
                <i class="bi bi-check2-square ni"></i> Validaciones
            </a>
        @endif
        {{-- Los demas modulos (alertas, reportes, configuracion...) se
             conectan cuando su ruta real exista. --}}
    </div>

    <div class="sidebar-footer">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn-logout">
                <i class="bi bi-box-arrow-left"></i> Cerrar Sesión
            </button>
        </form>
    </div>
</nav>
<div class="sidebar-overlay"></div>
