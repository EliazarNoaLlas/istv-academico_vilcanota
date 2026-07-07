<div class="c-panel">
    <div class="c-panel-header"><i class="bi bi-funnel"></i><h3>Filtros</h3></div>
    <div class="c-panel-body">
        <form method="GET" action="{{ route('director.itinerarios.index') }}" id="dir-iti-filtros" class="coord-filter-grid">
            <div class="coord-filter-field">
                <label>Buscar</label>
                <input type="text" name="q" class="input-inline" value="{{ $filters['q'] ?? '' }}"
                       placeholder="Programa, itinerario u oficio...">
            </div>
            <div class="coord-filter-field">
                <label>Programa</label>
                <select name="id_programa" class="input-inline">
                    <option value="">Todos los programas</option>
                    @foreach ($programas as $programa)
                        <option value="{{ $programa->id_programa }}" @selected(($filters['id_programa'] ?? '') == $programa->id_programa)>
                            {{ $programa->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="coord-filter-field">
                <label>Estado</label>
                <select name="estado" class="input-inline">
                    <option value="">Todos los estados</option>
                    @foreach (['BORRADOR', 'ACTIVO', 'ARCHIVADO'] as $estado)
                        <option value="{{ $estado }}" @selected(($filters['estado'] ?? '') === $estado)>{{ $estado }}</option>
                    @endforeach
                </select>
            </div>
            <div class="coord-filter-field">
                <label>Versión</label>
                <select name="version" class="input-inline">
                    <option value="">Todas las versiones</option>
                    @foreach ($versiones as $version)
                        <option value="{{ $version }}" @selected(($filters['version'] ?? '') === $version)>{{ $version }}</option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>
</div>
