<header id="header">
    <button class="hbtn sidebar-toggle" data-sidebar-toggle>
        <i class="bi bi-list"></i>
    </button>
    <div>
        <div class="header-title">{{ $title ?? 'Panel Principal' }}</div>
        @isset($subtitle)
            <span class="header-subtitle">{{ $subtitle }}</span>
        @endisset
    </div>
    <div class="header-actions">
        <div class="hbtn" title="Notificaciones">
            <i class="bi bi-bell"></i>
        </div>
    </div>
</header>
