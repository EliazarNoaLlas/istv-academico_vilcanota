<div class="coord-horarios-breadcrumb">Coordinador / Horarios / Planificador</div>
<div class="coord-hero">
    <div>
        <small>COORDINADOR ACADÉMICO</small>
        <h2>Horarios Académicos</h2>
        <p>Programa: <strong id="coord-horarios-programa-actual">{{ $programas[0]->nombre ?? '—' }}</strong> · Periodo: <strong>{{ $periodos->firstWhere('estado', 'ACTIVO')?->codigo ?? '—' }}</strong></p>
    </div>
    <div class="coord-horarios-actions">
        <button type="button" id="coord-horarios-nuevo" class="c-btn c-btn-primary c-btn-sm">
            <i class="bi bi-plus-lg"></i> Nuevo bloque
        </button>
        <button type="button" id="coord-horarios-guardar" class="c-btn c-btn-primary c-btn-sm">
            <i class="bi bi-save"></i> Guardar horario completo
        </button>
        <button type="button" id="coord-horarios-generar-semestre" class="c-btn c-btn-teal c-btn-sm">
            <i class="bi bi-magic"></i> Generar semestre
        </button>
        <button type="button" id="coord-horarios-generar-todos" class="c-btn c-btn-outline c-btn-sm">
            <i class="bi bi-stars"></i> Generar todos los semestres
        </button>
        <button type="button" id="coord-horarios-limpiar" class="c-btn c-btn-outline c-btn-sm">
            <i class="bi bi-arrow-clockwise"></i> Limpiar horario
        </button>
        <button type="button" id="coord-horarios-detectar" class="c-btn c-btn-outline c-btn-sm">
            <i class="bi bi-exclamation-triangle"></i> Detectar conflictos
        </button>
    </div>
</div>
<div class="coord-horarios-guardar-hint" id="coord-horarios-guardar-hint">
    <i class="bi bi-pencil-square"></i> Hay cambios sin guardar — arrastra los bloques que necesites y presiona "Guardar horario completo" para aplicarlos.
</div>
