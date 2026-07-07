@extends('layouts.app', ['title' => $titulo, 'subtitle' => auth()->user()->rol?->nombre])

@section('content')
    <div class="c-card doc-empty-state">
        <i class="bi {{ $icono }}"></i>
        <h3>{{ $titulo }}</h3>
        <p>{{ $descripcion }}</p>
        <p style="margin-top:14px"><span class="c-badge c-badge-gold"><i class="bi bi-hourglass-split"></i> Disponible próximamente</span></p>
    </div>
@endsection
