@php $conNavegacion = $conNavegacion ?? false; @endphp
<section class="dir-iti-panel-card" id="dir-iti-validaciones">
    <div class="dir-iti-panel-header">
        <h4>Validación de totales</h4>
        <span class="dir-iti-badge {{ count($validaciones) ? 'is-borrador' : 'is-activo' }}" id="dir-iti-validaciones-count">{{ count($validaciones) }}</span>
        <button type="button" class="dir-iti-icon-btn" data-iti-collapse="dir-iti-validaciones-lista" title="Colapsar">–</button>
    </div>
    <div class="dir-iti-panel-body" id="dir-iti-validaciones-lista">
        @if (empty($validaciones))
            <div class="dir-iti-valid-ok">✅ Todo cuadra: los créditos y horas registrados coinciden con la suma real de las unidades didácticas.</div>
        @else
            @foreach ($validaciones as $validacion)
                <div class="dir-iti-valid-item {{ $validacion['nivel'] === 'ERROR' ? 'is-error' : 'is-warning' }}">
                    <div class="dir-iti-valid-title">
                        {{ $validacion['nivel'] === 'ERROR' ? '⚠' : 'ℹ' }} {{ $validacion['titulo'] }}
                    </div>
                    <div class="dir-iti-valid-ambito">{{ $validacion['ambito'] }}</div>

                    @if (isset($validacion['comparacion']))
                        <div class="dir-iti-compare-row">
                            <div class="dir-iti-compare-box">
                                <div class="dir-iti-compare-label">{{ $validacion['comparacion']['etiqueta'] }} calculado</div>
                                <div class="dir-iti-compare-value">{{ $validacion['comparacion']['calculado'] }}</div>
                            </div>
                            <div class="dir-iti-compare-neq">≠</div>
                            <div class="dir-iti-compare-box">
                                <div class="dir-iti-compare-label">{{ $validacion['comparacion']['etiqueta'] }} registrado</div>
                                <div class="dir-iti-compare-value">{{ $validacion['comparacion']['registrado'] }}</div>
                            </div>
                        </div>
                    @else
                        <p class="dir-iti-valid-detalle">{{ $validacion['detalle'] }}</p>
                    @endif

                    <div class="dir-iti-valid-recomendacion"><strong>Recomendación:</strong> {{ $validacion['recomendacion'] }}</div>

                    @if ($conNavegacion && isset($validacion['id_bloque']))
                        <button type="button" class="dir-iti-btn outline dir-iti-goto-btn" data-goto-bloque="{{ $validacion['id_bloque'] }}">
                            Ir al bloque
                        </button>
                    @endif
                </div>
            @endforeach
        @endif
    </div>
</section>
