<div class="coord-horarios-ciclo-bar">
    <span class="coord-muted">CICLO:</span>
    <button type="button" class="c-btn c-btn-outline c-btn-sm" data-ciclo="">Todos</button>
    @foreach (['I', 'II', 'III', 'IV', 'V', 'VI'] as $ciclo)
        <button type="button" class="c-btn c-btn-outline c-btn-sm" data-ciclo="{{ $ciclo }}">{{ $ciclo }}</button>
    @endforeach
</div>

<div class="coord-filter-grid coord-horarios-filtros">
    <div class="coord-filter-field">
        <label>Programa</label>
        <select id="coord-horarios-filtro-programa">
            <option value="">Todos los programas</option>
            @foreach ($programas as $programa)
                <option value="{{ $programa->id_programa }}">{{ $programa->nombre }}</option>
            @endforeach
        </select>
    </div>
    <div class="coord-filter-field">
        <label>Periodo académico</label>
        <select id="coord-horarios-filtro-periodo">
            @foreach ($periodos as $periodo)
                <option value="{{ $periodo->codigo }}" data-id-periodo="{{ $periodo->id_periodo }}" {{ $periodo->estado === 'ACTIVO' ? 'selected' : '' }}>{{ $periodo->codigo }}</option>
            @endforeach
        </select>
    </div>
    <div class="coord-filter-field">
        <label>Buscar docente</label>
        <select id="coord-horarios-filtro-docente">
            <option value="">Todos los docentes</option>
            @foreach ($docentes as $docente)
                <option value="{{ $docente->id_docente }}">{{ $docente->usuario->nombres }} {{ $docente->usuario->apellidos }}</option>
            @endforeach
        </select>
    </div>
    <div class="coord-filter-field">
        <label>Generación con IA</label>
        <div class="coord-horarios-ia-nota">
            <i class="bi bi-shield-lock"></i> La clave del proveedor vive en el servidor (.env) — nunca se pide aquí.
        </div>
    </div>
</div>
