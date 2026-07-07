<div class="dir-kpis dir-iti-kpis">
    <div class="c-stat-card navy">
        <i class="bi bi-mortarboard c-stat-icon"></i>
        <div class="c-stat-label">Programas activos</div>
        <div class="c-stat-value">{{ $kpis['programas_activos'] }}</div>
    </div>
    <div class="c-stat-card teal">
        <i class="bi bi-layers c-stat-icon"></i>
        <div class="c-stat-label">Itinerarios activos</div>
        <div class="c-stat-value">{{ $kpis['itinerarios_activos'] }}</div>
    </div>
    <div class="c-stat-card gold">
        <i class="bi bi-award c-stat-icon"></i>
        <div class="c-stat-label">Créditos totales</div>
        <div class="c-stat-value">{{ number_format($kpis['creditos_totales']) }}</div>
    </div>
    <div class="c-stat-card navy">
        <i class="bi bi-clock-history c-stat-icon"></i>
        <div class="c-stat-label">Horas totales</div>
        <div class="c-stat-value">{{ number_format($kpis['horas_totales']) }}</div>
    </div>
    <div class="c-stat-card {{ $kpis['alertas_validacion'] > 0 ? 'red' : 'teal' }}">
        <i class="bi bi-exclamation-triangle c-stat-icon"></i>
        <div class="c-stat-label">Alertas de validación</div>
        <div class="c-stat-value">{{ $kpis['alertas_validacion'] }}</div>
    </div>
</div>
