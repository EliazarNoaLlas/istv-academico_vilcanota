@extends('layouts.app', ['title' => 'Registro de Asistencia', 'subtitle' => auth()->user()->rol?->nombre])

@section('content')
    <div class="c-card">
        <p style="color:var(--text-muted);font-size:13px">
            El registro de asistencia se conecta a esta vista en la Fase 4.
        </p>
    </div>
@endsection
