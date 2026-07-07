@php
    /** @var \App\Models\ItinerarioFormativo $itinerario */
    $modo = $modo ?? 'show';
    $badgeEstado = ['ACTIVO' => 'is-activo', 'BORRADOR' => 'is-borrador', 'ARCHIVADO' => 'is-archivado'][$itinerario->estado] ?? 'is-borrador';
@endphp
<div class="dir-iti-toolbar">
    <a href="{{ route('director.itinerarios.index') }}" class="dir-iti-btn ghost" id="dir-iti-btn-volver">
        <i class="bi bi-chevron-left"></i> Volver
    </a>

    <div class="dir-iti-toolbar-title">
        <span>{{ $modo === 'edit' ? 'Editar Itinerario Formativo' : 'Itinerario Formativo' }}</span>
        <span class="dir-iti-badge {{ $badgeEstado }}" id="dir-iti-estado-badge">{{ $itinerario->estado }}</span>
    </div>

    <div class="dir-iti-toolbar-actions">
        @if ($modo === 'edit')
            <button type="submit" form="dir-iti-meta-form" class="dir-iti-btn primary">
                <i class="bi bi-save"></i> Guardar cambios
            </button>
            <button type="button" class="dir-iti-btn success" id="dir-iti-btn-validar">
                <i class="bi bi-check2-circle"></i> Validar totales
            </button>
            <button type="button" class="dir-iti-btn outline" id="dir-iti-btn-recalcular">
                <i class="bi bi-arrow-repeat"></i> Recalcular totales
            </button>
        @else
            <a href="{{ route('director.itinerarios.edit', $itinerario) }}" class="dir-iti-btn primary">
                <i class="bi bi-pencil-square"></i> Editar
            </a>
            <form method="POST" action="{{ route('director.itinerarios.duplicar', $itinerario) }}"
                  onsubmit="return confirm('¿Duplicar este itinerario como nueva versión en borrador?')">
                @csrf
                <button type="submit" class="dir-iti-btn outline"><i class="bi bi-copy"></i> Duplicar</button>
            </form>
            @if ($itinerario->estado !== 'ACTIVO')
                <form method="POST" action="{{ route('director.itinerarios.activar', $itinerario) }}"
                      onsubmit="return confirm('¿Activar este itinerario? Las demás versiones activas del programa se archivarán.')">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="dir-iti-btn success"><i class="bi bi-check-circle"></i> Activar</button>
                </form>
            @endif
            @if ($itinerario->estado !== 'ARCHIVADO')
                <form method="POST" action="{{ route('director.itinerarios.archivar', $itinerario) }}"
                      onsubmit="return confirm('¿Archivar este itinerario? Dejará de ser la malla vigente.')">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="dir-iti-btn danger-outline"><i class="bi bi-archive"></i> Archivar</button>
                </form>
            @endif
        @endif

        <a href="{{ route('director.itinerarios.export.pdf', $itinerario) }}" target="_blank" rel="noopener" class="dir-iti-btn outline">
            <i class="bi bi-eye"></i> Vista previa PDF
        </a>
        <a href="{{ route('director.itinerarios.export.excel', $itinerario) }}" class="dir-iti-btn outline">
            <i class="bi bi-file-earmark-excel"></i> Exportar Excel
        </a>
        <a href="{{ route('director.itinerarios.export.pdf', $itinerario) }}" class="dir-iti-btn danger-outline">
            <i class="bi bi-file-earmark-pdf"></i> Exportar PDF
        </a>
    </div>
</div>
