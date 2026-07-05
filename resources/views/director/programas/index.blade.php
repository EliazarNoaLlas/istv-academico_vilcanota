@extends('layouts.app', ['title' => 'Programas de Estudio', 'subtitle' => 'Oferta académica institucional'])

@section('content')
    <div class="dir-shell">
        <div class="dir-hero">
            <div>
                <small>DIRECCIÓN ACADÉMICA</small>
                <h2>Programas de Estudio</h2>
                <p>Estudiantes matriculados y cursos por programa.</p>
            </div>
        </div>

        <div class="dir-kpis" id="dir-programas-grid">
            <div class="c-stat-card teal"><div class="c-stat-label">Cargando…</div></div>
        </div>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/director/programas.js')
@endpush
