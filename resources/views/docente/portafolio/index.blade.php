@extends('layouts.app', ['title' => 'Portafolio Docente', 'subtitle' => auth()->user()->rol?->nombre])

@section('content')
    <div class="c-card">
        <p style="color:var(--text-muted);font-size:13px">
            El portafolio docente (sílabos, sesiones, evidencias) se conecta a esta vista en la Fase 5.
        </p>
    </div>
@endsection
