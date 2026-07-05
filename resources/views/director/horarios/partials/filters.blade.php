<div class="coord-filter-grid coord-horarios-filtros">
    <div class="coord-filter-field">
        <label>Programa</label>
        <select id="dir-horarios-filtro-programa">
            <option value="">Todos los programas</option>
            @foreach ($programas as $programa)
                <option value="{{ $programa->id_programa }}">{{ $programa->nombre }}</option>
            @endforeach
        </select>
    </div>
    <div class="coord-filter-field">
        <label>Periodo académico</label>
        <select id="dir-horarios-filtro-periodo">
            @foreach ($periodos as $periodo)
                <option value="{{ $periodo->codigo }}" data-id-periodo="{{ $periodo->id_periodo }}" {{ $periodo->estado === 'ACTIVO' ? 'selected' : '' }}>{{ $periodo->codigo }}</option>
            @endforeach
        </select>
    </div>
    <div class="coord-filter-field">
        <label>Docente</label>
        <select id="dir-horarios-filtro-docente">
            <option value="">Todos los docentes</option>
            @foreach ($docentes as $docente)
                <option value="{{ $docente->id_docente }}">{{ $docente->usuario->nombres }} {{ $docente->usuario->apellidos }}</option>
            @endforeach
        </select>
    </div>
    <div class="coord-filter-field">
        <label>Semestre</label>
        <select id="dir-horarios-filtro-semestre">
            <option value="">Todos</option>
            @foreach (['I', 'II', 'III', 'IV', 'V', 'VI'] as $ciclo)
                <option value="{{ $ciclo }}">Semestre {{ $ciclo }}</option>
            @endforeach
        </select>
    </div>
</div>
