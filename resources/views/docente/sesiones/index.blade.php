@extends('layouts.app', ['title' => 'Sesiones de Aprendizaje', 'subtitle' => auth()->user()->rol?->nombre])

@section('content')
    <div class="c-card">
        <p style="color:var(--text-muted);font-size:13px">
            Las sesiones de aprendizaje se conectan a esta vista en la Fase 5.
        </p>
    </div>
@endsection
