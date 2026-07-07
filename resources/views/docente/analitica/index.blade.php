@extends('layouts.app', ['title' => 'Analítica', 'subtitle' => auth()->user()->rol?->nombre])

@section('content')
    <div class="c-card">
        <p style="color:var(--text-muted);font-size:13px">
            La analítica docente se conecta a esta vista en la Fase 6.
        </p>
    </div>
@endsection
