@php
    $badgeEstado = ['ACTIVO' => 'c-badge-green', 'BORRADOR' => 'c-badge-gold', 'ARCHIVADO' => 'c-badge-navy'][$itinerario->estado] ?? 'c-badge-navy';
    $unidadesPreview = $itinerario->modulos->first()?->bloques->flatMap->unidades->take(4) ?? collect();
@endphp
<article class="dir-iti-card">
    <div class="dir-iti-card-head">
        <div>
            <small class="dir-iti-card-programa">{{ $itinerario->programa->nombre }}</small>
            <h3>{{ $itinerario->nombre }}</h3>
            <p class="dir-iti-card-oficio">
                <i class="bi bi-file-earmark-ruled"></i>
                {{ $itinerario->resolucion_oficio ?: 'Sin oficio / resolución registrada' }}
            </p>
        </div>
        <div class="dir-iti-card-badges">
            <span class="c-badge {{ $badgeEstado }}">{{ $itinerario->estado }}</span>
            <span class="c-badge c-badge-navy">v. {{ $itinerario->version }}</span>
        </div>
    </div>

    <div class="dir-iti-card-stats">
        <div><strong>{{ $itinerario->duracion_ciclos }}</strong><span>Ciclos</span></div>
        <div><strong>{{ $itinerario->total_creditos }}</strong><span>Créditos</span></div>
        <div><strong>{{ number_format($itinerario->total_horas) }}</strong><span>Horas</span></div>
        <div><strong>{{ $itinerario->modulos->count() }}</strong><span>Módulos</span></div>
    </div>

    @if ($unidadesPreview->isNotEmpty())
        <div class="dir-iti-card-preview">
            <table>
                <thead>
                    <tr><th>Unidad didáctica</th><th>Ciclo</th><th>Cr.</th><th>H. U.D.</th></tr>
                </thead>
                <tbody>
                    @foreach ($unidadesPreview as $unidad)
                        <tr>
                            <td>
                                <span class="dir-iti-dot" style="--dot-color: {{ $unidad->bloque->color_hex ?: '#FFFFFF' }}"></span>
                                {{ $unidad->nombre }}
                            </td>
                            <td>{{ $unidad->ciclo }}</td>
                            <td>{{ $unidad->creditos }}</td>
                            <td>{{ $unidad->horas_ud }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="dir-iti-card-preview dir-iti-card-preview-empty">
            Aún no hay módulos ni unidades didácticas registradas.
        </div>
    @endif

    <div class="dir-iti-card-footer">
        <small><i class="bi bi-clock"></i> Actualizado: {{ $itinerario->updated_at?->format('d/m/Y H:i') ?? '—' }}</small>
        <div class="dir-iti-card-actions">
            <a href="{{ route('director.itinerarios.show', $itinerario) }}" class="c-btn c-btn-sm"><i class="bi bi-eye"></i> Ver</a>
            <a href="{{ route('director.itinerarios.edit', $itinerario) }}" class="c-btn c-btn-sm c-btn-primary"><i class="bi bi-pencil-square"></i> Editar</a>
            <a href="{{ route('director.itinerarios.export.excel', $itinerario) }}" class="c-btn c-btn-sm"><i class="bi bi-file-earmark-excel"></i> Excel</a>
            <a href="{{ route('director.itinerarios.export.pdf', $itinerario) }}" class="c-btn c-btn-sm"><i class="bi bi-file-earmark-pdf"></i> PDF</a>
        </div>
    </div>
</article>
