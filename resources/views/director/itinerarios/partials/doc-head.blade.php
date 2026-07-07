{{-- Cabecera institucional del documento (estilo oficial MINEDU / ISTV). --}}
<header class="dir-iti-inst-header">
    <div class="dir-iti-inst-logo" title="Ministerio de Educación">
        <svg viewBox="0 0 48 48" width="42" height="42" aria-hidden="true">
            <rect x="2" y="2" width="44" height="44" rx="6" fill="#D91023"/>
            <path d="M24 8 L38 16 V32 L24 40 L10 32 V16 Z" fill="#fff"/>
            <path d="M24 14 L32 19 V29 L24 34 L16 29 V19 Z" fill="#D91023"/>
        </svg>
        <div class="dir-iti-inst-logo-text">
            <span class="dir-iti-inst-peru">PERÚ</span>
            <span class="dir-iti-inst-minedu">Ministerio<br>de Educación</span>
        </div>
    </div>

    <div class="dir-iti-inst-titulos">
        <h1>Instituto de Educación Superior Tecnológico Público&nbsp;"Vilcanota"</h1>
        <h2>Itinerario formativo del programa de estudios</h2>
        <h3>{{ mb_strtoupper($itinerario->programa->nombre) }}</h3>
        <p class="dir-iti-inst-oficio">{{ $itinerario->resolucion_oficio ?: 'Sin oficio / resolución registrada' }}</p>
    </div>

    <div class="dir-iti-inst-logo dir-iti-inst-logo-istv">
        <img src="{{ asset('images/logo.jpg') }}" alt="Logo ISTV">
    </div>
</header>
