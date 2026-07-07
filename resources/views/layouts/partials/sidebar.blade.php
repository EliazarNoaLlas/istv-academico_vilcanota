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
            <a href="{{ route('director.horarios.index') }}" class="nav-item {{ request()->routeIs('director.horarios.*') ? 'active' : '' }}">
                <i class="bi bi-calendar-week ni"></i> Horarios
            </a>
            <a href="{{ route('director.estudiantes.index') }}" class="nav-item {{ request()->routeIs('director.estudiantes.*') ? 'active' : '' }}">
                <i class="bi bi-people ni"></i> Estudiantes
            </a>
            <a href="{{ route('director.cursos.index') }}" class="nav-item {{ request()->routeIs('director.cursos.*') ? 'active' : '' }}">
                <i class="bi bi-journal-bookmark ni"></i> Cursos
            </a>
            <a href="{{ route('director.itinerarios.index') }}" class="nav-item {{ request()->routeIs('director.itinerarios.*') ? 'active' : '' }}">
                <i class="bi bi-layers ni"></i> Itinerarios Formativos
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
            <a href="{{ route('coordinador.analitica.index') }}" class="nav-item {{ request()->routeIs('coordinador.analitica.*') ? 'active' : '' }}">
                <i class="bi bi-graph-up ni"></i> Analítica
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
        @if ($rol === 'docente')
            <a href="{{ route('docente.cursos.index') }}" class="nav-item {{ request()->routeIs('docente.cursos.*') ? 'active' : '' }}">
                <i class="bi bi-journal-bookmark ni"></i> Mis Cursos
            </a>
            <a href="{{ route('docente.horario.index') }}" class="nav-item {{ request()->routeIs('docente.horario.*') ? 'active' : '' }}">
                <i class="bi bi-calendar-week ni"></i> Mi Horario
            </a>
            <a href="{{ route('docente.analitica.index') }}" class="nav-item {{ request()->routeIs('docente.analitica.*') ? 'active' : '' }}">
                <i class="bi bi-graph-up ni"></i> Analítica
            </a>
            <a href="{{ route('docente.notas.index') }}" class="nav-item {{ request()->routeIs('docente.notas.*') ? 'active' : '' }}">
                <i class="bi bi-clipboard-data ni"></i> Registro de Notas
            </a>
            <a href="{{ route('docente.asistencia.index') }}" class="nav-item {{ request()->routeIs('docente.asistencia.*') ? 'active' : '' }}">
                <i class="bi bi-check2-square ni"></i> Registro de Asistencia
            </a>
            <a href="{{ route('docente.portafolio.index') }}" class="nav-item {{ request()->routeIs('docente.portafolio.*') ? 'active' : '' }}">
                <i class="bi bi-folder2-open ni"></i> Portafolio
            </a>
            <a href="{{ route('docente.sesiones.index') }}" class="nav-item {{ request()->routeIs('docente.sesiones.*') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-text ni"></i> Sesiones de Aprendizaje
            </a>
        @endif
        @if ($rol === 'jua')
            <div class="nav-section-title">Gestión Académica</div>
            <a href="{{ route('jua.programas.index') }}" class="nav-item {{ request()->routeIs('jua.programas.*') ? 'active' : '' }}">
                <i class="bi bi-mortarboard ni"></i> Programas de Estudio
            </a>
            <a href="{{ route('director.itinerarios.index') }}" class="nav-item {{ request()->routeIs('director.itinerarios.*') ? 'active' : '' }}">
                <i class="bi bi-layers ni"></i> Itinerarios Formativos
            </a>
            <a href="{{ route('jua.unidades.index') }}" class="nav-item {{ request()->routeIs('jua.unidades.*') ? 'active' : '' }}">
                <i class="bi bi-journal-text ni"></i> Unidades Didácticas
            </a>
            <a href="{{ route('jua.mallas.index') }}" class="nav-item {{ request()->routeIs('jua.mallas.*') ? 'active' : '' }}">
                <i class="bi bi-diagram-3 ni"></i> Mallas Curriculares
            </a>
            <a href="{{ route('jua.creditos.index') }}" class="nav-item {{ request()->routeIs('jua.creditos.*') ? 'active' : '' }}">
                <i class="bi bi-clock-history ni"></i> Créditos y Horas
            </a>
            <a href="{{ route('jua.calendario.index') }}" class="nav-item {{ request()->routeIs('jua.calendario.*') ? 'active' : '' }}">
                <i class="bi bi-calendar3 ni"></i> Calendario Académico
            </a>

            <div class="nav-section-title">Reportes y Analítica</div>
            <a href="{{ route('jua.consolidados.index') }}" class="nav-item {{ request()->routeIs('jua.consolidados.*') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-bar-graph ni"></i> Consolidados
            </a>
            <a href="{{ route('jua.reportes.index') }}" class="nav-item {{ request()->routeIs('jua.reportes.*') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-text ni"></i> Reportes
            </a>
            <a href="{{ route('jua.indicadores.index') }}" class="nav-item {{ request()->routeIs('jua.indicadores.*') ? 'active' : '' }}">
                <i class="bi bi-graph-up ni"></i> Indicadores
            </a>

            <div class="nav-section-title">Configuración</div>
            <a href="{{ route('jua.parametros.index') }}" class="nav-item {{ request()->routeIs('jua.parametros.*') ? 'active' : '' }}">
                <i class="bi bi-sliders ni"></i> Parámetros
            </a>
            <a href="{{ route('jua.usuarios.index') }}" class="nav-item {{ request()->routeIs('jua.usuarios.*') ? 'active' : '' }}">
                <i class="bi bi-people ni"></i> Usuarios
            </a>
            <a href="{{ route('jua.roles.index') }}" class="nav-item {{ request()->routeIs('jua.roles.*') ? 'active' : '' }}">
                <i class="bi bi-shield-lock ni"></i> Roles y Permisos
            </a>
        @endif
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
